<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2) {
    echo '[]';
    exit;
}

define('RAWG_KEY', 'aec4cad0aafe49ee826417388c344632');

$url = 'https://api.rawg.io/api/games?' . http_build_query([
    'key'       => RAWG_KEY,
    'search'    => $q,
    'page_size' => 8,
    'ordering'  => '-added',
]);

$ctx = stream_context_create(['http' => [
    'timeout' => 6,
    'header'  => "User-Agent: GameBacklogTracker/1.0\r\n",
]]);

$raw = @file_get_contents($url, false, $ctx);
if ($raw === false) {
    http_response_code(502);
    echo '[]';
    exit;
}

$data = json_decode($raw, true);
$results = [];

foreach (($data['results'] ?? []) as $game) {
    $results[] = [
        'name'  => $game['name'],
        'cover' => $game['background_image'] ?? null,
        'year'  => isset($game['released']) ? substr($game['released'], 0, 4) : null,
    ];
}

echo json_encode($results);
