<?php
require 'config.php';

// Only logged-in users can ask questions
if (!isset($_SESSION['user'])) {
  header('Location: login.php');
  exit;
}

// Handle POST submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title']);
  $description = trim($_POST['description']);
  $tags = trim($_POST['tags']);
  
  if ($title && $description) {
    // Get current user ID
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$_SESSION['user']]);
    $user = $stmt->fetch();
    $user_id = $user['id'];
    
    // Insert into questions
    $stmt = $pdo->prepare("INSERT INTO questions (user_id, title, description) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $title, $description]);
    $question_id = $pdo->lastInsertId();
    
    // Save tags (optional enhancement: separate table for tags)
    if (!empty($tags)) {
      $tagsArray = explode(',', $tags);
      foreach ($tagsArray as $tag) {
        $cleanTag = trim($tag);
        if ($cleanTag) {
          // Here you can insert into a `question_tags` table if you want
          // For now we just store tags as text, or skip
        }
      }
    }
    
    header('Location: index.php');
    exit;
  } else {
    $error = "Please fill in all fields.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Ask a Question</title>
  <link rel="stylesheet" href="assets/style.css">
  <script src="assets/jquery.min.js"></script>
</head>
<body>
  <header>
    <h1>Ask a Question</h1>
    <nav>
      <a href="index.php">Home</a>
      <a href="logout.php">Logout</a>
    </nav>
  </header>

  <main>
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="post" id="askForm">
      <label>Title:</label><br>
      <input type="text" name="title" required><br><br>

      <label>Description:</label><br>
      <textarea name="description" rows="8" required></textarea><br><br>

      <label>Tags (comma-separated):</label><br>
      <input type="text" name="tags"><br><br>

      <button type="submit">Submit Question</button>
    </form>
  </main>

  <script>
    $(document).ready(function () {
      $('#askForm').on('submit', function () {
        $(this).find('button').text('Submitting...').prop('disabled', true);
      });
    });
  </script>
</body>
</html>
