<?php

require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}


/* =========================================
   FILTER VALUES
========================================= */

$event_id = isset($_GET["event_id"])
    ? (int) $_GET["event_id"]
    : 0;

$date = isset($_GET["date"])
    ? trim($_GET["date"])
    : "";

$department = isset($_GET["department"])
    ? trim($_GET["department"])
    : "";

$student_id = isset($_GET["student_id"])
    ? (int) $_GET["student_id"]
    : 0;


/* =========================================
   GET EVENTS FOR FILTER
========================================= */

$events = $conn->query(
    "SELECT
        event_id,
        event_title,
        event_date
     FROM events
     ORDER BY event_date DESC"
);


/* =========================================
   GET DEPARTMENTS FOR FILTER
========================================= */

$departments = $conn->query(
    "SELECT DISTINCT department
     FROM students
     ORDER BY department ASC"
);


/* =========================================
   GET STUDENTS FOR FILTER
========================================= */

$studentList = $conn->query(
    "SELECT
        student_id,
        name,
        roll_number
     FROM students
     ORDER BY name ASC"
);


/* =========================================
   MAIN REPORT QUERY
========================================= */

$sql = "
    SELECT

        e.event_id,
        e.event_title,
        e.event_date,
        e.category,
        e.venue,

        s.student_id,
        s.name AS student_name,
        s.roll_number,
        s.department,
        s.semester,

        r.registration_date,

        a.status AS attendance_status

    FROM registrations r

    INNER JOIN events e
        ON r.event_id = e.event_id

    INNER JOIN students s
        ON r.student_id = s.student_id

    LEFT JOIN attendance a
        ON a.event_id = r.event_id
        AND a.student_id = r.student_id

    WHERE
        (? = 0 OR e.event_id = ?)

        AND
        (? = '' OR e.event_date = ?)

        AND
        (? = '' OR s.department = ?)

        AND
        (? = 0 OR s.student_id = ?)

    ORDER BY
        e.event_date DESC,
        e.event_title ASC,
        s.name ASC
";


$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "iissssii",

    $event_id,
    $event_id,

    $date,
    $date,

    $department,
    $department,

    $student_id,
    $student_id
);

$stmt->execute();

$result = $stmt->get_result();


/* =========================================
   STORE REPORT DATA
========================================= */

$reportRows = [];

$totalRegistrations = 0;
$totalPresent = 0;
$totalAbsent = 0;
$totalNotMarked = 0;

$eventSummary = [];
$studentSummary = [];


