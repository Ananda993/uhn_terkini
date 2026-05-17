<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'uhn-terkini');

// Cloudflare Turnstile Captcha
// Isi lewat environment variable untuk production, atau ganti string kosong di bawah saat development.
define('TURNSTILE_SITE_KEY', getenv('TURNSTILE_SITE_KEY') ?: '0x4AAAAAADNu7UpvcsuVrCDu');
define('TURNSTILE_SECRET_KEY', getenv('TURNSTILE_SECRET_KEY') ?: '0x4AAAAAADNu7WRDqIVxl7E48vBFcvJ0lY8');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$conn->query("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db(DB_NAME);

// Set charset
$conn->set_charset("utf8mb4");

// Create tables
$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin','publisher','mahasiswa') DEFAULT 'mahasiswa',
        nim VARCHAR(20) DEFAULT NULL,
        prodi VARCHAR(100) DEFAULT NULL,
        foto_profil VARCHAR(255) DEFAULT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(100) NOT NULL,
        slug VARCHAR(100) UNIQUE NOT NULL,
        icon VARCHAR(50) DEFAULT 'fas fa-folder',
        warna VARCHAR(7) DEFAULT '#6366f1',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS informasi (
        id INT AUTO_INCREMENT PRIMARY KEY,
        judul VARCHAR(255) NOT NULL,
        slug VARCHAR(255) UNIQUE NOT NULL,
        deskripsi TEXT NOT NULL,
        gambar VARCHAR(255) DEFAULT NULL,
        deadline DATE DEFAULT NULL,
        category_id INT,
        user_id INT,
        status ENUM('pending','approved','rejected') DEFAULT 'pending',
        views INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS bookmarks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        informasi_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_bookmark (user_id, informasi_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (informasi_id) REFERENCES informasi(id) ON DELETE CASCADE
    )"
];

foreach ($tables as $sql) {
    $conn->query($sql);
}

// Add new columns if they don't exist (migration-safe)
function columnExists($conn, $table, $column) {
    $result = $conn->query("SELECT COUNT(*) as c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME = '$table' AND COLUMN_NAME = '$column'");
    return $result->fetch_assoc()['c'] > 0;
}
if (!columnExists($conn, 'users', 'nim')) {
    $conn->query("ALTER TABLE users ADD COLUMN nim VARCHAR(20) DEFAULT NULL AFTER role");
}
if (!columnExists($conn, 'users', 'prodi')) {
    $conn->query("ALTER TABLE users ADD COLUMN prodi VARCHAR(100) DEFAULT NULL AFTER nim");
}
if (!columnExists($conn, 'users', 'is_active')) {
    $conn->query("ALTER TABLE users ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER foto_profil");
}

// Seed default categories if empty
$result = $conn->query("SELECT COUNT(*) as count FROM categories");
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    $categories = [
        ['Beasiswa', 'beasiswa', 'fas fa-graduation-cap', '#6366f1'],
        ['Lomba', 'lomba', 'fas fa-trophy', '#f59e0b'],
        ['Seminar', 'seminar', 'fas fa-chalkboard-teacher', '#10b981'],
        ['Kegiatan Kampus', 'kegiatan-kampus', 'fas fa-university', '#ef4444'],
        ['Akademik', 'akademik', 'fas fa-book', '#3b82f6'],
        ['Organisasi', 'organisasi', 'fas fa-users', '#8b5cf6'],
    ];
    $stmt = $conn->prepare("INSERT INTO categories (nama, slug, icon, warna) VALUES (?, ?, ?, ?)");
    foreach ($categories as $cat) {
        $stmt->bind_param("ssss", $cat[0], $cat[1], $cat[2], $cat[3]);
        $stmt->execute();
    }
    $stmt->close();
}

// Seed default admin if no users
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    $admin_pass = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (nama, email, password, role, is_active) VALUES (?, ?, ?, ?, 1)");
    $nama = 'Administrator';
    $email = 'admin@uhn.ac.id';
    $role = 'admin';
    $stmt->bind_param("ssss", $nama, $email, $admin_pass, $role);
    $stmt->execute();
    $stmt->close();
}


// Helper function
function sanitize($data) {
    global $conn;
    return $conn->real_escape_string(htmlspecialchars(trim($data)));
}

// Auth helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function isPublisher() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'publisher';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /auth/login.php');
        exit;
    }
}

function requireAdmin() {
    if (!isLoggedIn() || !isAdmin()) {
        header('Location: /auth/login.php');
        exit;
    }
}

function getCurrentUser() {
    global $conn;
    if (!isLoggedIn()) return null;
    $stmt = $conn->prepare("SELECT id, nama, email, role, nim, prodi, foto_profil FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    return $user;
}

function isCaptchaEnabled() {
    return TURNSTILE_SITE_KEY !== '' && TURNSTILE_SECRET_KEY !== '';
}

function verifyTurnstile($token) {
    if (!isCaptchaEnabled()) {
        return true;
    }

    if (empty($token)) {
        return false;
    }

    $payload = http_build_query([
        'secret' => TURNSTILE_SECRET_KEY,
        'response' => $token,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
    ]);

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => $payload,
            'timeout' => 8,
        ],
    ]);

    $response = @file_get_contents(
        'https://challenges.cloudflare.com/turnstile/v0/siteverify',
        false,
        $context
    );

    if ($response === false) {
        return false;
    }

    $result = json_decode($response, true);
    return is_array($result) && !empty($result['success']);
}

session_start();
?>
