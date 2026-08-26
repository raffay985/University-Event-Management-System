<?php

require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

$result = $conn->query(
    "SELECT
        e.*,
        u.username AS organizer_name
     FROM events e
     LEFT JOIN users u
        ON e.organizer_id = u.user_id
     WHERE e.organizer_id IS NOT NULL
     ORDER BY e.created_at DESC"
);

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Event Approval</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >
</head>

<body>

<h1>Event Approval</h1>

<a href="dashboard.php">
    Back to Admin Dashboard
</a>

<table>

<tr>
    <th>ID</th>
    <th>Event</th>
    <th>Organizer</th>
    <th>Category</th>
    <th>Date</th>
    <th>Venue</th>
    <th>Status</th>
    <th>Action</th>
</tr>

<?php while ($event = $result->fetch_assoc()) { ?>

<tr>

    <td>
        <?php echo $event["event_id"]; ?>
    </td>

    <td>
        <?php echo htmlspecialchars($event["event_title"]); ?>
    </td>

    <td>
        <?php echo htmlspecialchars($event["organizer_name"] ?? "Unknown"); ?>
    </td>

    <td>
        <?php echo htmlspecialchars($event["category"]); ?>
    </td>

    <td>
        <?php echo $event["event_date"]; ?>
    </td>

    <td>
        <?php echo htmlspecialchars($event["venue"]); ?>
    </td>

    <td>
        <strong>
            <?php echo ucfirst($event["approval_status"]); ?>
        </strong>
    </td>

    <td>

        <?php if ($event["approval_status"] === "pending") { ?>

            <a href="approve_event.php?id=<?php echo $event["event_id"]; ?>">
                Approve
            </a>

            <a href="reject_event.php?id=<?php echo $event["event_id"]; ?>">
                Reject
            </a>

        <?php } else { ?>

            No action required

        <?php } ?>

    </td>

</tr>

<?php } ?>

</table>

</body>
</html>