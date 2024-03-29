<?php
// Checks if the user has requested to create a new animation, then updates cookies and session accordingly.
if (isset($_POST["createNew"]) && $_POST["createNew"] === "true") {
    unset($_SESSION["matrix"]);
    unset($_SESSION["editor"]);
    unset($_COOKIE["data"]);
    unset($_COOKIE["fps"]);
    unset($_COOKIE["colour"]);
}

// Checks if the user has selected a pre-made animation, and updates session.
if (isset($_POST["preMade"]) && $_POST["preMade"] !== "New") {
    function findAnimation($value)
    {
        return $value->id === $_POST["preMade"];
    }
    // Searches for the animation.
    $animation = array_values(array_filter(unserialize($_SESSION["user"])->animations, "findAnimation"))[0];
    // Updates the session variables.
    $_SESSION["matrix"]["width"] = $animation->width;
    $_SESSION["matrix"]["height"] = $animation->height;
    $_SESSION["matrix"]["type"] = $animation->type;
    $_SESSION["matrix"]["name"] = $animation->name;
    $_SESSION["matrix"]["id"] = $animation->id;
    $frames = is_null($animation->frames) ? [] : $animation->frames;
    $_SESSION["editor"]["data"] = json_encode(array_map("mapToBinary", $frames));
}
// Checks that the user has specified a setup.
else if (isset($_POST["setup"]) && isset($_POST["name"]) && !is_null($_POST["name"])) {
    // Updates session.
    $_SESSION["matrix"]["name"] = $_POST["name"];
    $_SESSION["matrix"]["id"] = $_POST["id"];
    switch ($_POST["setup"]) {
        case "1":
            // Micro:Bit Internal
            $_SESSION["matrix"]["width"] = 5;
            $_SESSION["matrix"]["height"] = 5;
            $_SESSION["matrix"]["type"] = 1;
            break;
        case "2":
            // Neopixel
            $_SESSION["matrix"]["width"] = 8;
            $_SESSION["matrix"]["height"] = 8;
            $_SESSION["matrix"]["type"] = 2;
            break;
        case "3":
            // Pico Keypad
            $_SESSION["matrix"]["width"] = 4;
            $_SESSION["matrix"]["height"] = 4;
            $_SESSION["matrix"]["type"] = 2;
            break;
        case "4":
            // LoL Shield
            $_SESSION["matrix"]["width"] = 14;
            $_SESSION["matrix"]["height"] = 9;
            $_SESSION["matrix"]["type"] = 0;
            break;
        case "5":
            // Scroll
            $_SESSION["matrix"]["width"] = 17;
            $_SESSION["matrix"]["height"] = 7;
            $_SESSION["matrix"]["type"] = 1;
            break;
        case "0":
        default:
            // Custom
            $_SESSION["matrix"]["width"] = $_POST["width"];
            $_SESSION["matrix"]["height"] = $_POST["height"];
            $_SESSION["matrix"]["type"] = $_POST["type"];
            break;
    }
}

