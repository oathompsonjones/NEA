<?php
require_once "../../Classes/PHP/user.php";
require_once "../Functions/arrayMappers.php";
require_once "../../Classes/PHP/group.php";
require_once "../../Database/database.php";
$_SESSION["database"] = new Database();
$db = $_SESSION["database"];

$classID = $_POST["classID"];
$class = new Group($classID);
$userIsMuted = in_array($_POST["username"], array_map("mapToUsernames", $class->mutedUsers));
if ($userIsMuted) echo "true";
else echo "false";
