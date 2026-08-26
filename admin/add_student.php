<?php

require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $roll_number = trim($_POST["roll_number"]);
    $email = trim($_POST["email"]);
    $department = trim($_POST["department"]);
    $semester = (int) $_POST["semester"];
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {

        $conn->begin_transaction();

        // Create student login account
        $stmt = $conn->prepare(
            "INSERT INTO users (username, password, role)
             VALUES (?, ?, 'student')"
        );

        $stmt->bind_param("ss", $username, $hashedPassword);
        $stmt->execute();

        $user_id = $conn->insert_id;

        // Create student record
        $stmt = $conn->prepare(
            "INSERT INTO students
            (user_id, name, roll_number, email, department, semester)
            VALUES (?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "issssi",
            $user_id,
            $name,
            $roll_number,
            $email,
            $department,
            $semester
        );

        $stmt->execute();

        $conn->commit();

        $message = "Student added successfully!";

    } catch (Exception $e) {

        $conn->rollback();
        $message = "Could not add student. Username, roll number or email may already exist.";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Student</title>
    <link rel="stylesheet" href="../css/style.css"></head>

<body>

<h1>Add Student</h1>

<a href="students.php">Back to Students</a>

<br><br>

<?php
if ($message != "") {
    echo "<p>" . htmlspecialchars($message) . "</p>";
}
?>

<form method="POST">

    <label>Name</label><br>
    <input type="text" name="name" required>

    <br><br>

    <label>Roll Number</label><br>
    <input type="text" name="roll_number" required>

    <br><br>

    <label>Email</label><br>
    <input type="email" name="email" required>

    <br><br>

    <label>Department</label><br>
    <input type="text" name="department" required>

    <br><br>

    <label>Semester</label><br>
    <input type="number" name="semester" min="1" max="12" required>

    <br><br>

    <h3>Student Login Details</h3>

    <label>Username</label><br>
    <input type="text" name="username" required>

    <br><br>

    <label>Password</label><br>
    <input type="password" name="password" required>

    <br><br>

    <button type="submit">Add Student</button>

</form>

</body>
</html>


