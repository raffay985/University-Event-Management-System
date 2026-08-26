<?php

require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION["user_id"];


/* Get student */

$stmt = $conn->prepare(
    "SELECT student_id
     FROM students
     WHERE user_id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$studentResult = $stmt->get_result();
$student = $studentResult->fetch_assoc();

if (!$student) {
    die("Student record not found.");
}

$student_id = $student["student_id"];


/* Find events where student was PRESENT */

$stmt = $conn->prepare(
    "SELECT
        e.event_id,
        e.event_title,
        e.event_date,
        a.status
     FROM attendance a
     INNER JOIN events e
        ON a.event_id = e.event_id
     WHERE a.student_id = ?
     AND a.status = 'present'
     ORDER BY e.event_date DESC"
);

$stmt->bind_param("i", $student_id);
$stmt->execute();

$attendanceResult = $stmt->get_result();


/* Generate certificates when needed */

while ($event = $attendanceResult->fetch_assoc()) {

    $event_id = $event["event_id"];

    $check = $conn->prepare(
        "SELECT certificate_id
         FROM certificates
         WHERE student_id = ?
         AND event_id = ?"
    );

    $check->bind_param(
        "ii",
        $student_id,
        $event_id
    );

    $check->execute();

    $checkResult = $check->get_result();

    if ($checkResult->num_rows === 0) {

        $certificate_number =
            "UEMS-" .
            date("Y") .
            "-" .
            str_pad($event_id, 3, "0", STR_PAD_LEFT) .
            "-" .
            str_pad($student_id, 4, "0", STR_PAD_LEFT);

        $issue_date = date("Y-m-d");

        $insert = $conn->prepare(
            "INSERT INTO certificates
            (
                student_id,
                event_id,
                certificate_number,
                issue_date
            )
            VALUES (?, ?, ?, ?)"
        );

        $insert->bind_param(
            "iiss",
            $student_id,
            $event_id,
            $certificate_number,
            $issue_date
        );

        if ($insert->execute()) {

            $type = "certificate_availability";

            $message =
                "Your certificate for '" .
                $event["event_title"] .
                "' is now available.";

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
    }
}


/* Get certificates */

$stmt = $conn->prepare(
    "SELECT
        c.certificate_id,
        c.certificate_number,
        c.issue_date,
        e.event_title,
        e.event_date
     FROM certificates c
     INNER JOIN events e
        ON c.event_id = e.event_id
     WHERE c.student_id = ?
     ORDER BY c.issue_date DESC"
);

$stmt->bind_param("i", $student_id);
$stmt->execute();

$certificates = $stmt->get_result();

?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Certificates</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

</head>

<body>

<h1>My Certificates</h1>

<a href="dashboard.php">
    Back to Student Dashboard
</a>

<br><br>

<?php if ($certificates->num_rows > 0) { ?>

<table>

<tr>
    <th>Event</th>
    <th>Event Date</th>
    <th>Certificate Number</th>
    <th>Issue Date</th>
    <th>Action</th>
</tr>

<?php while ($certificate = $certificates->fetch_assoc()) { ?>

<tr>

    <td>
        <?php echo htmlspecialchars($certificate["event_title"]); ?>
    </td>

    <td>
        <?php echo htmlspecialchars($certificate["event_date"]); ?>
    </td>

    <td>
        <?php echo htmlspecialchars($certificate["certificate_number"]); ?>
    </td>

    <td>
        <?php echo htmlspecialchars($certificate["issue_date"]); ?>
    </td>

    <td>

        <a href="view_certificate.php?id=<?php echo $certificate["certificate_id"]; ?>">
            View Certificate
        </a>

    </td>

</tr>

<?php } ?>

</table>

<?php } else { ?>

<p>
    No certificates are available yet.
</p>

<p>
    Certificates become available after you are marked Present for an event.
</p>

<?php } ?>

</body>

</html>