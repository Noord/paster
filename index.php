<?php
require_once 'config.php.inc';
require_once 'util.php';

function check_authorized()
{
    if (!isset($_SERVER['HTTP_APIKEY']) || !in_array($_SERVER['HTTP_APIKEY'], ALLOWED_TOKENS))
        spit(403, "FORBIDDEN");
}

function paste_list($dir)
{
    $files = array_diff(scandir($dir), array('.gitkeep', '..', '.'));
    usort($files, function ($a, $b) {
        return filemtime(PASTES_DIR . DIRECTORY_SEPARATOR . $b) - filemtime(PASTES_DIR . DIRECTORY_SEPARATOR . $a);
    });
    return $files;
}

function get_paste($uuid)
{
    $path = implode(DIRECTORY_SEPARATOR, [__DIR__, 'pastes', $uuid]);
    if (!file_exists($path))
        spit(400, 'Not found');

    return preg_replace("/\r\n|\r|\n/", PHP_EOL, file_get_contents($path));
}

function new_paste($content)
{
    $uuid = gen_uuid();
    $path = implode([PASTES_DIR, $uuid]);
    file_put_contents($path, $content);
    return $uuid;
}

function format_files()
{
    $files = paste_list(PASTES_DIR);
    $content = file_get_contents(VIEWS_DIR . 'header.html') . "<ul>";
    foreach ($files as $v) {
        $link = get_actual_link() . $v;
        $content .= "<li><a href =\"$link\"> $v</a></li>";
    }
    $content .= "</ul>" . file_get_contents(VIEWS_DIR . 'footer.html');
    return $content;
}

$uri = $_SERVER["REQUEST_URI"];
if ($uri == '/' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    spit(200, format_files(), 'text/html');
} else if ($uri == '/' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    check_authorized(ALLOWED_TOKENS);
    $content = file_get_contents('php://input');
    $link = get_actual_link() . new_paste($content);
    spit(200, $link);
} else {
    $uri = str_replace(".", "", $uri); // sanitize
    $uuid = substr($uri, 1);
    $description = get_paste($uuid);
    spit(200, $description);
}