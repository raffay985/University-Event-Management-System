<?php

require_once __DIR__ . "/../config/session.php";
require_once __DIR__ . "/../config/database.php";
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET["id"])) {
    header("Location: students.php");
    exit();
}

$id = (int) $_GET["id"];

$stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    die("Student not found.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $roll_number = trim($_POST["roll_number"]);
    $email = trim($_POST["email"]);
    $department = trim($_POST["department"]);
    $semester = (int) $_POST["semester"];

    try {

        $stmt = $conn->prepare(
            "UPDATE students
             SET name = ?, roll_number = ?, email = ?, department = ?, semester = ?
             WHERE student_id = ?"
        );

        $stmt->bind_param(
            "ssssii",
            $name,
            $roll_number,
            $email,
            $department,
            $semester,
            $id
        );

        $stmt->execute();

        $message = "Student updated successfully!";

        $student["name"] = $name;
        $student["roll_number"] = $roll_number;
        $student["email"] = $email;
        $student["department"] = $department;
        $student["semester"] = $semester;

    } catch (Exception $e) {

        $message = "Could not update student.";
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Student</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<h1>Edit Student</h1>

<a href="students.php">Back to Students</a>

<br><br>

<?php
if ($message != "") {
    echo "<p>" . htmlspecialchars($message) . "</p>";
}
?>

<form method="POST">

    <label>Name</label><br>
    <input
        type="text"
        name="name"
        value="<?php echo htmlspecialchars($student["name"]); ?>"
        required
    >

    <br><br>

    <label>Roll Number</label><br>
    <input
        type="text"
        name="roll_number"
        value="<?php echo htmlspecialchars($student["roll_number"]); ?>"
        required
    >

    <br><br>

    <label>Email</label><br>
    <input
        type="email"
        name="email"
        value="<?php echo htmlspecialchars($student["email"]); ?>"
        required
    >

    <br><br>

    <label>Department</label><br>
    <input
        type="text"
        name="department"
        value="<?php echo htmlspecialchars($student["department"]); ?>"
        required
    >

    <br><br>

    <label>Semester</label><br>
    <input
        type="number"
        name="semester"
        min="1"
        max="12"
        value="<?php echo $student["semester"]; ?>"
        required
    >

    <br><br>

    <button type="submit">Update Student</button>

</form>

</body>
</html>
