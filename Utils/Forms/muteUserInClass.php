<?php
require_once "../../Classes/PHP/group.php";
require_once "../../Database/database.php";
$_SESSION["database"] = new Database();

(new Group($_POST["classID"]))->muteUser($_POST["username"]);
