<?php

require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET["id"])) {
    header("Location: events.php");
    exit();
}

$event_id = (int) $_GET["id"];


/* =========================================
   GET APPROVED EVENT
========================================= */

$stmt = $conn->prepare(
    "SELECT *
     FROM events
     WHERE event_id = ?
     AND approval_status = 'approved'"
);

$stmt->bind_param(
    "i",
    $event_id
);

$stmt->execute();

$result = $stmt->get_result();

$event = $result->fetch_assoc();


if (!$event) {
    die("This event is not available.");
}


/* =========================================
   AUTOMATIC WEBSITE ADDRESS
========================================= */

$currentHost =
    $_SERVER["HTTP_HOST"] ?? "localhost";


/*
If running locally using localhost,
use laptop Wi-Fi IP so phone can scan QR.
*/

if (
    $currentHost === "localhost:8000" ||
    $currentHost === "localhost" ||
    $currentHost === "127.0.0.1:8000" ||
    $currentHost === "127.0.0.1"
) {

    $baseUrl =
        "http://192.168.18.14:8000";

} else {

    /* Detect HTTP or HTTPS */

    $protocol = "http";


    if (
        (!empty($_SERVER["HTTPS"]) &&
         $_SERVER["HTTPS"] !== "off")
        ||
        (
            isset($_SERVER["HTTP_X_FORWARDED_PROTO"])
            &&
            $_SERVER["HTTP_X_FORWARDED_PROTO"] === "https"
        )
    ) {

        $protocol = "https";
    }


    $baseUrl =
        $protocol .
        "://" .
        $currentHost;
}


/* =========================================
   PUBLIC EVENT PAGE URL
========================================= */

$publicEventUrl =
    $baseUrl .
    "/public_event.php?id=" .
    $event["event_id"];


/* =========================================
   QR CODE
========================================= */

$qrEncoded =
    urlencode(
        $publicEventUrl
    );


/*
Cache buster prevents old QR image
from being reused.
*/

$cacheBuster =
    time();


$qrUrl =
    "https://quickchart.io/qr" .
    "?text=" .
    $qrEncoded .
    "&size=220" .
    "&margin=2" .
    "&v=" .
    $cacheBuster;

?>

<!DOCTYPE html>

