<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if (!empty($_SESSION['user_id'])) {
    redirect('/dashboard.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $errors[] = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare('SELECT id, password FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'Invalid username or password.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            set_flash('success', 'Welcome back, ' . $username . '!');
            redirect('/dashboard.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Game Backlog</title>
    <link rel="stylesheet" href="<?= base_path('/assets/css/style.css') ?>">
</head>
<body>
    <nav class="nav">
        <a href="<?= base_path('/') ?>" class="nav-brand">Game Backlog</a>
    </nav>

    <main class="auth-page">
        <div class="auth-card">
            <h1 class="auth-title">Sign In</h1>

            <?php if ($errors): ?>
                <div class="alert alert--error">
                    <?= h(implode(' ', $errors)) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username"
                           value="<?= h($_POST['username'] ?? '') ?>"
                           autocomplete="username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                           autocomplete="current-password" required>
                </div>

                <button type="submit" class="btn btn--primary btn--full">Sign In</button>
            </form>

            <p class="auth-switch">
                Don't have an account?
                <a href="<?= base_path('/register.php') ?>">Create one</a>
            </p>
        </div>
    </main>

    <script>window.BASE_PATH = '<?= rtrim($GLOBALS['BASE_PATH_NORMALIZED'] ?? '', '/') ?>';</script>
    <script src="<?= base_path('/assets/js/main.js') ?>"></script>
</body>
</html>
