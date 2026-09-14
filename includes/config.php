<?php
// 2008 ROBLOX recreation - shared config
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const DB_HOST = '127.0.0.1';
const DB_NAME = 'roblox08';
const DB_USER = 'root';
const DB_PASS = '';

// Absolute site base URL, used by RCC render scripts to fetch assets
const SITE_URL = 'http://localhost/early08';
// RCCService SOAP endpoint (see database/rcc_schema.sql readme)
const RCC_IP = '127.0.0.1';
const RCC_PORT = 65435;

// Allowed BrickColor numbers, indexed 1..32 (same lookup the 2008 site uses)
$GLOBALS['RobloxColors'] = array(
    1, 208, 194, 199, 26, 21, 24, 226, 23, 107, 102, 11,
    45, 135, 106, 105, 141, 28, 37, 119, 29, 151, 38, 192,
    104, 9, 101, 5, 153, 217, 18, 125
);
$GLOBALS['RobloxColorsHtml'] = array(
    '#F2F3F2', '#E5E4DE', '#A3A2A4', '#635F61', '#1B2A34', '#C4281B',
    '#F5CD2F', '#FDEA8C', '#0D69AB', '#008F9B', '#6E99C9', '#80BBDC',
    '#B4D2E4', '#74869C', '#DA8541', '#E29B40', '#27462D', '#287F47',
    '#4B974B', '#A4BD47', '#A1C48C', '#789082', '#A05F35', '#694028',
    '#6B327C', '#E8BAC8', '#DA867A', '#D7C59A', '#957977', '#7C5C46',
    '#CC8E69', '#EAB892'
);

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            exit('<p>Database connection failed. Make sure MySQL is running.</p>');
        }
    }
    return $pdo;
}

// Currently logged-in user, or null
function current_user(): ?array {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    static $user = false;
    if ($user === false) {
        $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch() ?: null;
        if (!$user) {
            unset($_SESSION['user_id']);
        }
    }
    return $user;
}

function is_logged_in(): bool {
    return current_user() !== null;
}

function is_admin(): bool {
    $u = current_user();
    return $u !== null && (int)$u['is_admin'] === 1;
}

// Simple HTML-escape helper
function e(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Avatar image source for a user row (uses avatar_id, falls back to id modulo)
function avatar_src(array $user): string {
    $n = (int)($user['avatar_id'] ?? 0);
    if ($n < 1 || $n > 10) {
        $n = ((int)($user['id'] ?? 1) % 10) + 1;
    }
    return "resources/avatars/avatar{$n}.png";
}

// RCC-rendered avatar thumbnail URL (falls back to the legacy figure PNG)
function avatar_thumb(array $user, string $type = 'normal'): string {
    $col = $type === 'small' ? 'thumbnailsmall' : ($type === 'friends' ? 'thumbnailfriends' : ($type === 'character' ? 'thumbnailcharacter' : 'thumbnail'));

    // Sparse arrays (just id/avatar_id) don't carry thumbnail columns — fetch the row
    if (!array_key_exists($col, $user)) {
        static $cache = [];
        $uid = (int)($user['id'] ?? 0);
        if ($uid > 0) {
            if (!array_key_exists($uid, $cache)) {
                $stmt = db()->prepare('SELECT id, avatar_id, thumbnail, thumbnailsmall, thumbnailfriends, thumbnailcharacter FROM users WHERE id = ?');
                $stmt->execute([$uid]);
                $cache[$uid] = $stmt->fetch() ?: ['id' => $uid];
            }
            $user = $cache[$uid];
        }
    }

    if (!empty($user[$col])) {
        return "Thumbs/Avatar.php?id=" . (int)($user['id'] ?? 0) . "&type=" . $type;
    }
    return avatar_src($user);
}

// Human-friendly "X years/months/days/hours ago" (matches the revival item page)
function time_ago(?string $datetime): string {
    if (!$datetime) {
        return 'never';
    }
    $diff = max(0, time() - strtotime($datetime));
    $years = (int)floor($diff / 31557600);
    $months = (int)floor($diff / 2592000);
    $days = (int)floor($diff / 86400);
    $hours = (int)floor($diff / 3600);
    if ($years > 0) {
        return $years . ($years === 1 ? ' year' : ' years') . ' ago';
    }
    if ($months > 0) {
        return $months . ($months === 1 ? ' month' : ' months') . ' ago';
    }
    if ($days > 0) {
        return $days . ($days === 1 ? ' day' : ' days') . ' ago';
    }
    if ($hours > 0) {
        return $hours . ($hours === 1 ? ' hour' : ' hours') . ' ago';
    }
    return 'just now';
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function maintenance_mode(): bool {
    $stmt = db()->query("SELECT setting_value FROM site_settings WHERE setting_key = 'maintenance_mode'");
    $row = $stmt->fetch();
    return $row !== false && $row['setting_value'] === '1';
}

// Global maintenance redirect: every non-admin visitor gets sent to maintenance.php
if (maintenance_mode() && !is_admin()) {
    $script = basename($_SERVER['SCRIPT_NAME'] ?? '');
    if ($script !== 'maintenance.php') {
        redirect('maintenance.php');
    }
}

// Banned users (non-admins) only ever see the disabled-account page
$kbCur = current_user();
if ($kbCur !== null && (int)$kbCur['is_banned'] === 1 && !is_admin()) {
    $script = basename($_SERVER['SCRIPT_NAME'] ?? '');
    if (!in_array($script, ['banned.php', 'logout.php'], true)) {
        redirect('banned.php');
    }
}

// Log a sign-in: sets session + updates last_login
function do_login(int $userId): void {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    $_SESSION['notices'][] = ['type' => 'success', 'message' => 'You have been logged in!'];
    $stmt = db()->prepare('UPDATE users SET last_login = NOW() WHERE id = ?');
    $stmt->execute([$userId]);
    $stmt = db()->prepare('UPDATE users SET is_online = 1 WHERE id = ?');
    $stmt->execute([$userId]);
}

// Session notices (flash messages) - empty after display
function consume_notices(): array {
    $n = $_SESSION['notices'] ?? [];
    unset($_SESSION['notices']);
    return $n;
}

// Active database system alerts (info/warning/maintenance/error)
function system_alerts(): array {
    return db()->query('SELECT * FROM site_alerts WHERE is_active = 1 ORDER BY created DESC')->fetchAll();
}

function do_logout(): void {
    $u = current_user();
    if ($u) {
        $stmt = db()->prepare('UPDATE users SET is_online = 0 WHERE id = ?');
        $stmt->execute([$u['id']]);
    }
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}