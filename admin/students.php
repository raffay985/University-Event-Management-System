
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
        "SELECT * FROM students
         WHERE name LIKE ?
         OR roll_number LIKE ?
         OR email LIKE ?
         OR department LIKE ?"
    );

    $searchTerm = "%" . $search . "%";

    $stmt->bind_param(
        "ssss",
        $searchTerm,
        $searchTerm,
        $searchTerm,
        $searchTerm
    );

    $stmt->execute();
    $result = $stmt->get_result();

} else {

    $result = $conn->query("SELECT * FROM students");
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Manage Students</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<h1>Manage Students</h1>

<a href="dashboard.php">Back to Dashboard</a>

<br><br>

<a href="add_student.php">Add New Student</a>

<br><br>

<form method="GET">

    <input
        type="text"
        name="search"
        placeholder="Search students"
        value="<?php echo htmlspecialchars($search); ?>"
    >

    <br><br>

    <button type="submit">Search</button>

</form>

<table>

<tr>
    <th>Student ID</th>
    <th>Name</th>
    <th>Roll Number</th>
    <th>Email</th>
    <th>Department</th>
    <th>Semester</th>
    <th>Actions</th>
</tr>

<?php while ($student = $result->fetch_assoc()) { ?>

<tr>

    <td>
        <?php echo $student["student_id"]; ?>
    </td>

    <td>
        <?php echo htmlspecialchars($student["name"]); ?>
    </td>

    <td>
        <?php echo htmlspecialchars($student["roll_number"]); ?>
    </td>

    <td>
        <?php echo htmlspecialchars($student["email"]); ?>
    </td>

    <td>
        <?php echo htmlspecialchars($student["department"]); ?>
    </td>

    <td>
        <?php echo $student["semester"]; ?>
    </td>

    <td>

        <a href="edit_student.php?id=<?php echo $student["student_id"]; ?>">
            Edit
        </a>

        |

        <a href="delete_student.php?id=<?php echo $student["student_id"]; ?>">
            Delete
        </a>

    </td>

</tr>

<?php } ?>

</table>

</body>
</html>