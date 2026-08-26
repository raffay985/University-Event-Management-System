<?php

require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "organizer") {

    header("Location: ../login.php");

    exit();
}


if (!isset($_GET["id"])) {

    header("Location: participants.php");

    exit();
}


$event_id =
    (int) $_GET["id"];


$organizer_id =
    $_SESSION["user_id"];


/*
Organizer can view participants for:

1. Their approved events
2. Admin-created approved events
*/

$stmt = $conn->prepare(
    "SELECT

        event_title,
        event_date,
        venue,
        organizer_id

     FROM events

     WHERE event_id = ?

     AND approval_status = 'approved'

     AND
     (
        organizer_id = ?
        OR organizer_id IS NULL
     )"
);


$stmt->bind_param(
    "ii",
    $event_id,
    $organizer_id
);


$stmt->execute();


$eventResult =
    $stmt->get_result();


$event =
    $eventResult->fetch_assoc();


if (!$event) {

    die(
        "Event not found or access denied."
    );
}


/* Get registered students */

$stmt = $conn->prepare(
    "SELECT

        s.student_id,
        s.name,
        s.roll_number,
        s.email,
        s.department,
        s.semester,

        r.registration_date,

        a.status
            AS attendance_status

     FROM registrations r

     INNER JOIN students s
        ON r.student_id =
           s.student_id

     LEFT JOIN attendance a

        ON a.event_id =
           r.event_id

        AND a.student_id =
            r.student_id

     WHERE r.event_id = ?

     ORDER BY
        s.name ASC"
);


$stmt->bind_param(
    "i",
    $event_id
);


$stmt->execute();


$result =
    $stmt->get_result();

?>

<!DOCTYPE html>

<html>

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Event Participants</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

</head>

<body>


<h1>

    <?php
    echo htmlspecialchars(
        $event["event_title"]
    );
    ?>

</h1>


<p>

    <strong>
        Created By:
    </strong>

    <?php

    if ($event["organizer_id"] === null) {

        echo "Admin";

    } else {

        echo "Organizer";
    }

    ?>

    <br>


    <strong>
        Date:
    </strong>

    <?php
    echo htmlspecialchars(
        $event["event_date"]
    );
    ?>


    <br>


    <strong>
        Venue:
    </strong>

    <?php
    echo htmlspecialchars(
        $event["venue"]
    );
    ?>

</p>


<a href="participants.php">
    Back to Events
</a>


<br><br>


<?php if ($result->num_rows > 0) { ?>


<table>


<tr>

    <th>Student ID</th>

    <th>Name</th>

    <th>Roll Number</th>

    <th>Email</th>

    <th>Department</th>

    <th>Semester</th>

    <th>Registration Date</th>

    <th>Attendance</th>

</tr>


<?php while ($student = $result->fetch_assoc()) { ?>


<tr>


    <td>

        <?php
        echo $student["student_id"];
        ?>

    </td>


    <td>

        <?php
        echo htmlspecialchars(
            $student["name"]
        );
        ?>

    </td>


    <td>

        <?php
        echo htmlspecialchars(
            $student["roll_number"]
        );
        ?>

    </td>


    <td>

        <?php
        echo htmlspecialchars(
            $student["email"]
        );
        ?>

    </td>


    <td>

        <?php
        echo htmlspecialchars(
            $student["department"]
        );
        ?>

    </td>


    <td>

        <?php
        echo htmlspecialchars(
            $student["semester"]
        );
        ?>

    </td>


    <td>

        <?php
        echo htmlspecialchars(
            $student["registration_date"]
        );
        ?>

    </td>


    <td>

        <?php

        if ($student["attendance_status"] === "present") {

            echo "Present";

        } elseif ($student["attendance_status"] === "absent") {

            echo "Absent";

        } else {

            echo "Not Marked";
        }

        ?>

    </td>


</tr>


<?php } ?>


</table>


<?php } else { ?>


<p>
    No students have registered for this event yet.
</p>


<?php } ?>


</body>

</html>