<?php

require_once __DIR__ . "/config/session.php";
require_once __DIR__ . "/config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare(
        "SELECT user_id, username, password, role
         FROM users
         WHERE username = ?"
    );

    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $user = $result->fetch_assoc();

        if (password_verify($password, $user["password"])) {

            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["role"] = $user["role"];

            if ($user["role"] === "admin") {

                header("Location: admin/dashboard.php");
                exit();

            } elseif ($user["role"] === "student") {

                header("Location: student/dashboard.php");
                exit();

            } elseif ($user["role"] === "organizer") {

                header("Location: organizer/dashboard.php");
                exit();

            } else {

                $message = "Invalid user role.";
            }

        } else {

            $message = "Invalid username or password.";
        }

    } else {

        $message = "Invalid username or password.";
    }
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
        University Event Management System
    </title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>

<body class="login-page">

<div class="login-wrapper">

    <div class="login-card">

        <div class="login-logo">
            UEMS
        </div>

        <h1 class="login-title">
            University Event
            <br>
            Management System
        </h1>

        <p class="login-subtitle">
            Admin • Student • Organizer
        </p>

        <?php if ($message != "") { ?>

            <div class="login-error">

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php } ?>

        <form
            method="POST"
            class="login-form"
        >

            <div class="form-group">

                <label>
                    Username
                </label>

                <input
                    type="text"
                    name="username"
                    placeholder="Enter your username"
                    autocomplete="username"
                    required
                >

            </div>

            <div class="form-group">

                <label>
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    placeholder="Enter your password"
                    autocomplete="current-password"
                    required
                >

            </div>

            <button
                type="submit"
                class="login-button"
            >
                Login
            </button>

        </form>

        <div class="login-footer">

            <p>
                University Event Management Portal
            </p>

            <span>
                Admin, Student & Organizer Access
            </span>

        </div>

    </div>

</div>

</body>
</html>