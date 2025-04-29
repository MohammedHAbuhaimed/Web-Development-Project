<?php
require 'config.php';

// Fetch questions
$stmt = $pdo->query("SELECT q.*, u.username FROM questions q JOIN users u ON q.user_id = u.id ORDER BY q.created_at DESC");
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if user is logged in
$userLoggedIn = isset($_SESSION['user']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Mini StackOverflow</title>
  <link rel="stylesheet" href="assets/style.css">
  <script src="assets/jquery.min.js"></script>
</head>
<body>
  <header>
    <h1>Mini StackOverflow</h1>
    <nav>
      <a href="index.php">Home</a>
      <?php if ($userLoggedIn): ?>
        <a href="ask_question.php">Ask Question</a>
        <a href="logout.php">Logout (<?= htmlspecialchars($_SESSION['user']) ?>)</a>
      <?php else: ?>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
      <?php endif; ?>
    </nav>
  </header>

  <main>
    <h2>Latest Questions</h2>
    <?php if ($questions): ?>
      <?php foreach ($questions as $q): ?>
        <div class="question-box">
          <h3><a href="view_question.php?id=<?= $q['id'] ?>"><?= htmlspecialchars($q['title']) ?></a></h3>
          <p><?= htmlspecialchars($q['description']) ?></p>
          <small>Asked by <?= htmlspecialchars($q['username']) ?> on <?= $q['created_at'] ?></small>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p>No questions yet. Be the first to ask!</p>
    <?php endif; ?>
  </main>

</body>
</html>
