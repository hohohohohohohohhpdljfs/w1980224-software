<?php
session_start();
require_once __DIR__ . "/config.php";

$error = "";
$email= trim($_POST["email"] ?? "");

//handle form submission for user login
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $password = $_POST["password"] ?? "";

  //basic validation to stop empty form submission
  if ($email === "" || $password === "") {
    $error = "Please fill in all fields.";

    //validate email format before querying database
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email.";

    }else{
      //look up user by email address
        $stmt = $pdo->prepare("SELECT id, name, password_hash FROM users WHERE email = ?");
         $stmt->execute([$email]);
         $user =  $stmt->fetch();

         //verify user hashed password securely using password_verify
         if (!$user || !password_verify($password, $user["password_hash"])) {
            $error = "Email or password is incorrect.";
         } else {
            //store user session after successful authentication
            $_SESSION["user_id"] = (int)$user["id"];
            $_SESSION["user_name"] = $user["name"];
            //redirect authenticated user to dashboard
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
<title>Login Habit Tracker</title>
<link rel="stylesheet" href="style.css">
</head>


<body class="auth-page">

<div class="wrap auth-wrap">
<a href="/app/#/" class="home-btn">  Homepage</a>

<div class="brand">
<h1>Welcome back</h1>
<p>Login to continue tracking your habits</p>
</div>

<div class="card auth-card">
<?php if ($error): ?>
<div class="alert error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post" novalidate>
<div class="grid">
<div class="field">
<label for="email">Email</label>
<input id="email" name="email" type="email" placeholder="you@example.com" 
value="<?= htmlspecialchars($email) ?>" required />
</div>

<div class="field">
<label for="password">Password</label>
<input id="password" name="password" type="password" placeholder="Your password"  required />
</div>

<button class="btn" type="submit">Login</button>
</div>
</form>

<div class="footer">
New here? <a href="register.php">Create an account</a>
</div>
</div>
</div>
</body>
</html>
