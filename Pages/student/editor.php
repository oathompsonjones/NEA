<?php
    $type = $_POST["type"] == null ? -1 : array_search(
        $_POST["type"], 
        array("Monochromatic", "Variable brightness", "RGB")
    );
    $width = $_POST["width"];
    $height = $_POST["height"];

    if($type === -1) {
        echo <<<HTML
            <h1>Animation Settings</h1>
            <form method="post">
                <label for="width">Width:</label>
                <input type="number" name="width" min="1" max="25" value="5">
                <br>
                <label for="height">Height:</label>
                <input type="number" name="height" min="1" max="25" value="5">
                <br>
                <label for="type">Type:</label>
                <input name="type" list="types">
                <datalist id="types">
                    <option value="Monochromatic">
                    <option value="Variable brightness">
                    <option value="RGB">
                </datalist>
                <br>
                <input class="btn btn-primary btn-sm" type="submit">
            </form>
        HTML;
    } else {
        include "Include/editor.inc";
    }
?>