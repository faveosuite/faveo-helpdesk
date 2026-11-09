<?php
/**
 * Standalone server requirements check.
 *
 * This page runs before Laravel bootstraps the installer wizard, so it must
 * not rely on the framework being available yet. On success it hands off to
 * the 'pre-license' route, which unlocks the License Agreement step.
 */
error_reporting(0);

define('STATUS_OK', 'Ok');
define('STATUS_WARNING', 'Warning');
define('STATUS_ERROR', 'Error');

class ProbeResult
{
    public $label;
    public $message;
    public $status;

    public function __construct($label, $message, $status = STATUS_OK)
    {
        $this->label = $label;
        $this->message = $message;
        $this->status = $status;
    }
}

$basePath = dirname(__DIR__);

$appName = 'Faveo HELPDESK';
$appVersion = '';
$configIni = $basePath.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'faveoconfig.ini';
if (is_file($configIni) && is_readable($configIni)) {
    $ini = parse_ini_file($configIni, false, INI_SCANNER_RAW);
    if (is_array($ini)) {
        $appName = $ini['APP_NAME'] ?? $appName;
        $appVersion = $ini['APP_VERSION'] ?? $appVersion;
    }
}

$results = [];
$errorCount = 0;

// PHP version
if (version_compare(PHP_VERSION, '8.2.0', '>=')) {
    $results[] = new ProbeResult('PHP Version', 'Your PHP version is '.PHP_VERSION, STATUS_OK);
} else {
    $errorCount++;
    $results[] = new ProbeResult('PHP Version', 'PHP version 8.2.0 or greater is required. Your PHP version: '.PHP_VERSION, STATUS_ERROR);
}

// Memory limit
function probeMemoryLimitToBytes($val)
{
    $val = trim((string) $val);
    if ($val === '' || $val === '-1') {
        return -1;
    }
    $last = strtolower($val[strlen($val) - 1]);
    $num = (int) $val;
    switch ($last) {
        case 'g':
            $num *= 1024;
            // no break
        case 'm':
            $num *= 1024;
            // no break
        case 'k':
            $num *= 1024;
    }

    return $num;
}

$memoryLimitBytes = probeMemoryLimitToBytes(ini_get('memory_limit'));
if ($memoryLimitBytes === -1 || $memoryLimitBytes >= 67108864) {
    $results[] = new ProbeResult('Memory Limit', 'Your memory limit is '.ini_get('memory_limit'), STATUS_OK);
} else {
    $errorCount++;
    $results[] = new ProbeResult('Memory Limit', 'Your memory limit is too low ('.ini_get('memory_limit').'). Minimum required is 64MB.', STATUS_ERROR);
}

// Required PHP extensions
$extensions = [
    'curl', 'ctype', 'imap', 'mbstring', 'openssl', 'tokenizer', 'zip',
    'pdo', 'mysqli', 'bcmath', 'iconv', 'xml', 'json', 'fileinfo',
];
foreach ($extensions as $extension) {
    if (extension_loaded($extension)) {
        $results[] = new ProbeResult("Extension '$extension'", 'Enabled', STATUS_OK);
    } else {
        $errorCount++;
        $results[] = new ProbeResult("Extension '$extension'", "Not enabled. Please open '".php_ini_loaded_file()."' and add 'extension = $extension'.", STATUS_ERROR);
    }
}

// Directory permissions
function probeCheckDirectory($dir, $label, array &$results, &$errorCount)
{
    if (is_dir($dir) && is_readable($dir) && is_writable($dir)) {
        $results[] = new ProbeResult($label, 'Read/Write', STATUS_OK);
    } else {
        $errorCount++;
        $results[] = new ProbeResult($label, 'Directory should be readable and writable by your web server. Recommended permissions: 755 for directories and 644 for files.', STATUS_ERROR);
    }
}
probeCheckDirectory($basePath.DIRECTORY_SEPARATOR.'storage', $basePath.DIRECTORY_SEPARATOR.'storage', $results, $errorCount);
probeCheckDirectory($basePath.DIRECTORY_SEPARATOR.'bootstrap', $basePath.DIRECTORY_SEPARATOR.'bootstrap', $results, $errorCount);

// .env file should not exist yet for a fresh install
$envPath = $basePath.DIRECTORY_SEPARATOR.'.env';
if (!is_file($envPath)) {
    $results[] = new ProbeResult('.env file', 'Not found', STATUS_OK);
} else {
    $results[] = new ProbeResult('.env file', 'Found. Faveo appears to already be installed on this system.', STATUS_OK);
}

// Disabled functions required by Laravel
$disabledFunctions = array_map('trim', explode(',', (string) ini_get('disable_functions')));
if (in_array('escapeshellarg', $disabledFunctions, true)) {
    $errorCount++;
    $results[] = new ProbeResult("Function 'escapeshellarg'", 'This function is required and is currently disabled. Please enable it in php.ini or contact your server administrator.', STATUS_ERROR);
} else {
    $results[] = new ProbeResult("Function 'escapeshellarg'", 'Enabled', STATUS_OK);
}

// Maximum execution time (recommended, not a hard blocker)
$maxExecutionTime = (int) ini_get('max_execution_time');
if ($maxExecutionTime === 0 || $maxExecutionTime >= 120) {
    $results[] = new ProbeResult('Max Execution Time', 'Maximum execution time is as per requirement ('.ini_get('max_execution_time').'s).', STATUS_OK);
} else {
    $results[] = new ProbeResult('Max Execution Time', 'Maximum execution time is too low ('.$maxExecutionTime.'s). Recommended: 120 seconds or more.', STATUS_WARNING);
}

