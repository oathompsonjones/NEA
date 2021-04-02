<?php
require_once "../../Classes/PHP/animation.php";
require_once "../../Database/database.php";
require_once "../Functions/arrayMappers.php";
$_SESSION["database"] = new Database();
$db = $_SESSION["database"];

// Get session variables.
$width = $_POST["width"];
$height = $_POST["height"];
$type = $_POST["type"];
$id = $_POST["id"];
$name = $_POST["name"];
$frames = isset($_POST["data"]) && !is_null($_POST["data"]) ? json_decode($_POST["data"]) : [];
$username = $_POST["username"];
// Get a list of the currently saved animations.
$currentIDs = array_map("mapToFirstItem", $db->select("AnimationID", "Animation"));
// Check if the current animation already exists in the database.
$animationExists = $currentIDs ? in_array($id, $currentIDs) : FALSE;
// If it does, delete the saved frames.
if ($animationExists) $db->delete("Frame", "AnimationID = '$id'");
// If it doesn't add it.
else $db->insert("Animation", "AnimationID, Name, Username, Width, Height, Type", "'$id', '$name', '$username', $width, $height, $type");
// Get all of the current frames.
for ($i = 0; $i < count($frames); ++$i) $db->insert("Frame", "FrameID, AnimationID, FramePosition, BinaryString", "'$id$i', '$id', $i, '$frames[$i]'");
