<?php

require_once 'lib/AltoViewer.php';

$config = parse_ini_file('./config/altoview.ini');
if ($config === false) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Failed to read the configuration file.';
    exit;
}

$requested = isset($_GET['file']) ? (string) $_GET['file'] : '';

if ($requested === '') {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Invalid "file" parameter.';
    exit;
}

$availableImages = AltoViewer::listAvailableFiles($config['altoDir'], $config['imageDir']);
if (!in_array($requested, $availableImages, true)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Image not found.';
    exit;
}

$imagePath = rtrim($config['imageDir'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $requested . '.tif.png';

if (!is_readable($imagePath)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Image not found.';
    exit;
}

header('Content-Type: image/png');
readfile($imagePath);

