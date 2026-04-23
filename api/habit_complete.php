<?php
session_start ();
require_once __DIR__ . "/../config.php";

date_default_timezone_set("Europe/London");

header("Access-Control-Allow-Origin: https://w1980224.users.ecs.westminster.ac.uk");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json; charset=utf-8");

//if browser is just checking access, stop here
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

//stop is user is not logged in 
if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["error" => "Not logged in"]);
    exit;
}

//get logged in user ID
$user_id = (int)$_SESSION["user_id"];

//POST = mark habit as completed today
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true) ?? [];
    $habit_id = (int)($data["habit_id"] ?? 0);

    //check habit ID is valid
    if ($habit_id <= 0) {
        http_response_code(400);
        echo json_encode(["error" => "Habit id is required"]);
        exit;
    }

    try {
        //check if this habit was already completed today
    $check = $pdo->prepare("SELECT id FROM habit_logs WHERE habit_id = ? AND user_id = ? AND log_date = CURDATE() LIMIT 1");
    $check->execute([$habit_id, $user_id]);
    $existingId = $check->fetchColumn();

    // if already completed, return exisiting record
    if ($existingId) {
        echo json_encode([
            "ok" => true,
            "id" => (int)$existingId,
            "message" => "Habit already completed today"
        ]);
        exit;
    }
    
    //save todays completion in the database
    $stmt = $pdo->prepare("
    INSERT INTO habit_logs (habit_id, user_id, log_date, completed)
    VALUES (?, ?, CURDATE(), 1)
  ");

  $stmt->execute([$habit_id, $user_id]);
  
  //return success message
     echo json_encode([
          "ok" => true, 
          "id" => (int)$pdo->lastInsertId()
          ]);
       } catch (Throwable $e) {
        //return error if something goes wrong
           http_response_code(500);
           echo json_encode([
           "error" => $e->getMessage()
        ]);
    }
    exit;
  }

  //DELETE = remove todays completion
   if ($_SERVER["REQUEST_METHOD"] === "DELETE") {
    //read JSON data from the frontend
    $data = json_decode(file_get_contents("php://input"), true) ?? [];
    $habit_id = (int)($data["habit_id"] ?? 0);

    //check habit ID is valid
    if ($habit_id <= 0) {
        http_response_code(400);
        echo json_encode(["error" => "Habit id is required"]);
        exit;
      }

try {
    //delete todays completion for this habit
    $stmt = $pdo->prepare("
    DELETE FROM habit_logs
    WHERE habit_id = ?
    AND user_id = ?
    AND log_date = CURDATE()
");

   $stmt->execute([$habit_id, $user_id]);

    //return success message
   echo json_encode([
          "ok" => true, 
          "deleted_rows" => $stmt->rowCount()
        ]);
       } catch (Throwable $e) {
        //return error if something goes wrong
           http_response_code(500);
           echo json_encode([
           "error" => $e->getMessage()
        ]);
    }

 exit;
   }

   // if request type is not allowed, return error
http_response_code(405);
echo json_encode(["error" => "Method not allowed"]);


