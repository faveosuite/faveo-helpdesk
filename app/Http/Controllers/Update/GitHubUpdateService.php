<?php

namespace App\Http\Controllers\Update;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GitHubUpdateService
{
    protected string $owner;

    protected string $repo;

    protected ?string $token;

    protected string $tempPath;

    public function __construct()
    {
        $this->owner = config('update.github.owner');
        $this->repo = config('update.github.repo');
        $this->token = config('update.github.token');
        $this->tempPath = base_path(config('update.temp_directory'));
    }

    /**
     * Fetch the latest release metadata from GitHub Releases API.
     *
     * Successful lookups are cached for 10 minutes: the update pages and the banner
     * each ask several times, and unauthenticated GitHub API calls are limited to
     * 60 per hour per IP. Pass $fresh to bypass the cache (the actual download).
     */
    public function getLatestRelease(bool $fresh = false): ?array
    {
        $key = "faveo-update-latest-release:{$this->owner}/{$this->repo}";

        if (!$fresh && ($cached = Cache::get($key))) {
            return $cached;
        }

        $release = $this->fetchLatestRelease();

        if ($release) {
            Cache::put($key, $release, 600);
        }

        return $release;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function fetchLatestRelease(): ?array
    {
        $response = $this->github("/repos/{$this->owner}/{$this->repo}/releases/latest");

        if ($response->failed()) {
            Log::warning('GitHub Update: failed to fetch latest release', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return null;
        }

        $data = $response->json();

        return [
            'version'      => ltrim($data['tag_name'] ?? '', 'v'),
            'tag'          => $data['tag_name'] ?? null,
            'name'         => $data['name'] ?? null,
            'body'         => $data['body'] ?? null,
            'zipball_url'  => $data['zipball_url'] ?? null,
            'published_at' => $data['published_at'] ?? null,
            'html_url'     => $data['html_url'] ?? null,
        ];
    }

    /**
     * Get only the latest version string.
     */
    public function getLatestVersion(): ?string
    {
        return $this->getLatestRelease()['version'] ?? null;
    }

    /**
     * Fetch recent releases from GitHub (for the version timeline).
     */
    public function getRecentReleases(int $limit = 10): array
    {
        $response = $this->github("/repos/{$this->owner}/{$this->repo}/releases?per_page={$limit}");

        if ($response->failed()) {
            return [];
        }

        return collect($response->json())->map(fn ($release) => [
            'version'      => ltrim($release['tag_name'] ?? '', 'v'),
            'tag'          => $release['tag_name'] ?? null,
            'name'         => $release['name'] ?? null,
            'body'         => $release['body'] ?? null,
            'published_at' => $release['published_at'] ?? null,
            'html_url'     => $release['html_url'] ?? null,
            'prerelease'   => $release['prerelease'] ?? false,
        ])->all();
    }

    /**
     * Download the release ZIP archive to the temp directory.
     *
     * @throws Exception
     *
     * @return string Path to the downloaded zip file.
     */
    public function downloadRelease(): string
    {
        $release = $this->getLatestRelease(true);

        if (!$release || !$release['zipball_url'] || !$release['tag']) {
            throw new Exception('Unable to retrieve the download URL from GitHub.');
        }

        // Always start from an empty directory: never reuse an archive (or a
        // half-downloaded one) left behind by an earlier, failed attempt.
        $this->clearTempDirectory();
        $this->ensureTempDirectory();

        $zipPath = $this->zipPath();

        $downloadUrl = "https://api.github.com/repos/{$this->owner}/{$this->repo}/zipball/refs/tags/".rawurlencode($release['tag']);

        $response = Http::withHeaders($this->headers())
            ->withOptions(['sink' => $zipPath])
            ->timeout(300)
            ->get($downloadUrl);

        if ($response->failed() || !File::exists($zipPath) || File::size($zipPath) < 1000) {
            $this->clearTempDirectory();

            throw new Exception('Failed to download release archive from GitHub (HTTP '.$response->status().').');
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CHECKCONS) !== true) {
            $this->clearTempDirectory();

            throw new Exception('The downloaded release archive is incomplete or corrupt. Please try again.');
        }
        $zip->close();

        // Remember which release this archive is, so install can refuse a mismatch.
        File::put($this->tempPath.'/release.json', json_encode(['tag' => $release['tag'], 'version' => $release['version']], JSON_THROW_ON_ERROR));

        Log::info('GitHub Update: release downloaded', [
            'version' => $release['version'],
            'size'    => File::size($zipPath),
        ]);

        return $zipPath;
    }

    /**
     * Release recorded next to the downloaded archive, or null.
     *
     * @return array<string, mixed>|null
     */
    public function downloadedRelease(): ?array
    {
        $file = $this->tempPath.'/release.json';

        return File::exists($file) ? json_decode(File::get($file), true) : null;
    }

    /**
     * Remove the temp directory (archive, staging copy, old vendor) entirely.
     */
    public function clearTempDirectory(): void
    {
        if (File::isDirectory($this->tempPath)) {
            File::deleteDirectory($this->tempPath);
        }
    }

    /**
     * Get the path to the downloaded zip file.
     */
    public function zipPath(): string
    {
        return $this->tempPath.'/latest-release.zip';
    }

    /**
     * Get the temp directory path.
     */
    public function getTempPath(): string
    {
        return $this->tempPath;
    }

    // ------------------------------------------------------------------
    //  Internal helpers
    // ------------------------------------------------------------------

    protected function github(string $endpoint)
    {
        // Short timeouts: this also runs from the update banner check, and a server
        // with no outbound internet must not hang a page waiting for GitHub.
        $request = Http::withHeaders($this->headers())->connectTimeout(5)->timeout(15);

        if ($this->token) {
            $request = $request->withToken($this->token);
        }

        return $request->get("https://api.github.com{$endpoint}");
    }

    protected function headers(): array
    {
        $headers = [
            'Accept'     => 'application/vnd.github+json',
            'User-Agent' => config('app.name', 'Faveo-Helpdesk'),
        ];

        if ($this->token) {
            $headers['Authorization'] = 'Bearer '.$this->token;
        }

        return $headers;
    }

    protected function ensureTempDirectory(): void
    {
        if (!File::isDirectory($this->tempPath)) {
            File::makeDirectory($this->tempPath, 0755, true);
        }
    }
}
