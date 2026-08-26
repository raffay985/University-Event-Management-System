<?php

require_once __DIR__ . "/../config/session.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "organizer") {
    header("Location: ../login.php");
    exit();
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

    <title>Organizer Dashboard</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

</head>

<body>

<h1>Event Organizer Dashboard</h1>

<p>
    Welcome,
    <strong>
        <?php echo htmlspecialchars($_SESSION["username"]); ?>
    </strong>
</p>

<ul>

    <li>
        <a href="create_event.php">
            Create New Event
        </a>
    </li>

    <li>
        <a href="my_events.php">
            Manage My Events
        </a>
    </li>

    <li>
        <a href="participants.php">
            View Registered Participants
        </a>
    </li>

    <li>
        <a href="attendance.php">
            Manage Attendance
        </a>
    </li>

    <li>
        <a href="../notifications.php">
            Notifications
        </a>
    </li>

    <li>
        <a href="../logout.php">
            Logout
        </a>
    </li>

</ul>

</body>

</html>