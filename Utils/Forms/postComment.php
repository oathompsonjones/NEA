<?php
require_once "../../Classes/PHP/post.php";
require_once "../../Database/database.php";
$_SESSION["database"] = new Database();

$postID = $_POST["postID"];
$username = $_POST["username"];
$content = $_POST["content"];
$post = new Post($postID);
$post->comment($username, $content);
