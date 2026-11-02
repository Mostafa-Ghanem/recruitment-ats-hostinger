<?php
/**
 * OpenCATS Hostinger CLI-only environment preflight.
 * Usage: php tools/hostinger-preflight.php
 * Optional database check: ATS_DB_HOST, ATS_DB_NAME, ATS_DB_USER, ATS_DB_PASS.
 * Never print or commit credentials. This does NOT install or modify anything.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$failed = false;
$check = static function (string $name, bool $ok, bool $required = true) use (&$failed): void {
    echo sprintf("[%s] %s%s\n", $ok ? 'OK' : ($required ? 'FAIL' : 'WARN'), $name, $required ? '' : ' (recommended)');
    if (!$ok && $required) {
        $failed = true;
    }
};

echo "OpenCATS Hostinger preflight (read-only)\n";
echo "PHP version: " . PHP_VERSION . "\n";
$check('PHP >= 8.4.1 (PHP 8 series)', PHP_MAJOR_VERSION === 8 && version_compare(PHP_VERSION, '8.4.1', '>='));
$check('mysqli extension', extension_loaded('mysqli'));
$check('mbstring extension', extension_loaded('mbstring'));
foreach (['gd', 'zip', 'soap'] as $extension) {
    $check($extension . ' extension', extension_loaded($extension), false);
}
$check('session extension', extension_loaded('session'));

$settings = [
    'host' => getenv('ATS_DB_HOST') ?: '',
    'name' => getenv('ATS_DB_NAME') ?: '',
    'user' => getenv('ATS_DB_USER') ?: '',
    'pass' => getenv('ATS_DB_PASS') ?: '',
];

if ($settings['host'] !== '' && $settings['name'] !== '' && $settings['user'] !== '') {
    if (!extension_loaded('mysqli')) {
        echo "[SKIP] MariaDB check (mysqli unavailable)\n";
    } else {
        try {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            $db = new mysqli($settings['host'], $settings['user'], $settings['pass'], $settings['name']);
            $db->set_charset('utf8mb4');
            $version = $db->server_info;
            echo "[OK] Connected to database (server: " . $version . ")\n";
            echo "[INFO] OpenCATS CI baseline uses MariaDB 10.7; verify actual compatibility independently.\n";
            $db->close();
        } catch (mysqli_sql_exception $error) {
            echo "[FAIL] Database connection or utf8mb4 check failed. Inspect host/user/db and grants privately.\n";
            $failed = true;
        }
    }
} else {
    echo "[SKIP] MariaDB check. Set ATS_DB_HOST, ATS_DB_NAME and ATS_DB_USER (ATS_DB_PASS optional).\n";
}

echo $failed ? "Preflight: NOT READY\n" : "Preflight: BASIC CHECKS PASSED (not a security or deployment approval)\n";
exit($failed ? 1 : 0);
