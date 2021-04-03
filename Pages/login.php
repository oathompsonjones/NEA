<?php
// Valid usernames and passwords.
$result = $_SESSION["database"]->select("Username, PasswordHash", "User");
$credentials = [];
if (!is_null($result)) while ($row = array_shift($result)) $credentials[$row[0]] = $row[1];

// Checks that the auth variable exists and is true.
if (isset($_SESSION["auth"]) && $_SESSION["auth"]) {
    // Redirects you back to the home page if already logged in.
    require_once "Include/redirect.inc";
} else if (isset($_GET["submit"])) {
    // Get form data.
    $username = $_POST["username"];
    $password = $_POST["password"];
    // Tell user if username or password are incorrect.
    if ($credentials[$username] !== md5($password)) {
        echo <<<HTML
            <div class="alert alert-danger" role="alert">
                Incorrect username or password.
            </div>
        HTML;
    }
    // Login
    else {
        // Set session variable to keep track of the user.
        $_SESSION["auth"] = true;
        $_SESSION["user"] = serialize(new User($username));
        require_once "Include/redirect.inc";
    }
    require_once "Include/loginForm.inc";
} else require_once "Include/loginForm.inc";
