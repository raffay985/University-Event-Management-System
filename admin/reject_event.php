<?php

require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET["id"])) {
    header("Location: event_approval.php");
    exit();
}

$event_id = (int) $_GET["id"];

/* Get event and organizer */
$stmt = $conn->prepare(
    "SELECT event_title, organizer_id
     FROM events
     WHERE event_id = ?"
);

$stmt->bind_param("i", $event_id);
$stmt->execute();

$result = $stmt->get_result();
$event = $result->fetch_assoc();

if ($event) {

    /* Reject event */
    $stmt = $conn->prepare(
        "UPDATE events
         SET approval_status = 'rejected'
         WHERE event_id = ?"
    );

    $stmt->bind_param("i", $event_id);
    $stmt->execute();

    /* Notify organizer */
    if ($event["organizer_id"] !== null) {

        $message =
            "Your event '" .
            $event["event_title"] .
            "' has been rejected.";

        $type = "event_rejection";

        $stmt = $conn->prepare(
            "INSERT INTO notifications
            (user_id, type, message)
            VALUES (?, ?, ?)"
        );

        $stmt->bind_param(
            "iss",
            $event["organizer_id"],
            $type,
            $message
        );

        $stmt->execute();
    }
}

header("Location: event_approval.php");
exit();

?>