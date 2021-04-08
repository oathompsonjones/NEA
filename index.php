<?php
// Start the session.
session_start();

// Create instance of database class and store in session.
require_once "Database/database.php";
$_SESSION["database"] = new Database();
$db = $_SESSION["database"];

// Import all classes.
require_once "Classes/PHP/user.php";
require_once "Classes/PHP/animation.php";
require_once "Classes/PHP/frame.php";
require_once "Classes/PHP/post.php";
require_once "Classes/PHP/comment.php";
require_once "Classes/PHP/group.php";
require_once "Classes/PHP/message.php";
require_once "Classes/PHP/assignment.php";
require_once "Classes/PHP/assignmentWork.php";

// Import useful functions.
require_once "Utils/Functions/arrayMappers.php";
require_once "Utils/Functions/arraySorters.php";

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
echo <<<HTML
    <script>
        const setCookie = (name, value) => document.cookie = name + "=" + value + ";";
        const getCookie = (name) => document.cookie.match(/(firstName=)([^;]*)(;\s)/g)?.[0].split("=")[1].replace("; ", "");
    </script>
HTML;

// Render a message telling the user they need to log in.
if (!$loggedIn && !$loggingInOrOut) {
    echo <<<HTML
        <h1>Microcontroller Animations</h1>
        <div class="alert alert-danger" role="alert">
            You are <strong>not</strong> logged in.
        </div>
    HTML;
}
// Redirect user to the requested page, taking into account their access level.
else {
    // Get the path for the requested page.
    $page = "Pages/$page.php";
    if (!file_exists($page)) $page = "Pages/404.php";
    // Render the requested page.
    require_once $page;
}

// Render page footer.
require_once "Include/footer.inc";