while ($row = $result->fetch_assoc()) {

    $reportRows[] = $row;

    $totalRegistrations++;


    /* Attendance statistics */

    if ($row["attendance_status"] === "present") {

        $totalPresent++;

    } elseif ($row["attendance_status"] === "absent") {

        $totalAbsent++;

    } else {

        $totalNotMarked++;
    }


    /* =====================================
       EVENT-WISE SUMMARY
    ===================================== */

    $eventKey = $row["event_id"];

    if (!isset($eventSummary[$eventKey])) {

        $eventSummary[$eventKey] = [

            "event_title" =>
                $row["event_title"],

            "event_date" =>
                $row["event_date"],

            "registrations" => 0,

            "present" => 0,

            "absent" => 0,

            "not_marked" => 0
        ];
    }


    $eventSummary[$eventKey]["registrations"]++;


    if ($row["attendance_status"] === "present") {

        $eventSummary[$eventKey]["present"]++;

    } elseif ($row["attendance_status"] === "absent") {

        $eventSummary[$eventKey]["absent"]++;

    } else {

        $eventSummary[$eventKey]["not_marked"]++;
    }


    /* =====================================
       STUDENT PARTICIPATION SUMMARY
    ===================================== */

    $studentKey = $row["student_id"];

    if (!isset($studentSummary[$studentKey])) {

        $studentSummary[$studentKey] = [

            "student_name" =>
                $row["student_name"],

            "roll_number" =>
                $row["roll_number"],

            "department" =>
                $row["department"],

            "events" => 0,

            "present" => 0,

            "absent" => 0
        ];
    }


    $studentSummary[$studentKey]["events"]++;


    if ($row["attendance_status"] === "present") {

        $studentSummary[$studentKey]["present"]++;

    } elseif ($row["attendance_status"] === "absent") {

        $studentSummary[$studentKey]["absent"]++;
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

    <title>Reports</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >


    <style>

        .report-summary {

            width: 95%;
            max-width: 900px;

            margin: 25px auto;

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 12px;
        }


        .report-card {

            background: #fffaf7;

            border: 1px solid #d7ccc8;

            border-radius: 10px;

            padding: 14px;

            box-shadow:
                0 3px 10px
                rgba(78, 52, 46, 0.12);

            text-align: center;
        }


        .report-card span {

            display: block;

            font-size: 13px;

            color: #795548;

            margin-bottom: 5px;
        }


        .report-card strong {

            font-size: 22px;

            color: #4e342e;
        }


        .report-section {

            width: 95%;
            max-width: 1200px;

            margin:
                35px
                auto;
        }


        .report-section h2 {

            margin-bottom: 10px;
        }


        @media (max-width: 700px) {

            .report-summary {

                grid-template-columns:
                    repeat(2, 1fr);
            }

        }

    </style>

</head>


<body>


<h1>Event Reports</h1>


<a href="dashboard.php">
    Back to Admin Dashboard
</a>


<!-- =====================================
     FILTER FORM
===================================== -->

<form method="GET">


    <h2>Filter Reports</h2>


    <label>
        Event
    </label>

    <select name="event_id">

        <option value="0">
            All Events
        </option>

        <?php while ($event = $events->fetch_assoc()) { ?>

            <option

                value="<?php echo $event["event_id"]; ?>"

                <?php

                if (
                    $event_id ==
                    $event["event_id"]
                ) {
                    echo "selected";
                }

                ?>

            >

                <?php

                echo htmlspecialchars(
                    $event["event_title"]
                );

                ?>

                -

                <?php
                echo htmlspecialchars(
                    $event["event_date"]
                );
                ?>

            </option>

        <?php } ?>

    </select>


    <br><br>


    <label>
        Date
    </label>

    <input

        type="date"

        name="date"

        value="<?php
        echo htmlspecialchars($date);
        ?>"

    >


    <br><br>


    <label>
        Department
    </label>

    <select name="department">

        <option value="">
            All Departments
        </option>


        <?php
        while (
            $dept =
            $departments->fetch_assoc()
        ) {
        ?>

            <option

                value="<?php
                echo htmlspecialchars(
                    $dept["department"]
                );
                ?>"

                <?php

                if (
                    $department ===
                    $dept["department"]
                ) {
                    echo "selected";
                }

                ?>

            >

                <?php
                echo htmlspecialchars(
                    $dept["department"]
                );
                ?>

            </option>

        <?php } ?>

    </select>


    <br><br>


    <label>
        Student
    </label>

    <select name="student_id">

        <option value="0">
            All Students
        </option>


        <?php
        while (
            $student =
            $studentList->fetch_assoc()
        ) {
        ?>

            <option

                value="<?php
                echo $student["student_id"];
                ?>"

                <?php

                if (
                    $student_id ==
                    $student["student_id"]
                ) {
                    echo "selected";
                }

                ?>

            >

                <?php
                echo htmlspecialchars(
                    $student["name"]
                );
                ?>

                -

                <?php
                echo htmlspecialchars(
                    $student["roll_number"]
                );
                ?>

            </option>

        <?php } ?>

    </select>


    <br><br>


    <button type="submit">
        Apply Filters
    </button>


    <a href="reports.php">
        Clear Filters
    </a>


</form>



<!-- =====================================
     SUMMARY STATISTICS
===================================== -->

<div class="report-summary">


    <div class="report-card">

        <span>
            Registrations
        </span>

        <strong>
            <?php echo $totalRegistrations; ?>
        </strong>

    </div>


    <div class="report-card">

        <span>
            Present
        </span>

        <strong>
            <?php echo $totalPresent; ?>
        </strong>

    </div>


    <div class="report-card">

        <span>
            Absent
        </span>

        <strong>
            <?php echo $totalAbsent; ?>
        </strong>

    </div>


    <div class="report-card">

        <span>
            Not Marked
        </span>

        <strong>
            <?php echo $totalNotMarked; ?>
        </strong>

    </div>


</div>



<!-- =====================================
     EVENT-WISE REGISTRATION REPORT
===================================== -->

<div class="report-section">


<h2>
    Event-Wise Registration Report
</h2>


<?php if (count($eventSummary) > 0) { ?>


<table>


<tr>

    <th>
        Event
    </th>

    <th>
        Date
    </th>

    <th>
        Registrations
    </th>

    <th>
        Present
    </th>

    <th>
        Absent
    </th>

    <th>
        Not Marked
    </th>

</tr>


<?php foreach ($eventSummary as $event) { ?>


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
        echo htmlspecialchars(
            $event["event_date"]
        );
        ?>

    </td>


    <td>
        <?php echo $event["registrations"]; ?>
    </td>


    <td>
        <?php echo $event["present"]; ?>
    </td>


    <td>
        <?php echo $event["absent"]; ?>
    </td>


    <td>
        <?php echo $event["not_marked"]; ?>
    </td>


</tr>


<?php } ?>


</table>


<?php } else { ?>


<p>
    No registration data found.
</p>


<?php } ?>


</div>



<!-- =====================================
     STUDENT PARTICIPATION REPORT
===================================== -->

<div class="report-section">


<h2>
    Student Participation Report
</h2>


<?php if (count($studentSummary) > 0) { ?>


<table>


<tr>

    <th>
        Student
    </th>

    <th>
        Roll Number
    </th>

    <th>
        Department
    </th>

    <th>
        Registered Events
    </th>

    <th>
        Present
    </th>

    <th>
        Absent
    </th>

</tr>


<?php foreach ($studentSummary as $student) { ?>


<tr>


    <td>

        <?php
        echo htmlspecialchars(
            $student["student_name"]
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
            $student["department"]
        );
        ?>

    </td>


    <td>

        <?php
        echo $student["events"];
        ?>

    </td>


    <td>

        <?php
        echo $student["present"];
        ?>

    </td>


    <td>

        <?php
        echo $student["absent"];
        ?>

    </td>


</tr>


<?php } ?>


</table>


<?php } else { ?>


<p>
    No student participation data found.
</p>


<?php } ?>


</div>



<!-- =====================================
     DETAILED ATTENDANCE REPORT
===================================== -->

<div class="report-section">


<h2>
    Attendance Report
</h2>


<?php if (count($reportRows) > 0) { ?>


<table>


<tr>

    <th>
        Event
    </th>

    <th>
        Date
    </th>

    <th>
        Student
    </th>

    <th>
        Roll Number
    </th>

    <th>
        Department
    </th>

    <th>
        Registration Date
    </th>

    <th>
        Attendance
    </th>

</tr>


<?php foreach ($reportRows as $row) { ?>


<tr>


    <td>

        <?php
        echo htmlspecialchars(
            $row["event_title"]
        );
        ?>

    </td>


    <td>

        <?php
        echo htmlspecialchars(
            $row["event_date"]
        );
        ?>

    </td>


    <td>

        <?php
        echo htmlspecialchars(
            $row["student_name"]
        );
        ?>

    </td>


    <td>

        <?php
        echo htmlspecialchars(
            $row["roll_number"]
        );
        ?>

    </td>


    <td>

        <?php
        echo htmlspecialchars(
            $row["department"]
        );
        ?>

    </td>


    <td>

        <?php
        echo htmlspecialchars(
            $row["registration_date"]
        );
        ?>

    </td>


    <td>

        <?php

        if (
            $row["attendance_status"]
            === "present"
        ) {

            echo "Present";

        } elseif (
            $row["attendance_status"]
            === "absent"
        ) {

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
    No attendance data found for the selected filters.
</p>


<?php } ?>


</div>


</body>

</html>