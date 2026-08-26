<?php

require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    header("Location: ../login.php");
    exit();
}


/* ===============================
   FILTER VALUES
================================ */

$event_id = isset($_GET["event_id"])
    ? (int) $_GET["event_id"]
    : 0;

$date = isset($_GET["date"])
    ? trim($_GET["date"])
    : "";

$department = isset($_GET["department"])
    ? trim($_GET["department"])
    : "";

$filter_student_id = isset($_GET["student_id"])
    ? (int) $_GET["student_id"]
    : 0;


/* ===============================
   EVENTS FOR FILTER
================================ */

$events = $conn->query(
    "SELECT
        event_id,
        event_title,
        event_date
     FROM events
     WHERE approval_status = 'approved'
     ORDER BY event_date DESC"
);


/* ===============================
   DEPARTMENTS FOR FILTER
================================ */

$departments = $conn->query(
    "SELECT DISTINCT department
     FROM students
     ORDER BY department ASC"
);


/* ===============================
   STUDENTS FOR FILTER
================================ */

$students = $conn->query(
    "SELECT
        student_id,
        name,
        roll_number
     FROM students
     ORDER BY name ASC"
);


/* ===============================
   REPORT QUERY
================================ */

$sql = "
    SELECT

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

    WHERE e.approval_status = 'approved'

    AND
        (? = 0 OR e.event_id = ?)

    AND
        (? = '' OR e.event_date = ?)

    AND
        (? = '' OR s.department = ?)

    AND
        (? = 0 OR s.student_id = ?)

    ORDER BY
        e.event_date DESC,
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

    $filter_student_id,
    $filter_student_id
);

$stmt->execute();

$result = $stmt->get_result();


/* ===============================
   STATISTICS
================================ */

$rows = [];

$total = 0;
$present = 0;
$absent = 0;
$not_marked = 0;


while ($row = $result->fetch_assoc()) {

    $rows[] = $row;

    $total++;

    if ($row["attendance_status"] === "present") {

        $present++;

    } elseif ($row["attendance_status"] === "absent") {

        $absent++;

    } else {

        $not_marked++;
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

    <title>Event Reports</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

    <style>

        .student-report-summary {

            width: 95%;
            max-width: 750px;

            margin: 25px auto;

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 12px;
        }


        .student-report-card {

            background: #fffaf7;

            border: 1px solid #d7ccc8;

            border-radius: 10px;

            padding: 15px;

            box-shadow:
                0 3px 10px
                rgba(78, 52, 46, 0.12);
        }


        .student-report-card span {

            display: block;

            font-size: 13px;

            color: #795548;

            margin-bottom: 6px;
        }


        .student-report-card strong {

            font-size: 22px;

            color: #4e342e;
        }


        @media (max-width: 700px) {

            .student-report-summary {

                grid-template-columns:
                    repeat(2, 1fr);
            }

        }

    </style>

</head>


<body>


<h1>Event Reports</h1>


<a href="dashboard.php">
    Back to Student Dashboard
</a>


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

                if ($event_id == $event["event_id"]) {
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

        value="<?php echo htmlspecialchars($date); ?>"

    >


    <br><br>


    <label>
        Department
    </label>

    <select name="department">

        <option value="">
            All Departments
        </option>


        <?php while ($dept = $departments->fetch_assoc()) { ?>

            <option

                value="<?php
                echo htmlspecialchars(
                    $dept["department"]
                );
                ?>"

                <?php

                if ($department === $dept["department"]) {
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


        <?php while ($student = $students->fetch_assoc()) { ?>

            <option

                value="<?php echo $student["student_id"]; ?>"

                <?php

                if (
                    $filter_student_id ==
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


    &nbsp;


    <a href="reports.php">
        Clear Filters
    </a>

</form>


<!-- REPORT STATISTICS -->

<div class="student-report-summary">


    <div class="student-report-card">

        <span>
            Registrations
        </span>

        <strong>
            <?php echo $total; ?>
        </strong>

    </div>


    <div class="student-report-card">

        <span>
            Present
        </span>

        <strong>
            <?php echo $present; ?>
        </strong>

    </div>


    <div class="student-report-card">

        <span>
            Absent
        </span>

        <strong>
            <?php echo $absent; ?>
        </strong>

    </div>


    <div class="student-report-card">

        <span>
            Not Marked
        </span>

        <strong>
            <?php echo $not_marked; ?>
        </strong>

    </div>


</div>


<h2>Participation & Attendance Report</h2>


<?php if (count($rows) > 0) { ?>


<table>

<tr>

    <th>Event</th>

    <th>Event Date</th>

    <th>Student</th>

    <th>Roll Number</th>

    <th>Department</th>

    <th>Semester</th>

    <th>Registration Date</th>

    <th>Attendance</th>

</tr>


<?php foreach ($rows as $row) { ?>


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
            $row["semester"]
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

        if ($row["attendance_status"] === "present") {

            echo "Present";

        } elseif ($row["attendance_status"] === "absent") {

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
    No report records found for the selected filters.
</p>


<?php } ?>


</body>

</html>