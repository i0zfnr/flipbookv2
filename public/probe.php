<?php
$data = [];
$data['shell_exec_exists'] = function_exists('shell_exec');
$data['disabled_functions'] = ini_get('disable_functions');

if (function_exists('shell_exec')) {
    $data['ps'] = shell_exec('ps aux');
    $data['netstat'] = shell_exec('netstat -tlpn 2>&1 || ss -tlpn 2>&1');
    $data['hostname'] = shell_exec('hostname');
}

echo json_encode($data, JSON_PRETTY_PRINT);
