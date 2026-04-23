<?php
session_start();
 if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
 }
 require_once __DIR__ . "/config.php";

 $name = $_SESSION["user_name"] ?? "User";

 $previewMode = !isset($pdo) || !$pdo;
 ?>
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
</div>

<hr>

<a class="logout" href="login.php">Log out</a>
</div>
</aside>

</div>

</body>
</html>

