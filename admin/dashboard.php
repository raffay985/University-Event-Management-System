<?php

require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {

    header("Location: ../login.php");
    exit();
}


/* =========================================
   TOTAL EVENTS
========================================= */

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM events"
);

$total_events =
    $result->fetch_assoc()["total"];


/* =========================================
   UPCOMING EVENTS
========================================= */

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM events
     WHERE event_date >= CURDATE()
     AND approval_status = 'approved'"
);

$upcoming_events =
    $result->fetch_assoc()["total"];


/* =========================================
   COMPLETED EVENTS
========================================= */

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM events
     WHERE event_date < CURDATE()"
);

$completed_events =
    $result->fetch_assoc()["total"];


/* =========================================
   TOTAL STUDENTS
========================================= */

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM students"
);

$total_students =
    $result->fetch_assoc()["total"];


/* =========================================
   TOTAL REGISTRATIONS
========================================= */

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM registrations"
);

$total_registrations =
    $result->fetch_assoc()["total"];


/* =========================================
   TOTAL PARTICIPANTS
========================================= */

$result = $conn->query(
    "SELECT COUNT(DISTINCT student_id) AS total
     FROM registrations"
);

$total_participants =
    $result->fetch_assoc()["total"];


/* =========================================
   PENDING EVENTS
========================================= */

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM events
     WHERE approval_status = 'pending'"
);

$pending_events =
    $result->fetch_assoc()["total"];


/* =========================================
   CHART PERCENTAGES
========================================= */

$chart_max = max(
    1,
    $upcoming_events,
    $completed_events,
    $pending_events
);

$upcoming_width =
    ($upcoming_events / $chart_max) * 100;

$completed_width =
    ($completed_events / $chart_max) * 100;

$pending_width =
    ($pending_events / $chart_max) * 100;

?>

<!DOCTYPE html>

<html>

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Admin Dashboard</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >


    <style>

        /* ========================================
           SMALL DASHBOARD CHART
        ======================================== */

        .mini-chart {

            margin-top: 18px;

            padding-top: 14px;

            border-top:
                1px solid #d7ccc8;
        }


        .mini-chart-title {

            margin:
                0
                0
                12px;

            font-size: 14px;

            font-weight: bold;

            text-align: center;

            color: #4e342e;
        }


        .chart-item {

            margin-bottom: 11px;
        }


        .chart-label {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 4px;

            font-size: 12px;

            color: #5d4037;
        }


        .chart-track {

            width: 100%;

            height: 8px;

            overflow: hidden;

            background: #e5d8d0;

            border-radius: 10px;
        }


        .chart-bar {

            height: 100%;

            min-width: 3px;

            border-radius: 10px;

            background:
                linear-gradient(
                    90deg,
                    #a1887f,
                    #6d4c41
                );
        }

    </style>

</head>


<body class="admin-dashboard-page">


<h1>
    Admin Dashboard
</h1>


<p class="admin-welcome">

    Welcome,

    <strong>

        <?php
        echo htmlspecialchars(
            $_SESSION["username"]
        );
        ?>

    </strong>

</p>



<div class="admin-dashboard-layout">


    <!-- =====================================
         LEFT SIDE MANAGEMENT
    ====================================== -->

    <div class="admin-main-menu">


        <h2>
            Management
        </h2>


        <div class="admin-menu-links">


            <a href="students.php">
                Manage Students
            </a>


            <a href="events.php">
                Manage Events
            </a>


            <a href="event_approval.php">
                Event Approval
            </a>


            <a href="reports.php">
                Reports
            </a>


            <a href="../notifications.php">
                Notifications
            </a>


            <a href="../logout.php">
                Logout
            </a>


        </div>


    </div>



    <!-- =====================================
         RIGHT SIDE STATISTICS
    ====================================== -->

    <div class="admin-stats-box">


        <h3>
            Event Statistics
        </h3>



        <div class="small-stat">

            <span>
                Total Events
            </span>

            <strong>
                <?php echo $total_events; ?>
            </strong>

        </div>



        <div class="small-stat">

            <span>
                Upcoming
            </span>

            <strong>
                <?php echo $upcoming_events; ?>
            </strong>

        </div>



        <div class="small-stat">

            <span>
                Completed
            </span>

            <strong>
                <?php echo $completed_events; ?>
            </strong>

        </div>



        <div class="small-stat">

            <span>
                Students
            </span>

            <strong>
                <?php echo $total_students; ?>
            </strong>

        </div>



        <div class="small-stat">

            <span>
                Registrations
            </span>

            <strong>
                <?php echo $total_registrations; ?>
            </strong>

        </div>



        <div class="small-stat">

            <span>
                Participants
            </span>

            <strong>
                <?php echo $total_participants; ?>
            </strong>

        </div>



        <div class="small-stat">

            <span>
                Pending
            </span>

            <strong>
                <?php echo $pending_events; ?>
            </strong>

        </div>



        <!-- =================================
             SMALL EVENT CHART
        ================================== -->

        <div class="mini-chart">


            <p class="mini-chart-title">
                Event Overview
            </p>



            <!-- UPCOMING -->

            <div class="chart-item">

                <div class="chart-label">

                    <span>
                        Upcoming
                    </span>

                    <span>
                        <?php echo $upcoming_events; ?>
                    </span>

                </div>


                <div class="chart-track">

                    <div
                        class="chart-bar"
                        style="width:
                        <?php echo $upcoming_width; ?>%;"
                    ></div>

                </div>

            </div>



            <!-- COMPLETED -->

            <div class="chart-item">

                <div class="chart-label">

                    <span>
                        Completed
                    </span>

                    <span>
                        <?php echo $completed_events; ?>
                    </span>

                </div>


                <div class="chart-track">

                    <div
                        class="chart-bar"
                        style="width:
                        <?php echo $completed_width; ?>%;"
                    ></div>

                </div>

            </div>



            <!-- PENDING -->

            <div class="chart-item">

                <div class="chart-label">

                    <span>
                        Pending
                    </span>

                    <span>
                        <?php echo $pending_events; ?>
                    </span>

                </div>


                <div class="chart-track">

                    <div
                        class="chart-bar"
                        style="width:
                        <?php echo $pending_width; ?>%;"
                    ></div>

                </div>

            </div>


        </div>


    </div>


</div>


</body>

</html>