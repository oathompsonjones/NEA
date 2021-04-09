<?php
$id = $_GET["animationID"];
if (!isset($id) || is_null($id)) require_once "Include/redirect.inc";
$animation = new Animation($id);
if (is_null($animation->name)) require_once "Include/redirect.inc";

echo <<<HTML
    <h1>$animation->name</h1>
    <h4>$animation->width x $animation->height - $animation->typeString</h4>
    <div class="form-floating">
        <select class="form-control bg-dark text-light border-dark" id="microControllerOptions" name="microController">
            <option value="BBC Micro:Bit">BBC Micro:Bit</option>
            <option value="Arduino">Arduino</option>
            <option value="Raspberry Pi Pico">Raspberry Pi Pico</option>
        </select>
        <label for="microControllerOptions">Micro Controller</label>
    </div>
    <br>
    <div class="form-floating">
        <select class="form-control bg-dark text-light border-dark" id="ledMatrixOptions" name="ledMatrix"></select>
        <label for="ledMatrixOptions">LED Matrix</label>
    </div>
    <br>
    <div class="form-floating">
        <select class="form-control bg-dark text-light border-dark" id="languageOptions" name="language"></select>
        <label for="languageOptions">Language</label>
    </div>
    <br>
    <div class="form-floating bg-dark text-white border-dark">
        <input type="number" class="form-control bg-dark text-white border-dark" id="fps" name="fps" placeholder="FPS" min=1 max=60 value=1>
        <label for="fps">FPS</label>
    </div>
    <script>
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
                name: "AdaFruit 9x16",
                width: 9,
                height: 16,
                type: 0,
                requiredBoards: []
            }
        ];
        const boardLanguages = {
            "Arduino": ["C++"],
            "Raspberry Pi Pico": ["C++", "MicroPython"],
            "BBC Micro:Bit": ["MicroPython", "TypeScript", "Hex File"]
        };
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
        const copyToClipboard = () => navigator.clipboard.writeText(document.getElementById("code").innerHTML.replace(/&amp;/g, "&").replace(/&gt;/g, ">").replace(/&lt;/g, "<"));
    </script>
    <br>
    <div class="btn-group">
        <button class="btn btn-dark" type="button" onclick="generateCode();">Generate Code</button>
        <button class="btn btn-dark" type="button" onclick="copyToClipboard();" style="display: none;" id="copyBtn">Copy</button>
    </div>
    <br>
    <br>
    <pre><code id="code"></code></pre>
HTML;
