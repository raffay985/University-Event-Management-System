<?php

require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET["id"])) {
    header("Location: registered_events.php");
    exit();
}

$registration_id = (int) $_GET["id"];
$user_id = $_SESSION["user_id"];

/* Get logged-in student's ID */
$stmt = $conn->prepare(
    "SELECT student_id
     FROM students
     WHERE user_id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    die("Student record not found.");
}

$student_id = $student["student_id"];

/* Delete only this student's registration */
$stmt = $conn->prepare(
    "DELETE FROM registrations
     WHERE registration_id = ?
     AND student_id = ?"
);

$stmt->bind_param(
    "ii",
    $registration_id,
    $student_id
);

$stmt->execute();

header("Location: registered_events.php");
exit();

?>