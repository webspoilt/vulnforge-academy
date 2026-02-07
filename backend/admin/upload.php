<?php
/**
 * Admin File Upload - EXTREMELY VULNERABLE
 * - No authentication check (relies on session but doesn't verify)
 * - No file type validation
 * - No size limit enforcement
 * - Files uploaded to web-accessible directory
 * - Original filename preserved (path injection possible)
 */
require_once '../config.php';

$uploadDir = __DIR__ . '/../uploads/admin/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];

    // VULNERABLE: No real validation!
    // "Check" extension but accept anything
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // Fake security - blocks nothing useful
    $blocked = ['exe', 'bat', 'cmd', 'com'];
    if (in_array($ext, $blocked)) {
        die("Blocked extension");
    }

    // VULNERABLE: Uses original filename (can contain ../ for traversal)
    $targetFile = $uploadDir . basename($file['name']);

    // VULNERABLE: No content validation - PHP files work fine!
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        echo "File uploaded: <a href='/uploads/admin/" . htmlspecialchars(basename($file['name'])) . "'>View File</a>";
        echo "<br><br>Full path: " . $targetFile;
    } else {
        echo "Upload failed";
    }
} else {
    header('Location: index.php');
}
