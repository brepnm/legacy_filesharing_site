<?php
require 'auth.php';
require_login();

$download_dir = __DIR__ . '/ytdlp_downloads';

if (!isset($_GET['file'])) {
    http_response_code(400);
    exit;
}

$file = basename($_GET['file']);
$path = $download_dir . '/' . $file;

if (!is_file($path)) {
    http_response_code(404);
    exit;
}

$size  = filesize($path);
$start = 0;
$end   = $size - 1;

header('Content-Type: video/mp4');
header('Accept-Ranges: bytes');

if (isset($_SERVER['HTTP_RANGE'])) {
    if (preg_match('/bytes=(\d+)-(\d+)?/', $_SERVER['HTTP_RANGE'], $m)) {
        $start = (int)$m[1];
        if (isset($m[2])) {
            $end = (int)$m[2];
        }
    }
    http_response_code(206);
    header("Content-Range: bytes $start-$end/$size");
}

$length = $end - $start + 1;
header("Content-Length: $length");

$fp = fopen($path, 'rb');
fseek($fp, $start);

$buffer = 8192;
while (!feof($fp) && ftell($fp) <= $end) {
    echo fread($fp, $buffer);
    flush();
}

fclose($fp);
