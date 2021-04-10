<?php
require_once "../../Classes/PHP/user.php";
require_once "../../Classes/PHP/message.php";
require_once "../Functions/arrayMappers.php";
require_once "../../Classes/PHP/group.php";
require_once "../../Database/database.php";
$_SESSION["database"] = new Database();
$db = $_SESSION["database"];

$user = new User($_POST["username"]);
$classID = $_POST["classID"];
$class = new Group($classID);
$messages = $class->chatMessages;
$messageCount = count($messages);
if ($_POST["messageCount"] === $messageCount) echo $_POST["currentHTML"];
else {
    $html = "";
    for ($i = 0; $i < $messageCount; ++$i) $html = $html . $messages[$i]->render($user);
    echo $html;
}
