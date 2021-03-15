<?php
const PASTES_DIR = __DIR__ . DIRECTORY_SEPARATOR . 'pastes' . DIRECTORY_SEPARATOR;
const HEADER = <<<header
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pastes</title>
    <style> h1{ text-align: center;} </style>
</head>
<body>
<h1> Pastes </h1>
<hr>
header;

const FOOTER = <<<footer
</body>
</html>
footer;

if (!file_exists('cred.local.php.inc'))
    die('No config file given.');

require_once 'cred.local.php.inc';

function rid($length = 5) { return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyz', ceil($length / strlen($x)))), 1, $length); } // borrowed from https://stackoverflow.com/a/13212994

function get_actual_link() { return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/"; }

function is($method_type) { return $_SERVER['REQUEST_METHOD'] === $method_type; }

function spit($code, $payload, $content_type = "Content-Type: text/plain")
{
    http_response_code($code);
    header($content_type);
    echo $payload;
    die();
}

function get_paste($uri)
{
    $uri = str_replace(".", "", $uri); // sanitize
    $uuid = substr($uri, 1);
    $path = PASTES_DIR . $uuid;
    if (!file_exists($path))
        spit(400, 'Not found');

    return file_get_contents($path);
}

function new_paste()
{
    $content = file_get_contents('php://input');
    if (empty(trim($content)))
        spit(400, "Empty payload");

    $content = preg_replace("/\r\n|\r|\n/", PHP_EOL, $content); // windows to unix
    $uuid = rid();
    $path = implode([PASTES_DIR, $uuid]);
    file_put_contents($path, $content);
    return get_actual_link() . $uuid;
}

function paste_list($dir)
{
    $files = array_diff(scandir($dir), array('.gitkeep', '..', '.'));
    usort($files, function ($a, $b) {
        return filemtime(PASTES_DIR . $b) - filemtime(PASTES_DIR . $a);
    });
    return $files;
}

function html()
{
    $files = paste_list(PASTES_DIR);
    $content = HEADER . "<ul>";
    foreach ($files as $v)
        $content .= "<li><a href =". get_actual_link() . $v ."> $v</a></li>";
    $content .= "</ul>". FOOTER;
    return $content;
}

$uri = $_SERVER["REQUEST_URI"];
if ($uri == '/') {
    if (is('GET'))
        spit(200, html(), 'Content-Type: text/html; charset=utf-8');
    if (is('POST')) {
        if (!isset($_SERVER['HTTP_APIKEY']) || !in_array($_SERVER['HTTP_APIKEY'], ALLOWED_TOKENS)) // check authorized
            spit(403, "FORBIDDEN");
        spit(200, new_paste());
    }
} else {
    spit(200, get_paste($uri));
}