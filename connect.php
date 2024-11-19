<?php

try {
    $pdo = new PDO("mysql:host=localhost;dbname=promptbook;charset=utf8mb4", "bit_academy", "bit_academy");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
    exit();
}