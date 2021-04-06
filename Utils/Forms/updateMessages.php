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
if ($_POST["messageCount"] === count($messages)) echo $_POST["currentHTML"];
else {
    $html = "";
    for ($i = 0; $i < count($messages); ++$i) $html = $html . $messages[$i]->render($user);
    echo $html;
}
