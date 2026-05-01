<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/video-game-backlog-tracker/dashboard.php');
}

verify_csrf();

$uid    = get_current_user_id();
$gameId = (int) ($_POST['id'] ?? 0);

$stmt = $pdo->prepare('SELECT title FROM games WHERE id = ? AND user_id = ?');
$stmt->execute([$gameId, $uid]);
$game = $stmt->fetch();

if ($game) {
    $stmt = $pdo->prepare('DELETE FROM games WHERE id = ? AND user_id = ?');
    $stmt->execute([$gameId, $uid]);
    set_flash('success', '"' . $game['title'] . '" removed from your backlog.');
} else {
    set_flash('error', 'Game not found.');
}

redirect('/video-game-backlog-tracker/dashboard.php');
