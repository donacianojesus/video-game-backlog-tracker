<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (!empty($_SESSION['user_id'])) {
    redirect('/video-game-backlog-tracker/dashboard.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($username === '') {
        $errors[] = 'Username is required.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{1,50}$/', $username)) {
        $errors[] = 'Username may only contain letters, numbers, and underscores (max 50 characters).';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $errors[] = 'That username is already taken.';
        }
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
        $stmt->execute([$username, $hash]);
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $pdo->lastInsertId();
        set_flash('success', 'Account created! Welcome, ' . $username . '.');
        redirect('/video-game-backlog-tracker/dashboard.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — Game Backlog</title>
    <link rel="stylesheet" href="/video-game-backlog-tracker/assets/css/style.css">
</head>
<body>
    <nav class="nav">
        <a href="/video-game-backlog-tracker/" class="nav-brand">Game Backlog</a>
    </nav>

    <main class="auth-page">
        <div class="auth-card">
            <h1 class="auth-title">Create Account</h1>

            <?php if ($errors): ?>
                <div class="alert alert--error">
                    <?php foreach ($errors as $err): ?>
                        <div><?= h($err) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username"
                           value="<?= h($_POST['username'] ?? '') ?>"
                           autocomplete="username" required>
                    <span class="form-hint">Letters, numbers, underscores — max 50 characters</span>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                           autocomplete="new-password" required>
                    <span class="form-hint">Minimum 8 characters</span>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password"
                           autocomplete="new-password" required>
                </div>

                <button type="submit" class="btn btn--primary btn--full">Create Account</button>
            </form>

            <p class="auth-switch">
                Already have an account?
                <a href="/video-game-backlog-tracker/login.php">Sign in</a>
            </p>
        </div>
    </main>

    <script src="/video-game-backlog-tracker/assets/js/main.js"></script>
</body>
</html>
