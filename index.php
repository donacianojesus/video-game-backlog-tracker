<?php
session_start();
if (!empty($_SESSION['user_id'])) {
    header('Location: /video-game-backlog-tracker/dashboard.php');
} else {
    header('Location: /video-game-backlog-tracker/login.php');
}
exit;
