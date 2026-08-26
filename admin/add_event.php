<?php

require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

$message = "";

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

        /*
        Admin-created events are automatically approved.
        organizer_id stays NULL.
        */

        $approval_status = "approved";

        $stmt = $conn->prepare(
            "INSERT INTO events
            (
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
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "sssssssis",
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

            $message = "Event added successfully!";

        } else {

            $message = "Could not add event.";
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

    <title>Add Event</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

</head>

<body>

<h1>Add Event</h1>

<a href="events.php">
    Back to Events
</a>


<?php if ($message != "") { ?>

    <p class="success">

        <?php
        echo htmlspecialchars($message);
        ?>

    </p>

<?php } ?>


<form method="POST">


    <label>
        Event Title
    </label>

    <input
        type="text"
        name="event_title"
        required
    >


    <br><br>


    <label>
        Description
    </label>

    <textarea
        name="description"
        required
    ></textarea>


    <br><br>


    <label>
        Category
    </label>

    <input
        type="text"
        name="category"
        placeholder="Workshop, Seminar, Sports..."
        required
    >


    <br><br>


    <label>
        Event Date
    </label>

    <input
        type="date"
        name="event_date"
        required
    >


    <br><br>


    <label>
        Start Time
    </label>

    <input
        type="time"
        name="start_time"
        required
    >


    <br><br>


    <label>
        End Time
    </label>

    <input
        type="time"
        name="end_time"
        required
    >


    <br><br>


    <label>
        Venue
    </label>

    <input
        type="text"
        name="venue"
        required
    >


    <br><br>


    <label>
        Maximum Participants
    </label>

    <input
        type="number"
        name="max_participants"
        min="1"
        required
    >


    <br><br>


    <button type="submit">
        Add Event
    </button>


</form>

</body>

</html>