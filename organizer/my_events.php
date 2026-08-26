<?php

require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "organizer") {
    header("Location: ../login.php");
    exit();
}

$organizer_id = $_SESSION["user_id"];

$message = "";

if (isset($_GET["reminder"]) && $_GET["reminder"] === "sent") {
    $message = "Event reminder sent successfully!";
}


$stmt = $conn->prepare(
    "SELECT *
     FROM events
     WHERE organizer_id = ?
     ORDER BY event_date ASC"
);

$stmt->bind_param("i", $organizer_id);
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

    <title>My Events</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

</head>

<body>

<h1>My Events</h1>

<a href="dashboard.php">
    Back to Organizer Dashboard
</a>

<br><br>

<a href="create_event.php">
    Create New Event
</a>


<?php if ($message != "") { ?>

    <p class="success">

        <?php
        echo htmlspecialchars($message);
        ?>

    </p>

<?php } ?>


<?php if ($result->num_rows > 0) { ?>

<table>

<tr>

    <th>ID</th>

    <th>Event Title</th>

    <th>Category</th>

    <th>Date</th>

    <th>Time</th>

    <th>Venue</th>

    <th>Max Participants</th>

    <th>Status</th>

    <th>Actions</th>

</tr>


<?php while ($event = $result->fetch_assoc()) { ?>

<tr>

    <td>
        <?php echo $event["event_id"]; ?>
    </td>


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
            $event["category"]
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

    </td>


    <td>

        <?php
        echo htmlspecialchars(
            $event["venue"]
        );
        ?>

    </td>


    <td>
        <?php echo $event["max_participants"]; ?>
    </td>


    <td>

        <strong>

            <?php

            echo ucfirst(
                htmlspecialchars(
                    $event["approval_status"]
                )
            );

            ?>

        </strong>

    </td>


    <td>


        <!-- VIEW -->

        <a href="view_event.php?id=<?php echo $event["event_id"]; ?>">
            View
        </a>


        <!-- EDIT -->

        <?php if ($event["approval_status"] !== "cancelled") { ?>

            <a href="edit_event.php?id=<?php echo $event["event_id"]; ?>">
                Edit
            </a>

        <?php } ?>


        <!-- SEND REMINDER -->

        <?php if ($event["approval_status"] === "approved") { ?>

            <a
                href="send_reminder.php?id=<?php echo $event["event_id"]; ?>"
                onclick="return confirm('Send reminder to all registered students?');"
            >
                Send Reminder
            </a>

        <?php } ?>


        <!-- CANCEL EVENT -->

        <?php

        if (
            $event["approval_status"] === "approved" ||
            $event["approval_status"] === "pending"
        ) {

        ?>

            <a
                href="cancel_event.php?id=<?php echo $event["event_id"]; ?>"
                onclick="return confirm('Are you sure you want to cancel this event?');"
            >
                Cancel
            </a>

        <?php } ?>


    </td>

</tr>

<?php } ?>

</table>


<?php } else { ?>

<p>
    You have not created any events yet.
</p>

<?php } ?>


</body>

</html>