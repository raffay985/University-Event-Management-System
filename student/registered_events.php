<?php

require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

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

$stmt = $conn->prepare(
    "SELECT
        r.registration_id,
        e.event_id,
        e.event_title,
        e.category,
        e.event_date,
        e.start_time,
        e.end_time,
        e.venue
     FROM registrations r
     INNER JOIN events e
        ON r.event_id = e.event_id
     WHERE r.student_id = ?
     ORDER BY e.event_date ASC"
);

$stmt->bind_param("i", $student_id);
$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>My Registered Events</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<h1>My Registered Events</h1>

<a href="dashboard.php">Back to Dashboard</a>

<br><br>

<table border="1" cellpadding="10">

<tr>
    <th>Title</th>
    <th>Category</th>
    <th>Date</th>
    <th>Start Time</th>
    <th>End Time</th>
    <th>Venue</th>
    <th>Action</th>
</tr>

<?php while ($event = $result->fetch_assoc()) { ?>

<tr>

    <td>
        <?php echo htmlspecialchars($event["event_title"]); ?>
    </td>

    <td>
        <?php echo htmlspecialchars($event["category"]); ?>
    </td>

    <td>
        <?php echo $event["event_date"]; ?>
    </td>

    <td>
        <?php echo $event["start_time"]; ?>
    </td>

    <td>
        <?php echo $event["end_time"]; ?>
    </td>

    <td>
        <?php echo htmlspecialchars($event["venue"]); ?>
    </td>

    <td>
        <a href="cancel_registration.php?id=<?php echo $event["registration_id"]; ?>">
            Cancel
        </a>
    </td>

</tr>

<?php } ?>

</table>

</body>
</html>
