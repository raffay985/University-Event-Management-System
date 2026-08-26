<?php

require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "organizer") {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET["id"])) {
    header("Location: my_events.php");
    exit();
}

$event_id = (int) $_GET["id"];
$organizer_id = $_SESSION["user_id"];


/* Get event and make sure it belongs to organizer */

$stmt = $conn->prepare(
    "SELECT
        event_title,
        event_date,
        start_time,
        venue,
        approval_status
     FROM events
     WHERE event_id = ?
     AND organizer_id = ?"
);

$stmt->bind_param(
    "ii",
    $event_id,
    $organizer_id
);

$stmt->execute();

$result = $stmt->get_result();
$event = $result->fetch_assoc();

if (!$event) {
    die("Event not found or access denied.");
}


/* Reminder can only be sent for approved events */

if ($event["approval_status"] !== "approved") {
    die("Reminder can only be sent for approved events.");
}


/* Get registered students */

$stmt = $conn->prepare(
    "SELECT DISTINCT
        u.user_id
     FROM registrations r

     INNER JOIN students s
        ON r.student_id = s.student_id

     INNER JOIN users u
        ON s.user_id = u.user_id

     WHERE r.event_id = ?"
);

$stmt->bind_param("i", $event_id);
$stmt->execute();

$students = $stmt->get_result();


/* Create reminder */

$type = "event_reminder";

$message =
    "Reminder: '" .
    $event["event_title"] .
    "' is scheduled for " .
    $event["event_date"] .
    " at " .
    $event["start_time"] .
    " in " .
    $event["venue"] .
    ".";


/* Send reminder to every registered student */

while ($student = $students->fetch_assoc()) {

    $user_id = $student["user_id"];

    $notify = $conn->prepare(
        "INSERT INTO notifications
        (
            user_id,
            type,
            message
        )
        VALUES (?, ?, ?)"
    );

    $notify->bind_param(
        "iss",
        $user_id,
        $type,
        $message
    );

    $notify->execute();
}


/* Return to organizer events */

header("Location: my_events.php?reminder=sent");
exit();

?>