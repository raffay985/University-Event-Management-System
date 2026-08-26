<?php

require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

$search = "";

if (isset($_GET["search"])) {
    $search = trim($_GET["search"]);
}

if ($search != "") {

    $stmt = $conn->prepare(
        "SELECT * FROM events
         WHERE event_title LIKE ?
         OR category LIKE ?
         OR venue LIKE ?"
    );

    $searchTerm = "%" . $search . "%";

    $stmt->bind_param(
        "sss",
        $searchTerm,
        $searchTerm,
        $searchTerm
    );

    $stmt->execute();
    $result = $stmt->get_result();

} else {

    $result = $conn->query(
        "SELECT * FROM events ORDER BY event_date ASC"
    );
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Manage Events</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<h1>Manage Events</h1>

<a href="dashboard.php">Back to Dashboard</a>

<br><br>

<a href="add_event.php">Add New Event</a>

<br><br>

<form method="GET">

    <input
        type="text"
        name="search"
        placeholder="Search events"
        value="<?php echo htmlspecialchars($search); ?>"
    >

    <br><br>

    <button type="submit">Search</button>

</form>

<table>

<tr>
    <th>ID</th>
    <th>Title</th>
    <th>Description</th>
    <th>Category</th>
    <th>Date</th>
    <th>Start Time</th>
    <th>End Time</th>
    <th>Venue</th>
    <th>Max Participants</th>
    <th>Actions</th>
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
        <?php echo htmlspecialchars($event["description"]); ?>
    </td>

    <td>
        <?php echo htmlspecialchars($event["category"]); ?>
    </td>

    <td>
        <?php echo $event["event_date"]; ?>
    </td>

    <td>
        <?php echo $event["start_time"]; ?>
    </td>

    <td>
        <?php echo $event["end_time"]; ?>
    </td>

    <td>
        <?php echo htmlspecialchars($event["venue"]); ?>
    </td>

    <td>
        <?php echo $event["max_participants"]; ?>
    </td>

    <td>

        <a href="edit_event.php?id=<?php echo $event["event_id"]; ?>">
            Edit
        </a>

        |

        <a href="delete_event.php?id=<?php echo $event["event_id"]; ?>">
            Delete
        </a>

    </td>

</tr>

<?php } ?>

</table>

</body>
</html>