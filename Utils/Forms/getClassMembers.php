<?php
require_once "../../Classes/PHP/user.php";
require_once "../Functions/arrayMappers.php";
require_once "../../Classes/PHP/group.php";
require_once "../../Database/database.php";
$_SESSION["database"] = new Database();

$class = new Group($_POST["classID"]);
$students = $class->students;
$teachers = $class->teachers;

foreach ($teachers as $teacher) echo $teacher->username . "<br>";
foreach ($students as $student) echo $student->username . "<br>";
