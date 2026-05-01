<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$uid    = get_current_user_id();
$gameId = (int) ($_GET['id'] ?? 0);
$errors = [];

$stmt = $pdo->prepare('SELECT * FROM games WHERE id = ? AND user_id = ?');
$stmt->execute([$gameId, $uid]);
$game = $stmt->fetch();

if (!$game) {
    set_flash('error', 'Game not found.');
    redirect('/dashboard.php');
}

$input = [
    'title'     => $game['title'],
    'status'    => $game['status'],
    'rating'    => $game['rating']    ?? '',
    'notes'     => $game['notes']     ?? '',
    'cover_url' => $game['cover_url'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $input['title']     = trim($_POST['title']     ?? '');
    $input['status']    = trim($_POST['status']    ?? '');
    $input['rating']    = trim($_POST['rating']    ?? '');
    $input['notes']     = trim($_POST['notes']     ?? '');
    $input['cover_url'] = trim($_POST['cover_url'] ?? '');

    if ($input['title'] === '') {
        $errors[] = 'Title is required.';
    } elseif (mb_strlen($input['title']) > 255) {
        $errors[] = 'Title must be 255 characters or fewer.';
    }

    if (!valid_status($input['status'])) {
        $errors[] = 'Invalid status selected.';
    }

    $rating = null;
    if ($input['status'] === 'completed') {
        $r = (int) $input['rating'];
        if ($r < 1 || $r > 5) {
            $errors[] = 'Rating must be between 1 and 5 for completed games.';
        } else {
            $rating = $r;
        }
    }

    $notes    = $input['notes']     !== '' ? $input['notes']     : null;
    $coverUrl = $input['cover_url'] !== '' ? $input['cover_url'] : null;

    if (!$errors) {
        $stmt = $pdo->prepare(
            'UPDATE games SET title=?, status=?, rating=?, notes=?, cover_url=? WHERE id=? AND user_id=?'
        );
        $stmt->execute([$input['title'], $input['status'], $rating, $notes, $coverUrl, $gameId, $uid]);
        set_flash('success', '"' . $input['title'] . '" updated.');
        redirect('/dashboard.php');
    }
}

get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Game — Game Backlog</title>
    <link rel="stylesheet" href="<?= base_path('/assets/css/style.css') ?>">
</head>
<body>
    <nav class="nav">
        <a href="<?= base_path('/') ?>" class="nav-brand">Game Backlog</a>
        <div class="nav-right">
            <form method="POST" action="<?= base_path('/logout.php') ?>" class="logout-form">
                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                <button type="submit" class="btn btn--sm">Logout</button>
            </form>
        </div>
    </nav>

    <main class="main-content">
        <div class="form-page">
            <div class="form-page-header">
                <a href="<?= base_path('/dashboard.php') ?>" class="back-link">← Back</a>
                <h1 class="page-title">Edit Game</h1>
            </div>

            <?php if ($errors): ?>
                <div class="alert alert--error">
                    <?php foreach ($errors as $err): ?>
                        <div><?= h($err) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="game-form" class="game-form">
                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="cover_url" id="cover_url" value="<?= h($input['cover_url']) ?>">

                <div class="form-group search-wrap">
                    <label for="title">Game Title</label>
                    <input type="text" id="title" name="title"
                           value="<?= h($input['title']) ?>"
                           autocomplete="off" required>
                    <div id="search-dropdown" class="search-dropdown"></div>
                </div>

                <?php if ($input['cover_url']): ?>
                <div id="cover-preview" class="cover-preview">
                    <img id="cover-img" src="<?= h($input['cover_url']) ?>" alt="Cover art">
                    <button type="button" id="cover-clear" class="cover-clear">✕ Remove art</button>
                </div>
                <?php else: ?>
                <div id="cover-preview" class="cover-preview" style="display:none">
                    <img id="cover-img" src="" alt="Cover art">
                    <button type="button" id="cover-clear" class="cover-clear">✕ Remove art</button>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="want"      <?= $input['status'] === 'want'      ? 'selected' : '' ?>>Want to Play</option>
                        <option value="playing"   <?= $input['status'] === 'playing'   ? 'selected' : '' ?>>Currently Playing</option>
                        <option value="completed" <?= $input['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                    </select>
                </div>

                <div class="form-group" id="rating-group">
                    <label for="rating">Rating</label>
                    <select id="rating" name="rating">
                        <option value="">— Select —</option>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?= $i ?>" <?= (int) $input['rating'] === $i ? 'selected' : '' ?>>
                                <?= $i ?> / 5
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="notes">Notes <span class="form-hint">(optional)</span></label>
                    <textarea id="notes" name="notes"><?= h($input['notes']) ?></textarea>
                </div>

                <div class="form-actions">
                    <a href="<?= base_path('/dashboard.php') ?>" class="btn">Cancel</a>
                    <button type="submit" class="btn btn--primary">Save Changes</button>
                </div>
            </form>
        </div>
    </main>

    <script>window.BASE_PATH = '<?= rtrim($GLOBALS['BASE_PATH_NORMALIZED'] ?? '', '/') ?>';</script>
    <script src="<?= base_path('/assets/js/main.js') ?>"></script>
</body>
</html>
