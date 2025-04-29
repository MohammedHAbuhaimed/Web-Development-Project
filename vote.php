<?php
require 'config.php';

if (!isset($_SESSION['user'])) {
  header('Location: login.php');
  exit;
}

if (isset($_GET['type'], $_GET['id'], $_GET['vote'])) {
  $type = $_GET['type']; // 'question' or 'answer'
  $id = (int)$_GET['id'];
  $vote = (int)$_GET['vote']; // +1 or -1

  // Get user ID
  $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
  $stmt->execute([$_SESSION['user']]);
  $user_id = $stmt->fetchColumn();

  if ($type === 'question') {
    $stmt = $pdo->prepare("INSERT INTO question_votes (question_id, user_id, vote) VALUES (?, ?, ?)
      ON DUPLICATE KEY UPDATE vote = VALUES(vote)");
    $stmt->execute([$id, $user_id, $vote]);
  } elseif ($type === 'answer') {
    $stmt = $pdo->prepare("INSERT INTO answer_votes (answer_id, user_id, vote) VALUES (?, ?, ?)
      ON DUPLICATE KEY UPDATE vote = VALUES(vote)");
    $stmt->execute([$id, $user_id, $vote]);
  }
}

header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;
