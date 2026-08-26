<?php

require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET["id"])) {
    header("Location: events.php");
    exit();
}

$event_id = (int) $_GET["id"];
$user_id = $_SESSION["user_id"];
$message = "";

/* Get student record */

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

/* Check that event exists and is approved */

$stmt = $conn->prepare(
    "SELECT event_title, max_participants
     FROM events
     WHERE event_id = ?
     AND approval_status = 'approved'"
);

$stmt->bind_param("i", $event_id);
$stmt->execute();

$result = $stmt->get_result();
$event = $result->fetch_assoc();

if (!$event) {
    die("This event is not available for registration.");
}

/* Check duplicate registration */

$stmt = $conn->prepare(
    "SELECT registration_id
     FROM registrations
     WHERE student_id = ?
     AND event_id = ?"
);

$stmt->bind_param(
    "ii",
    $student_id,
    $event_id
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {

    $message = "You are already registered for this event.";

} else {

    /* Count existing registrations */

    $stmt = $conn->prepare(
        "SELECT COUNT(*) AS total
         FROM registrations
         WHERE event_id = ?"
    );

    $stmt->bind_param("i", $event_id);
    $stmt->execute();

    $countResult = $stmt->get_result()->fetch_assoc();

    $totalRegistrations = $countResult["total"];

    if ($totalRegistrations >= $event["max_participants"]) {

        $message = "Registration is full for this event.";

    } else {

        /* Register student */

        $stmt = $conn->prepare(
            "INSERT INTO registrations
            (student_id, event_id)
            VALUES (?, ?)"
        );

        $stmt->bind_param(
            "ii",
            $student_id,
            $event_id
        );

        if ($stmt->execute()) {

            $message = "Registration successful!";

            /* Create notification */

            $notification_type = "registration_confirmation";

            $notification_message =
                "You have successfully registered for '" .
                $event["event_title"] .
                "'.";

            $stmt = $conn->prepare(
                "INSERT INTO notifications
                (user_id, type, message)
                VALUES (?, ?, ?)"
            );

            $stmt->bind_param(
                "iss",
                $user_id,
                $notification_type,
                $notification_message
            );

            $stmt->execute();

        } else {

            $message = "Registration failed.";
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

    <title>Event Registration</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

</head>

<body>

<h1>Event Registration</h1>

<p>
    <?php echo htmlspecialchars($message); ?>
</p>

<a href="events.php">
    Back to Events
</a>

<br><br>

<a href="registered_events.php">
    My Registered Events
</a>

</body>

</html>
</html>
