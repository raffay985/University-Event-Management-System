<?php

require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET["id"])) {
    header("Location: students.php");
    exit();
}

$id = (int) $_GET["id"];

$stmt = $conn->prepare(
    "SELECT user_id FROM students WHERE student_id = ?"
);

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$student = $result->fetch_assoc();

if ($student) {

    $user_id = $student["user_id"];

    $stmt = $conn->prepare(
        "DELETE FROM users WHERE user_id = ?"
    );

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
}

header("Location: students.php");
exit();

?>