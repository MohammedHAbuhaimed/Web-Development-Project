<?php
// login.php and register.php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  $action = $_POST['action'];
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';

  if ($action === 'login') {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
      $_SESSION['user'] = $user['username'];
      echo json_encode(["success" => true]);
    } else {
      echo json_encode(["success" => false, "error" => "Invalid credentials"]);
    }
  } elseif ($action === 'register') {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
      echo json_encode(["success" => false, "error" => "Username already exists"]);
      exit;
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->execute([$username, $hashed]);
    $_SESSION['user'] = $username;
    echo json_encode(["success" => true]);
  }
}
