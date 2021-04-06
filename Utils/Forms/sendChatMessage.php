<?php
require_once "../../Classes/PHP/group.php";
require_once "../../Database/database.php";
$_SESSION["database"] = new Database();
$db = $_SESSION["database"];

$username = $_POST["username"];
$classID = $_POST["classID"];
$content = $_POST["message"];
$createdAt = time();
$id = md5(time() . $username);
$db->insert("ChatMessage", "MessageID, Username, ClassID, Content, CreatedAt", "'$id', '$username', '$classID', '$content', '$createdAt'");
