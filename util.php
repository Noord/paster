<?php

function get_actual_link()
{
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/";
}

function gen_uuid($length = 4)
{
    return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyz', ceil($length / strlen($x)))), 1, $length);
}

function spit($code, $payload, $content_type = "Content-Type: text/plain")
{
    http_response_code($code);
    header($content_type);
    echo $payload;
    die();
}