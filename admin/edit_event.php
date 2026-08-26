<?php

require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET["id"])) {
    header("Location: events.php");
    exit();
}

$id = (int) $_GET["id"];

$stmt = $conn->prepare("SELECT * FROM events WHERE event_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$event = $result->fetch_assoc();

if (!$event) {
    die("Event not found.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $event_title = trim($_POST["event_title"]);
    $description = trim($_POST["description"]);
    $category = trim($_POST["category"]);
    $event_date = $_POST["event_date"];
    $start_time = $_POST["start_time"];
    $end_time = $_POST["end_time"];
    $venue = trim($_POST["venue"]);
    $max_participants = (int) $_POST["max_participants"];

    $stmt = $conn->prepare(
        "UPDATE events
         SET event_title = ?, description = ?, category = ?,
             event_date = ?, start_time = ?, end_time = ?,
             venue = ?, max_participants = ?
         WHERE event_id = ?"
    );

    $stmt->bind_param(
        "sssssssii",
        $event_title,
        $description,
        $category,
        $event_date,
        $start_time,
        $end_time,
        $venue,
        $max_participants,
        $id
    );

    if ($stmt->execute()) {

        $message = "Event updated successfully!";

        $event["event_title"] = $event_title;
        $event["description"] = $description;
        $event["category"] = $category;
        $event["event_date"] = $event_date;
        $event["start_time"] = $start_time;
        $event["end_time"] = $end_time;
        $event["venue"] = $venue;
        $event["max_participants"] = $max_participants;
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Event</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<h1>Edit Event</h1>

<a href="events.php">Back to Events</a>

<br><br>

<?php
if ($message != "") {
    echo "<p>" . htmlspecialchars($message) . "</p>";
}
?>

<form method="POST">

    <label>Event Title</label><br>
    <input
        type="text"
        name="event_title"
        value="<?php echo htmlspecialchars($event["event_title"]); ?>"
        required
    >

    <br><br>

    <label>Description</label><br>
    <textarea name="description" required><?php
        echo htmlspecialchars($event["description"]);
    ?></textarea>

    <br><br>

    <label>Category</label><br>
    <input
        type="text"
        name="category"
        value="<?php echo htmlspecialchars($event["category"]); ?>"
        required
    >

    <br><br>

    <label>Date</label><br>
    <input
        type="date"
        name="event_date"
        value="<?php echo $event["event_date"]; ?>"
        required
    >

    <br><br>

    <label>Start Time</label><br>
    <input
        type="time"
        name="start_time"
        value="<?php echo $event["start_time"]; ?>"
        required
    >

    <br><br>

    <label>End Time</label><br>
    <input
        type="time"
        name="end_time"
        value="<?php echo $event["end_time"]; ?>"
        required
    >

    <br><br>

    <label>Venue</label><br>
    <input
        type="text"
        name="venue"
        value="<?php echo htmlspecialchars($event["venue"]); ?>"
        required
    >

    <br><br>

    <label>Maximum Participants</label><br>
    <input
        type="number"
        name="max_participants"
        min="1"
        value="<?php echo $event["max_participants"]; ?>"
        required
    >

    <br><br>

    <button type="submit">Update Event</button>

</form>

</body>
</html>
