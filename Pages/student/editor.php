<?php
$tmpWidth = $_POST["width"];
$tmpHeight = $_POST["height"];
$tmpType = $_POST["type"];

if (isset($_POST["setup"])) {
    switch ($_POST["setup"]) {
        case "microbit": {
            $tmpWidth = 5;
            $tmpHeight = 5;
            $tmpType = 1;
        }
    }
}

if (!isset($_SESSION["width"])) $_SESSION["width"] = $tmpWidth;
if (!isset($_SESSION["height"])) $_SESSION["height"] = $tmpHeight;
if (!isset($_SESSION["type"])) $_SESSION["type"] = $tmpType;

if (!isset($_SESSION["type"]) || !isset($_SESSION["width"]) || !isset($_SESSION["height"])) {
    echo <<<HTML
        <h1>Animation Settings</h1>
        <form method="post">
            <div id="setup">
                <h3>Setup</h3>
                <input type="radio" id="custom" name="setup" value="custom" checked>
                <label for="custom">Custom</label>
                <input type="radio" id="microbit" name="setup" value="microbit">
                <label for="microbit">Micro:Bit</label>
            </div>
            <div id="customSetup">
                <h3>Custom Setup</h3>
                <label for="width">Width:</label>
                <input type="number" id="width" name="width" min=1 max=25 value=5>
                <br>
                <label for="height">Height:</label>
                <input type="number" id="height" name="height" min=1 max=25 value=5>
                <br>
                <input type="radio" id="0" name="type" value=0 checked>
                <label for="0">Monochromatic</label>
                <input type="radio" id="1" name="type" value=1>
                <label for="1">Variable brightness</label>
                <input type="radio" id="2" name="type" value=2>
                <label for="2">RGB</label>
                <br>
            </div>
            <input class="btn btn-primary btn-sm" type="submit">
        </form>
        <script>
            $("input[name=setup]").on("click", () => {
                const customRadioButton = document.getElementById("custom");
                const customSetupDiv = document.getElementById("customSetup");
                if (customRadioButton.checked) customSetupDiv.style.display = "block";
                else customSetupDiv.style.display = "none";
            });
        </script>
    HTML;
} else include "Include/editor.inc";
?>