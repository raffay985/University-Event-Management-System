<?php

/* =========================================
   SESSION CONFIGURATION
   Works locally and online
========================================= */

$currentHost = $_SERVER["HTTP_HOST"] ?? "localhost";


/* =========================================
   LOCAL DEVELOPMENT
========================================= */

$isLocal =
    $currentHost === "localhost" ||
    $currentHost === "localhost:8000" ||
    $currentHost === "127.0.0.1" ||
    $currentHost === "127.0.0.1:8000" ||
    strpos($currentHost, "192.168.18.14") !== false;


/*
Locally we use our own sessions folder
because this solved the previous session issue.
*/

if ($isLocal) {

    $sessionPath =
        dirname(__DIR__) .
        DIRECTORY_SEPARATOR .
        "sessions";


    if (!is_dir($sessionPath)) {

        mkdir(
            $sessionPath,
            0777,
            true
        );
    }


    session_save_path(
        $sessionPath
    );
}


/*
Online InfinityFree will use
its normal PHP session handling.
*/

if (
    session_status() !==
    PHP_SESSION_ACTIVE
) {

    session_start();
}

?>