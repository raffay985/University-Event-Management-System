<?php

require_once __DIR__ . "/../config/session.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
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

    <title>Student Dashboard</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

</head>

<body>


<h1>Student Dashboard</h1>


<p>

    Welcome,

    <strong>

        <?php
        echo htmlspecialchars(
            $_SESSION["username"]
        );
        ?>

    </strong>

</p>


<ul>


    <li>

        <a href="events.php">
            View Events
        </a>

    </li>


    <li>

        <a href="registered_events.php">
            My Registered Events
        </a>

    </li>


    <li>

        <a href="reports.php">
            Event Reports
        </a>

    </li>


    <li>

        <a href="certificates.php">
            My Certificates
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