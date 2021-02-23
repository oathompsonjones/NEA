<?php
$width = $_SESSION["width"];
$height = $_SESSION["height"];
$type = $_SESSION["type"];

if (isset($_GET["frames"])) $_SESSION["frames"] = $_GET["frames"];
$frames = $_SESSION["frames"];

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
            background: rgba(0, 0, 0, 0.1);
            border: 1px white solid;
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

// JS
echo <<<HTML
    <script>
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map((tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl));
    </script>
    <script src="Classes/JavaScript/Matrix.js"></script>
    <script src="Classes/JavaScript/AnimationEditor.js"></script>
    <script>
        const editor = createAnimationEditor($type, $width, $height, $frames);
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
        editor.setControls();
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

?>