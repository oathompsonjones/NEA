"use strict";
const pixelSize = 100;
const map = (x, inMin, inMax, outMin, outMax) => ((x - inMin) * (outMax - outMin)) / (inMax - inMin) + outMin;
class AnimationEditor {
    matrixWidth;
    matrixHeight;
    LEDBitLength;
    _binaryString = "";
    _shiftIsDown = false;
    bitCount;
    cssDefaultOffColour = "rgba(255, 255, 255, 0.1)";
    defaultOffColour;
    frames = [];
    LEDs = [];
    playbackTimeout = 0;
    shiftedLEDs = [];
    get binaryString() {
        return this._binaryString;
    }
    set binaryString(val) {
        this._binaryString = val;
        this.updateLEDs();
    }
    get int32Frames() {
        return (this.frames
            .map((frame) => frame.padStart(32 - frame.length % 32 + frame.length, "0"))
            .join("")
            .match(/.{1,32}/g) ?? [])
            .map((bits) => parseInt(bits, 2));
    }
    ;
    get currentLEDBitPatterns() {
        return this.binaryString.match(new RegExp(`.{1,${this.LEDBitLength}}`, "g"));
    }
    ;
    get matrix() {
        const matrixData = Matrix.create0Array(this.matrixWidth, this.matrixHeight);
        this.currentLEDBitPatterns.forEach((bit, i) => matrixData[Math.floor(i / this.matrixWidth)][i % this.matrixHeight] = parseInt(bit, 2));
        return new Matrix(matrixData);
    }
    get playbackFPS() {
        const fps = document.getElementById("fps");
        return parseInt(fps.value, 10);
    }
    get shiftIsDown() {
        return this._shiftIsDown;
    }
    set shiftIsDown(val) {
        this._shiftIsDown = val;
        if (val === false)
            this.shiftedLEDs = [];
        this.displayShiftState();
    }
    get typeInt() {
        if (this instanceof RGBAnimationEditor)
            return 2;
        if (this instanceof VariableBrightnessAnimationEditor)
            return 1;
        return 0;
    }
    defaultOnColour;
    constructor(matrixWidth, matrixHeight, data, LEDBitLength) {
        this.matrixWidth = matrixWidth;
        this.matrixHeight = matrixHeight;
        this.LEDBitLength = LEDBitLength;
        this.bitCount = this.matrixWidth * this.matrixHeight * this.LEDBitLength;
        this.defaultOffColour = "0".repeat(this.LEDBitLength);
        this.frames = data;
        this.clearScreen();
    }
    calculateBresenhamLine(x0, y0, x1, y1) {
        const coords = [];
        const plotLineLow = (x0, y0, x1, y1) => {
            const dx = x1 - x0;
            let dy = y1 - y0;
            let yi = 1;
            if (dy < 0) {
                yi = -1;
                dy = -dy;
            }
            let D = 2 * dy - dx;
            let y = y0;
            for (let x = x0; x <= x1; ++x) {
                coords.push({ x, y });
                if (D > 0) {
                    y += yi;
                    D += 2 * (dy - dx);
                }
                else
                    D += 2 * dy;
            }
        };
        const plotLineHigh = (x0, y0, x1, y1) => {
            let dx = x1 - x0;
            const dy = y1 - y0;
            let xi = 1;
            if (dx < 0) {
                xi = -1;
                dx = -dx;
            }
            let D = 2 * dx - dy;
            let x = x0;
            for (let y = y0; y <= y1; ++y) {
                coords.push({ x, y });
                if (D > 0) {
                    x += xi;
                    D += 2 * (dx - dy);
                }
                else
                    D += 2 * dx;
            }
        };
        if (Math.abs(y1 - y0) < Math.abs(x1 - x0)) {
            if (x0 > x1)
                plotLineLow(x1, y1, x0, y0);
            else
                plotLineLow(x0, y0, x1, y1);
        }
        else {
            if (y0 > y1)
                plotLineHigh(x1, y1, x0, y0);
            else
                plotLineHigh(x0, y0, x1, y1);
        }
        return coords;
    }
    clearScreen() {
        this.binaryString = "0".repeat(this.bitCount);
    }
    convertMatrixToString(M) {
        return M.value.map((row) => row.map((x) => x.toString(2).padStart(this.LEDBitLength, "0")).join("")).join("");
    }
    displayIcons() {
        const frameIconsDiv = document.getElementById("frameIcons");
        frameIconsDiv.innerHTML = this.makeFrameIcons().map((icon, i) => `
            <div class="icon firstIcon">
                <img src="${icon.image}">
                <div class="buttons">
                    <button class="btn btn-secondary btn-sm" data-toggle="tooltip" data-placement="top" title="Copy this frame to the editor" onclick="editor.onFrameCopy('${icon.binary}')">Copy</button><br>
                    <button class="btn btn-secondary btn-sm" data-toggle="tooltip" data-placement="top" title="Delete this frame" onclick="editor.onFrameDelete(${i})">Delete</button>
                </div>
                <p>${icon.binary}</p>
            </div>
        `).join("");
    }
    displayShiftState() {
        const shiftButton = document.getElementById("shiftBtn");
        shiftButton.className = this.shiftIsDown ? "btn btn-dark active btn-sm" : "btn btn-dark btn-sm";
    }
    drawPlus() {
        const bitPatterns = this.currentLEDBitPatterns;
        for (let y = 0; y < this.matrixHeight; ++y) {
            for (let x = 0; x < this.matrixWidth; ++x) {
                const validX = x === Math.floor(this.matrixWidth / 2) || x === Math.floor((this.matrixWidth - 1) / 2);
                const validY = y === Math.floor(this.matrixHeight / 2) || y === Math.floor((this.matrixHeight - 1) / 2);
                if (validX || validY)
                    bitPatterns[this.matrixWidth * y + x] = this.onColour;
            }
        }
        this.binaryString = bitPatterns.join("");
    }
    drawCross() {
        const bitPatterns = this.currentLEDBitPatterns;
        const coords = this.calculateBresenhamLine(0, 0, this.matrixWidth - 1, this.matrixHeight - 1)
            .concat(this.calculateBresenhamLine(this.matrixWidth - 1, 0, 0, this.matrixHeight - 1));
        for (let y = 0; y < this.matrixHeight; ++y)
            for (let x = 0; x < this.matrixWidth; ++x)
                if (coords.filter((c) => c.x === x && c.y === y).length > 0)
                    bitPatterns[this.matrixWidth * y + x] = this.onColour;
        this.binaryString = bitPatterns.join("");
    }
    drawBorder() {
        const bitPatterns = this.currentLEDBitPatterns;
        for (let y = 0; y < this.matrixHeight; ++y) {
            for (let x = 0; x < this.matrixWidth; ++x) {
                const validX = x === 0 || x === this.matrixWidth - 1;
                const validY = y === 0 || y === this.matrixHeight - 1;
                if (validX || validY)
                    bitPatterns[this.matrixWidth * y + x] = this.onColour;
            }
        }
        this.binaryString = bitPatterns.join("");
    }
    drawCircle(full = false) {
        const bitPatterns = this.currentLEDBitPatterns;
        const size = Math.min(this.matrixWidth, this.matrixHeight);
        const radius = (size % 2 === 0 ? size : size - 1) / 2;
        for (let y = 0; y < this.matrixHeight; ++y) {
            for (let x = 0; x < this.matrixWidth; ++x) {
                const xDistance = size % 2 === 0 ? radius - x - 0.5 : radius - x;
                const yDistance = size % 2 === 0 ? radius - y - 0.5 : radius - y;
                const distance = Math.round(Math.sqrt(xDistance ** 2 + yDistance ** 2));
                if ((full && distance <= radius) || distance === radius)
                    bitPatterns[this.matrixWidth * y + x] = this.onColour;
            }
        }
        this.binaryString = bitPatterns.join("");
    }
    drawNoEntry() {
        const bitPatterns = this.currentLEDBitPatterns;
        const size = Math.min(this.matrixWidth, this.matrixHeight);
        const radius = (size % 2 === 0 ? size : size - 1) / 2;
        for (let y = 0; y < this.matrixHeight; ++y) {
            for (let x = 0; x < this.matrixWidth; ++x) {
                const xDistance = size % 2 === 0 ? radius - x - 0.5 : radius - x;
                const yDistance = size % 2 === 0 ? radius - y - 0.5 : radius - y;
                const distance = Math.round(Math.sqrt(xDistance ** 2 + yDistance ** 2));
                if ((x === y && distance <= radius) || distance === radius)
                    bitPatterns[this.matrixWidth * y + x] = this.onColour;
            }
        }
        this.binaryString = bitPatterns.join("");
    }
    fillScreen() {
        this.binaryString = this.onColour.repeat(this.matrixWidth * this.matrixHeight);
    }
    flip(reverseRows) {
        this.binaryString = this.convertMatrixToString(reverseRows ? this.matrix.reversedRows : this.matrix.reversedColumns);
    }
    invertScreen() {
        this.binaryString = this.binaryString.split("").map((x) => (parseInt(x) ? "0" : "1")).join("");
    }
    move(direction) {
        this.binaryString = this.convertMatrixToString(this.matrix.translate(direction));
    }
    new() {
        unsetCookie("data");
        unsetCookie("fps");
        unsetCookie("colour");
        const form = document.createElement("form");
        form.style.display = "none";
        form.setAttribute("method", "post");
        const input = document.createElement("input");
        input.setAttribute("type", "text");
        input.setAttribute("name", "createNew");
        input.setAttribute("value", "true");
        form.appendChild(input);
        document.getElementsByTagName("body")[0].appendChild(form);
        form.submit();
    }
    playback() {
        clearTimeout(this.playbackTimeout);
        const playbackDiv = document.getElementById("playback");
        const frames = this.makeFrameIcons();
        let i = 0;
        const renderFrame = () => {
            const currentFrame = frames[i++];
            playbackDiv.innerHTML = `<img src=${currentFrame.image}>`;
            this.playbackTimeout = setTimeout(renderFrame, 1000 / this.playbackFPS);
        };
        renderFrame();
    }
    onFrameCopy(binary) {
        this.binaryString = binary;
    }
    onFrameDelete(index) {
        this.frames = this.frames.filter((_, i) => i !== index);
        this.updateIcons();
    }
    onLEDClicked(index) {
        const bitPatterns = this.currentLEDBitPatterns;
        if (this.shiftIsDown) {
            if (this.shiftedLEDs.length === 0) {
                this.shiftedLEDs.push(index);
                bitPatterns[index] = parseInt(bitPatterns[index], 2) ? this.defaultOffColour : this.onColour;
            }
            else if (this.shiftedLEDs.length === 1) {
                this.shiftedLEDs.push(index);
                const coords = this.calculateBresenhamLine(this.shiftedLEDs[0] % this.matrixWidth, Math.floor(this.shiftedLEDs[0] / this.matrixWidth), this.shiftedLEDs[1] % this.matrixWidth, Math.floor(this.shiftedLEDs[1] / this.matrixWidth));
                coords.forEach((buttonCoords) => {
                    const i = buttonCoords.x + buttonCoords.y * this.matrixWidth;
                    bitPatterns[i] = this.onColour;
                });
                this.shiftedLEDs = [];
            }
        }
        else
            bitPatterns[index] = parseInt(bitPatterns[index], 2) ? this.defaultOffColour : this.onColour;
        this.binaryString = bitPatterns.join("");
    }
    rotate(angle) {
        this.binaryString = this.convertMatrixToString(this.matrix.rotate(angle));
    }
    save() {
        const width = this.matrixWidth;
        const height = this.matrixHeight;
        const type = this.typeInt;
        const data = JSON.stringify(this.frames);
        const id = SESSION.matrix.id;
        const name = SESSION.matrix.name;
        const username = SESSION.username;
        $.post("Utils/Forms/saveAnimation", { width, height, type, id, name, data, username }, () => document.getElementById("saveAlert").innerHTML = `
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Animation Saved!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `);
    }
    setControls(fps, colour) {
        const controlsDiv = document.getElementById("controls");
        let html = `
            <br>
            <div class="btn-group">
                <button class="btn btn-dark btn-sm" onclick="editor.clearScreen();   " data-toggle="tooltip" data-placement="top" title="Turn off all LEDs">Clear</button>
                <button class="btn btn-dark btn-sm" onclick="editor.fillScreen();    " data-toggle="tooltip" data-placement="top" title="Turn on all LEDs">Fill</button>
                <button class="btn btn-dark btn-sm" onclick="editor.invertScreen();  " data-toggle="tooltip" data-placement="top" title="Invert all LEDs">Invert</button>
                <button class="btn btn-dark btn-sm" onclick="editor.toggleShift();   " data-toggle="tooltip" data-placement="top" title="Hold shift to draw straight lines" id="shiftBtn" >Shift</button>
                <button class="btn btn-dark btn-sm" onclick="editor.flip(false);     " data-toggle="tooltip" data-placement="top" title="Flip the image vertically">↕</button>
                <button class="btn btn-dark btn-sm" onclick="editor.flip(true);      " data-toggle="tooltip" data-placement="top" title="Flip the image horizontally">↔</button>
                <button class="btn btn-dark btn-sm" onclick="editor.move('left');    " ${this.matrixWidth === this.matrixHeight ? 'data-toggle="tooltip" data-placement="top" title="Move the image to the left"' : "disabled"} >⬅</button>
                <button class="btn btn-dark btn-sm" onclick="editor.move('right');   " ${this.matrixWidth === this.matrixHeight ? 'data-toggle="tooltip" data-placement="top" title="Move the image to the right"' : "disabled"} >➡</button>
                <button class="btn btn-dark btn-sm" onclick="editor.move('up');      " ${this.matrixWidth === this.matrixHeight ? 'data-toggle="tooltip" data-placement="top" title="Move the image up"' : "disabled"} >⬆</button>
                <button class="btn btn-dark btn-sm" onclick="editor.move('down');    " ${this.matrixWidth === this.matrixHeight ? 'data-toggle="tooltip" data-placement="top" title="Move the image down"' : "disabled"} >⬇</button>
                <button class="btn btn-dark btn-sm" onclick="editor.rotate(270);     " ${this.matrixWidth === this.matrixHeight ? 'data-toggle="tooltip" data-placement="top" title="Rotate the image anticlockwise"' : "disabled"} >↪</button>
                <button class="btn btn-dark btn-sm" onclick="editor.rotate(90);      " ${this.matrixWidth === this.matrixHeight ? 'data-toggle="tooltip" data-placement="top" title="Rotate the image clockwise"' : "disabled"} >↩</button>
            </div>
            <br>
            <div class="btn-group">
                <button class="btn btn-dark btn-sm" onclick="editor.drawPlus();      " data-toggle="tooltip" data-placement="top" title="Draw a plus sign">➕</button>
                <button class="btn btn-dark btn-sm" onclick="editor.drawCross();     " data-toggle="tooltip" data-placement="top" title="Draw a diagonal cross">❌</button>
                <button class="btn btn-dark btn-sm" onclick="editor.drawBorder();    " data-toggle="tooltip" data-placement="top" title="Draw a border">🔲</button>
                <button class="btn btn-dark btn-sm" onclick="editor.drawCircle();    " ${this.matrixWidth === this.matrixHeight ? 'data-toggle="tooltip" data-placement="top" title="Draw a circle outline"' : "disabled"}>⭕</button>
                <button class="btn btn-dark btn-sm" onclick="editor.drawCircle(true);" ${this.matrixWidth === this.matrixHeight ? 'data-toggle="tooltip" data-placement="top" title="Draw a circle"' : "disabled"}>🔴</button>
                <button class="btn btn-dark btn-sm" onclick="editor.drawNoEntry();   " ${this.matrixWidth === this.matrixHeight ? 'data-toggle="tooltip" data-placement="top" title="Draw a no entry sign"' : "disabled"}>🚫</button>
            </div>
            <br>
            <div class="input-group">
        `;
        switch (this.typeInt) {
            case 0:
                html += `
                    <div class="form-floating bg-dark text-white border-dark">
                        <input type="number" class="form-control bg-dark text-white border-dark" id="fps" name="fps" placeholder="FPS" min=1 max=60 value=1>
                        <label for="fps">FPS</label>
                    </div>
                `;
                break;
            case 1:
                html += `
                    <div class="form-floating bg-dark text-white border-dark">
                        <input type="number" class="form-control bg-dark text-white border-dark" id="fps" name="fps" placeholder="FPS" min=1 max=60 value=1>
                        <label for="fps">FPS</label>
                    </div>
                    <div class="form-floating bg-dark text-white border-dark" style="width: 7%">
                        <input type="number" class="form-control bg-dark text-white border-dark" id="colourInput" name="colour" placeholder="Brightness" min=1 max=255 value=${this.defaultOnColour}>
                        <label for="colourInput">Brightness</label>
                    </div>
                `;
                break;
            case 2:
                html += `
                    <div class="form-floating bg-dark text-white border-dark">
                        <input type="number" class="form-control bg-dark text-white border-dark" id="fps" name="fps" placeholder="FPS" min=1 max=60 value=1>
                        <label for="fps">FPS</label>
                    </div>
                    <input id="colourInput" class="form-control form-control-color bg-dark border-dark" type="color" name="colour" value="${this.defaultOnColour}">
                `;
                break;
        }
        html += `
            </div>
            <div class="btn-group">
                <button class="btn btn-dark btn-sm" onclick="editor.frames.push(editor.binaryString); editor.updateIcons(); editor.clearScreen();" data-toggle="tooltip" data-placement="top" title="Save this frame and make a new one">➕</button>
                <button class="btn btn-dark btn-sm" onclick="editor.playback();" data-toggle="tooltip" data-placement="top" title="Play the animation">Play</button>
            </div>
            <div class="btn-group">
                <button id="save" class="btn btn-dark btn-sm" onclick="editor.save()" data-toggle="tooltip" data-placement="top" title="Save this animation to the database">Save</button>
                <button id="new" class="btn btn-dark btn-sm" onclick="editor.new()" data-toggle="tooltip" data-placement="top" title="Create a new animation">New</button>
            </div>
        `;
        controlsDiv.innerHTML = html;
        const fpsInput = document.getElementById("fps");
        fpsInput.value = fps.toString();
        if (colour !== null) {
            const colourInput = document.getElementById("colourInput");
            if (this instanceof VariableBrightnessAnimationEditor)
                colourInput.value = parseInt(colour, 2).toString(10);
            else if (this instanceof RGBAnimationEditor)
                colourInput.value = `#${parseInt(colour, 2).toString(16)}`;
        }
    }
    toggleShift() {
        this.shiftIsDown = !this.shiftIsDown;
    }
    updateIcons() {
        this.displayIcons();
        setCookie("data", JSON.stringify(this.frames));
        setCookie("fps", this.playbackFPS.toString());
        setCookie("colour", this.onColour.toString());
    }
}
class RGBAnimationEditor extends AnimationEditor {
    defaultOnColour = "#ff0000";
    get onColour() {
        const colourInput = document.getElementById("colourInput");
        return parseInt(colourInput.value.slice(1), 16).toString(2).padStart(24, "0");
    }
    ;
    constructor(width, height, data) {
        super(width, height, data, 24);
    }
    makeFrameIcons() {
        return this.frames.map((frame) => {
            const canvas = document.createElement("canvas");
            canvas.width = this.matrixWidth * pixelSize;
            canvas.height = this.matrixHeight * pixelSize;
            const context = canvas.getContext("2d");
            if (context === null)
                throw Error();
            frame.match(/.{1,24}/g)
                .map((x) => x.match(/.{1,8}/g).map((y) => parseInt(y, 2)))
                .forEach((bytes, i) => {
                context.fillStyle = bytes.some((byte) => byte > 0)
                    ? `rgb(${bytes[0]}, ${bytes[1]}, ${bytes[2]})`
                    : this.cssDefaultOffColour;
                const x = (i % this.matrixWidth) * pixelSize;
                const y = Math.floor(i / this.matrixWidth) * pixelSize;
                context.fillRect(x, y, pixelSize, pixelSize);
            });
            return {
                binary: frame,
                image: canvas.toDataURL(),
            };
        });
    }
    updateLEDs() {
        this.LEDs.forEach((button, i) => {
            const offColour = this.cssDefaultOffColour;
            let colour = offColour;
            const bits = this.currentLEDBitPatterns[i];
            const num = parseInt(bits, 2);
            const bitsArr = bits.match(/.{1,8}/g);
            const numArr = bitsArr.map((x) => parseInt(x, 2));
            colour = num ? "rgb(" + numArr.join(", ") + ")" : offColour;
            button.style.background = colour;
        });
    }
}
class VariableBrightnessAnimationEditor extends AnimationEditor {
    defaultOnColour = "255";
    get onColour() {
        const colourInput = document.getElementById("colourInput");
        return parseInt(colourInput.value).toString(2).padStart(8, "0");
    }
    ;
    constructor(width, height, data) {
        super(width, height, data, 8);
    }
    makeFrameIcons() {
        const map = (x, inMin, inMax, outMin, outMax) => ((x - inMin) * (outMax - outMin)) / (inMax - inMin) + outMin;
        return this.frames.map((frame) => {
            const canvas = document.createElement("canvas");
            canvas.width = this.matrixWidth * pixelSize;
            canvas.height = this.matrixHeight * pixelSize;
            const context = canvas.getContext("2d");
            if (context === null)
                throw Error();
            frame.match(/.{1,8}/g)
                .map((x) => parseInt(x, 2))
                .forEach((byte, i) => {
                context.fillStyle =
                    byte > 0
                        ? `rgba(255, 0, 0, ${map(byte, 0, 255, 0, 1)})`
                        : this.cssDefaultOffColour;
                const x = (i % this.matrixWidth) * pixelSize;
                const y = Math.floor(i / this.matrixWidth) * pixelSize;
                context.fillRect(x, y, pixelSize, pixelSize);
            });
            return {
                binary: frame,
                image: canvas.toDataURL(),
            };
        });
    }
    updateLEDs() {
        this.LEDs.forEach((button, i) => {
            const offColour = this.cssDefaultOffColour;
            let colour = offColour;
            const bits = this.currentLEDBitPatterns[i];
            const num = parseInt(bits, 2);
            colour = num
                ? `rgba(255, 0, 0, ${map(num, 0, 255, 0, 1)})`
                : offColour;
            button.style.background = colour;
        });
    }
}
class MonochromaticAnimationEditor extends AnimationEditor {
    defaultOnColour = "1";
    get onColour() {
        return this.defaultOnColour;
    }
    ;
    constructor(width, height, data) {
        super(width, height, data, 1);
    }
    makeFrameIcons() {
        return this.frames.map((frame) => {
            const canvas = document.createElement("canvas");
            canvas.width = this.matrixWidth * pixelSize;
            canvas.height = this.matrixHeight * pixelSize;
            const context = canvas.getContext("2d");
            if (context === null)
                throw Error();
            frame
                .split("")
                .map((x) => parseInt(x, 2))
                .forEach((bit, i) => {
                context.fillStyle = bit === 1 ? "red" : this.cssDefaultOffColour;
                const x = (i % this.matrixWidth) * pixelSize;
                const y = Math.floor(i / this.matrixWidth) * pixelSize;
                context.fillRect(x, y, pixelSize, pixelSize);
            });
            return {
                binary: frame,
                image: canvas.toDataURL(),
            };
        });
    }
    updateLEDs() {
        this.LEDs.forEach((button, i) => {
            const offColour = this.cssDefaultOffColour;
            let colour = offColour;
            const bits = this.currentLEDBitPatterns[i];
            const num = parseInt(bits, 2);
            colour = num ? "rgb(255, 0, 0)" : offColour;
            button.style.background = colour;
        });
    }
}
const createAnimationEditor = (type, width, height, data = []) => new [MonochromaticAnimationEditor, VariableBrightnessAnimationEditor, RGBAnimationEditor][type](width, height, data);
