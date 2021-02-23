<?php
if (!isset($_SESSION["width"])) $_SESSION["width"] = $_POST["width"];
if (!isset($_SESSION["height"])) $_SESSION["height"] = $_POST["height"];
if (!isset($_SESSION["type"])) $_SESSION["type"] = $_POST["type"];

if (!isset($_SESSION["type"]) || !isset($_SESSION["width"]) || !isset($_SESSION["height"])) {
    echo <<<HTML
        <h1>Animation Settings</h1>
        <form method="post">
            <label for="width">Width:</label>
            <input type="number" id="width" name="width" min="1" max="25" value="5">
            <br>
            <label for="height">Height:</label>
            <input type="number" id="height" name="height" min="1" max="25" value="5">
            <br>
            <input type="radio" id="0" name="type" value="0" checked>
            <label for="0">Monochromatic</label>
            <input type="radio" id="1" name="type" value="1">
            <label for="1">Variable brightness</label>
            <input type="radio" id="2" name="type" value="2">
            <label for="2">RGB</label>
            <br>
            <input class="btn btn-primary btn-sm" type="submit">
        </form>
    HTML;
} else include "Include/editor.inc";
?>