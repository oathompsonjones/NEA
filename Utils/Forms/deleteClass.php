<?php
require_once "../../Classes/PHP/group.php";
require_once "../../Database/database.php";
$_SESSION["database"] = new Database();
$db = $_SESSION["database"];

$id = $_POST["classID"];
$class = new Group($id);
$class->delete();
