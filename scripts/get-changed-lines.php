<?php
/**
 * Parses git diff and outputs a JSON map of repo-relative paths to the
 * line numbers that were ADDED or CHANGED on this branch vs the base branch.
 *
 * Usage: php scripts/get-changed-lines.php [workspace] [base-ref]
 *
 * Output (stdout): { "app/Http/Controllers/Foo.php": [10, 11, 15] }
 *
 * Third-party and generated trees are excluded IN THE GIT COMMAND, not after
 * parsing. vendor/ is committed in this repository, so a dependency bump puts
 * ~15,700 third-party .php files in the diff — 122 MB of output, which exhausts
 * PHP's memory limit before a single line is parsed. Excluding them at the git
 * level brings the same diff down to ~2 MB.
 *
 * The output is also streamed rather than read into one string, so memory use
 * stays flat regardless of how large the diff is.
 */

$workspace = rtrim($argv[1] ?? getcwd(), '/');
$baseRef   = $argv[2] ?? 'origin/development';

$excludes = [
    ':(exclude)vendor/*',
    ':(exclude)node_modules/*',
    ':(exclude)public/*',
    ':(exclude)storage/*',
    ':(exclude)bootstrap/cache/*',
];

$cmd = 'git -C ' . escapeshellarg($workspace)
     . ' diff --unified=0 --diff-filter=d ' . escapeshellarg($baseRef . '..HEAD')
     . ' -- ' . escapeshellarg('*.php');

foreach ($excludes as $exclude) {
    $cmd .= ' ' . escapeshellarg($exclude);
}

$cmd .= ' 2>/dev/null';

$handle = popen($cmd, 'r');
if ($handle === false) {
    fwrite(STDERR, "Failed to run git diff\n");
    echo '{}';
    exit(1);
}

$result      = [];
$currentFile = null;
$newLineNum  = 0;

while (($line = fgets($handle)) !== false) {
    $line = rtrim($line, "\r\n");

    // +++ b/app/Http/Controllers/Admin/helpdesk/HomeController.php
    if (preg_match('#^\+\+\+ b/(.+)$#', $line, $m)) {
        $currentFile = $m[1];
        $result[$currentFile] ??= [];
        continue;
    }

    if ($currentFile === null) continue;

    if (str_starts_with($line, '--- ')
        || str_starts_with($line, 'diff ')
        || str_starts_with($line, 'index ')) {
        continue;
    }

    // Hunk header: @@ -old +new[,count] @@
    if (preg_match('/^@@ -\S+ \+(\d+)/', $line, $m)) {
        $newLineNum = (int) $m[1];
        continue;
    }

    $ch = $line[0] ?? '';

    if ($ch === '+') {
        $result[$currentFile][] = $newLineNum++;
    } elseif ($ch === '-') {
        // removed — does not advance the new-file counter
    } else {
        $newLineNum++;  // context line
    }
}

pclose($handle);

// Test files are never the subject of these checks.
$result = array_filter($result, static function (string $path): bool {
    $lower = strtolower($path);
    return !str_starts_with($lower, 'tests/')
        && !preg_match('#(^|/)tests?/#', $lower);
}, ARRAY_FILTER_USE_KEY);

echo json_encode($result, JSON_UNESCAPED_SLASHES);
