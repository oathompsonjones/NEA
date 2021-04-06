<?php
require_once "../../Classes/PHP/group.php";
require_once "../../Database/database.php";
$_SESSION["database"] = new Database();
$db = $_SESSION["database"];

$name = $_POST["name"];
$chatEnabled = $_POST["chatEnabled"];
$id = $_POST["classID"];
$db->update("Class", ["Name", "ChatEnabled"], [$name, intval($chatEnabled)], "ClassID = '$id'");
