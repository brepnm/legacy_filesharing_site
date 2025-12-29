<?php
$config = require '/etc/private-site/config.php';

function is_logged_in() {
    global $config;
    return isset($_COOKIE['auth']) && hash_equals($config['auth_token'], $_COOKIE['auth']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}
