<?php

require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "organizer") {
    header("Location: ../login.php");
    exit();
}

$organizer_id = $_SESSION["user_id"];


/*
Show:
1. Events created by this organizer
2. Events created directly by Admin

Only APPROVED events are available for attendance.
*/

$stmt = $conn->prepare(
    "SELECT
        e.event_id,
        e.event_title,
        e.event_date,
        e.venue,
        e.approval_status,
        e.organizer_id,
        COUNT(r.registration_id) AS total_registered

     FROM events e

     LEFT JOIN registrations r
        ON e.event_id = r.event_id

     WHERE
        e.approval_status = 'approved'

        AND
        (
            e.organizer_id = ?
            OR e.organizer_id IS NULL
        )

     GROUP BY
        e.event_id,
        e.event_title,
        e.event_date,
        e.venue,
        e.approval_status,
        e.organizer_id

     ORDER BY e.event_date ASC"
);

$stmt->bind_param(
    "i",
    $organizer_id
);

$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>

<html>

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Manage Attendance</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

</head>

<body>


<h1>Manage Attendance</h1>


<a href="dashboard.php">
    Back to Organizer Dashboard
</a>


<br><br>


<?php if ($result->num_rows > 0) { ?>


<table>


<tr>

    <th>Event</th>

    <th>Created By</th>

    <th>Date</th>

    <th>Venue</th>

    <th>Registered Students</th>

    <th>Attendance</th>

</tr>


<?php while ($event = $result->fetch_assoc()) { ?>


<tr>


    <td>

        <?php
        echo htmlspecialchars(
            $event["event_title"]
        );
        ?>

    </td>


    <td>

        <?php

        if ($event["organizer_id"] === null) {

            echo "Admin";

        } else {

            echo "Organizer";
        }

        ?>

    </td>


    <td>

        <?php
        echo htmlspecialchars(
            $event["event_date"]
        );
        ?>

    </td>


    <td>

        <?php
        echo htmlspecialchars(
            $event["venue"]
        );
        ?>

    </td>


    <td>

        <?php
        echo $event["total_registered"];
        ?>

    </td>


    <td>

        <a href="mark_attendance.php?id=<?php echo $event["event_id"]; ?>">

            Mark Attendance

        </a>

    </td>


</tr>


<?php } ?>


</table>


<?php } else { ?>


<p>
    No approved events are available for attendance.
</p>


<?php } ?>


</body>

</html>