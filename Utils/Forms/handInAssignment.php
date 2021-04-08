<?php
require_once "../../Classes/PHP/assignment.php";
require_once "../../Database/database.php";
$_SESSION["database"] = new Database();
$db = $_SESSION["database"];

$assignmentID = $_POST["assignmentID"];
$animationID = $_POST["animationID"];
$username = $_POST["username"];
$createdAt = time();
$id = md5($assignmentID . $animationID . $username . $createdAt);
$db->insert("AssignmentWork", "WorkID, AssignmentID, Username, AnimationID, CreatedAt", "'$id', '$assignmentID', '$username', '$animationID', $createdAt");
