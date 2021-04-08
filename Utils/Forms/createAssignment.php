<?php
require_once "../../Classes/PHP/assignment.php";
require_once "../../Database/database.php";
$_SESSION["database"] = new Database();
$db = $_SESSION["database"];

$description = $_POST["assignmentDescription"];
$dueAt = strtotime($_POST["assignmentDueAt"]);
$classID = $_POST["classID"];
$createdAt = time();
$id = md5($description . $createdAt . $dueAt);
$db->insert("Assignment", "AssignmentID, ClassID, Description, CreatedAt, DueAt", "'$id', '$classID', '$description', $createdAt, $dueAt");
