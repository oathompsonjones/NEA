<?php
require_once "../../Classes/PHP/animation.php";
require_once "../../Database/database.php";
$_SESSION["database"] = new Database();

$animationID = $_POST["animationID"];
$username = $_POST["username"];
$fps = $_POST["fps"];
$animation = new Animation($animationID);
$animation->share($username, $fps);
