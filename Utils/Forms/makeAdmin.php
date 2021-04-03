<?php
require_once "../../Classes/PHP/user.php";
require_once "../../Database/database.php";
$_SESSION["database"] = new Database();

(new User($_POST["username"]))->makeAdmin();
