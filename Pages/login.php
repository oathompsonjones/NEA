<?php
// Valid usernames and passwords.
$credentials = $_SESSION["database"]->getLoginCredentials();

// Checks that the auth variable exists and is true.
if (isset($_SESSION["auth"]) && $_SESSION["auth"]) {
    // Redirects you back to the home page if already logged in.
    require "Include/redirect.inc";
} else if (isset($_GET["submit"])) {
    // Get form data.
    $username = $_POST["username"];
    $password = $_POST["password"];
    // Tell user if username wasn't provided.
    if (!strlen($username)) {
        echo <<<HTML
            <div class="alert alert-danger" role="alert">
                No username was given.
            </div>
        HTML;
    }
    // Tell user if password wasn't provided.
    else if (!strlen($password)) {
        echo <<<HTML
            <div class="alert alert-danger" role="alert">
                No password was given.
            </div>
        HTML;
    }
    // Tell user if username or password are incorrect.
    else if ($credentials[$username] != md5($password)) {
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
        require "Include/redirect.inc";
    }
    require "Include/loginForm.inc";
} else require "Include/loginForm.inc";
?>