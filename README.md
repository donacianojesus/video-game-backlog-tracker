# Video Game Backlog Tracker

A small PHP + MySQL app for tracking games you want to play, are currently playing, or have completed.

## Requirements

- PHP 8+ with PDO MySQL enabled
- MySQL or MariaDB

The app connects with the settings in `includes/db.php`:

- Host: `localhost`
- Database: `game_backlog`
- User: `root`
- Password: empty by default

If your local MySQL user or password is different, update `includes/db.php`.

## Database Setup

Import the schema from `sql/schema.sql`.

### macOS

If you installed MySQL with Homebrew:

```bash
cd /Users/jesusdonaciano/Desktop/projects/video-game-backlog-tracker
mysql -u root -p < sql/schema.sql
```

If your root user has no password, you can omit `-p`:

```bash
mysql -u root < sql/schema.sql
```

### Windows

From PowerShell or Command Prompt, run the MySQL client and import the schema:

```powershell
cd C:\path\to\video-game-backlog-tracker
mysql -u root -p < sql\schema.sql
```

If `mysql` is not on your PATH, use the full path to `mysql.exe` from your MySQL or XAMPP install.

## Run the App

### macOS

From the project root, start PHP's built-in server:

```bash
cd /Users/jesusdonaciano/Desktop/projects/video-game-backlog-tracker
php -S localhost:8000 -t .
```

Open:

```text
http://localhost:8000/
```

### Windows

From the project folder, start the built-in PHP server:

```powershell
cd C:\path\to\video-game-backlog-tracker
php -S localhost:8000 -t .
```

Open:

```text
http://localhost:8000/
```

If you use XAMPP/WAMP/IIS instead of the PHP built-in server, point the web root to the project folder or set the `BASE_PATH` described below.

## BASE_PATH for Subfolder Deployments

The app supports running from the web root or from a subfolder such as `/video-game-backlog-tracker`.

Set the `BASE_PATH` environment variable before starting PHP:

### macOS

```bash
export BASE_PATH=/video-game-backlog-tracker
php -S localhost:8000 -t /Users/jesusdonaciano/Desktop/projects/video-game-backlog-tracker
```

### Windows PowerShell

```powershell
$env:BASE_PATH = "/video-game-backlog-tracker"
php -S localhost:8000 -t C:\path\to\video-game-backlog-tracker
```

Then open:

```text
http://localhost:8000/video-game-backlog-tracker/
```

If you serve the project at the web root, leave `BASE_PATH` empty.

## MySQL Command Cheat Sheet

Connect to the database:

```bash
mysql -u root -p game_backlog
```

Common commands once you are inside the MySQL prompt:

```sql
SHOW DATABASES;
USE game_backlog;
SHOW TABLES;
DESCRIBE users;
DESCRIBE games;
SELECT * FROM users;
SELECT * FROM games;
SELECT id, username FROM users;
SELECT title, status FROM games WHERE status = 'completed';
```

Examples of common writes:

```sql
INSERT INTO users (username, password) VALUES ('demo', 'hashed-password-here');
UPDATE games SET status = 'completed', rating = 5 WHERE id = 1;
DELETE FROM games WHERE id = 1;
```

Exit MySQL:

```sql
exit;
```

## Notes

- `register.php` creates new users.
- `dashboard.php` is the main list view.
- `add_game.php` and `edit_game.php` require a logged-in user.
- `search_games.php` is used by the live title search in the form.
