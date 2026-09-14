<?php
$ch = curl_init("http://127.0.0.1:8001/");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
curl_close($ch);
echo substr($res, 0, 1000);
