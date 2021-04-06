<?php
require_once "../../Classes/PHP/group.php";
require_once "../../Database/database.php";
$_SESSION["database"] = new Database();
$db = $_SESSION["database"];

$name = $_POST["className"];
$id = md5(time() . $name);
$db->insert("Class", "ClassID, Name, ChatEnabled", "'$id', '$name', 0");
$class = new Group($id);
$class->addUser($_POST["username"]);

echo $id;
