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
        no_hp VARCHAR(20) DEFAULT NULL,
        fakultas VARCHAR(50) DEFAULT NULL,
        ormawa VARCHAR(100) DEFAULT NULL,
        ukm_id INT DEFAULT NULL,
        periode VARCHAR(50) DEFAULT NULL,
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
    "CREATE TABLE IF NOT EXISTS fakultas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(100) NOT NULL,
        slug VARCHAR(100) UNIQUE NOT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS ormawa (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(120) NOT NULL,
        slug VARCHAR(120) UNIQUE NOT NULL,
        scope ENUM('univ','fakultas') DEFAULT 'fakultas',
        fakultas_id INT DEFAULT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (fakultas_id) REFERENCES fakultas(id) ON DELETE SET NULL
    )",
    "CREATE TABLE IF NOT EXISTS ukm (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(120) NOT NULL,
        slug VARCHAR(120) UNIQUE NOT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS informasi (
        id INT AUTO_INCREMENT PRIMARY KEY,
        judul VARCHAR(255) NOT NULL,
        slug VARCHAR(255) UNIQUE NOT NULL,
        sumber VARCHAR(100) DEFAULT NULL,
        deskripsi TEXT NOT NULL,
        gambar VARCHAR(255) DEFAULT NULL,
        deadline DATE DEFAULT NULL,
        category_id INT,
        user_id INT,
        is_urgent TINYINT(1) DEFAULT 0,
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
if (!columnExists($conn, 'users', 'no_hp')) {
    $conn->query("ALTER TABLE users ADD COLUMN no_hp VARCHAR(20) DEFAULT NULL AFTER role");
}
if (!columnExists($conn, 'users', 'fakultas')) {
    $conn->query("ALTER TABLE users ADD COLUMN fakultas VARCHAR(50) DEFAULT NULL AFTER no_hp");
}
if (!columnExists($conn, 'users', 'ormawa')) {
    $conn->query("ALTER TABLE users ADD COLUMN ormawa VARCHAR(100) DEFAULT NULL AFTER fakultas");
}
if (!columnExists($conn, 'users', 'is_active')) {
    $conn->query("ALTER TABLE users ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER foto_profil");
}
if (!columnExists($conn, 'informasi', 'sumber')) {
    $conn->query("ALTER TABLE informasi ADD COLUMN sumber VARCHAR(100) DEFAULT NULL AFTER slug");
}
if (!columnExists($conn, 'informasi', 'is_urgent')) {
    $conn->query("ALTER TABLE informasi ADD COLUMN is_urgent TINYINT(1) DEFAULT 0 AFTER user_id");
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

// Seed fakultas if empty
$result = $conn->query("SELECT COUNT(*) as count FROM fakultas");
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    $fakultasList = ['FDA', 'FDD', 'FBW', 'FAST', 'Kedokteran'];
    $stmt = $conn->prepare("INSERT INTO fakultas (nama, slug, is_active) VALUES (?, ?, 1)");
    foreach ($fakultasList as $name) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        $stmt->bind_param("ss", $name, $slug);
        $stmt->execute();
    }
    $stmt->close();
}

// Seed ormawa if empty
$result = $conn->query("SELECT COUNT(*) as count FROM ormawa");
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    $fakultasMap = [];
    $res = $conn->query("SELECT id, nama FROM fakultas");
    while ($f = $res->fetch_assoc()) {
        $fakultasMap[$f['nama']] = (int)$f['id'];
    }

    $ormawaSeed = [
        ['BEM UNIV', 'univ', null],
        ['DPM UNIV', 'univ', null],
        ['BPM FDD', 'fakultas', $fakultasMap['FDD'] ?? null],
        ['BEM FDD', 'fakultas', $fakultasMap['FDD'] ?? null],
        ['HMJ HUKUM', 'fakultas', $fakultasMap['FDD'] ?? null],
        ['HMJ KWU', 'fakultas', $fakultasMap['FDD'] ?? null],
        ['HMJ INFORMATIKA', 'fakultas', $fakultasMap['FDD'] ?? null],
        ['HMJ PARBUD', 'fakultas', $fakultasMap['FDD'] ?? null],
        ['BPM FDA', 'fakultas', $fakultasMap['FDA'] ?? null],
        ['BEM FDA', 'fakultas', $fakultasMap['FDA'] ?? null],
        ['HMJ PGSD', 'fakultas', $fakultasMap['FDA'] ?? null],
        ['BPM FBW', 'fakultas', $fakultasMap['FBW'] ?? null],
        ['BEM FBW', 'fakultas', $fakultasMap['FBW'] ?? null]
    ];

    $stmt = $conn->prepare("INSERT INTO ormawa (nama, slug, scope, fakultas_id, is_active) VALUES (?, ?, ?, ?, 1)");
    foreach ($ormawaSeed as $item) {
        [$name, $scope, $fakId] = $item;
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        $stmt->bind_param("sssi", $name, $slug, $scope, $fakId);
        $stmt->execute();
    }
    $stmt->close();
}

// Seed ukm if empty
$result = $conn->query("SELECT COUNT(*) as count FROM ukm");
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    $stmt = $conn->prepare("INSERT INTO ukm (nama, slug, is_active) VALUES (?, ?, 1)");
    $defaults = ['UKM Seni', 'UKM Olahraga', 'UKM Kewirausahaan'];
    foreach ($defaults as $name) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        $stmt->bind_param("ss", $name, $slug);
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
    $stmt = $conn->prepare("SELECT id, nama, email, role, no_hp, fakultas, ormawa, ukm_id, periode, foto_profil FROM users WHERE id = ?");
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
