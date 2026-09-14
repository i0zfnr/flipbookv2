<?php
$data = [];

function get_http($port, $path = '/') {
    $ch = curl_init("http://127.0.0.1:$port$path");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_HEADER, true);
    $res = curl_exec($ch);
    $info = curl_getinfo($ch);
    $err = curl_error($ch);
    curl_close($ch);
    return [
        'http_code' => $info['http_code'],
        'error' => $err,
        'response' => substr($res, 0, 500)
    ];
}

$data['http_8000'] = get_http(8000);
$data['http_8001'] = get_http(8001);
$data['http_9000'] = get_http(9000);

echo json_encode($data, JSON_PRETTY_PRINT);
