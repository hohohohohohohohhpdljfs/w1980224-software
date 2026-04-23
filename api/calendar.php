
<?php
session_start ();
require_once __DIR__ . "/../config.php";

//allow frontend requests during development
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json; charset=utf-8");

//handle broswer permission check (OPTION requests) to allow communication between the frontend and backend.
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

//block unauthenticated users from accessing calendar data
if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["error" => "Not logged in"]);
    exit;
}

//get logged in users ID from the session
$user_id = (int)$_SESSION["user_id"];

//decide whether frontend wants day view data or month data
$type = $_GET["type"] ?? "day";

if ($type == "day") {
    //use selected date from the frontend, otherwise default to today 
    $date = $_GET["date"] ?? date("Y-m-d");

    //load habits for this user and show whether each one was completed on that date 
    $stmt = $pdo->prepare("
        SELECT
        h.id,
        h.title,
        h.category,
        h.reminder_time,
        h.created_at, 
        COALESCE(h1.completed, 0) AS completed
        FROM habits h 
        LEFT JOIN habit_logs h1
        ON h1.habit_id = h.id
        AND h1.user_id = h.user_id
        AND h1.log_date = ?
    WHERE h.user_id = ?
    AND DATE(h.created_at) <= ?
    ORDER BY h.id DESC
     ");
        
        //run query and get results
        $stmt->execute([$date, $user_id, $date]);
        $habits = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //send back habits for that day
        echo json_encode([
            "type" => "day",
            "date" => $date,
            "habits" => $habits
        ]);
        exit;
    }

        if ($type == "month") {
            //get selected month or use current month
            $month = $_GET["month"] ?? date("Y-m");
            //get start and end dates of that month
            $startDate = $month . "-01";
            $endDate = date("Y-m-t", strtotime($startDate));

        //count how many babits were completed each day 
        $stmt = $pdo->prepare("
             SELECT
                 h1.log_date,
                 COUNT(*) AS total_completed
                FROM habit_logs h1
                INNER JOIN habits h
                    ON h.id = h1.habit_id
                WHERE h1.user_id = ?
                AND h1.completed = 1
                AND h1.log_date BETWEEN ? AND ?
                GROUP BY h1.log_date
                ORDER BY h1.log_date ASC
     ");
        
        //run query and get results
        $stmt->execute([$user_id, $startDate, $endDate]);
        $days = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        //send back monhly data
        echo json_encode([
            "type" => "month",
            "month" => $month,
            "days" => $days
        ]);
        exit;
     }

     //if the request is invalid, return error
http_response_code(400);
echo json_encode(["error" => "Invalid type"]);
