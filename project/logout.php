<?php
session_start();
$_SESSION = [];
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}
session_destroy();
header('Location: /login.php');
exit;
