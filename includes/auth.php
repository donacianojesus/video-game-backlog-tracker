<?php
function require_login(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['user_id'])) {
        require_once __DIR__ . '/functions.php';
        redirect('/login.php');
    }
}

function get_current_user_id(): int {
    return (int) $_SESSION['user_id'];
}
