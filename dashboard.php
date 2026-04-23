<?php
session_start();

require_once __DIR__ . "/config.php";

//Check if user is logged in securely 
>>>>>>> aa9639b (Final version for submission)
 if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
 }

 require_once __DIR__ . "/config.php";

 $name = $_SESSION["user_name"] ?? "User";

 $previewMode = !isset($pdo) || !$pdo;
 ?>


 $name = $_SESSION["user_name"] ?? "User";
 $user_id = (int)$_SESSION["user_id"];

 //counts habits with reminders that are not completed today
 $stmt = $pdo->prepare("
       SELECT COUNT(*)
       FROM habits h
       WHERE h.user_id = ?
       AND h.reminder_time IS NOT NULL
       AND NOT EXISTS (
           SELECT 1
           FROM habit_logs h1
           WHERE h1.habit_id = h.id
           AND h1.user_id = h.user_id
           AND h1.log_date = curdate()
           and h1.completed = 1
        )
    
 ");
 $stmt->execute([$user_id]);
 $remindersToday = (int)$stmt->fetchColumn();
  
//get upcoming reminder (earliest time)
 $stmt = $pdo->prepare("
       SELECT h.title, h.reminder_time
       FROM habits h
       WHERE h.user_id = ?
       AND h.reminder_time IS NOT NULL
       AND NOT EXISTS (
           SELECT 1
           FROM habit_logs h1
           WHERE h1.habit_id = h.id
           AND h1.user_id = h.user_id
           AND h1.log_date = curdate()
           and h1.completed = 1
        ) 
    ORDER BY h.reminder_time ASC
    LIMIT 1 
");

$stmt->execute([$user_id]);
$nextReminder = $stmt->fetch(PDO::FETCH_ASSOC);

//count incomplete habits today
$stmt = $pdo->prepare("
       SELECT COUNT(*)
       FROM habits h
       WHERE h.user_id = ?
       AND NOT EXISTS (
           SELECT 1
           FROM habit_logs h1
           WHERE h1.habit_id = h.id
           AND h1.user_id = h.user_id
           AND h1.log_date = curdate()
           and h1.completed = 1
        ) 
");
$stmt->execute([$user_id]);
$incompleteToday = (int)$stmt->fetchColumn();

//total number of habits
$stmt = $pdo->prepare("
       SELECT COUNT(*)
       FROM habits 
       WHERE user_id = ?
    
 ");
 $stmt->execute([$user_id]);
 $totalHabits = (int)$stmt->fetchColumn();

 //count completed today
 $stmt = $pdo->prepare("
       SELECT COUNT(*)
       FROM habit_logs
       WHERE user_id = ?
        AND log_date = CURDATE()
        AND completed = 1
 ");

 $stmt->execute([$user_id]);
 $completedToday = (int)$stmt->fetchColumn();

 //calcuate weekly activty 
 $stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT log_date)
    FROM habit_logs
    WHERE user_id = ?
    AND completed = 1
    AND log_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
  ");
  $stmt->execute([$user_id]);
  $bestStreak = (int)$stmt->fetchColumn();

  //progress calcuation
 $progressPercent = $totalHabits > 0
     ? round(($completedToday / $totalHabits) * 100)
     : 0;

     //rule based feedback system (behaviour prediction)
 $dashboardMessage = "";

     if ($totalHabits === 0) {
        $dashboardMessage = "Start by adding your first habit.";
     } elseif ($bestStreak >= 5) {
        $dashboardMessage = "🔥 Amazing - you've been active for {$bestStreak} days!";
     } elseif ($completedToday === $totalHabits) {
        $dashboardMessage = "Amazing $name! You've completed all habits completed today!";
     } elseif ($completedToday > 0) {
        $dashboardMessage = "Nice work $name - you're building consistency.";
     } else {
         $dashboardMessage = "$name, start small today and build momentum.";
     }
    
//Badge system 
$badgeLabel = "";
$badgeEmoji = "";

 if ($totalHabits === 0) {
    $badgeLabel = "New Here";
    $badgeEmoji = "🍃";
 } else if ($completedToday === $totalHabits && $totalHabits >0) {
    $badgeLabel = "All done";
    $badgeEmoji = "🏆";
 } elseif ($bestStreak >= 5) {
    $badgeLabel = "Consistent";
    $badgeEmoji = "🔥";
 } elseif ($completedToday > 0) {
    $badgeLabel = "Great start";
    $badgeEmoji = "⭐";
 } else {
    $badgeLabel = "Keep going";
    $badgeEmoji = "✨";
 }
 ?>

>>>>>>> aa9639b (Final version for submission)
 <!doctype html>
 <html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Dashboard - HabitFlow</title>

        <link rel="stylesheet" href="style.css">
</head>

 <body>
        <div class="shell">
        
    <aside class="left">

        <div class="brandRow">
        <div class="brandLogo"></div>
        <div>
            <div class="brandName">HabitFlow</div>
            <div class="brandSub">Build better habits, gently.</div>
</div>
</div>

<div class="searchWrap">
    <input placeholder="Search..." />
    <div class="searchIcon"></div>
</div>

<div class="sideTabs">
    <a class="sideTab active" href="#">Today</a>
    <a class="sideTab" href="#">Archive</a>
</div>

<div class="sideCard">
    <div class="sideCardTitle">Quick tip</div>
    <div class="sideCardSub">
    Keep it small. One tiny habit repeated daily beats a big habit you avoid.
</div>
<a class="sideBtn" href="#">Open guide</a>
</div>
</aside>

<main class="main">
    <div class="mainTop">
        <div class="mainTitle">Today</div>
        <div class="mainTitle"></div>
</div>

<section class="cards">
    <a class="cardBig gradPurple" href="#">
        <div class="iconBox"></div>
    <div>
        <div class="cardTitle">How to prepare fruits and vegetables</div>
        <div class="cardSub">Read today's lesson</div>
</div>
</a>

<a class="cardBig gradCyan" href="#">
    <div class="iconBox"></div>
    <div>
        <div class="cardTitle">Track your habits</div>
        <div class="cardSub">Quick daily check-ins</div>
</div>
</a>
 <a class="cardSmall gradPink" href="#">
    <div class="iconBox"></div>
    <div>
        <div class="cardTitle">Portions</div>
        <div class="cardSub">Your new habit</div>
</div>
</a>

<a class="cardSmall darkGrey" href="#">
    <div class="iconBox"></div>
    <div>
        <div class="cardTitle">Our philosophy</div>
        <div class="cardSub">What to expect</div>
</div>
</a>
</section>
</main>

<aside class="right">
    <div class="profile">
        <div class="welcome">Welcome to your dashboard,</div>
        <div class="nameBig><?= htmlspecialchars($name) ?>!</div>

        <div class="pfp"></div>
        <a class="viewProfile" href="#">View profile</a>
    <hr>

    <div class="menu">
        <div class="menuItem">👤 Account Setttings</div>
        <div class="menuItem active">👩‍💻 Information</div>
        <div class="menuItem">🏃 Habit Tracker</div>
        <div class="menuItem">🙌 Rewards</div>
        <div class="menuItem">🕓 Archives</div>
        <div class="menuItem">👩‍👩‍👧‍👦 Community</div>
        <div class="menuItem">✏️ Quickstart guide</div>
=======

        <link rel="stylesheet" href="style.css?v=<?= time() ?>">
  </head>

   <body>
    
   <div class="shell">

 <aside class="right">
    
    <div class ="brandRow">
        <div class="brandLogo"><div>
      </div>
        
        </div>
    <div class="profile">
        <div class="welcome">
            Welcome back, <?= htmlspecialchars($name) ?>!
      </div>
      

    <div class="pfp">
        <?= strtoupper(substr($name, 0, 1)) ?>
    </div>
    
    <div class="profileSub">Stay consistent, one habit at a time.</div>
    <div class="userBadge">
        <span class="badgeEmoji"><?= $badgeEmoji ?></span>
        <span class="badgeText"><?= htmlspecialchars($badgeLabel) ?></span>
</div>

    <div class="miniStats">
        <div class="miniStatBox">
            <strong><?= $totalHabits ?></strong>
            <span>Habits</span>
       </div>

       <div class="miniStatBox">
        <strong><?= $incompleteToday ?></strong>
        <span>Left</span>
    </div>

    <div class="miniStatBox">
        <strong><?= $completedToday ?></strong>
        <span>Done</span>
    </div>
</div>
    <hr>

    <div class="menu">
        <a class="menuItem active" href="dashboard.php"> Dashboard</a>
        <a class="menuItem" href="tracker.php"> Habit Tracker</a>
        <a class="menuItem" href="tracker.php?route=calendar">Calendar</a>
        <a class="menuItem" href="tracker.php?route=reports"> Reports</a>
>>>>>>> aa9639b (Final version for submission)
</div>

<hr>

<a class="logout" href="login.php">Log out</a>
</div>
</aside>


</div>

=======
<main class="main">
    <div class="mainTop">
        <div class="mainTitle">Today's Overview</div>
        <div class="mainTitle"></div>
</div>

<section class="cards">

    <a class="cardBig gradPurple" href="#">
        <div class="iconBox iconReminders">

        <svg xmlns="http://www.w3.org/2000/svg" width="24" 
        height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" 
        class="lucide lucide-bell-icon lucide-bell">
        <path d="M10.268 21a2 2 0 0 0 3.464 0"/><path d="M3.262 15.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.673C19.41 13.956 18 12.499 18 8A6 6 0 0 0 6 8c0 4.499-1.411 5.956-2.738 7.326"/>
    </svg>
</div>
    <div>
        <div class="cardTitle">Today's Reminders</div>

        <div class="cardSub">
            <strong><?= $remindersToday ?></strong>
            reminder<?= $remindersToday === 1 ? '' : 's' ?> today
</div>

<div class="cardSub">
    Next: <?=$nextReminder 
        ? htmlspecialchars($nextReminder["title"]) . " at " . substr($nextReminder["reminder_time"], 0, 5)
        : "None" ?>
</div>

<div class="cardSub">
    <strong><?= $incompleteToday ?></strong>
    habit<?= $incompleteToday === 1 ? '' : 's' ?> incomplete
    
</div>
</div>

</a>

<a class="cardBig progressCard" href="#">
   <div>
       <div class="cardTitle">Today's progress</div>

       <div class="progressBar">
           <div class="progressFill" style="width: <?= $progressPercent ?>%;"></div>
</div>

<div class="cardSub">
    <strong><?= $completedToday ?></strong> of 
    <strong><?= $totalHabits ?></strong>
    habits completed (<?= $progressPercent ?>%)
</div>

<div class="cardSub">
    <?= htmlspecialchars($dashboardMessage) ?>
</div>
</div>

</a>

<a class="cardBig gradCyan" href="tracker.php">
    <div class="iconBox iconTrack">

    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" 
    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
    class="lucide lucide-square-check-big-icon lucide-square-check-big">
    <path d="M21 10.656V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h12.344"/><path d="m9 11 3 3L22 4"/>
</svg>

    </div>
    <div>
        <div class="cardTitle">Track your habits</div>
        <div class="cardSub">Quick daily check-ins</div>
</div>
</a>


 <a class="cardSmall gradPink" href="#">
    <div class="iconBox iconStreak">

    <svg <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" 
    fill="none" stroke="currentColor" stroke-width="2"
    stroke-linecap="round" stroke-linejoin="round" 
    class="lucide lucide-flame-icon lucide-flame">
    <path d="M12 3q1 4 4 6.5t3 5.5a1 1 0 0 1-14 0 5 5 0 0 1 1-3 1 1 0 0 0 5 0c0-2-1.5-3-1.5-5q0-2 2.5-4"/>
</svg>
    </div>
    <div>
        <div class="cardTitle">Streaks</div>
        <div class="cardSub">
            Active this week: <strong><?= $bestStreak ?></strong> day<?= $bestStreak === 1 ? '' : 's' ?>
    </div>
</div>
</a>

<a class="cardSmall darkGrey" href="tracker.php?route=calendar">
    <div class="iconBox iconCalendar">

    <svg xmlns="http://www.w3.org/2000/svg" 
    width="24" height="24" viewBox="0 0 24 24" fill="none" 
    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
    class="lucide lucide-calendar-check-icon lucide-calendar-check"><path d="M8 2v4"/>
    <path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="m9 16 2 2 4-4"/>
</svg>
</div>
    <div>
        <div class="cardTitle">Calendar</div>
        <div class="cardSub">What to expect</div>
</div>
</a>

<a class="cardSmall gradReport" href="tracker.php?route=reports">
    <div class="iconBox iconReport">

    <svg xmlns="http://www.w3.org/2000/svg"
    width="24" height="24" viewBox="0 0 24 24" fill="none"
    stroke="currentColor" stroke-width="2a" stroke-linecap="round" stroke-linejoin="round"
    class="lucide lucide-chart-column">
    <path d="M3 3v18h18"/>
    <path d="M18 17V9"/>
    <path d="M13 17V5"/>
    <path d="M8 17v-3"/>
</svg>
</div>
<div>
    <div class="cardTitle">Habit Report</div>
    <div class="cardSub">Weekly & monthly insights</div>
</div>
</a>



</section>
</main>


</div>

<div class="cardOverlay" id="cardOverlay">
    <div class="cardPopup" id="cardPopup">
        <button class="closePopup" id="closePopup">&times;</button>
        <div class="popupIcon" id="popupIcon"></div>
        <h2 id="popupTitle"></h2>
        <p id="popupText"></p>
</div>
</div>

<script>
    const cards = document.querySelectorAll(".interactiveCard");
    const overlay = document.getElementById("cardOverlay");
    const popupTitle = document.getElementById("popupTitle");
    const popupText = document.getElementById("popupText");
    const closePopup = document.getElementById("closePopup");

    cards.forEach(card => {
        card.addEventListener("click", function (e) {
            const href = card.getAttribute("href");

            if (href === "#" || href === "" || href === null) {
                e.preventDefault();
          } else if (href.includes("/reports")) {
            return;
         } else if (href.includes("tracker.php")) {
            return;
       } else {
        e.preventDefault();
         }

         popupTitle.textContent = card.dataset.title || "Card";
         popupText.textContent = card.dataset.text || "More details here.";
         overlay.classList.add("show");
     });
   });
        closePopup.addEventListener("click", () => {
            overlay.classList.remove("show");
        });

        overlay.addEventListener("click", (e) => {
            if (e.target === overlay) {
                overlay.classList.remove("show");
            }
        });

        document.addEventListener("keydown", (e) => {
            if (e.key === "Escape") {
                overlay.classList.remove("show");
            }
        });
</script>
</body>
</html>

