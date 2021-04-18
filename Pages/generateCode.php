<?php
// Get the animation.
$id = $_GET["animationID"];
if (!isset($id) || is_null($id)) require_once "Include/redirect.inc";
$animation = new Animation($id);
if (is_null($animation->name)) require_once "Include/redirect.inc";

echo <<<HTML
    <!-- Tell user which animation it is. -->
    <h1>$animation->name</h1>
    <!-- Tell them the dimensions and colour depth. -->
    <h4>$animation->width x $animation->height - $animation->typeString</h4>
    <!-- Allow user to select their board. -->
    <div class="form-floating">
        <select class="form-control bg-dark text-light border-dark" id="microControllerOptions" name="microController">
            <option value="BBC Micro:Bit">BBC Micro:Bit</option>
            <option value="Arduino">Arduino</option>
            <option value="Raspberry Pi Pico">Raspberry Pi Pico</option>
        </select>
        <label for="microControllerOptions">Micro Controller</label>
    </div>
    <br>
    <!-- Allow user to select their matrix. -->
    <div class="form-floating">
        <select class="form-control bg-dark text-light border-dark" id="ledMatrixOptions" name="ledMatrix"></select>
        <label for="ledMatrixOptions">LED Matrix</label>
    </div>
    <br>
    <!-- Allow user to select their language. -->
    <div class="form-floating">
        <select class="form-control bg-dark text-light border-dark" id="languageOptions" name="language"></select>
        <label for="languageOptions">Language</label>
    </div>
    <br>
    <!-- Allow user to select their FPS. -->
    <div class="form-floating bg-dark text-white border-dark">
        <input type="number" class="form-control bg-dark text-white border-dark" id="fps" name="fps" placeholder="FPS" min=1 max=60 value=1>
        <label for="fps">FPS</label>
    </div>
    <script>
        // Supported matrices and their properties.
        const LEDMatrices = [
            {
                name: "Micro:Bit Internal",
                width: 5,
                height: 5,
                type: 1,
                requiredBoards: ["BBC Micro:Bit"]
            }, {
                name: "LoL Shield",
                width: 14,
                height: 9,
                type: 0,
                requiredBoards: ["Arduino"]
            }, {
                name: "Pico RGB Keypad",
                width: 4,
                height: 4,
                type: 2,
                requiredBoards: ["Raspberry Pi Pico"]
            }, {
                name: "AdaFruit Neo Pixel RGB 8x8",
                width: 8,
                height: 8,
                type: 2,
                requiredBoards: []
            }, {
                name: "Scroll Bit",
                width: 17,
                height: 7,
                type: 1,
                requiredBoards: []
            }, {
                name: "HL-M1388AR 8x8",
                width: 8,
                height: 8,
                type: 0,
                requiredBoards: []
            }, {
                name: "AdaFruit 16x9",
                width: 16,
                height: 9,
                type: 1,
                requiredBoards: []
            }
        ];
        // Which boards support which languages.
        const boardLanguages = {
            "Arduino": ["C++"],
            "Raspberry Pi Pico": ["C++", "MicroPython"],
            "BBC Micro:Bit": ["MicroPython", "TypeScript"/* , "Hex File" */]
        };
        // Work out which languages and matrices ar valid based on the board selection and the animation properties.
        const createMatrixAndLanguageOptions = () => {
            const microControllerOptions = document.getElementById("microControllerOptions");
            const ledMatrixOptions = document.getElementById("ledMatrixOptions");
            const languageOptions = document.getElementById("languageOptions");
            const width = $animation->width;
            const height = $animation->height;
            const type = $animation->type;
            const validMatrices = LEDMatrices.filter((matrix) => 
                matrix.width === width
                && matrix.height === height
                && matrix.type === type
                && (matrix.requiredBoards.length > 0 ? matrix.requiredBoards.includes(microControllerOptions.value) : true)
            );
            ledMatrixOptions.innerHTML = "";
            [...validMatrices, { name: "Custom" }].forEach((matrix) => {
                const option = document.createElement("option");
                option.value = matrix.name;
                option.text = matrix.name;
                ledMatrixOptions.add(option);
            });
            const validLanguages = boardLanguages[microControllerOptions.value];
            languageOptions.innerHTML = "";
            validLanguages.forEach((language) => {
                const option = document.createElement("option");
                option.value = language;
                option.text = language;
                languageOptions.add(option);
            })
        };
        $("#microControllerOptions").change(createMatrixAndLanguageOptions);
        createMatrixAndLanguageOptions();
        // Gets the code via AJAX.
        const generateCode = () => {
            const animationID = "$id";
            const board = document.getElementById("microControllerOptions").value;
            const matrix = document.getElementById("ledMatrixOptions").value;
            const language = document.getElementById("languageOptions").value;
            const fps = document.getElementById("fps").value;
            console.log({ animationID, board, matrix, language });
            $.post("Utils/Forms/generateCode.php", { animationID, board, matrix, language, fps }, (code) => {
                document.getElementById("code").innerHTML = code;
                document.getElementById("copyBtn").style.display = "";
            });
        };
        // Copies the code to the clipboard.
        const copyToClipboard = () => navigator.clipboard.writeText(document.getElementById("code").innerHTML.replace(/&amp;/g, "&").replace(/&gt;/g, ">").replace(/&lt;/g, "<"));
    </script>
    <br>
    <!-- Buttons to generate and to copy code to clipboard. -->
    <div class="btn-group">
        <button class="btn btn-dark" type="button" onclick="generateCode();">Generate Code</button>
        <button class="btn btn-dark" type="button" onclick="copyToClipboard();" style="display: none;" id="copyBtn">Copy</button>
    </div>
    <br>
    <br>
    <!-- The code output goes here. -->
    <pre><code id="code"></code></pre>
HTML;
