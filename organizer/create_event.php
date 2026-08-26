<?php

require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "organizer") {
    header("Location: ../login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $organizer_id = $_SESSION["user_id"];

    $event_title = trim($_POST["event_title"]);
    $description = trim($_POST["description"]);
    $category = trim($_POST["category"]);
    $event_date = $_POST["event_date"];
    $start_time = $_POST["start_time"];
    $end_time = $_POST["end_time"];
    $venue = trim($_POST["venue"]);
    $max_participants = (int) $_POST["max_participants"];

    $approval_status = "pending";

    $stmt = $conn->prepare(
        "INSERT INTO events
        (
            organizer_id,
            event_title,
            description,
            category,
            event_date,
            start_time,
            end_time,
            venue,
            max_participants,
            approval_status
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "isssssssis",
        $organizer_id,
        $event_title,
        $description,
        $category,
        $event_date,
        $start_time,
        $end_time,
        $venue,
        $max_participants,
        $approval_status
    );

    if ($stmt->execute()) {

        $message = "Event created successfully and sent for Admin approval!";

    } else {

        $message = "Could not create event.";
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

    <title>Create Event</title>

    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<h1>Create New Event</h1>

<a href="dashboard.php">
    Back to Organizer Dashboard
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
        required
    >

    <br><br>

    <label>Description</label>

    <textarea
        name="description"
        required
    ></textarea>

    <br><br>

    <label>Category</label>

    <input
        type="text"
        name="category"
        placeholder="Workshop, Seminar, Sports..."
        required
    >

    <br><br>

    <label>Event Date</label>

    <input
        type="date"
        name="event_date"
        required
    >

    <br><br>

    <label>Start Time</label>

    <input
        type="time"
        name="start_time"
        required
    >

    <br><br>

    <label>End Time</label>

    <input
        type="time"
        name="end_time"
        required
    >

    <br><br>

    <label>Venue</label>

    <input
        type="text"
        name="venue"
        required
    >

    <br><br>

    <label>Maximum Participants</label>

    <input
        type="number"
        name="max_participants"
        min="1"
        required
    >

    <br><br>

    <button type="submit">
        Create Event
    </button>

</form>

</body>
</html>