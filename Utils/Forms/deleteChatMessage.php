<?php
require_once "../../Classes/PHP/message.php";
require_once "../../Database/database.php";
$_SESSION["database"] = new Database();

$id = $_POST["messageID"];
$message = new Message($id);
$message->delete();
