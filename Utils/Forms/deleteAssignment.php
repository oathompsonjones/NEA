<?php
require_once "../../Classes/PHP/assignment.php";
require_once "../../Database/database.php";
$_SESSION["database"] = new Database();

$id = $_POST["assignmentID"];
$assignment = new Assignment($id);
$assignment->delete();
