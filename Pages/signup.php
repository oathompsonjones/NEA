<?php
// Valid usernames.
$usernames = $_SESSION["database"]->select("Username", "User");

// Checks that the auth variable exists and is true.
if (isset($_SESSION["auth"]) && $_SESSION["auth"]) {
    // Redirects you back to the home page if already logged in.
    require_once "Include/redirect.inc";
} else if (isset($_GET["submit"])) {
    // Get form data.
    $username = substr(preg_replace("/[^0-9A-Za-z]/", "", $_POST["username"]), 0, 32);
    $password = $_POST["password"];
    $confirmPassword = $_POST["passwordConfirmation"];
    $type = intval($_POST["type"]);
    // Tell user to enter a username.
    if (is_null($username) || strlen($username) === 0) {
        echo <<<HTML
            <div class="alert alert-danger" role="alert">
                No username was entered.
            </div>
        HTML;
    }
    // Tell user to enter a password.
    else if (is_null($password) || strlen($password) === 0) {
        echo <<<HTML
            <div class="alert alert-danger" role="alert">
                No password was entered.
            </div>
        HTML;
    }
    // Tell user to enter a password confirmation.
    else if (is_null($confirmPassword) || strlen($confirmPassword) === 0) {
        echo <<<HTML
            <div class="alert alert-danger" role="alert">
                No password confirmation was entered.
            </div>
        HTML;
    }
    // Tell user if username is taken.
    else if (!is_null($_SESSION["database"]->select("Username", "User", "Username = '$username'")[0])) {
        echo <<<HTML
            <div class="alert alert-danger" role="alert">
                Username is already taken.
            </div>
        HTML;
    }
    // Tell user if passwords don't match.
    else if ($password != $confirmPassword) {
        echo <<<HTML
            <div class="alert alert-danger" role="alert">
                Passwords do not match.
            </div>
        HTML;
    }
    // Create new user in db.
    else {
        $passwordHash = md5($password);
        $_SESSION["database"]->insert("User", "Username, PasswordHash, Type", "'$username', '$passwordHash', '$type'");
        // Log in new user and set session variable to keep track of the user.
        $_SESSION["auth"] = true;
        $_SESSION["user"] = serialize(new User($username));
        require_once "Include/redirect.inc";
    }
    require_once "Include/signupForm.inc";
} else require_once "Include/signupForm.inc";
