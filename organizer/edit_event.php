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
$message = "";


/* Get organizer's event */

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


/* Update event */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $event_title = trim($_POST["event_title"]);
    $description = trim($_POST["description"]);
    $category = trim($_POST["category"]);
    $event_date = $_POST["event_date"];
    $start_time = $_POST["start_time"];
    $end_time = $_POST["end_time"];
    $venue = trim($_POST["venue"]);
    $max_participants = (int) $_POST["max_participants"];


    if ($max_participants < 1) {

        $message = "Maximum participants must be at least 1.";

    } elseif ($end_time <= $start_time) {

        $message = "End time must be after start time.";

    } else {

        $stmt = $conn->prepare(
            "UPDATE events
             SET
                event_title = ?,
                description = ?,
                category = ?,
                event_date = ?,
                start_time = ?,
                end_time = ?,
                venue = ?,
                max_participants = ?
             WHERE event_id = ?
             AND organizer_id = ?"
        );

        $stmt->bind_param(
            "sssssssiii",
            $event_title,
            $description,
            $category,
            $event_date,
            $start_time,
            $end_time,
            $venue,
            $max_participants,
            $event_id,
            $organizer_id
        );

        if ($stmt->execute()) {

            $message = "Event updated successfully!";

            /* Reload updated information */

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

        } else {

            $message = "Could not update event.";
        }
    }
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

    <title>Edit Event</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

</head>

<body>

<h1>Edit Event</h1>

<a href="my_events.php">
    Back to My Events
</a>

<?php if ($message != "") { ?>

    <p class="success">
        <?php echo htmlspecialchars($message); ?>
    </p>

<?php } ?>

<form method="POST">

    <label>Event Title</label>

    <input
        type="text"
        name="event_title"
        value="<?php echo htmlspecialchars($event["event_title"]); ?>"
        required
    >

    <br><br>


    <label>Description</label>

    <textarea
        name="description"
        required
    ><?php echo htmlspecialchars($event["description"]); ?></textarea>

    <br><br>


    <label>Category</label>

    <input
        type="text"
        name="category"
        value="<?php echo htmlspecialchars($event["category"]); ?>"
        required
    >

    <br><br>


    <label>Event Date</label>

    <input
        type="date"
        name="event_date"
        value="<?php echo htmlspecialchars($event["event_date"]); ?>"
        required
    >

    <br><br>


    <label>Start Time</label>

    <input
        type="time"
        name="start_time"
        value="<?php echo htmlspecialchars($event["start_time"]); ?>"
        required
    >

    <br><br>


    <label>End Time</label>

    <input
        type="time"
        name="end_time"
        value="<?php echo htmlspecialchars($event["end_time"]); ?>"
        required
    >

    <br><br>


    <label>Venue</label>

    <input
        type="text"
        name="venue"
        value="<?php echo htmlspecialchars($event["venue"]); ?>"
        required
    >

    <br><br>


    <label>Maximum Participants</label>

    <input
        type="number"
        name="max_participants"
        min="1"
        value="<?php echo $event["max_participants"]; ?>"
        required
    >

    <br><br>


    <button type="submit">
        Update Event
    </button>

</form>

</body>

</html>