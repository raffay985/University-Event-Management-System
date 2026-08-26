<?php

require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "organizer") {

    header("Location: ../login.php");
    exit();
}

if (!isset($_GET["id"])) {

    header("Location: attendance.php");
    exit();
}

$event_id = (int) $_GET["id"];
$organizer_id = $_SESSION["user_id"];

$message = "";


/* =====================================================
   CHECK EVENT ACCESS

   Organizer can manage attendance for:
   1. Their own approved events
   2. Admin-created approved events
===================================================== */

$stmt = $conn->prepare(
    "SELECT
        event_id,
        event_title,
        event_date,
        start_time,
        end_time,
        venue,
        organizer_id,
        approval_status

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

$eventResult = $stmt->get_result();

$event = $eventResult->fetch_assoc();


if (!$event) {

    die(
        "Event not found or you do not have permission to manage attendance for this event."
    );
}


/* =====================================================
   SAVE ATTENDANCE
===================================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (
        isset($_POST["attendance"]) &&
        is_array($_POST["attendance"])
    ) {

        foreach (
            $_POST["attendance"]
            as $student_id => $status
        ) {

            $student_id = (int) $student_id;


            if (
                $status !== "present" &&
                $status !== "absent"
            ) {

                continue;
            }


            /* Check registration */

            $check = $conn->prepare(
                "SELECT registration_id
                 FROM registrations
                 WHERE event_id = ?
                 AND student_id = ?"
            );

            $check->bind_param(
                "ii",
                $event_id,
                $student_id
            );

            $check->execute();

            $checkResult = $check->get_result();


            if ($checkResult->num_rows === 1) {

                $save = $conn->prepare(
                    "INSERT INTO attendance
                    (
                        event_id,
                        student_id,
                        status
                    )

                    VALUES (?, ?, ?)

                    ON DUPLICATE KEY UPDATE

                        status = VALUES(status),

                        marked_at = CURRENT_TIMESTAMP"
                );

                $save->bind_param(
                    "iis",
                    $event_id,
                    $student_id,
                    $status
                );

                $save->execute();
            }
        }


        $message = "Attendance saved successfully!";
    }
}


/* =====================================================
   GET REGISTERED STUDENTS
===================================================== */

$stmt = $conn->prepare(
    "SELECT

        s.student_id,
        s.name,
        s.roll_number,
        s.email,
        s.department,
        s.semester,

        a.status AS attendance_status

     FROM registrations r

     INNER JOIN students s
        ON r.student_id = s.student_id

     LEFT JOIN attendance a
        ON a.student_id = s.student_id

        AND a.event_id = r.event_id

     WHERE r.event_id = ?

     ORDER BY s.name ASC"
);

$stmt->bind_param(
    "i",
    $event_id
);

$stmt->execute();

$students = $stmt->get_result();

?>

<!DOCTYPE html>

