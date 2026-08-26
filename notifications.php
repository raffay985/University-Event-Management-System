<?php

require_once __DIR__ . "/config/session.php";
require_once __DIR__ . "/config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

/* Mark all notifications as read */
$stmt = $conn->prepare(
    "UPDATE notifications
     SET is_read = 1
     WHERE user_id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

/* Get notifications */
$stmt = $conn->prepare(
    "SELECT *
     FROM notifications
     WHERE user_id = ?
     ORDER BY created_at DESC"
);

$stmt->bind_param("i", $user_id);
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

    <title>Notifications</title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>

<body>

<h1>Notifications</h1>

<?php if ($_SESSION["role"] === "admin") { ?>

    <a href="admin/dashboard.php">
        Back to Dashboard
    </a>

<?php } elseif ($_SESSION["role"] === "student") { ?>

    <a href="student/dashboard.php">
        Back to Dashboard
    </a>

<?php } elseif ($_SESSION["role"] === "organizer") { ?>

    <a href="organizer/dashboard.php">
        Back to Dashboard
    </a>

<?php } ?>

<br><br>

<table>

<tr>
    <th>Type</th>
    <th>Message</th>
    <th>Date</th>
</tr>

<?php while ($notification = $result->fetch_assoc()) { ?>

<tr>

    <td>
        <?php
        echo htmlspecialchars(
            ucwords(
                str_replace(
                    "_",
                    " ",
                    $notification["type"]
                )
            )
        );
        ?>
    </td>

    <td>
        <?php
        echo htmlspecialchars(
            $notification["message"]
        );
        ?>
    </td>

    <td>
        <?php echo $notification["created_at"]; ?>
    </td>

</tr>

<?php } ?>

</table>

</body>

</html>