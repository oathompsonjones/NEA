<?php
require_once "../../Classes/PHP/group.php";
require_once "../../Database/database.php";
$_SESSION["database"] = new Database();

$class = new Group($_POST["classID"]);
$class->addUser($_POST["username"]);
