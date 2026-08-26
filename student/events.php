<?php

require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    header("Location: ../login.php");
    exit();
}

$search = "";
$category = "";

if (isset($_GET["search"])) {
    $search = trim($_GET["search"]);
}

if (isset($_GET["category"])) {
    $category = trim($_GET["category"]);
}

/* Only categories from approved upcoming events */
$categoryResult = $conn->query(
    "SELECT DISTINCT category
     FROM events
     WHERE approval_status = 'approved'
     AND event_date >= CURDATE()
     ORDER BY category"
);

/* Only approved upcoming events */
$sql = "
    SELECT *
    FROM events
    WHERE approval_status = 'approved'
    AND event_date >= CURDATE()
";

$params = [];
$types = "";

if ($search != "") {

    $sql .= "
        AND (
            event_title LIKE ?
            OR description LIKE ?
            OR venue LIKE ?
        )
    ";

    $searchTerm = "%" . $search . "%";

    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;

    $types .= "sss";
}

if ($category != "") {

    $sql .= " AND category = ?";

    $params[] = $category;
    $types .= "s";
}

$sql .= " ORDER BY event_date ASC, start_time ASC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

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

    <title>Upcoming Events</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

</head>

<body>

<h1>Upcoming Events</h1>

<a href="dashboard.php">
    Back to Dashboard
</a>

<form method="GET">

    <label>Search Events</label>

    <input
        type="text"
        name="search"
        placeholder="Search by title, description or venue"
        value="<?php echo htmlspecialchars($search); ?>"
    >

    <br><br>

    <label>Category</label>

    <select name="category">

        <option value="">
            All Categories
        </option>

        <?php while ($cat = $categoryResult->fetch_assoc()) { ?>

            <option
                value="<?php echo htmlspecialchars($cat["category"]); ?>"
                <?php
                if ($category == $cat["category"]) {
                    echo "selected";
                }
                ?>
            >

                <?php
                echo htmlspecialchars($cat["category"]);
                ?>

            </option>

        <?php } ?>

    </select>

    <br><br>

    <button type="submit">
        Search / Filter
    </button>

</form>

<table>

<tr>

    <th>Title</th>
    <th>Category</th>
    <th>Date</th>
    <th>Start Time</th>
    <th>End Time</th>
    <th>Venue</th>
    <th>Action</th>

</tr>

<?php while ($event = $result->fetch_assoc()) { ?>

<tr>

    <td>
        <?php echo htmlspecialchars($event["event_title"]); ?>
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

        <a href="event_details.php?id=<?php echo $event["event_id"]; ?>">
            View Details
        </a>

        <a href="register.php?id=<?php echo $event["event_id"]; ?>">
            Register
        </a>

    </td>

</tr>

<?php } ?>

</table>

</body>
</html>
