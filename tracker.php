
<?php
session_start();

//get route from URL 
$route = $_GET["route"] ?? "tracker";
//remove any leading slashes for safety
$route = ltrim($route, "/");

//whitelist allowed routes (prevents invalid or malicious input)
$allowedRoutes = ["tracker", "calendar", "reports"];
if (!in_array($route, $allowedRoutes, true)) {
    $route = "tracker";
}

//check is user is authenticated before allowing access
 if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
 }
?>
<!doctype html>
<html lang="en">

<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Habit Tracker</title>
<style>
    html, body {
        margin: 0;
        padding: 0;
        width: 100%;
        height: 100%;
        background: #f7f4ef;
        overflow-y: auto;
    }
    
    .trackerFrame {
        width: 100%;
        height: 100vh;
        border: 0;
        display: block;
        background: transparent;
    }
  </style>
  </head>

  <body>

  //embed react frontend using iframe
    <iframe
          class="trackerFrame"
          src="/app/#/<?= htmlspecialchars($route) ?>"
          title="Habit tracker"
    ></iframe>
</body>
</html>

