<?php
require_once "../../Classes/PHP/animation.php";
require_once "../../Database/database.php";
$_SESSION["database"] = new Database();

$animationID = $_POST["animationID"];
$fps = $_POST["fps"];
$animation = new Animation($animationID);
$animation->share($fps);
