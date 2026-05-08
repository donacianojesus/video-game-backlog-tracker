<?php
require_once __DIR__ . '/igdb_credentials.php';

define('IGDB_TOKEN_FILE', __DIR__ . '/.igdb_token.json');

function igdb_get_token(): ?string {
    if (is_file(IGDB_TOKEN_FILE)) {
        $cached = json_decode((string)@file_get_contents(IGDB_TOKEN_FILE), true);
        if (isset($cached['access_token'], $cached['expires_at']) && $cached['expires_at'] > time() + 60) {
            return $cached['access_token'];
        }
    }

    $url = 'https://id.twitch.tv/oauth2/token?' . http_build_query([
        'client_id'     => IGDB_CLIENT_ID,
        'client_secret' => IGDB_CLIENT_SECRET,
        'grant_type'    => 'client_credentials',
    ]);

    $ctx = stream_context_create(['http' => [
        'method'        => 'POST',
        'timeout'       => 6,
        'ignore_errors' => true,
        'header'        => "Content-Length: 0\r\n",
    ]]);

    $raw = @file_get_contents($url, false, $ctx);
    if ($raw === false) return null;
    $data = json_decode($raw, true);
    if (!isset($data['access_token'], $data['expires_in'])) return null;

    @file_put_contents(IGDB_TOKEN_FILE, json_encode([
        'access_token' => $data['access_token'],
        'expires_at'   => time() + (int)$data['expires_in'],
    ]));
    @chmod(IGDB_TOKEN_FILE, 0600);

    return $data['access_token'];
}

function igdb_search_games(string $query, int $limit = 8): array {
    $token = igdb_get_token();
    if ($token === null) return [];

    $safe = str_replace('"', '\\"', $query);
    $body = sprintf(
        'search "%s"; fields name, cover.image_id, first_release_date; limit %d;',
        $safe,
        max(1, min(50, $limit))
    );

    $ctx = stream_context_create(['http' => [
        'method'        => 'POST',
        'timeout'       => 6,
        'ignore_errors' => true,
        'header'        => implode("\r\n", [
            'Client-ID: ' . IGDB_CLIENT_ID,
            'Authorization: Bearer ' . $token,
            'Content-Type: text/plain',
            'Accept: application/json',
        ]),
        'content'       => $body,
    ]]);

    $raw = @file_get_contents('https://api.igdb.com/v4/games', false, $ctx);
    if ($raw === false) return [];
    $data = json_decode($raw, true);
    if (!is_array($data)) return [];

    $results = [];
    foreach ($data as $game) {
        $cover = null;
        if (!empty($game['cover']['image_id'])) {
            $cover = 'https://images.igdb.com/igdb/image/upload/t_cover_big/' . $game['cover']['image_id'] . '.jpg';
        }
        $year = null;
        if (!empty($game['first_release_date'])) {
            $year = date('Y', (int)$game['first_release_date']);
        }
        $results[] = [
            'name'  => $game['name'] ?? '',
            'cover' => $cover,
            'year'  => $year,
        ];
    }
    return $results;
}
