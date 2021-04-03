<?php
require_once "../../Classes/PHP/user.php";
require_once "../../Database/database.php";
$_SESSION["database"] = new Database();

$loggedInUser = new User($_POST["loggedInUsername"]);
$loggedInUser->unfollowUser($_POST["username"]);