<html>

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Event Details
    </title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >


    <style>

        .event-details-page {

            width: 95%;

            max-width: 950px;

            margin:
                0
                auto;

            text-align:
                center;
        }


        .event-details-wrapper {

            width: 100%;

            margin:
                30px
                auto;

            display:
                grid;

            grid-template-columns:
                minmax(0, 1fr)
                260px;

            gap:
                25px;

            align-items:
                start;
        }


        /* ========================================
           EVENT INFORMATION
        ======================================== */

        .event-information {

            background:
                var(--soft2, #f3fbfa);

            border:
                1px solid
                var(--border, #a8dcd7);

            border-radius:
                14px;

            padding:
                30px;

            box-shadow:
                0 5px 16px
                var(--shadow, rgba(18, 79, 89, 0.17));

            text-align:
                center;
        }


        .event-information h2 {

            margin-top:
                0;

            margin-bottom:
                25px;

            color:
                var(--darkest, #0d3b45);

            font-size:
                27px;
        }


        .event-information p {

            margin:
                16px
                auto;

            line-height:
                1.6;

            color:
                var(--text, #163f46);

            text-align:
                center;
        }


        .event-information strong {

            color:
                var(--dark, #124f59);
        }


        /* ========================================
           QR CODE BOX
        ======================================== */

        .event-qr-box {

            background:
                var(--soft2, #f3fbfa);

            border:
                1px solid
                var(--border, #a8dcd7);

            border-radius:
                14px;

            padding:
                18px;

            box-shadow:
                0 5px 16px
                var(--shadow, rgba(18, 79, 89, 0.17));

            text-align:
                center;
        }


        .event-qr-box h3 {

            margin-top:
                0;

            margin-bottom:
                12px;

            color:
                var(--darkest, #0d3b45);

            font-size:
                18px;
        }


        .event-qr-box img {

            width:
                210px;

            max-width:
                100%;

            height:
                auto;

            display:
                block;

            margin:
                10px
                auto
                15px;

            border-radius:
                8px;
        }


        .qr-label {

            display:
                inline-block;

            padding:
                7px
                13px;

            background:
                linear-gradient(
                    135deg,
                    var(--main, #168b86),
                    var(--dark, #124f59)
                );

            color:
                white;

            border-radius:
                7px;

            font-size:
                12px;

            font-weight:
                bold;
        }


        .event-qr-box p {

            margin:
                12px
                auto
                0;

            font-size:
                13px;

            line-height:
                1.5;

            color:
                var(--muted, #52777b);

            text-align:
                center;
        }


        /* ========================================
           BUTTONS
        ======================================== */

        .event-actions {

            margin:
                25px
                auto;

            text-align:
                center;
        }


        .event-actions a {

            display:
                inline-block;

            margin:
                5px;

            padding:
                11px
                20px;

            background:
                linear-gradient(
                    135deg,
                    var(--main, #168b86),
                    var(--dark, #124f59)
                );

            color:
                white;

            border-radius:
                8px;

            text-decoration:
                none;

            font-weight:
                600;
        }


        .event-actions a:hover {

            color:
                white;

            transform:
                translateY(-2px);
        }


        /* ========================================
           MOBILE
        ======================================== */

        @media (max-width: 700px) {

            .event-details-wrapper {

                grid-template-columns:
                    1fr;

                width:
                    96%;
            }


            .event-information {

                padding:
                    22px;
            }


            .event-qr-box {

                width:
                    100%;

                max-width:
                    290px;

                margin:
                    auto;
            }

        }

    </style>

</head>


<body>


<div class="event-details-page">


    <h1>
        Event Details
    </h1>


    <div class="event-details-wrapper">


        <!-- EVENT INFORMATION -->

        <div class="event-information">


            <h2>

                <?php
                echo htmlspecialchars(
                    $event["event_title"]
                );
                ?>

            </h2>


            <p>

                <strong>
                    Description
                </strong>

                <br>

                <?php
                echo htmlspecialchars(
                    $event["description"]
                );
                ?>

            </p>


            <p>

                <strong>
                    Category
                </strong>

                <br>

                <?php
                echo htmlspecialchars(
                    $event["category"]
                );
                ?>

            </p>


            <p>

                <strong>
                    Date
                </strong>

                <br>

                <?php
                echo htmlspecialchars(
                    $event["event_date"]
                );
                ?>

            </p>


            <p>

                <strong>
                    Start Time
                </strong>

                <br>

                <?php
                echo htmlspecialchars(
                    $event["start_time"]
                );
                ?>

            </p>


            <p>

                <strong>
                    End Time
                </strong>

                <br>

                <?php
                echo htmlspecialchars(
                    $event["end_time"]
                );
                ?>

            </p>


            <p>

                <strong>
                    Venue
                </strong>

                <br>

                <?php
                echo htmlspecialchars(
                    $event["venue"]
                );
                ?>

            </p>


            <p>

                <strong>
                    Maximum Participants
                </strong>

                <br>

                <?php
                echo $event["max_participants"];
                ?>

            </p>


        </div>



        <!-- QR CODE -->

        <div class="event-qr-box">


            <h3>
                Event QR Code
            </h3>


            <img

                src="<?php
                echo htmlspecialchars(
                    $qrUrl
                );
                ?>"

                alt="Event QR Code"

            >


            <span class="qr-label">

                Scan Event Details

            </span>


            <p>

                Scan with your phone to
                open this event directly.

            </p>


        </div>


    </div>



    <!-- BUTTONS -->

    <div class="event-actions">


        <a href="register.php?id=<?php echo $event["event_id"]; ?>">

            Register for Event

        </a>


        <a href="events.php">

            Back to Events

        </a>


    </div>


</div>


</body>

</html>