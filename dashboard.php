<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$uid = get_current_user_id();

$stmt = $pdo->prepare("SELECT id, username FROM users WHERE id = ?");
$stmt->execute([$uid]);
$currentUser = $stmt->fetch();

$stmt = $pdo->prepare(
    "SELECT id, title, status, rating, notes, cover_url
     FROM games WHERE user_id = ?
     ORDER BY title ASC"
);
$stmt->execute([$uid]);
$allGames = $stmt->fetchAll();

$counts = ['all' => count($allGames), 'playing' => 0, 'want' => 0, 'completed' => 0];
foreach ($allGames as $g) $counts[$g['status']]++;

$stmt = $pdo->prepare(
    "SELECT COUNT(*) AS total,
            COALESCE(SUM(status='completed'),0) AS completed_count,
            ROUND(AVG(CASE WHEN status='completed' THEN rating END),1) AS avg_rating
     FROM games WHERE user_id = ?"
);
$stmt->execute([$uid]);
$stats = $stmt->fetch();

$flash = get_flash();

$statusLabels = ['playing' => 'Currently Playing', 'want' => 'Want to Play', 'completed' => 'Completed'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Backlog — Game Backlog</title>
    <link rel="stylesheet" href="<?= base_path('/assets/css/style.css') ?>">
</head>
<body>
    <nav class="nav">
        <a href="<?= base_path('/') ?>" class="nav-brand">Game Backlog</a>
        <div class="nav-right">
            <span class="nav-username"><?= h($currentUser['username']) ?></span>
            <form method="POST" action="<?= base_path('/logout.php') ?>" class="logout-form">
                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                <button type="submit" class="btn btn--sm">Logout</button>
            </form>
        </div>
    </nav>

    <main class="main-content">
        <?php if ($flash): ?>
            <div class="alert alert--<?= h($flash['type']) ?>"><?= h($flash['message']) ?></div>
        <?php endif; ?>

        <div class="dashboard-header">
            <div>
                <h1 class="page-title"><?= h($currentUser['username']) ?>'s Backlog</h1>
                <div class="stats-inline">
                    <?= (int)$stats['total'] ?> games
                    <span class="sep">·</span>
                    <?= (int)$stats['completed_count'] ?> completed
                    <?php if ($stats['avg_rating'] !== null): ?>
                        <span class="sep">·</span>avg score <?= h((string)$stats['avg_rating']) ?>
                    <?php endif; ?>
                </div>
            </div>
            <a href="<?= base_path('/add_game.php') ?>" class="btn btn--primary">+ Add Game</a>
        </div>

        <div class="list-container">
            <div class="list-tabs">
                <button class="tab-btn tab-btn--active" data-tab="all">
                    All Games <span class="tab-count"><?= $counts['all'] ?></span>
                </button>
                <button class="tab-btn" data-tab="playing">
                    Currently Playing <span class="tab-count"><?= $counts['playing'] ?></span>
                </button>
                <button class="tab-btn" data-tab="want">
                    Want to Play <span class="tab-count"><?= $counts['want'] ?></span>
                </button>
                <button class="tab-btn" data-tab="completed">
                    Completed <span class="tab-count"><?= $counts['completed'] ?></span>
                </button>
            </div>

            <?php if (empty($allGames)): ?>
                <div class="empty-state">
                    No games yet. <a href="<?= base_path('/add_game.php') ?>">Add your first game</a>
                </div>
            <?php else: ?>
                <table class="game-table" id="game-table">
                    <thead>
                        <tr>
                            <th class="col-num">#</th>
                            <th class="col-cover"></th>
                            <th class="col-title">Title</th>
                            <th class="col-status">Status</th>
                            <th class="col-rating">Score</th>
                            <th class="col-actions"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allGames as $i => $game): ?>
                            <tr data-status="<?= h($game['status']) ?>">
                                <td class="col-num"><?= $i + 1 ?></td>
                                <td class="col-cover">
                                    <?php if ($game['cover_url']): ?>
                                        <img src="<?= h($game['cover_url']) ?>"
                                             alt="<?= h($game['title']) ?>"
                                             class="cover-thumb"
                                             loading="lazy">
                                    <?php else: ?>
                                        <div class="cover-thumb cover-thumb--empty"></div>
                                    <?php endif; ?>
                                </td>
                                <td class="col-title">
                                    <span class="game-title-text"><?= h($game['title']) ?></span>
                                    <?php if ($game['notes']): ?>
                                        <span class="game-notes-sub"><?= h(mb_strimwidth($game['notes'], 0, 80, '…')) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="col-status"><?= h($statusLabels[$game['status']]) ?></td>
                                <td class="col-rating">
                                    <?php if ($game['status'] === 'completed' && $game['rating']): ?>
                                        <span class="score-num"><?= (int)$game['rating'] ?></span><span class="score-denom">/5</span>
                                    <?php else: ?>
                                        <span class="score-na">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="col-actions">
                                                <a href="<?= base_path('/edit_game.php?id=' . (int)$game['id']) ?>"
                                       class="action-link">Edit</a>
                                    <span class="action-sep">·</span>
                                    <form method="POST" action="<?= base_path('/delete_game.php') ?>"
                                          class="delete-form" style="display:inline">
                                        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                                        <input type="hidden" name="id" value="<?= (int)$game['id'] ?>">
                                        <button type="submit" class="action-link action-link--danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>

    <script>window.BASE_PATH = '<?= rtrim($GLOBALS['BASE_PATH_NORMALIZED'] ?? '', '/') ?>';</script>
    <script src="<?= base_path('/assets/js/main.js') ?>"></script>
</body>
</html>
