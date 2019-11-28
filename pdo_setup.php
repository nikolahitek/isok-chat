<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=chatdb", 'root', '');
} catch (PDOException $pe) {
    die("Could not connect to the database chatdb :" . $pe->getMessage());
}