<?php

error_reporting(-1);
ini_set('display_errors', 'on');

define('PUN_ROOT', dirname(__FILE__).'/');

require PUN_ROOT . 'include/pages.php';

function fileNotFound()
{
    header('HTTP/1.0 404 Not Found');
    die('File not found');
}

$page_uri = Pages::uri($query);
if (substr($page_uri, -11) == '/index.html') {
    $page_uri = substr($page_uri, 0, -11);
    header("Location: {$page_uri}/{$query}");
    exit;
}
if (substr($page_uri, -1) == '/')
    $page_uri .= 'index.html';

// It's not as silly as it may seems
parse_str(substr($query, 1), $_GET);

$page_uri = Pages::withoutPrefix($page_uri);
if (substr($page_uri, 0, 10) == 'img/thumb/') {
    require PUN_ROOT . 'include/user/thumb_url.php';
} else {
    require PUN_ROOT . 'include/user/page_url.php';
}