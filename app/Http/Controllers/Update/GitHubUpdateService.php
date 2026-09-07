<?php

namespace App\Http\Controllers\Update;

use Exception;
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
     */
    public function getLatestRelease(): ?array
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
        $release = $this->getLatestRelease();

        if (!$release || !$release['zipball_url']) {
            throw new Exception('Unable to retrieve the download URL from GitHub.');
        }

        $this->ensureTempDirectory();

        $zipPath = $this->zipPath();

        $downloadUrl = "https://api.github.com/repos/{$this->owner}/{$this->repo}/zipball/refs/tags/{$release['tag']}";

        $response = Http::withHeaders($this->headers())
            ->withOptions(['sink' => $zipPath])
            ->timeout(300)
            ->get($downloadUrl);

        if ($response->failed() || File::size($zipPath) < 1000) {
            File::delete($zipPath);

            throw new Exception('Failed to download release archive from GitHub (HTTP '.$response->status().').');
        }

        Log::info('GitHub Update: release downloaded', [
            'version' => $release['version'],
            'size'    => File::size($zipPath),
        ]);

        return $zipPath;
    }

    /**
     * Check whether a previously downloaded archive exists.
     */
    public function hasDownload(): bool
    {
        return File::exists($this->zipPath());
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
        $request = Http::withHeaders($this->headers())->timeout(30);

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
