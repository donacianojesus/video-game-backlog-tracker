<?php
// Base path for the app when served from a subdirectory, e.g. '/video-game-backlog-tracker'
// Leave empty or set via environment variable for root deployment
if (!defined('BASE_PATH')) {
    $env = getenv('BASE_PATH');
    if ($env === false) $env = '';
    // normalize the environment value
    $base = trim($env);
    if ($base !== '') {
        if (strpos($base, '/') !== 0) $base = '/' . $base;
        $base = rtrim($base, '/');
    }
    define('BASE_PATH', $base);
}

// Provide a fallback global for internal use by templates.
$GLOBALS['BASE_PATH_NORMALIZED'] = BASE_PATH;

// Provide a fallback global for internal use by templates.
if (!isset($GLOBALS['BASE_PATH_NORMALIZED'])) {
    $GLOBALS['BASE_PATH_NORMALIZED'] = $BASE_PATH_NORMALIZED;
}

?>
