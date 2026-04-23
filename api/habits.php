<?php
session_start ();
require_once __DIR__ . "/../config.php";

date_default_timezone_set("Europe/London");

header("Access-Control-Allow-Origin: https://w1980224.users.ecs.westminster.ac.uk");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

//stop if user is not logged in
if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["error" => "Not logged in"]);
    exit;
}

//get logged in users ID
$user_id = (int)$_SESSION["user_id"];

//check if habit is completed today
function isHabitDoneToday(PDO $pdo, int $habit_id, int $user_id): bool {
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM habit_logs
        WHERE habit_id = ?
        AND user_id = ?
        AND completed = 1 
        AND log_date = CURDATE()
        ");

        $stmt->execute([$habit_id, $user_id]);
        return (int)$stmt->fetchColumn() > 0;

}

//calcuate streak 
function getHabitStreak(PDO $pdo, int $habit_id, int $user_id): int {
    $stmt = $pdo->prepare("
        SELECT DISTINCT log_date
        FROM habit_logs
        WHERE habit_id = ?
        AND user_id = ?
        AND completed = 1
        ORDER BY log_date DESC
     ");
        $stmt->execute([$habit_id, $user_id]);
        
        $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!$dates) {
            return 0;
        }
        
        //steup dates
        $tz = new DateTimeZone("Europe/London");
        $today = new DateTime("today", $tz);
        $yesterday = (clone $today)->modify("-1 day");

        $firstDate = new DateTime($dates[0], $tz);
        
        //if last completion not recent, streak = 0
        if ( 
            $firstDate->format("Y-m-d") !== $today->format("Y-m-d") &&
            $firstDate->format("Y-m-d") !== $yesterday->format("Y-m-d")
        ) {
            return 0;
        }
    //count conseuctive days
    $streak = 0;
    $expectedDate = clone $firstDate;

        foreach ($dates as  $date) {
            $logDate = new DateTime($date, $tz);

            if ($logDate->format("Y-m-d") === $expectedDate->format("Y-m-d")) {
                $streak++;
                $expectedDate->modify("-1 day");
            } else {
                break;
            }
        }
        
        return $streak;
    
    }

    // count completion in last 7 days
    function getWeeklyCompletionCount(PDO $pdo, int $habit_id, int $user_id): int {
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM habit_logs
        WHERE habit_id = ?
        AND user_id = ?
        AND completed = 1 
        AND log_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        ");

        $stmt->execute([$habit_id, $user_id]);
        return (int)$stmt->fetchColumn();

    }
    
    //check if habit was done yesterday
    function wasHabitDoneYesterday(PDO $pdo, int $habit_id, int $user_id): bool {
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM habit_logs
        WHERE habit_id = ?
        AND user_id = ?
        AND completed = 1 
        AND log_date = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
        ");

        $stmt->execute([$habit_id, $user_id]);
        return (int)$stmt->fetchColumn() > 0;
    }

    //decide habit status (your behaviour prediction)
    function getHabitPrediction(int $streak, int $weeklyCount, bool $doneYesterday): string {
        if ($streak >= 5 || $weeklyCount >= 5) {
            return "On track";
        }

        if ($weeklyCount >= 3 && $doneYesterday) {
            return "Likely to continue";
        }

        if ($weeklyCount >= 1) {
            return "Needs attention";
        }

        return "At risk";
    }

     // GET = load all habits with stats
    if ($_SERVER["REQUEST_METHOD"] === "GET") {
      $stmt = $pdo->prepare("
        SELECT id, title, category, frequency, created_at, reminder_time
        FROM habits
        WHERE user_id = ?
        ORDER BY id DESC
        ");

        $stmt->execute([$user_id]);
        $habits = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($habits as &$habit) {

            $habitId = (int)$habit["id"];
           
            //add calcuated data to each habit
            $habit["done_today"] = isHabitDoneToday($pdo, $habitId, $user_id);
            $habit["streak"] = getHabitStreak($pdo, $habitId, $user_id);
            $habit["progress"] = $habit["done_today"] ? 1 : 0;

            $weeklyCount = getWeeklyCompletionCount($pdo, $habitId, $user_id);
            $doneYesterday = wasHabitDoneYesterday($pdo, $habitId, $user_id);

        //add behaviour prediction result
            $habit["weekly_count"] = $weeklyCount;
            $habit["done_yesterday"] = $doneYesterday;
            $habit["prediction"] = getHabitPrediction(
                (int)$habit["streak"],
                $weeklyCount, 
                $doneYesterday
            );
        }

        unset($habit);

        echo json_encode(["habits" => $habits]);
        exit;
    }


//POST = create new habit
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true) ?? [];
    $title = trim($data["title"] ?? ""); 
    $frequency = trim($data["frequency"] ?? "daily");
    $category = trim($data["category"] ?? "Health & Fitness");
    $reminder_time = $data["reminder_time"] ?? null;

    if ($reminder_time == "") {
        $reminder_time = null;
    }
   //validate input
    if ($title == "") {
        http_response_code(400);
        echo json_encode(["error" => "Habit title is required"]);
        exit;
    }

    try {
    $stmt = $pdo->prepare("INSERT INTO habits (user_id, title, category, frequency, reminder_time) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $title, $category, $frequency, $reminder_time]);

    echo json_encode([
        "ok" => true, 
        "id" => (int)$pdo->lastInsertId()
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            "error" => $e->getMessage()
        ]);
    }

    exit;
}

//put = update habit
if ($_SERVER["REQUEST_METHOD"] === "PUT") {
    $data = json_decode(file_get_contents("php://input"), true) ?? [];
    $id = (int)($data["id"] ?? 0);
    $title = trim($data["title"] ?? "");
    $category = trim($data["category"] ?? "Health & Fitness");
    $frequency = trim($data["frequency"] ?? "daily");
    $reminder_time = $data["reminder_time"] ?? null;

    if ($reminder_time == "") {
        $reminder_time = null;
    }

    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(["error" => "Habit id is required"]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE habits SET title = ?, category = ?, frequency = ?, reminder_time = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$title, $category, $frequency, $reminder_time, $id, $user_id]);
        
        echo json_encode([
            "ok" => true,
            "updated_rows" => $stmt->rowCount()
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            "error" => $e->getMessage()
        ]);
    }

    exit;
}

//delete = remove habit
if ($_SERVER["REQUEST_METHOD"] === "DELETE") {
    $data = json_decode(file_get_contents("php://input"), true) ?? [];
    $id = (int)($data["id"] ?? 0);

    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(["error" => "Habit id is required"]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM habits WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);

        echo json_encode([
            "ok" => true,
            "deleted_rows" => $stmt->rowCount()
        ]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            "error" => $e->getMessage()
        ]);
    }

    exit;
}

//if request type is not allowed
http_response_code(405);
echo json_encode(["error" => "Method not allowed"]);
