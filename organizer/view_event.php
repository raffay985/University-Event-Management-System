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

$stmt = $conn->prepare(
    "SELECT *
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

?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Event Information</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

</head>

<body>

<h1>Event Information</h1>

<div class="card">

    <h2>
        <?php echo htmlspecialchars($event["event_title"]); ?>
    </h2>

    <p>
        <strong>Description:</strong><br>
        <?php echo htmlspecialchars($event["description"]); ?>
    </p>

    <p>
        <strong>Category:</strong><br>
        <?php echo htmlspecialchars($event["category"]); ?>
    </p>

    <p>
        <strong>Date:</strong><br>
        <?php echo htmlspecialchars($event["event_date"]); ?>
    </p>

    <p>
        <strong>Start Time:</strong><br>
        <?php echo htmlspecialchars($event["start_time"]); ?>
    </p>

    <p>
        <strong>End Time:</strong><br>
        <?php echo htmlspecialchars($event["end_time"]); ?>
    </p>

    <p>
        <strong>Venue:</strong><br>
        <?php echo htmlspecialchars($event["venue"]); ?>
    </p>

    <p>
        <strong>Maximum Participants:</strong><br>
        <?php echo $event["max_participants"]; ?>
    </p>

    <p>
        <strong>Approval Status:</strong><br>
        <?php echo ucfirst(htmlspecialchars($event["approval_status"])); ?>
    </p>

</div>

<a href="edit_event.php?id=<?php echo $event["event_id"]; ?>">
    Edit Event
</a>

<br><br>

<a href="my_events.php">
    Back to My Events
</a>

</body>

</html>