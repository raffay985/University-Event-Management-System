<?php

require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET["id"])) {
    header("Location: certificates.php");
    exit();
}

$certificate_id = (int) $_GET["id"];
$user_id = $_SESSION["user_id"];


/* Get certificate belonging to logged-in student */

$stmt = $conn->prepare(
    "SELECT
        c.certificate_id,
        c.certificate_number,
        c.issue_date,

        s.name AS student_name,
        s.roll_number,

        e.event_title,
        e.event_date

     FROM certificates c

     INNER JOIN students s
        ON c.student_id = s.student_id

     INNER JOIN events e
        ON c.event_id = e.event_id

     WHERE c.certificate_id = ?
     AND s.user_id = ?"
);

$stmt->bind_param(
    "ii",
    $certificate_id,
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();
$certificate = $result->fetch_assoc();

if (!$certificate) {
    die("Certificate not found or access denied.");
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

    <title>Certificate</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

    <style>

        .certificate-container {
            width: 90%;
            max-width: 900px;

            margin: 30px auto;

            padding: 55px;

            background: #fffaf7;

            border: 10px double #6d4c41;

            border-radius: 8px;

            box-shadow:
                0 8px 25px
                rgba(78, 52, 46, 0.20);

            text-align: center;
        }


        .certificate-container h1 {
            font-size: 40px;
            margin-bottom: 10px;
        }


        .certificate-university {
            font-size: 24px;
            font-weight: bold;
            color: #6d4c41;
        }


        .certificate-text {
            margin: 25px 0;
            font-size: 18px;
        }


        .certificate-name {
            font-size: 32px;
            font-weight: bold;
            color: #4e342e;
            margin: 15px 0;
        }


        .certificate-event {
            font-size: 24px;
            font-weight: bold;
            color: #6d4c41;
        }


        .certificate-info {
            margin-top: 35px;
            font-size: 14px;
        }


        .certificate-actions {
            margin: 20px auto;
        }


        @media print {

            body {
                background: white;
            }

            .certificate-actions {
                display: none;
            }

            .certificate-container {
                box-shadow: none;
                width: 100%;
            }
        }

    </style>

</head>

<body>


<div class="certificate-actions">

    <a href="certificates.php">
        Back to Certificates
    </a>

    &nbsp;&nbsp;

    <button onclick="window.print()">
        Print Certificate
    </button>

</div>


<div class="certificate-container">

    <div class="certificate-university">
        SZABIST
    </div>

    <h1>
        Certificate of Participation
    </h1>

    <p class="certificate-text">
        This certificate is proudly presented to
    </p>


    <div class="certificate-name">

        <?php
        echo htmlspecialchars(
            $certificate["student_name"]
        );
        ?>

    </div>


    <p class="certificate-text">

        for successfully participating in

    </p>


    <div class="certificate-event">

        <?php
        echo htmlspecialchars(
            $certificate["event_title"]
        );
        ?>

    </div>


    <p class="certificate-text">

        held on

        <strong>

            <?php
            echo htmlspecialchars(
                $certificate["event_date"]
            );
            ?>

        </strong>

    </p>


    <div class="certificate-info">

        <p>

            <strong>Certificate Number:</strong>

            <?php
            echo htmlspecialchars(
                $certificate["certificate_number"]
            );
            ?>

        </p>


        <p>

            <strong>Issue Date:</strong>

            <?php
            echo htmlspecialchars(
                $certificate["issue_date"]
            );
            ?>

        </p>

    </div>

</div>


</body>

</html>