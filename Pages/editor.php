<?php
function flattenIDs($value)
{
    return $value[0];
}
if (isset($_POST["createNew"]) && $_POST["createNew"] === "true") {
    unset($_SESSION["matrix"]);
    unset($_SESSION["editor"]);
}

if (isset($_POST["saveToDB"]) && $_POST["saveToDB"] === "true") {
    // Get session variables.
    $db = $_SESSION["database"];
    $width = $_SESSION["matrix"]["width"];
    $height = $_SESSION["matrix"]["height"];
    $type = $_SESSION["matrix"]["type"];
    $id = $_SESSION["matrix"]["id"];
    $name = $_SESSION["matrix"]["name"];
    $frames = isset($_SESSION["editor"]["data"]) && !is_null($_SESSION["editor"]["data"]) ? json_decode($_SESSION["editor"]["data"]) : [];
    $username = unserialize($_SESSION["user"])->username;
    // Get a list of the currently saved animations.
    $currentIDs = array_map("flattenIDs", $db->select("AnimationID", "Animation"));
    // Check if the current animation already exists in the database.
    $animationExists = $currentIDs ? in_array($id, $currentIDs) : FALSE;
    // If it does, delete the saved frames.
    if ($animationExists) $db->delete("Frame", "AnimationID = '$id'");
    // If it doesn't add it.
    else $db->insert("Animation", "AnimationID, Name, Username, Width, Height, Type", "'$id', '$name', '$username', $width, $height, $type");
    // Get all of the current frames.
    for ($i = 0; $i < count($frames); ++$i) $db->insert("Frame", "FrameID, AnimationID, FramePosition, BinaryString", "'$id$i', '$id', $i, '$frames[$i]'");
}

if (isset($_POST["preMade"]) && $_POST["preMade"] !== "New") {
    function findAnimation($value)
    {
        return $value->id === $_POST["preMade"];
    }
    $animation = array_values(array_filter(unserialize($_SESSION["user"])->animations, "findAnimation"))[0];
    $_SESSION["matrix"]["width"] = $animation->width;
    $_SESSION["matrix"]["height"] = $animation->height;
    $_SESSION["matrix"]["type"] = $animation->type;
    $_SESSION["matrix"]["name"] = $animation->name;
    $_SESSION["matrix"]["id"] = $animation->id;
    $frames = is_null($animation->frames) ? [] : $animation->frames;
    function mapFrames($value)
    {
        return $value->binary;
    }
    $_SESSION["editor"]["data"] = json_encode(array_map("mapFrames", $frames));
} else if (isset($_POST["setup"]) && isset($_POST["name"]) && !is_null($_POST["name"])) {
    $_SESSION["matrix"]["name"] = $_POST["name"];
    $_SESSION["matrix"]["id"] = $_POST["id"];
    switch ($_POST["setup"]) {
        case "Micro:Bit":
            $_SESSION["matrix"]["width"] = 5;
            $_SESSION["matrix"]["height"] = 5;
            $_SESSION["matrix"]["type"] = 1;
            break;
        case "NeoPixel RGB 8x8":
            $_SESSION["matrix"]["width"] = 8;
            $_SESSION["matrix"]["height"] = 8;
            $_SESSION["matrix"]["type"] = 2;
            break;
        default:
            $_SESSION["matrix"]["width"] = $_POST["width"];
            $_SESSION["matrix"]["height"] = $_POST["height"];
            $_SESSION["matrix"]["type"] = $_POST["type"];
            break;
    }
}

if (
    !isset($_SESSION["matrix"]["width"])
    || !isset($_SESSION["matrix"]["height"])
    || !isset($_SESSION["matrix"]["type"])
    || !isset($_SESSION["matrix"]["name"])
    || !isset($_SESSION["matrix"]["id"])
) {
    $timestamp = time();
    echo <<<HTML
        <h1>Animation Settings</h1>
        <form method="post">
            <input name="id" type="text" style="display: none;" value='$timestamp'>
            <div class="form-floating">
                <select class="form-control bg-dark text-light border-dark" id="preMade" name="preMade">
                    <option value="New">Create new</option>
    HTML;
    $animations = unserialize($_SESSION["user"])->animations;
    for ($i = 0; $i < count($animations); ++$i) {
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
                        <option value="Micro:Bit">Micro:Bit</option>
                        <option value="NeoPixel RGB 8x8">NeoPixel RGB 8x8</option>
                        <option value="Custom">Custom</option>
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
            $("select").on("change", () => {
                const preMade = document.getElementById("preMade");
                const setup = document.getElementById("setup");
                const setupOptions = document.getElementById("setupOptions");
                const inputName = document.getElementById("inputName");
                const customSetup = document.getElementById("customSetup");

                setup.style.display = preMade.value === "New" ? "block" : "none";
                inputName.required = preMade.value === "New";
                customSetup.style.display = setupOptions.value === "Custom" ? "block" : "none";
            });
        </script>
    HTML;
} else {
    $type = $_SESSION["matrix"]["type"];
    $width = $_SESSION["matrix"]["width"];
    $height = $_SESSION["matrix"]["height"];

    if (isset($_POST["data"])) $_SESSION["editor"]["data"] = $_POST["data"];
    if (is_null($_SESSION["editor"]["data"])) $_SESSION["editor"]["data"] = "[]";
    $data = $_SESSION["editor"]["data"];

    if (isset($_POST["fps"])) $_SESSION["editor"]["fps"] = $_POST["fps"];
    if (is_null($_SESSION["editor"]["fps"])) $_SESSION["editor"]["fps"] = 1;
    $fps = $_SESSION["editor"]["fps"];

    if (isset($_POST["colour"])) $_SESSION["editor"]["colour"] = $_POST["colour"];
    if (is_null($_SESSION["editor"]["colour"])) $_SESSION["editor"]["colour"] = "";
    $colour = $_SESSION["editor"]["colour"];

    // CSS
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

    // Alert box
    if (isset($_POST["saveToDB"]) && $_POST["saveToDB"] === "true") {
        echo <<<HTML
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Animation Saved!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        HTML;
    }

    // JS
    echo <<<HTML
        <script>
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            const tooltipList = tooltipTriggerList.map((tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl));
        </script>
        <script src="Classes/JavaScript/Matrix.js"></script>
        <script src="Classes/JavaScript/AnimationEditor.js"></script>
        <script>
            const editor = createAnimationEditor($type, $width, $height, $data);
            window.onkeydown = (e) => e.code === "ShiftLeft" && (editor.shiftIsDown = true);
            window.onkeyup = (e) => e.code === "ShiftLeft" && (editor.shiftIsDown = false);
        </script>
    HTML;

    echo <<<HTML
        <div id="playback"><img></div>
        <div class="grid">
    HTML;

    for ($i = 0; $i < $width * $height; ++$i) {
        echo <<<HTML
            <button class="cell" id="grid-cell-$i" onclick="editor.onLEDClicked($i);">
            <script>editor.LEDs.push(document.getElementById("grid-cell-$i"));</script>
        HTML;
    }

    echo <<<HTML
        </div>
        <div id="controls"></div>
        <div id="frameIcons"></div>
        <script>
            editor.setControls($fps, "$colour" || null);
            editor.displayIcons();
        </script>
        <script>
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
