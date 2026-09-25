<?php

namespace App\Http\Controllers\Admin\helpdesk;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;

/**
 * Admin > URL settings: WWW / non-WWW and HTTP / HTTPS redirects, written to public/.htaccess.
 *
 * The rules are kept in one managed block placed right after "RewriteEngine On", i.e. BEFORE
 * Laravel's front-controller rule. The old implementation appended them after a "#custom" marker
 * at the end of the file, after the front controller, so every page URL was redirected to
 * /index.php, the www rules hardcoded http://, and the non-www rule dropped the install folder.
 * That old block is cleaned up the next time an admin saves this page; until then nothing changes.
 */
class UrlSettingController extends Controller
{
    const BLOCK_BEGIN = '# BEGIN Faveo URL settings (managed by Admin > URL settings, do not edit)';

    const BLOCK_END = '# END Faveo URL settings';

    /** Exact lines the old implementation wrote after "#custom", removed when the settings are saved. */
    const LEGACY_LINES = [
        'RewriteCond %{HTTP_HOST} !^www\.',
        'RewriteRule ^(.*)$ http://www.%{HTTP_HOST}%{REQUEST_URI} [L,R=301]',
        'RewriteCond %{HTTP_HOST} ^www\.(.*)$ [NC]',
        'RewriteRule ^(.*)$ http://%1/$1 [R=301,L]',
        'RewriteCond %{HTTPS} off',
        'RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]',
    ];

    public function __construct()
    {
        $this->middleware(['auth', 'roles']);
    }

