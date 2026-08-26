<?php

$current_host = $_SERVER["HTTP_HOST"] ?? "localhost";

if (
    $current_host === "localhost" ||
    $current_host === "127.0.0.1" ||
    strpos($current_host, "192.168.18.14") !== false
) {

    $host = "localhost";
    $username = "YOUR_LOCAL_USERNAME";
    $password = "YOUR_LOCAL_PASSWORD";
    $database = "university_event_system";

} else {

    $host = "YOUR_ONLINE_MYSQL_HOST";
    $username = "YOUR_ONLINE_MYSQL_USERNAME";
    $password = "YOUR_ONLINE_MYSQL_PASSWORD";
    $database = "YOUR_ONLINE_DATABASE_NAME";
}

$conn = new mysqli(
    $host,
    $username,
    $password,
    $database
);

if ($conn->connect_error) {
    die(
        "Database connection failed: " .
        $conn->connect_error
    );
}

$conn->set_charset("utf8mb4");

?>