// Rewrite engine (recommended, not a hard blocker).
// apache_get_modules() exists only when PHP runs as an Apache module. Under
// PHP-FPM/FastCGI/CGI it is undefined, so the row is skipped there instead of
// calling it (which would be a fatal error).
if (function_exists('apache_get_modules')) {
    if (in_array('mod_rewrite', apache_get_modules(), true)) {
        $results[] = new ProbeResult('Rewrite Engine', 'ON', STATUS_OK);
    } else {
        $results[] = new ProbeResult('Rewrite Engine', 'OFF (please enable mod_rewrite and set AllowOverride to All)', STATUS_ERROR);
    }
}

$canContinue = $errorCount === 0;
?>
<html lang="en-US">
<head>
    <meta name="viewport" content="width=device-width">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Faveo HELPDESK</title>
    <link href="lb-faveo/media/images/favicon.ico" rel="shortcut icon" />
    <link href="lb-faveo/css/load-styles.css" rel="stylesheet" type="text/css" />
    <link href="lb-faveo/css/css.css" rel="stylesheet" type="text/css" />
    <link href="lb-faveo/css/admin.css" rel="stylesheet" type="text/css" />
    <link href="lb-faveo/css/setup.css" rel="stylesheet" type="text/css" />
    <link href="lb-faveo/css/activation.css" rel="stylesheet" type="text/css" />
    <link href="lb-faveo/css/style.css" rel="stylesheet" type="text/css" />
    <style type="text/css">
        table.t01 { width: 100%; border: 1px solid #000; border-collapse: collapse; margin: 0 0 24px; }
        table.t01 th, table.t01 td { border: 1px solid #000; padding: 5px; text-align: left; }
        table.t01 th { background-color: #71bee3; color: #fff; }
        table.t01 tr:nth-child(even) { background-color: #eee; }
        table.t01 tr:nth-child(odd) { background-color: #fff; }
        .ok { color: green; }
        .warning { color: #F89C0D; }
        .error { color: red; }
    </style>
</head>
<body class="setup wp-core-ui">
    <center><a href="http://www.faveohelpdesk.com">
        <img src="lb-faveo/media/installer/faveo.png" alt="faveo" width="250px"></a></center>
    <ol class="setup-steps">
        <li class="active">Server Requirements</li>
        <li>License Agreement</li>
        <li>Database Setup</li>
        <li>Locale Information</li>
        <li>Ready</li>
    </ol>
    <div class="setup-content">
        <center><h1>Server Requirements</h1></center>

        <table class="t01">
            <tr>
                <th style="width: 40%;">Directory</th>
                <th>Permissions</th>
            </tr>
<?php
foreach ($results as $result) {
    if (in_array($result->label, [$basePath.DIRECTORY_SEPARATOR.'storage', $basePath.DIRECTORY_SEPARATOR.'bootstrap'], true)) {
        echo '<tr><td>'.htmlspecialchars($result->label).'</td><td class="'.strtolower($result->status).'">'.$result->message.'</td></tr>';
    }
}
?>
        </table>

        <table class="t01">
            <tr>
                <th style="width: 40%;">Requisites</th>
                <th>Status</th>
            </tr>
<?php
foreach ($results as $result) {
    if (in_array($result->label, [$basePath.DIRECTORY_SEPARATOR.'storage', $basePath.DIRECTORY_SEPARATOR.'bootstrap'], true) || strpos($result->label, "Extension '") === 0) {
        continue;
    }
    echo '<tr><td>'.htmlspecialchars($result->label).'</td><td class="'.strtolower($result->status).'"><span>'.$result->status.'</span> &mdash; '.$result->message.'</td></tr>';
}
?>
        </table>

        <table class="t01">
            <tr>
                <th style="width: 40%;">PHP Extensions</th>
                <th>Status</th>
            </tr>
<?php
foreach ($results as $result) {
    if (strpos($result->label, "Extension '") === 0) {
        echo '<tr><td>'.htmlspecialchars($result->label).'</td><td class="'.strtolower($result->status).'">'.$result->message.'</td></tr>';
    }
}
?>
        </table>

<?php if (!$canContinue) { ?>
        <div class="woocommerce-message woocommerce-tracker">
            <p id="fail">This system does not meet Faveo system requirements</p>
        </div>
<?php } ?>

        <form action="pre-license" method="post" class="border-line">
            <input type="hidden" name="count" value="<?php echo (int) $errorCount; ?>" />
            <p class="setup-actions step">
                <span style="float: left; font-size: .8em;">
                    <b>App Name:</b> <?php echo htmlspecialchars($appName); ?><br/>
                    <b>App Version:</b> <?php echo htmlspecialchars($appVersion); ?>
                </span>

<?php if ($canContinue) { ?>
                <input type="submit" id="submitme" class="button-primary button button-large button-next" value="Continue">
<?php } else { ?>
                <button type="button" disabled="disabled" class="button-primary button button-large button-next" style="float: right;">Continue</button>
<?php } ?>
            </p>
        </form>
<?php if (!$canContinue) { ?>
        <p style='color:red;'>NOTE: Please delete the file 'probe.php' once you have fixed all the issues.</p>
<?php } ?>
    </div>
    <p style="text-align: center;"> Copyright &copy; 2015 - <?php echo date('Y'); ?> &middot; Ladybird Web Solution Pvt Ltd. All Rights Reserved. Powered by <a target="_blank" href="http://www.faveohelpdesk.com">Faveo </a></p>
</body>
</html>
