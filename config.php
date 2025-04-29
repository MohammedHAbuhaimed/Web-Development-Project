<?php
$host = 'localhost';
$db   = 'stackoverflow_clone';
$user = 'root';
$pass = ''; // change this if you set a password

try {
  $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Database connection failed: " . $e->getMessage());
}
session_start();
?>
