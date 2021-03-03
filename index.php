<?php
// Start the session.
session_start();


// Import all classes.
require_once "Classes/PHP/user.php";
require_once "Classes/PHP/animation.php";
require_once "Database/database.php";
// Create new instance of the database handling class.
$_SESSION["database"] = new Database();

// Check which page the user is looking for.
$page = explode("?", str_replace("/", "", $_SERVER["REQUEST_URI"]), 2)[0];
if (strlen($page) === 0) $page = "home";
// Get the title for the page the user has asked for.
$title = ucfirst($page);

// Check if the user is already logged in.
$loggedIn = isset($_SESSION["auth"]) && $_SESSION["auth"];
// Check if the user is trying to log in or out.
$loggingInOrOut = $page === "login" || $page === "signup" || $page === "logout";

// Render page header.
require_once "Include/header.inc";

// Render a message telling the user they need to log in.
if (!$loggedIn && !$loggingInOrOut) {
    echo <<<HTML
        <h1>Microcontroller Animations</h1>
        <div class="alert alert-danger" role="alert">
            You are <strong>not</strong> logged in.
        </div>
    HTML;
}
// Redirect user to loin, logout or signup.
else if ($loggingInOrOut) require_once "Pages/$page.php";
// Redirect user to the requested page, taking into account their access level.
else {
    // Get the object for the current user.
    $user = unserialize($_SESSION["user"]);
    // Get the path for the requested page.
    $page = "Pages/$user->type/" . $page . ".php";
    if (preg_match("/.*(login)|(logout)|(signup)\.php/", $page)) $page = str_replace("/$user->type", "", $page);
    if (!file_exists($page)) $page = "Pages/404.php";
    // Render the requested page.
    require_once $page;
}

// Render page footer.
require_once "Include/footer.inc";
