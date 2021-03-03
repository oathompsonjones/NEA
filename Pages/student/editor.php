<?php
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
    $frames = isset($_SESSION["editor"]["data"]) && !is_null($_SESSION["editor"]["data"])
        ? json_decode($_SESSION["editor"]["data"])
        : [];
    $username = unserialize($_SESSION["user"])->username;
    // Get a list of the currently saved animations.
    $currentIDs = $db->select("AnimationID", "Animation")[0];
    // Check if the current animation already exists in the database.
    $animationExists = $currentIDs ? in_array($id, $currentIDs) : [];
    // If it does, delete the saved frames.
    if ($animationExists) $db->delete("Frame", "AnimationID = '$id'");
    // If it doesn't add it.
    else $db->insert("Animation", "AnimationID, Name, Username, Width, Height, Type", "'$id', '$name', '$username', $width, $height, $type");
    // Get all of the current frames.
    for ($i = 0; $i < count($frames); ++$i)
        $db->insert("Frame", "FrameID, AnimationID, FramePosition, BinaryString", "'$id$i', $id, $i, '$frames[$i]'");
}

$tmpWidth = $_POST["width"];
$tmpHeight = $_POST["height"];
$tmpType = $_POST["type"];
$tmpName = $_POST["name"];
$tmpID = $_POST["id"];

if (isset($_POST["setup"])) {
    switch ($_POST["setup"]) {
        case "Micro:Bit":
            $tmpWidth = 5;
            $tmpHeight = 5;
            $tmpType = 1;
            break;
        case "NeoPixel RGB 8x8":
            $tmpWidth = 8;
            $tmpHeight = 8;
            $tmpType = 2;
            break;
    }
}

if (isset($_POST["preMade"]) && $_POST["preMade"] !== "New") {
    function findAnimation($value)
    {
        return $value->id === $_POST["preMade"];
    }
    $animation = array_values(array_filter(unserialize($_SESSION["user"])->animations, "findAnimation"))[0];
    $tmpWidth = $animation->width;
    $tmpHeight = $animation->height;
    $tmpType = $animation->type;
    $tmpName = $animation->name;
    $tmpId = $animation->id;
    $frames = is_null($animation->frames) ? [] : $animation->frames;
    function mapFrames($value)
    {
        return $value->binary;
    }
    $_SESSION["editor"]["data"] = json_encode(array_map("mapFrames", $frames));
}

if (!isset($_SESSION["matrix"]["width"])) $_SESSION["matrix"]["width"] = $tmpWidth;
if (!isset($_SESSION["matrix"]["height"])) $_SESSION["matrix"]["height"] = $tmpHeight;
if (!isset($_SESSION["matrix"]["type"])) $_SESSION["matrix"]["type"] = $tmpType;
if (!isset($_SESSION["matrix"]["name"])) $_SESSION["matrix"]["name"] = $tmpName;
if (!isset($_SESSION["matrix"]["id"])) $_SESSION["matrix"]["id"] = $tmpID;

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
                <select class="form-control bg-dark text-white" id="preMade" name="preMade">
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
                    <select class="form-control bg-dark text-white" id="setupOptions" name="setup">
                        <option value="Micro:Bit">Micro:Bit</option>
                        <option value="NeoPixel RGB 8x8">NeoPixel RGB 8x8</option>
                        <option value="Custom">Custom</option>
                    </select>
                    <label for="setupOptions">Matrix Setup</label>
                </div>
                <br>
                <div class="form-floating">
                    <input type="text" class="form-control bg-dark text-white" id="inputName" name="name" placeholder="Name">
                    <label for="inputName">Name</label>
                </div>
                <br>
                <div id="customSetup" style="display: none;">
                    <h3>Custom Setup</h3>
                    <div class="form-floating">
                        <select class="form-control bg-dark text-white" id="width" name="width" placeholder="Width">
    HTML;
    for ($i = 1; $i < 26; ++$i) echo <<<HTML
        <option value=$i>$i</option>
    HTML;
    echo <<<HTML
                        </select>
                        <label for="width">Width</label>
                    </div>
                    <br>
                    <div class="form-floating">
                        <select class="form-control bg-dark text-white" id="height" name="height" placeholder="Height">
    HTML;
    for ($i = 1; $i < 26; ++$i) echo <<<HTML
        <option value=$i>$i</option>
    HTML;
    echo <<<HTML
                        </select>
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
                const setupList = document.getElementById("setupOptions");
                const customSetupDiv = document.getElementById("customSetup");
                if (setupList.value === "Custom") customSetupDiv.style.display = "block";
                else customSetupDiv.style.display = "none";

                const preMadeList = document.getElementById("preMade");
                const setupDiv = document.getElementById("setup");
                if (preMadeList.value === "New") setupDiv.style.display = "block";
                else setupDiv.style.display = "none";
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
            .icon {
                height: 100%;
                vertical-align: top;
                padding: 5px;
                display: inline-block;
                position: relative;
            }
            .icon img {
                height: 100%;
                display: block;
            }
            .icon p {
                display: none;
            }
            .icon .buttons {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                -ms-transform: translate(-50%, -50%);
                opacity: 0;
                text-align: center;
            }
            .icon .buttons button {
                padding: 2px;
            }
            .icon:hover img {
                background-color: rgba(0, 0, 0, 0.5);
                filter: brightness(50%);
            }
            .icon:hover .buttons {
                opacity: 1;
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
    echo <<<HTML
        <div id="alert">
    HTML;
    if (isset($_POST["saveToDB"]) && $_POST["saveToDB"] === "true") {
        echo <<<HTML
            <div class="alert alert-success" role="alert">
                Animation Saved!
            </div>
        HTML;
    }
    echo <<<HTML
        </div>
    HTML;

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
