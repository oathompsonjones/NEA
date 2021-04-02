<?php
require_once "../../Classes/PHP/post.php";
require_once "../../Database/database.php";
$_SESSION["database"] = new Database();

$postID = $_POST["postID"];
$username = $_POST["username"];
$post = new Post($postID);
$post->like($username);
