<?php
require_once "../../Classes/PHP/animation.php";
require_once "../../Database/database.php";
$_SESSION["database"] = new Database();

$animation = new Animation($_POST["animationID"]);
$animation->delete();
