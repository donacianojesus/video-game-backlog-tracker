<?php
function require_login(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['user_id'])) {
        header('Location: /video-game-backlog-tracker/login.php');
        exit;
    }
}

function get_current_user_id(): int {
    return (int) $_SESSION['user_id'];
}
