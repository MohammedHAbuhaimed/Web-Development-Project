<?php
// register.php
require __DIR__ . '/config.php';    // session + $pdo
include __DIR__ . '/header.php';    // HTML head, nav, <main>

$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /* ------------------------------------------------------------------
       1. Collect & sanitize input
    ------------------------------------------------------------------ */
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password =        $_POST['password']        ?? '';
    $confirm  =        $_POST['confirm_password'] ?? '';

    /* ------------------------------------------------------------------
       2. Basic validation
    ------------------------------------------------------------------ */
    if ($username === '')                       $errors[] = "Username is required.";
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = "A valid email is required.";
    if (strlen($password) < 6)                  $errors[] = "Password must be at least 6 characters.";
    if ($password !== $confirm)                 $errors[] = "Passwords do not match.";

    /* ------------------------------------------------------------------
       3. Uniqueness check
    ------------------------------------------------------------------ */
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $errors[] = "Username or email already taken.";
        }
    }

    /* ------------------------------------------------------------------
       4. Create account
    ------------------------------------------------------------------ */
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $ins  = $pdo->prepare("
            INSERT INTO users (username, email, password)
            VALUES (?, ?, ?)
        ");
        $ins->execute([$username, $email, $hash]);

        /* --------------------------------------------------------------
           5. Auto‑login: store BOTH id and username in the session
        -------------------------------------------------------------- */
        $_SESSION['user_id']   = $pdo->lastInsertId();
        $_SESSION['username']  = $username;          // ← NEW LINE

        header('Location: index.php');
        exit;
    }
}
?>

<h2>Register</h2>

<?php if ($errors): ?>
    <ul style="color: red;">
        <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="register.php">
    <label>
        Username:<br>
        <input type="text" name="username"
            value="<?= htmlspecialchars($username ?? '') ?>" required>
    </label>
    <br><br>

    <label>
        Email:<br>
        <input type="email" name="email"
            value="<?= htmlspecialchars($email ?? '') ?>" required>
    </label>
    <br><br>

    <label>
        Password:<br>
        <input type="password" name="password" required>
    </label>
    <br><br>

    <label>
        Confirm Password:<br>
        <input type="password" name="confirm_password" required>
    </label>
    <br><br>

    <button type="submit">Register</button>
</form>

<?php
// close <main>, footer, </body></html>
include __DIR__ . '/footer.php';
?>