<?php
require_once "../../Classes/PHP/post.php";
require_once "../../Database/database.php";
$_SESSION["database"] = new Database();

$animationID = $_POST["animationID"];
$postID = $_SESSION["database"]->select("PostID", "Post", "AnimationID = '$animationID'")[0][0];
$post = new Post($postID);
$post->delete();