<html>

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Mark Attendance</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >


    <style>

        /* ========================================
           MARK ATTENDANCE PAGE FIX
        ======================================== */


        .attendance-page {

            width: 95%;
            max-width: 1200px;

            margin: 0 auto;

            text-align: center;
        }


        /* Event information box */

        .attendance-event-info {

            width: 90%;
            max-width: 700px;

            margin: 20px auto;

            padding: 18px 25px;

            background: #fffaf7;

            border: 1px solid #d7ccc8;

            border-radius: 12px;

            box-shadow:
                0 4px 12px
                rgba(78, 52, 46, 0.12);

            text-align: center;
        }


        .attendance-event-info p {

            margin: 8px 0;

        }


        /* ========================================
           IMPORTANT FIX:
           Override global form width
        ======================================== */

        form.attendance-form {

            width: 100%;

            max-width: 1150px;

            margin: 25px auto;

            padding: 20px;

            background: #fffaf7;

            border: 1px solid #d7ccc8;

            border-radius: 12px;

            box-shadow:
                0 5px 16px
                rgba(78, 52, 46, 0.13);

            text-align: center;

            overflow: hidden;
        }


        /* Table container */

        .attendance-table-wrapper {

            width: 100%;

            overflow-x: auto;

            border-radius: 10px;
        }


        /* Attendance table */

        .attendance-table {

            width: 100%;

            max-width: none;

            margin: 0;

            border-collapse: collapse;

            background: #fffaf7;

            box-shadow: none;
        }


        .attendance-table th {

            padding: 12px 10px;

            white-space: nowrap;

            background:
                linear-gradient(
                    135deg,
                    #6d4c41,
                    #4e342e
                );

            color: white;

            text-align: center;
        }


        .attendance-table td {

            padding: 12px 10px;

            border-bottom:
                1px solid #e7d8cf;

            vertical-align: middle;

            text-align: center;
        }


        .attendance-table tr:nth-child(even) {

            background: #f8f1ec;
        }


        /* Radio buttons */

        .attendance-table input[type="radio"] {

            width: 18px;

            height: 18px;

            margin: 0;

            cursor: pointer;

            accent-color: #6d4c41;
        }


        /* Save button area */

        .attendance-save-area {

            margin-top: 20px;

            text-align: center;
        }


        .attendance-save-area button {

            min-width: 180px;

            padding: 12px 25px;
        }


        /* Back button */

        .attendance-back {

            display: inline-block;

            margin: 10px 0 5px;

            padding: 9px 16px;

            background: #795548;

            color: white;

            border-radius: 7px;

            text-decoration: none;
        }


        .attendance-back:hover {

            background: #4e342e;

            color: white;
        }


        /* Empty message */

        .attendance-empty {

            width: 90%;

            max-width: 600px;

            margin: 25px auto;

            padding: 18px;

            background: #fffaf7;

            border: 1px solid #d7ccc8;

            border-radius: 10px;
        }


        /* Mobile */

        @media (max-width: 800px) {

            .attendance-page {

                width: 100%;
            }


            form.attendance-form {

                width: 100%;

                padding: 12px;
            }


            .attendance-table {

                min-width: 800px;
            }


            .attendance-event-info {

                width: 96%;
            }

        }

    </style>

</head>


<body>


<div class="attendance-page">


    <h1>
        Mark Attendance
    </h1>


    <div class="attendance-event-info">


        <h2>

            <?php
            echo htmlspecialchars(
                $event["event_title"]
            );
            ?>

        </h2>


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

        </p>


        <p>

            <strong>
                Date:
            </strong>

            <?php
            echo htmlspecialchars(
                $event["event_date"]
            );
            ?>

        </p>


        <p>

            <strong>
                Time:
            </strong>

            <?php
            echo htmlspecialchars(
                $event["start_time"]
            );
            ?>

            -

            <?php
            echo htmlspecialchars(
                $event["end_time"]
            );
            ?>

        </p>


        <p>

            <strong>
                Venue:
            </strong>

            <?php
            echo htmlspecialchars(
                $event["venue"]
            );
            ?>

        </p>


    </div>


    <a
        href="attendance.php"
        class="attendance-back"
    >
        Back to Attendance
    </a>


    <?php if ($message != "") { ?>


        <p class="success">

            <?php
            echo htmlspecialchars(
                $message
            );
            ?>

        </p>


    <?php } ?>


    <?php if ($students->num_rows > 0) { ?>


        <form
            method="POST"
            class="attendance-form"
        >


            <div class="attendance-table-wrapper">


                <table class="attendance-table">


                    <tr>

                        <th>
                            Name
                        </th>

                        <th>
                            Roll Number
                        </th>

                        <th>
                            Email
                        </th>

                        <th>
                            Department
                        </th>

                        <th>
                            Semester
                        </th>

                        <th>
                            Present
                        </th>

                        <th>
                            Absent
                        </th>

                    </tr>


                    <?php while ($student = $students->fetch_assoc()) { ?>


                        <tr>


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

                                <input

                                    type="radio"

                                    name="attendance[<?php echo $student["student_id"]; ?>]"

                                    value="present"

                                    <?php

                                    if (
                                        $student["attendance_status"]
                                        === "present"
                                    ) {

                                        echo "checked";
                                    }

                                    ?>

                                >

                            </td>


                            <td>

                                <input

                                    type="radio"

                                    name="attendance[<?php echo $student["student_id"]; ?>]"

                                    value="absent"

                                    <?php

                                    if (
                                        $student["attendance_status"]
                                        === "absent"
                                    ) {

                                        echo "checked";
                                    }

                                    ?>

                                >

                            </td>


                        </tr>


                    <?php } ?>


                </table>


            </div>


            <div class="attendance-save-area">


                <button type="submit">

                    Save Attendance

                </button>


            </div>


        </form>


    <?php } else { ?>


        <div class="attendance-empty">

            No students are registered for this event.

        </div>


    <?php } ?>


</div>


</body>

</html>