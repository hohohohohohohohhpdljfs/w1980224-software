<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json; charset=utf-8");

error_reporting(E_ALL);
ini_set("display_errors", 1);

session_start();
require_once __DIR__ . "/../config.php";


//stop here if browser is jsut checking API access
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

//Stop is user is not logged in
if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["error" => "Not logged in"]);
    exit;
}

//get logged in users ID
$user_id = (int)$_SESSION["user_id"];

try {
    //set today, start of the week, and start of the month
    $today = date("Y-m-d");
    $weekStart = date("Y-m-d", strtotime("monday this week"));
    $monthStart = date("Y-m-01");

    //count total habits
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM habits
        WHERE user_id = ?
");
$stmt->execute([$user_id]);
$totalHabits = (int) $stmt->fetchColumn();

//count completed habits this week
$stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM habit_logs
        WHERE user_id = ?
        AND completed = 1
        AND log_date BETWEEN ? AND ?
");
$stmt->execute([$user_id, $weekStart, $today]);
$weeklyCompleted = (int) $stmt->fetchColumn();

//count completed habits this month 
$stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM habit_logs
        WHERE user_id = ?
        AND completed = 1
        AND log_date BETWEEN ? AND ?
");
$stmt->execute([$user_id, $monthStart, $today]);
$monthlyCompleted = (int) $stmt->fetchColumn();

//work out how many days have passed so far
$daysSoFarWeek = (int) date("N");
$daysSoFarMonth = (int) date("j");

//calcuate possible target completions
$weeklyTarget = max(1, $totalHabits * $daysSoFarWeek);
$monthlyTarget = max(1, $totalHabits * $daysSoFarMonth);

//calcuate success rates
$weeklySuccessRate = $totalHabits > 0
? round(($weeklyCompleted / $weeklyTarget) * 100)
: 0;

$monthlySuccessRate = $totalHabits > 0
? round(($monthlyCompleted / $monthlyTarget) * 100)
: 0;

//find best habit this week
$stmt = $pdo->prepare("
        SELECT h.title, COUNT(*) AS completed_count
        FROM habit_logs hl
        INNER JOIN habits h ON h.id = hl.habit_id
        WHERE hl.user_id = ?
        AND hl.completed = 1
        AND hl.log_date BETWEEN ? AND ?
        GROUP BY h.id, h.title
        ORDER BY completed_count DESC, h.title ASC
        LIMIT 1
");
//get all habits with their weekly completion count
$stmt->execute([$user_id, $weekStart, $today]);
$bestHabit= $stmt->fetch(PDO::FETCH_ASSOC);


$stmt = $pdo->prepare("
        SELECT h.title, COUNT(hl.id) AS completed_count
        FROM habits h 
        LEFT JOIN habit_logs hl
        ON hl.habit_id = h.id
        AND hl.user_id = h.user_id
        AND hl.completed = 1
        AND hl.log_date BETWEEN ? AND ?
        WHERE h.user_id = ?
        GROUP BY h.id, h.title
        ORDER BY completed_count ASC, h.title ASC
");

$stmt->execute([$weekStart, $today, $user_id]);
$weakestHabitRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

//check if there are any weekly habit data
$hasWeeklyHabitData = $weeklyCompleted > 0;

if (!$hasWeeklyHabitData) {
    $bestHabit = null;
    $weakestHabit = null;
} else {
    //get all completion counts
    $counts = array_map(fn($row) => (int)["completed_count"], $weakestHabitRows);
    $minCount = min($counts);
    $maxCount = max($counts);

    //if all habits are equal, no weakest habit
    if ($minCount == $maxCount) {
        $weakestHabot = ["title" => "No habits need improvement right now"];  
    } else {
        //find lowest peforming habits
        $lowestHabits = array_vlaues(array_filter($weakestHabitRows, function ($row) use ($minCount) {
            return (int)$row["completed_count"] === $minCount;
        }));

        if (count($lowestHabits) > 1) {
            $weakestHabit = ["title" => "Mutiple habits tied"];
        } else {
            $weakestHabit = $lowestHabits[0];
        }
     }
    }

    //count active days this month
$stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT log_date)
        FROM habit_logs
        WHERE user_id = ?
        AND completed = 1
        AND log_date BETWEEN ? AND ?
");
$stmt->execute([$user_id, $monthStart, $today]);
$activeDays = (int) $stmt->fetchColumn();

//start chart from 6 days ago
$chartStart = date("Y-m-d", strtotime("$today -6 days"));

//get daily completion tools for chart
$stmt = $pdo->prepare("
        SELECT log_date, COUNT(*) AS total
        FROM habit_logs
        WHERE user_id = ?
        AND completed = 1
        AND log_date BETWEEN ? AND ?
        GROUP BY log_date
        ORDER BY log_date ASC
");
$stmt->execute([$user_id, $chartStart, $today]);
$chartRows= $stmt->fetchAll(PDO::FETCH_ASSOC);

//store chart tools by date
$chartMap = [];
foreach ($chartRows as $row) {
    $chartMap[$row["log_date"]] = (int) $row["total"];
}

//build 7 day chart data
$chartData = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date("Y-m-d", strtotime("$today -$i days"));
    $chartData[] = [
        "day" => date("D", strtotime($date)),
        "count" => $chartMap[$date] ?? 0
    ];
}
//return report data as JSON
echo json_encode([
    "weekly" => [
        "completed" => $weeklyCompleted,
        "successRate" => $weeklySuccessRate,
         "bestHabit" => $bestHabit["title"] ?? "No data yet",
         "weakestHabit" => $weakestHabit["title"] ?? "No data yet"
    ],
    "monthly" => [
        "completed" => $monthlyCompleted,
        "successRate" => $monthlySuccessRate,
        "activeDays" => $activeDays

    ],
    "chart" => $chartData

    ]);

} catch (Throwable $e) {
    //return error if report fails
    http_response_code(500);
    echo json_encode([
        "error" => "Failed to load report",
        "details" => $e->getMessage()
    ]);
}

