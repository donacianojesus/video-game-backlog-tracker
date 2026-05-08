<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/igdb.php';
require_login();

header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2) {
    echo '[]';
    exit;
}

$results = igdb_search_games($q, 8);
echo json_encode($results);
