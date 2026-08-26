<?php

require_once __DIR__ . "/config/database.php";


/* =========================================
   CHECK EVENT ID
========================================= */

if (!isset($_GET["id"])) {
    die("Event not found.");
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
        href="css/style.css"
    >


    <style>

        /* ========================================
           PUBLIC EVENT PAGE
           Uses colors from main style.css
        ======================================== */

        .public-event-page {

            width: 95%;
            max-width: 700px;

            margin: 20px auto;

            text-align: center;
        }


        /* ========================================
           EVENT CARD
        ======================================== */

        .public-event-card {

            width: 100%;

            margin: 25px auto;

            padding: 30px;

            background:
                var(--soft2, #f7f7f7);

            border:
                1px solid
                var(--border, #cccccc);

            border-radius: 15px;

            box-shadow:
                0 6px 20px
                var(--shadow, rgba(0, 0, 0, 0.15));

            text-align: center;

            color:
                var(--text, #292929);
        }


        .public-event-card h2 {

            margin-top: 0;

            margin-bottom: 25px;

            font-size: 28px;

            color:
                var(--dark, #242424);
        }


        /* ========================================
           APPROVED EVENT BADGE
        ======================================== */

        .public-event-badge {

            display: inline-block;

            margin-bottom: 20px;

            padding: 7px 15px;

            background:
                linear-gradient(
                    135deg,
                    var(--main, #555555),
                    var(--dark, #242424)
                );

            color: white;

            border-radius: 20px;

            font-size: 12px;

            font-weight: bold;

            box-shadow:
                0 3px 8px
                var(--shadow, rgba(0, 0, 0, 0.15));
        }


        /* ========================================
           EVENT INFORMATION
        ======================================== */

        .public-event-item {

            margin: 18px auto;

            padding-bottom: 12px;

            border-bottom:
                1px solid
                var(--border, #cccccc);
        }


        .public-event-item:last-child {

            border-bottom: none;
        }


        .public-event-item strong {

            display: block;

            margin-bottom: 5px;

            font-size: 15px;

            color:
                var(--dark, #242424);
        }


        .public-event-item span {

            font-size: 16px;

            line-height: 1.6;

            color:
                var(--text, #292929);
        }


        /* ========================================
           REGISTER BUTTON
        ======================================== */

        .public-event-actions {

            margin:
                25px
                auto
                10px;

            text-align: center;
        }


        .public-event-actions a {

            display: inline-block;

            margin: 5px;

            padding: 12px 24px;

            background:
                linear-gradient(
                    135deg,
                    var(--main, #555555),
                    var(--dark, #242424)
                );

            color: white;

            text-decoration: none;

            border-radius: 8px;

            font-weight: 600;

            box-shadow:
                0 4px 10px
                var(--shadow, rgba(0, 0, 0, 0.15));

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease;
        }


        .public-event-actions a:hover {

            color: white;

            transform:
                translateY(-2px);

            box-shadow:
                0 6px 14px
                var(--shadow, rgba(0, 0, 0, 0.22));
        }


        /* ========================================
           NOTE
        ======================================== */

        .public-note {

            margin-top: 18px;

            font-size: 13px;

            color:
                var(--muted, #696969);
        }


        /* ========================================
           MOBILE
        ======================================== */

        @media (max-width: 600px) {

            body {

                padding:
                    20px
                    10px;
            }


            .public-event-page {

                width: 100%;
            }


            .public-event-card {

                padding:
                    22px
                    16px;
            }


            .public-event-card h2 {

                font-size: 24px;
            }


            .public-event-actions a {

                width: 100%;

                max-width: 280px;
            }

        }

    </style>

</head>


<body>


<div class="public-event-page">


    <h1>
        University Event
    </h1>


    <div class="public-event-card">


        <span class="public-event-badge">

            Approved Event

        </span>


        <h2>

            <?php
            echo htmlspecialchars(
                $event["event_title"]
            );
            ?>

        </h2>



        <div class="public-event-item">

            <strong>
                Description
            </strong>

            <span>

                <?php
                echo htmlspecialchars(
                    $event["description"]
                );
                ?>

            </span>

        </div>



        <div class="public-event-item">

            <strong>
                Category
            </strong>

            <span>

                <?php
                echo htmlspecialchars(
                    $event["category"]
                );
                ?>

            </span>

        </div>



        <div class="public-event-item">

            <strong>
                Event Date
            </strong>

            <span>

                <?php
                echo htmlspecialchars(
                    $event["event_date"]
                );
                ?>

            </span>

        </div>



        <div class="public-event-item">

            <strong>
                Time
            </strong>

            <span>

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

            </span>

        </div>



        <div class="public-event-item">

            <strong>
                Venue
            </strong>

            <span>

                <?php
                echo htmlspecialchars(
                    $event["venue"]
                );
                ?>

            </span>

        </div>



        <div class="public-event-item">

            <strong>
                Maximum Participants
            </strong>

            <span>

                <?php
                echo $event["max_participants"];
                ?>

            </span>

        </div>



        <div class="public-event-actions">


            <a
                href="student/register.php?id=<?php echo $event["event_id"]; ?>"
            >

                Register for Event

            </a>


        </div>


        <p class="public-note">

            You may be asked to login as a student
            before completing registration.

        </p>


    </div>


</div>


</body>

</html>