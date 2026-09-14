<?php
$data = [];

$data['php_version'] = phpversion();
$data['sapi'] = php_sapi_name();
$data['cwd'] = getcwd();
$data['script_filename'] = $_SERVER['SCRIPT_FILENAME'] ?? null;
$data['document_root'] = $_SERVER['DOCUMENT_ROOT'] ?? null;

// Check 127.0.0.1:9000
$fp = @fsockopen('127.0.0.1', 9000, $errno, $errstr, 2);
if ($fp) {
    $data['port_9000'] = 'OPEN';
    fclose($fp);
} else {
    $data['port_9000'] = "CLOSED ($errno: $errstr)";
}

// Check ports 8000, 8080, 8001
foreach ([8000, 8001, 8080, 3000, 3306] as $port) {
    $p = @fsockopen('127.0.0.1', $port, $errno, $errstr, 1);
    if ($p) {
        $data["port_$port"] = 'OPEN';
        fclose($p);
    } else {
        $data["port_$port"] = "CLOSED ($errno: $errstr)";
    }
}

// Check if socket files exist
$sock_patterns = ['/tmp/*.sock', '/var/run/*.sock', '/run/*.sock', '/var/run/php*/*.sock'];
$data['sockets'] = [];
foreach ($sock_patterns as $pat) {
    $data['sockets'][$pat] = glob($pat) ?: [];
}

// Check parent directories
$data['parent_dir'] = @scandir('/www/sites/hosting_clients') ?: [];

echo json_encode($data, JSON_PRETTY_PRINT);
