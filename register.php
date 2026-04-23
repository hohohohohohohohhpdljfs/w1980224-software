<?php
<<<<<<< HEAD
 
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
=======
session_start();
>>>>>>> aa9639b (Final version for submission)
require_once __DIR__ . "/config.php";

$error = "";
$success = "";

$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $password = $_POST["password"] ?? "";
  $confirm = $_POST["confirm_password"] ?? "";

  if ($name === "" || $email === "" || $password === "" || $confirm === "") {
    $error = "Please fill in all fields.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  $error = "Please enter a valid email.";
} elseif (strlen($password) < 6) {
  $error = "Password must be at least 6 characters.";
} elseif ($password !== $confirm) {
  $error = "Passwords do not match.";
} else {
  $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
  $stmt->execute([$email]);

  if ($stmt->fetch()) {
    $error = "That email is already registered.";
  } else {
    $hash = password_hash($password, PASSWORD_DEFAULT);

<<<<<<< HEAD
    $stmt = $pdo->prepare(
      "INSERT INTO users (name, email, password_hash) VALUES (?,?,?)"
      );
=======
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash) VALUES (?,?,?)");
>>>>>>> aa9639b (Final version for submission)
    $stmt->execute([$name, $email, $hash]);

    $_SESSION["user_id"] = (int)$pdo->lastInsertId();
    $_SESSION["user_name"] = $name;

    header("Location: dashboard.php");
    exit;
  
}
}
}
?>
<!doctype html>
<html lang="en">

<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Register Habit Tracker</title>
<link rel="stylesheet" href="style.css">
</head>


<<<<<<< HEAD
<body>
<div class="wrap">
<div class="brand">
<div class="logo"></div>
=======
<body class="auth-page">

<div class="wrap auth-wrap">
<a href="/app/#/" class="home-btn">  Homepage</a>


<div class="brand">
>>>>>>> aa9639b (Final version for submission)
<h1>Create your account</h1>
<p>Start small, stay consistent. Your habits live here.</p>
</div>

<<<<<<< HEAD
<div class="card">
=======
<div class="card auth-card">
>>>>>>> aa9639b (Final version for submission)
<?php if ($error): ?>
<div class="alert error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>


<?php if ($success): ?>
<div class="alert success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<form method="post" novalidate>
<div class="grid">
<div class="field">
<label for="name">Name</label>
<input id="name" name="name" placeholder="e.g., Lilli " value="<?= htmlspecialchars($name) ?>" required />
</div>

<div class="field">
<label for="email">Email</label>
<input id="email" name="email" type="email" placeholder="you@example.com" value="<?= htmlspecialchars($email) ?>" required />
</div>

<div class="row">

<div class="field">
<label for="password">Password</label>
<input id="password" name="password" type="password" 
placeholder="Minimum 6 characters" required />
</div>

<div class="field">
<label for="confirm_password">Confirm</label>
<input id="confirm_password" name="confirm_password" type="password" 
placeholder="Repeat password" required />
</div>
</div>

<button class="btn" type="submit">Create account</button>
</div>
</form>

<div class="footer">
Already have an account? <a href="login.php">Login</a>
</div>

<div class="tiny">
By creating an account, you agree to keep it cute and consistent
</div>
</div>
</div>
</body>
</html>

