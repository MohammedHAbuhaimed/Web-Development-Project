<?php
require 'config.php';
if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit;
}

$type = $_POST['type'];
$target_id = (int)$_POST['target_id'];
$content = trim($_POST['content']);

// Get user ID
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$_SESSION['user']]);
$user_id = $stmt->fetchColumn();

if ($type === 'question') {
  $stmt = $pdo->prepare("INSERT INTO question_comments (question_id, user_id, content) VALUES (?, ?, ?)");
  $stmt->execute([$target_id, $user_id, $content]);
} elseif ($type === 'answer') {
  $stmt = $pdo->prepare("INSERT INTO answer_comments (answer_id, user_id, content) VALUES (?, ?, ?)");
  $stmt->execute([$target_id, $user_id, $content]);
}

header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
