<?php
require_once __DIR__ . '/includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
}

$_SESSION = [];
session_destroy();
redirect('/video-game-backlog-tracker/login.php');
