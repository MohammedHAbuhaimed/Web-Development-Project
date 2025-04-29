<?php
require 'config.php';

if (!isset($_GET['id'])) {
  echo "No question ID provided.";
  exit;
}

$question_id = (int)$_GET['id'];

// Fetch question
$stmt = $pdo->prepare("SELECT q.*, u.username FROM questions q JOIN users u ON q.user_id = u.id WHERE q.id = ?");
$stmt->execute([$question_id]);
$question = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$question) {
  echo "Question not found.";
  exit;
}

// Handle answer submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer']) && isset($_SESSION['user'])) {
  $content = trim($_POST['answer']);
  if ($content) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$_SESSION['user']]);
    $user_id = $stmt->fetchColumn();

    $stmt = $pdo->prepare("INSERT INTO answers (question_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$question_id, $user_id, $content]);
    header("Location: view_question.php?id=$question_id");
    exit;
  }
}

// Fetch question vote total
$stmt = $pdo->prepare("SELECT SUM(vote) FROM question_votes WHERE question_id = ?");
$stmt->execute([$question_id]);
$question_votes = (int) $stmt->fetchColumn();

// Fetch answers
$stmt = $pdo->prepare("SELECT a.*, u.username FROM answers a JOIN users u ON a.user_id = u.id WHERE a.question_id = ? ORDER BY a.created_at DESC");
$stmt->execute([$question_id]);
$answers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($question['title']) ?> - Mini StackOverflow</title>
  <link rel="stylesheet" href="assets/style.css">
  <script src="assets/jquery.min.js"></script>
</head>
<body>
  <header>
    <h1><a href="index.php">Mini StackOverflow</a></h1>
    <nav>
      <?php if (isset($_SESSION['user'])): ?>
        <a href="logout.php">Logout (<?= htmlspecialchars($_SESSION['user']) ?>)</a>
      <?php else: ?>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
      <?php endif; ?>
    </nav>
  </header>

  <main>
    <!-- QUESTION -->
    <div class="question-detail">
      <div class="votes" style="float:left; margin-right: 15px; text-align: center;">
        <a href="vote.php?type=question&id=<?= $question_id ?>&vote=1">⬆️</a><br>
        <div><?= $question_votes ?></div>
        <a href="vote.php?type=question&id=<?= $question_id ?>&vote=-1">⬇️</a>
      </div>
      <div style="overflow: hidden;">
        <h2><?= htmlspecialchars($question['title']) ?></h2>
        <p><?= nl2br(htmlspecialchars($question['description'])) ?></p>
        <small>Asked by <strong><?= htmlspecialchars($question['username']) ?></strong> on <?= $question['created_at'] ?></small>
      </div>
    </div>

    <!-- QUESTION COMMENTS -->
    <div style="margin-top: 10px;">
      <h4>Comments</h4>
      <?php
        $stmt = $pdo->prepare("SELECT qc.*, u.username FROM question_comments qc JOIN users u ON qc.user_id = u.id WHERE question_id = ? ORDER BY created_at ASC");
        $stmt->execute([$question_id]);
        foreach ($stmt as $c) {
          echo "<p><strong>{$c['username']}</strong>: " . htmlspecialchars($c['content']) . "</p>";
        }
      ?>
      <?php if (isset($_SESSION['user'])): ?>
        <form method="post" action="comment.php">
          <input type="hidden" name="type" value="question">
          <input type="hidden" name="target_id" value="<?= $question_id ?>">
          <input type="text" name="content" placeholder="Add a comment..." required>
          <button type="submit">Comment</button>
        </form>
      <?php endif; ?>
    </div>

    <!-- ANSWERS -->
    <h3><?= count($answers) ?> Answer(s)</h3>
    <?php foreach ($answers as $ans): ?>
      <?php
        $ans_id = $ans['id'];
        $stmt = $pdo->prepare("SELECT SUM(vote) FROM answer_votes WHERE answer_id = ?");
        $stmt->execute([$ans_id]);
        $answer_votes = (int)$stmt->fetchColumn();
      ?>
      <div class="answer-box">
        <div class="votes" style="float:left; margin-right: 15px; text-align: center;">
          <a href="vote.php?type=answer&id=<?= $ans_id ?>&vote=1">⬆️</a><br>
          <div><?= $answer_votes ?></div>
          <a href="vote.php?type=answer&id=<?= $ans_id ?>&vote=-1">⬇️</a>
        </div>
        <div style="overflow: hidden;">
          <p><?= nl2br(htmlspecialchars($ans['content'])) ?></p>
          <small>Answered by <strong><?= htmlspecialchars($ans['username']) ?></strong> on <?= $ans['created_at'] ?></small>
        </div>

        <!-- COMMENTS ON ANSWER -->
        <div style="margin-left: 20px; margin-top: 10px;">
          <h5>Comments</h5>
          <?php
            $stmt = $pdo->prepare("SELECT ac.*, u.username FROM answer_comments ac JOIN users u ON ac.user_id = u.id WHERE answer_id = ?");
            $stmt->execute([$ans_id]);
            foreach ($stmt as $ac) {
              echo "<p><strong>{$ac['username']}</strong>: " . htmlspecialchars($ac['content']) . "</p>";
            }
          ?>
          <?php if (isset($_SESSION['user'])): ?>
            <form method="post" action="comment.php">
              <input type="hidden" name="type" value="answer">
              <input type="hidden" name="target_id" value="<?= $ans_id ?>">
              <input type="text" name="content" placeholder="Add a comment..." required>
              <button type="submit">Comment</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>

    <!-- ANSWER FORM -->
    <?php if (isset($_SESSION['user'])): ?>
      <h3>Your Answer</h3>
      <form method="post" id="answerForm">
        <textarea name="answer" rows="6" required></textarea><br><br>
        <button type="submit">Post Answer</button>
      </form>
    <?php else: ?>
      <p><a href="login.php">Log in</a> to submit an answer.</p>
    <?php endif; ?>
  </main>

  <script>
    $('#answerForm').on('submit', function () {
      $(this).find('button').text('Posting...').prop('disabled', true);
    });
  </script>
</body>
</html>
