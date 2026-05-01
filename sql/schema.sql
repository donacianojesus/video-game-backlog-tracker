CREATE DATABASE IF NOT EXISTS game_backlog CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE game_backlog;

CREATE TABLE IF NOT EXISTS users (
    id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50)  NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS games (
    id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    title   VARCHAR(255) NOT NULL,
    status  ENUM('want','playing','completed') NOT NULL DEFAULT 'want',
    rating  TINYINT UNSIGNED NULL,
    notes     TEXT NULL,
    cover_url VARCHAR(500) NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