// Check for the required variables that allow you to edit an animation.
$widthSet = isset($_SESSION["matrix"]["width"]);
$heightSet = isset($_SESSION["matrix"]["height"]);
$typeSet = isset($_SESSION["matrix"]["type"]);
$nameSet = isset($_SESSION["matrix"]["name"]);
$idSet = isset($_SESSION["matrix"]["id"]);
// If any required variables aren't set, render the forms to set them.
if (!$widthSet || !$heightSet || !$typeSet || !$nameSet || !$idSet) {
    $timestamp = time();
    echo <<<HTML
        <h1>Animation Settings</h1>
        <form method="post">
            <input name="id" type="text" style="display: none;" value='$timestamp'>
            <div class="form-floating">
                <select class="form-control bg-dark text-light border-dark" id="preMade" name="preMade">
                    <option value="New">Create new</option>
    HTML;
    // AAllow user to select one of their own animations to edit.
    $animations = unserialize($_SESSION["user"])->animations;
    $animationCount = count($animations);
    for ($i = 0; $i < $animationCount; ++$i) {
        $id = $animations[$i]->id;
        $name = $animations[$i]->name;
        echo <<<HTML
            <option value="$id">$name</option>
        HTML;
    }
    echo <<<HTML
                </select>
                <label for="preMade">Saved Animations</label>
            </div>
            <br>
            <div id="setup">
                <div class="form-floating">
                    <select class="form-control bg-dark text-light border-dark" id="setupOptions" name="setup">
                        <!-- Presets. -->
                        <option value="1">Micro:Bit (5x5 Variable Brightness)</option>
                        <option value="2">NeoPixel RGB 8x8 (8x8 RGB)</option>
                        <option value="3">Raspberry Pi Pico RGB Keypad Base (4x4 RGB)</option>
                        <option value="4">LoL Shield SMD (14x9 Monochromatic)</option>
                        <option value="5">ScrollBit (17x7 Variable Brightness)</option>
                        <option value="0">Custom</option>
                    </select>
                    <label for="setupOptions">Matrix Setup</label>
                </div>
                <br>
                <div class="form-floating">
                    <input type="text" class="form-control bg-dark text-light border-dark" id="inputName" name="name" placeholder="Name" required>
                    <label for="inputName">Name</label>
                </div>
                <br>
                <div id="customSetup" style="display: none;">
                    <h3>Custom Setup</h3>
                    <div class="form-floating">
                        <input type="number" class="form-control bg-dark text-light border-dark" id="width" name="width" placeholder="Width" max=25 min=1 value=5>
                        <label for="width">Width</label>
                    </div>
                    <br>
                    <div class="form-floating">
                        <input type="number" class="form-control bg-dark text-light border-dark" id="height" name="height" placeholder="Height" max=25 min=1 value=5>
                        <label for="height">Height</label>
                    </div>
                    <br>
                    <input type="radio" id="0" name="type" value=0 checked>
                    <label for="0">Monochromatic</label>
                    <input type="radio" id="1" name="type" value=1>
                    <label for="1">Variable brightness</label>
                    <input type="radio" id="2" name="type" value=2>
                    <label for="2">RGB</label>
                    <br>
                </div>
            </div>
            <br>
            <input class="btn btn-dark btn-sm" type="submit">
        </form>
        <script>
            // Dynamically updates the form.
            $("select").on("change", () => {
                const preMade = document.getElementById("preMade");
                const setup = document.getElementById("setup");
                const setupOptions = document.getElementById("setupOptions");
                const inputName = document.getElementById("inputName");
                const customSetup = document.getElementById("customSetup");

                setup.style.display = preMade.value === "New" ? "block" : "none";
                inputName.required = preMade.value === "New";
                customSetup.style.display = setupOptions.value == 0 ? "block" : "none";
            });
            // Limits name input.
            $("#inputName").on("keypress", (e) => {
                const isValid = (n) => (/[0-9A-Za-z]/).test(String.fromCharCode(n)) && $("#inputName").val().length < 32;
                if (!isValid(e.which)) return false;
            });
        </script>
    HTML;
}
// Once all variables are set, render the editor.
else {
    // Access session and cookie data.
    $type = $_SESSION["matrix"]["type"];
    $width = $_SESSION["matrix"]["width"];
    $height = $_SESSION["matrix"]["height"];
    if (isset($_COOKIE["data"])) $_SESSION["editor"]["data"] = $_COOKIE["data"];
    if (is_null($_SESSION["editor"]["data"])) $_SESSION["editor"]["data"] = "[]";
    $data = $_SESSION["editor"]["data"];
    if (isset($_COOKIE["fps"])) $_SESSION["editor"]["fps"] = $_COOKIE["fps"];
    if (is_null($_SESSION["editor"]["fps"])) $_SESSION["editor"]["fps"] = 1;
    $fps = $_SESSION["editor"]["fps"];
    if (isset($_COOKIE["colour"])) $_SESSION["editor"]["colour"] = $_COOKIE["colour"];
    if (is_null($_SESSION["editor"]["colour"])) $_SESSION["editor"]["colour"] = "";
    $colour = $_SESSION["editor"]["colour"];

    // CSS - done here instead of main.css so it can use PHP values.
    $columnWidth = strval(25 / $height) . "%";
    echo <<<HTML
        <style>
            .grid {
                display: grid;
                grid-template-columns: repeat($width, $columnWidth);
                grid-auto-rows: 1fr;
            }
            .grid::before {
                content: "";
                width: 0;
                padding-bottom: 100%;
                grid-row: 1;
                grid-column: 1;
            }
            .grid > *:first-child {
                grid-row: 1;
                grid-column: 1;
            }
            .cell {
                width: 100%;
                height: 100%;
                background: rgba(255, 255, 255, 0.1);
                border: 1px #171717 solid;
            }
            #frameIcons {
                height: 100px;
                width: 100%;
                overflow-y: hidden;
                overflow-x: auto;
                white-space: nowrap;
            }
            #playback {
                position: absolute;
                top: 0;
                right: 0;
                padding-top: 60px;
                padding-right: 5px;
                z-index: 1000;
                max-width: 30%;
            }
            #playback img {
                width: 100%;
                height: 100%;
            }
        </style>
    HTML;

    // JS
    $jsonSession = json_encode($_SESSION);
    $username = unserialize($_SESSION["user"])->username;
    echo <<<HTML
        <!-- Import the matrix class. -->
        <script src="Classes/JavaScript/Matrix.js"></script>
        <!-- Create a JS object containing the PHP session. -->
        <script>const SESSION = { ...$jsonSession, username: '$username' };</script>
        <!-- Import the animation editor class. -->
        <script src="Classes/JavaScript/AnimationEditor.js"></script>
        <script>
            // Create a new editor.
            const editor = createAnimationEditor($type, $width, $height, $data);
            // Update shift key settings.
            window.onkeydown = (e) => e.code === "ShiftLeft" && (editor.shiftIsDown = true);
            window.onkeyup = (e) => e.code === "ShiftLeft" && (editor.shiftIsDown = false);
        </script>
    HTML;

    echo <<<HTML
        <!-- Div to display alerts. -->
        <div id="saveAlert"></div>
        <!-- Div and image to render the playback. -->
        <div id="playback"><img></div>
        <!-- Grid to store and edit current frame. -->
        <div class="grid">
    HTML;

    // Render each cell in the grid and hook it up to the editor.
    for ($i = 0; $i < $width * $height; ++$i) {
        echo <<<HTML
            <button class="cell" id="grid-cell-$i" onclick="editor.onLEDClicked($i);">
            <script>editor.LEDs.push(document.getElementById("grid-cell-$i"));</script>
        HTML;
    }

    echo <<<HTML
        </div>
        <!-- Stores the editor controls. -->
        <div id="controls"></div>
        <!-- Stores the frame icons. -->
        <div id="frameIcons"></div>
        <script>
            // Renders the controls.
            editor.setControls($fps, "$colour" || null);
            // Renders the icons.
            editor.displayIcons();
        </script>
        <script>
            // Allows icons to be reordered and updates the editor class when this happens.
            $("#frameIcons").sortable({
                stop: (event, ui) => {
                    const frameHolderDiv = event.target;
                    const frameStringsArr = frameHolderDiv.innerHTML.match(/<p>.*(?=(<\/p>))/g);
                    editor.frames = frameStringsArr.map((x) => x.replace(/<p>/g, ""));
                    editor.updateIcons();
                }
            });
        </script>
    HTML;
}