    public function settings(Request $request)
    {
        $url = $request->url();
        $saved = $this->savedSettings();

        // Show what is actually saved in .htaccess; fall back to the current URL when nothing is saved yet.
        $www = $this->toggle($saved['www'] ?? $this->checkWwwInUrl($url), 'www', 'nonwww');
        $https = $this->toggle($saved['https'] ?? $this->checkHttpsInUrl($url), 'https', 'http');

        try {
            return view('themes.default1.admin.helpdesk.settings.url.settings', compact('www', 'https'));
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function postSettings(Request $request)
    {
        $request->validate([
            'www' => 'required|in:yes,no',
            'ssl' => 'required|in:yes,no',
        ]);

        $wantWww = $request->input('www') === 'yes';
        $wantHttps = $request->input('ssl') === 'yes';

        // Enabling HTTPS without a working certificate would redirect every visitor, including this
        // admin, to a site that cannot load, and the setting could then only be undone by editing the
        // file by hand. Opening this page over https:// proves the certificate works in a browser.
        if ($wantHttps && !$this->requestIsHttps($request)) {
            return redirect()->back()->with('fails', 'To enable HTTPS, first make sure an SSL certificate is installed, then open this page using https:// and save again. HTTPS was not enabled.');
        }

        // Same idea for WWW: only switch to www. when the www. address is known to reach this site.
        if ($wantWww && !$this->checkWwwInUrl($request->getHost())) {
            return redirect()->back()->with('fails', 'To use the WWW address, first open this page using the www. address and save again. The setting was not changed.');
        }

        try {
            $this->writeHtaccess($this->buildRules($wantWww, $wantHttps));

            return redirect()->back()->with('success', trans('lang.updated'));
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     * The rewrite rules for the chosen settings. Each redirect is a single hop that keeps the full
     * path and query string (%{REQUEST_URI}, NE = no re-encoding), so it also works in a sub-folder.
     */
    public function buildRules(bool $www, bool $https): string
    {
        $rules = '';
        $scheme = 'https';
        if (!$https) {
            // Keep the visitor's own scheme. Behind an SSL proxy Apache itself only sees http, so the
            // proxy's X-Forwarded-Proto wins; otherwise an https visitor would be sent to http://.
            $rules = "    RewriteRule ^ - [E=FAVEO_SCHEME:%{REQUEST_SCHEME}]\n"
                ."    RewriteCond %{HTTP:X-Forwarded-Proto} =https [NC]\n"
                ."    RewriteRule ^ - [E=FAVEO_SCHEME:https]\n";
            $scheme = '%{ENV:FAVEO_SCHEME}';
        }

        if ($www) {
            $rules .= "    RewriteCond %{HTTP_HOST} !^www\\. [NC]\n"
                ."    RewriteRule ^ {$scheme}://www.%{HTTP_HOST}%{REQUEST_URI} [L,R=301,NE]\n";
        } else {
            $rules .= "    RewriteCond %{HTTP_HOST} ^www\\.(.+)$ [NC]\n"
                ."    RewriteRule ^ {$scheme}://%1%{REQUEST_URI} [L,R=301,NE]\n";
        }

        if ($https) {
            // X-Forwarded-Proto: behind a proxy / load balancer that handles SSL itself, Apache only
            // ever sees plain HTTP; without this condition every request would redirect forever.
            $rules .= "    RewriteCond %{HTTPS} !=on\n"
                ."    RewriteCond %{HTTP:X-Forwarded-Proto} !=https\n"
                ."    RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301,NE]\n";
        }

        return $rules;
    }

    /**
     * Returns the .htaccess content with the managed block (re)placed right after the first
     * "RewriteEngine On". Anything else in the file is left exactly as it was.
     *
     * @throws Exception when the file has no "RewriteEngine On" line to anchor the block to
     */
    public function buildHtaccess(string $content, string $rules): string
    {
        $content = $this->removeManagedBlock($this->removeLegacyRules($content));

        $block = '    '.self::BLOCK_BEGIN."\n".$rules.'    '.self::BLOCK_END."\n";

        $count = 0;
        $content = preg_replace_callback('/^[ \t]*RewriteEngine[ \t]+On[ \t]*\r?\n/mi', function ($match) use ($block) {
            return $match[0]."\n".$block;
        }, $content, 1, $count);

        if ($content === null || $count !== 1) {
            throw new Exception('Could not find "RewriteEngine On" in public/.htaccess, so the URL settings were not saved. Restore the original .htaccess file and try again.');
        }

        return $content;
    }

    public function writeHtaccess(string $rules): void
    {
        $file = $this->htaccessPath();

        if (!is_file($file) || !is_readable($file) || !is_writable($file)) {
            throw new Exception("{$file} is missing or not writable by the web server, so the URL settings were not saved.");
        }

        $current = file_get_contents($file);
        if ($current === false) {
            throw new Exception("Could not read {$file}.");
        }

        $content = $this->buildHtaccess($current, $rules);

        if (file_put_contents($file, $content, LOCK_EX) === false) {
            throw new Exception("Could not write {$file}.");
        }
    }

    public function htaccessPath(): string
    {
        return public_path('.htaccess');
    }

    /**
     * What the managed block in .htaccess currently enforces: ['www' => bool, 'https' => bool],
     * or [] when there is no managed block yet (fresh install, or not saved since upgrading).
     *
     * @return array<string, bool>
     */
    public function savedSettings(?string $content = null): array
    {
        if ($content === null) {
            $file = $this->htaccessPath();
            $content = is_readable($file) ? (string) file_get_contents($file) : '';
        }

        $begin = strpos($content, self::BLOCK_BEGIN);
        $end = $begin === false ? false : strpos($content, self::BLOCK_END, $begin);
        if ($begin === false || $end === false) {
            return [];
        }

        $block = substr($content, $begin, $end - $begin);

        return [
            'www'   => strpos($block, '!^www') !== false,
            'https' => strpos($block, '%{HTTPS} !=on') !== false,
        ];
    }

    private function removeManagedBlock(string $content): string
    {
        // The blank line that buildHtaccess() puts before the block is removed with it.
        $pattern = '/(?:^[ \t]*\r?\n)?^[ \t]*'.preg_quote(self::BLOCK_BEGIN, '/').'.*?'.preg_quote(self::BLOCK_END, '/')."[^\n]*\n/ms";

        return preg_replace($pattern, '', $content) ?? $content;
    }

    /**
     * Removes the "#custom" marker and the exact rule lines the old implementation wrote after it.
     * Any other line an admin may have added there is kept.
     */
    private function removeLegacyRules(string $content): string
    {
        $pos = strpos($content, '#custom');
        if ($pos === false) {
            return $content;
        }

        $head = substr($content, 0, $pos);
        $tail = substr($content, $pos + strlen('#custom'));

        $kept = array_filter(preg_split('/\r?\n/', $tail) ?: [], function ($line) {
            return trim($line) !== '' && !in_array(trim($line), self::LEGACY_LINES, true);
        });

        return rtrim($head)."\n".($kept ? implode("\n", $kept)."\n" : '');
    }

    private function requestIsHttps(Request $request): bool
    {
        return $request->isSecure() || strtolower((string) $request->header('X-Forwarded-Proto')) === 'https';
    }

    /**
     * @return array<string, bool>
     */
    private function toggle(bool $on, string $onKey, string $offKey): array
    {
        return [$onKey => $on, $offKey => !$on];
    }

    public function checkWwwInUrl($url)
    {
        $host = parse_url($url, PHP_URL_HOST) ?: $url;

        return stripos((string) $host, 'www.') === 0;
    }

    public function checkHttpsInUrl($url)
    {
        return stripos((string) $url, 'https://') === 0;
    }

    public function checkWWW($url)
    {
        return $this->toggle($this->checkWwwInUrl($url), 'www', 'nonwww');
    }

    public function checkHTTP($url)
    {
        return $this->toggle($this->checkHttpsInUrl($url), 'https', 'http');
    }
}
