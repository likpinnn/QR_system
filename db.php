<?php
session_start();
try {
    $conn = new PDO('sqlite:qr_report.db');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die('数据库连接失败: ' . $e->getMessage());
}

// users
$conn->exec('CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL,
    name TEXT NOT NULL,
    password TEXT NOT NULL,
    role TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)');


// report
$conn->exec('CREATE TABLE IF NOT EXISTS report (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    `action` TEXT NOT NULL,
    step TEXT NOT NULL,
    spec TEXT,
    `type` TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)');


//revision history
$conn->exec('CREATE TABLE IF NOT EXISTS revision_history (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ECO TEXT NOT NULL,
    Rev TEXT NOT NULL,
    `date` TEXT NOT NULL,
    `action` TEXT NOT NULL,
    `Author` TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)');

//bai No
$conn->exec('CREATE TABLE IF NOT EXISTS bai_no (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    `bai_no` TEXT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
)'); 

//rev
$conn->exec('CREATE TABLE IF NOT EXISTS rev (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    `rev` TEXT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
)');

//pdf
$conn->exec('CREATE TABLE IF NOT EXISTS pdf (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    `pdf` TEXT NOT NULL,
    `user_id` INTEGER NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
)');


date_default_timezone_set("Asia/Kuala_Lumpur");

?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>



