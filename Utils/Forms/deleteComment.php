<?php
require_once "../../Classes/PHP/comment.php";
require_once "../../Database/database.php";
$_SESSION["database"] = new Database();

$commentID = $_POST["commentID"];
$comment = new Comment($commentID);
$comment->delete();
