// Defines the width and height to render a single LED.
const pixelSize: number = 100;
/**
 * @description Maps a number from one range to a new range, keeping its position in the range the same.
 * @example map(5, 0, 10, 0, 20) => 10
 * @param {number} x The number to be mapped.
 * @param {number} inMin The minimum value of the initial range.
 * @param {number} inMax The maximum value of the initial range.
 * @param {number} outMin The minimum value of the output range.
 * @param {number} outMax The maximum value of the output range.
 * @return {*}  {number}
 */
const map = (x: number, inMin: number, inMax: number, outMin: number, outMax: number): number => ((x - inMin) * (outMax - outMin)) / (inMax - inMin) + outMin;

/**
 * @description Represents the editor used to create an animation.
 * @abstract
 * @class AnimationEditor
 */
abstract class AnimationEditor {
    /**
     * @description The binary string representing the frame which is currently being edited.
     * @private
     * @type {string}
     * @memberof AnimationEditor
     */
    private _binaryString: string = "";
    /**
     * @description Determines whether or not the shift key is currently being pressed.
     * @private
     * @type {boolean}
     * @memberof AnimationEditor
     */
    private _shiftIsDown: boolean = false;
    /**
     * @description The number of bits needed to store a single frame.
     * @type {number}
     * @memberof AnimationEditor
     */
    public bitCount: number;
    /**
     * @description The colour used to display a pixel as off, formatted for use as a CSS style.
     * @type {string}
     * @memberof AnimationEditor
     */
    public cssDefaultOffColour: string = "rgba(255, 255, 255, 0.1)";
    /**
     * @description The colour used to display a pixel as off, in binary.
     * @type {string}
     * @memberof AnimationEditor
     */
    public defaultOffColour: string;
    /**
     * @description An array of binary strings representing each frame that has already been created.
     * @type {string[]}
     * @memberof AnimationEditor
     */
    public frames: string[] = [];
    /**
     * @description An array storing each button used to represent the LEDs for the editor.
     * @type {HTMLButtonElement[]}
     * @memberof AnimationEditor
     */
    public LEDs: HTMLButtonElement[] = [];
    /**
     * @description Stores the timeout which handles playback of an animation.
     * @type {number}
     * @memberof AnimationEditor
     */
    public playbackTimeout: number = 0;
    /**
     * @description Stores which buttons should be switched on when holding shift.
     * @type {number[]}
     * @memberof AnimationEditor
     */
    public shiftedLEDs: number[] = [];

    /**
     * @description Gets the binary string representing the current frame.
     * @type {string}
     * @memberof AnimationEditor
     */
    public get binaryString(): string {
        return this._binaryString;
    }
    /**
     * @description Sets the binary string to a new value, and updates the colour of the buttons to reflect the change.
     * @memberof AnimationEditor
     */
    public set binaryString(val: string) {
        this._binaryString = val;
        this.updateLEDs();
    }
    /**
     * @description Gets a 2D array containing 32 bits integers which represent each frame.
     * @readonly
     * @type {number[]}
     * @memberof AnimationEditor
     */
    public get int32Frames(): number[] {
        return (this.frames
            // Make sure each frame is some-multiple-of-32 bits long.
            .map((frame) => frame.padStart(32 - frame.length % 32 + frame.length, "0"))
            // Create one string from all the frames.
            .join("")
            // Split the string into 32 bit chunks.
            .match(/.{1,32}/g) ?? [])
            // Map the 32 bit strings into numbers.
            .map((bits) => parseInt(bits, 2));
    };
    /**
     * @description Gets the current binary string, split up into each LED's data.
     * @readonly
     * @type {string[]}
     * @memberof AnimationEditor
     */
    public get currentLEDBitPatterns(): string[] {
        return this.binaryString.match(new RegExp(`.{1,${this.LEDBitLength}}`, "g")) as string[];
    };
    /**
     * @description Gets a matrix to represent the current editor state.
     * @readonly
     * @type {Matrix}
     * @memberof AnimationEditor
     */
    public get matrix(): Matrix {
        const matrixData = Matrix.create0Array(this.matrixWidth, this.matrixHeight);
        this.currentLEDBitPatterns.forEach((bit, i) => matrixData[Math.floor(i / this.matrixWidth)][i % this.matrixHeight] = parseInt(bit, 2));
        return new Matrix(matrixData);
    }
    /**
     * @description Gets the number of frames to play each second when playing back the animation.
     * @readonly
     * @type {number}
     * @memberof AnimationEditor
     */
    public get playbackFPS(): number {
        const fps = document.getElementById("fps") as HTMLInputElement;
        return parseInt(fps.value, 10);
    }
    /**
     * @description Gets whether or not the shift key is currently pressed.
     * @type {boolean}
     * @memberof AnimationEditor
     */
    public get shiftIsDown(): boolean {
        return this._shiftIsDown;
    }
    /**
     * @description Changes whether or not the shift key is currently pressed, and displays this information to the user.s
     * @memberof AnimationEditor
     */
    public set shiftIsDown(val: boolean) {
        this._shiftIsDown = val;
        if (val === false) this.shiftedLEDs = [];
        this.displayShiftState();
    }
    /**
     * @description Gets the class type, represented as an integer.
     * @example MonochromaticAnimationEditor => 0
     * @example VariableBrightnessAnimationEditor => 1
     * @example RGBAnimationEditor => 2
     * @readonly
     * @type {number}
     * @memberof AnimationEditor
     */
    public get typeInt(): number {
        if (this instanceof RGBAnimationEditor) return 2;
        if (this instanceof VariableBrightnessAnimationEditor) return 1;
        return 0;
    }

    /**
     * @description The default colour used to represent an LED which is on.
     * @abstract
     * @type {string}
     * @memberof AnimationEditor
     */
    public abstract defaultOnColour: string;
    /**
     * @description The current colour used to represent an LED which is on.
     * @readonly
     * @abstract
     * @type {string}
     * @memberof AnimationEditor
     */
    public abstract get onColour(): string;

    /**
     * Creates an instance of AnimationEditor. Protected has the same effect as making an abstract class.
     * @param {number} matrixWidth The width of the animation.
     * @param {number} matrixHeight The height of the animation.
     * @param {string[]} data Any data which already exists for the animation.
     * @param {number} LEDBitLength The number of bits needed to store a single LED.
     * @memberof AnimationEditor
     */
    protected constructor(protected matrixWidth: number, protected matrixHeight: number, data: string[], public LEDBitLength: number) {
        this.bitCount = this.matrixWidth * this.matrixHeight * this.LEDBitLength;
        this.defaultOffColour = "0".repeat(this.LEDBitLength);
        
        this.frames = data;
        
        this.clearScreen();
    }

    /**
     * @description Creates the icons to be displayed for each frame.
     * @abstract
     * @return {*}  {Array<{ image: string; binary: string }>}
     * @memberof AnimationEditor
     */
    public abstract makeFrameIcons(): Array<{ image: string; binary: string }>;
    /**
     * @description Updates the button elements.
     * @abstract
     * @memberof AnimationEditor
     */
    public abstract updateLEDs(): void;

    /**
     * @description Calculates which grid squares should be included to draw a straight line of any angle.
     * @param {number} x0 The first x-coordinate.
     * @param {number} y0 The first y-coordinate.
     * @param {number} x1 The second x-coordinate.
     * @param {number} y1 The second y-coordinate.
     * @return {*}  {Array<{ x: number; y: number; }>}
     * @memberof AnimationEditor
     */
    public calculateBresenhamLine(x0: number, y0: number, x1: number, y1: number): Array<{ x: number; y: number; }> {
        // https://en.wikipedia.org/wiki/Bresenham%27s_line_algorithm#Line_equation
        const coords: Array<{ x: number; y: number; }> = [];
        const plotLineLow = (x0: number, y0: number, x1: number, y1: number): void => {
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
                coords.push({x, y});
                if (D > 0) {
                    y += yi;
                    D += 2 * (dy - dx);
                } else D += 2 * dy;
            }
        };
        const plotLineHigh = (x0: number, y0: number, x1: number, y1: number): void => {
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
                coords.push({x, y});
                if (D > 0) {
                    x += xi;
                    D += 2 * (dx - dy);
                } else D += 2 * dx;
            }
        };
        if (Math.abs(y1 - y0) < Math.abs(x1 - x0)) {
            if (x0 > x1) plotLineLow(x1, y1, x0, y0);
            else plotLineLow(x0, y0, x1, y1);
        } else {
            if (y0 > y1) plotLineHigh(x1, y1, x0, y0);
            else plotLineHigh(x0, y0, x1, y1);
        }
        return coords;
    }

    /**
     * @description Switches off all LEDs.
     * @memberof AnimationEditor
     */
    public clearScreen(): void {
        this.binaryString = "0".repeat(this.bitCount);
    }

    /**
     * @description Creates a binary string out of a Matrix object.
     * @param {Matrix} M The matrix to convert.
     * @return {*}  {string}
     * @memberof AnimationEditor
     */
    public convertMatrixToString(M: Matrix): string {
        return M.value.map((row) => row.map((x) => x.toString(2).padStart(this.LEDBitLength, "0")).join("")).join("");
    }

    /**
     * @description Renders the icons for each frame.
     * @memberof AnimationEditor
     */
    public displayIcons(): void {
        const frameIconsDiv = document.getElementById("frameIcons") as HTMLDivElement;
        frameIconsDiv.innerHTML = this.makeFrameIcons().map((icon, i) => /*html*/ `
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

    /**
     * @description Renders the state of the shift key.
     * @memberof AnimationEditor
     */
    public displayShiftState(): void {
        const shiftButton = document.getElementById("shiftBtn") as HTMLButtonElement;
        shiftButton.className = this.shiftIsDown ? "btn btn-dark active btn-sm" : "btn btn-dark btn-sm";
    }

    /**
     * @description Draws a plus icon on any sized grid.
     * @memberof AnimationEditor
     */
    public drawPlus(): void {
        const bitPatterns: string[] = this.currentLEDBitPatterns;
        for (let y = 0; y < this.matrixHeight; ++y) {
            for (let x = 0; x < this.matrixWidth; ++x) {
                const validX = x === Math.floor(this.matrixWidth / 2) || x === Math.floor((this.matrixWidth - 1) / 2);
                const validY = y === Math.floor(this.matrixHeight / 2) || y === Math.floor((this.matrixHeight - 1) / 2);
                if (validX || validY) bitPatterns[this.matrixWidth * y + x] = this.onColour;
            }
        }
        this.binaryString = bitPatterns.join("");
    }

    /**
     * @description Draws a cross icon on any sized grid.
     * @memberof AnimationEditor
     */
    public drawCross(): void {
        const bitPatterns: string[] = this.currentLEDBitPatterns;
        const coords = this.calculateBresenhamLine(0, 0, this.matrixWidth - 1, this.matrixHeight - 1)
            .concat(this.calculateBresenhamLine(this.matrixWidth - 1, 0, 0, this.matrixHeight - 1));
        for (let y = 0; y < this.matrixHeight; ++y)
            for (let x = 0; x < this.matrixWidth; ++x)
                if (coords.filter((c) => c.x === x && c.y === y).length > 0)
                    bitPatterns[this.matrixWidth * y + x] = this.onColour;
        this.binaryString = bitPatterns.join("");
    }

    /**
     * @description Draws a border around and sized grid.
     * @memberof AnimationEditor
     */
    public drawBorder(): void {
        const bitPatterns: string[] = this.currentLEDBitPatterns;
        for (let y = 0; y < this.matrixHeight; ++y) {
            for (let x = 0; x < this.matrixWidth; ++x) {
                const validX = x === 0 || x === this.matrixWidth - 1;
                const validY = y === 0 || y === this.matrixHeight - 1;
                if (validX || validY) bitPatterns[this.matrixWidth * y + x] = this.onColour;
            }
        }
        this.binaryString = bitPatterns.join("");
    }

    /**
     * @description Draws a circle in any sized square grid.
     * @param {boolean} [full=false] Determines if the circle should be filled in.
     * @memberof AnimationEditor
     */
    public drawCircle(full: boolean = false): void {
        const bitPatterns: string[] = this.currentLEDBitPatterns;
        const size = Math.min(this.matrixWidth, this.matrixHeight);
        const radius = (size % 2 === 0 ? size : size - 1) / 2;
        for (let y = 0; y < this.matrixHeight; ++y) {
            for (let x = 0; x < this.matrixWidth; ++x) {
                // Pythagoras.
                const xDistance = size % 2 === 0 ? radius - x - 0.5 : radius - x;
                const yDistance = size % 2 === 0 ? radius - y - 0.5 : radius - y;
                const distance = Math.round(Math.sqrt(xDistance ** 2 + yDistance ** 2));
                if ((full && distance <= radius) || distance === radius)
                    bitPatterns[this.matrixWidth * y + x] = this.onColour;
            }
        }
        this.binaryString = bitPatterns.join("");
    }

    /**
     * @description Draws a no-entry icon on any sized square grid.
     * @memberof AnimationEditor
     */
    public drawNoEntry(): void {
        const bitPatterns: string[] = this.currentLEDBitPatterns;
        const size = Math.min(this.matrixWidth, this.matrixHeight);
        const radius = (size % 2 === 0 ? size : size - 1) / 2;
        for (let y = 0; y < this.matrixHeight; ++y) {
            for (let x = 0; x < this.matrixWidth; ++x) {
                // Pythagoras.
                const xDistance = size % 2 === 0 ? radius - x - 0.5 : radius - x;
                const yDistance = size % 2 === 0 ? radius - y - 0.5 : radius - y;
                const distance = Math.round(Math.sqrt(xDistance ** 2 + yDistance ** 2));
                if ((x === y && distance <= radius) || distance === radius)
                    bitPatterns[this.matrixWidth * y + x] = this.onColour;
            }
        }
        this.binaryString = bitPatterns.join("");
    }

    /**
     * @description Turns on every LED.
     * @memberof AnimationEditor
     */
    public fillScreen(): void {
        this.binaryString = this.onColour.repeat(this.matrixWidth * this.matrixHeight);
    }

    /**
     * @description Reflects the grid along the centre.
     * @param {boolean} reverseRows Determines if the line of reflection should be vertical (true) or horizontal (false).
     * @memberof AnimationEditor
     */
    public flip(reverseRows: boolean) {
        this.binaryString = this.convertMatrixToString(reverseRows ? this.matrix.reversedRows : this.matrix.reversedColumns);
    }

    /**
     * @description Switches all 1s to 0s and all 0s to 1s to invert the colour of every pixel.
     * @memberof AnimationEditor
     */
    public invertScreen(): void {
        this.binaryString = this.binaryString.split("").map((x) => (parseInt(x) ? "0" : "1")).join("");
    }

    /**
     * @description Shift the image by one pixel in the given direction.
     * @param {("left" | "right" | "up" | "down")} direction The direction to move the image in.
     * @memberof AnimationEditor
     */
    public move(direction: "left" | "right" | "up" | "down"): void {
        this.binaryString = this.convertMatrixToString(this.matrix.translate(direction));
    }

    /**
     * @description Creates and submits a form which will create a new animation.
     * @memberof AnimationEditor
     */
    public new(): void {
        // @ts-ignore
        unsetCookie("data");
        // @ts-ignore
        unsetCookie("fps");
        // @ts-ignore
        unsetCookie("colour");
        const form: HTMLFormElement = document.createElement("form");
        form.style.display = "none";
        form.setAttribute("method", "post");
        const input: HTMLInputElement = document.createElement("input");
        input.setAttribute("type", "text");
        input.setAttribute("name", "createNew");
        input.setAttribute("value", "true");
        form.appendChild(input);
        document.getElementsByTagName("body")[0].appendChild(form);
        form.submit();
    }

    /**
     * @description Plays an animation made up of each frame from the animation.
     * @memberof AnimationEditor
     */
    public playback(): void {
        (document.getElementById("saveAlert") as HTMLDivElement).innerHTML = "";
        clearTimeout(this.playbackTimeout);
        const playbackDiv = document.getElementById("playback") as HTMLDivElement;
        const frames = this.makeFrameIcons();
        let i = 0;
        const renderFrame = () => {
            const currentFrame = frames[i++];
            playbackDiv.innerHTML = `<img src=${currentFrame.image}>`;
            this.playbackTimeout = setTimeout(renderFrame, 1000 / this.playbackFPS);
        };
        renderFrame();
    }

    /**
     * @description Copies the current chosen frame to the current editor.
     * @param {string} binary The data from the chosen frame.
     * @memberof AnimationEditor
     */
    public onFrameCopy(binary: string): void {
        this.binaryString = binary;
    }

    /**
     * @description Delete the chosen frame.
     * @param {number} index The array index for the chosen frame.
     * @memberof AnimationEditor
     */
    public onFrameDelete(index: number): void {
        this.frames = this.frames.filter((_, i) => i !== index);
        this.updateIcons();
    }

    /**
     * @description Toggles the clicked LED, then, if the shift key is pressed, calculates which pixels need to be on for a straight line and switches them all on.
     * @param {number} index The array index of the LED which has been clicked.
     * @memberof AnimationEditor
     */
    public onLEDClicked(index: number): void {
        const bitPatterns: string[] = this.currentLEDBitPatterns;
        if (this.shiftIsDown) {
            if (this.shiftedLEDs.length === 0) {
                this.shiftedLEDs.push(index);
                bitPatterns[index] = parseInt(bitPatterns[index], 2) ? this.defaultOffColour : this.onColour;
            } else if (this.shiftedLEDs.length === 1) {
                this.shiftedLEDs.push(index);
                const coords = this.calculateBresenhamLine(
                    this.shiftedLEDs[0] % this.matrixWidth,
                    Math.floor(this.shiftedLEDs[0] / this.matrixWidth),
                    this.shiftedLEDs[1] % this.matrixWidth,
                    Math.floor(this.shiftedLEDs[1] / this.matrixWidth)
                );
                coords.forEach((buttonCoords) => {
                    const i: number = buttonCoords.x + buttonCoords.y * this.matrixWidth;
                    bitPatterns[i] = this.onColour;
                });
                this.shiftedLEDs = [];
            }
        } else bitPatterns[index] = parseInt(bitPatterns[index], 2) ? this.defaultOffColour : this.onColour;
        this.binaryString = bitPatterns.join("");
    }

    /**
     * @description Rotates the image by the given angle.
     * @param {(90 | 180 | 270)} angle The angle by which to rotate the image.
     * @memberof AnimationEditor
     */
    public rotate(angle: 90 | 180 | 270): void {
        this.binaryString = this.convertMatrixToString(this.matrix.rotate(angle));
    }

    /**
     * @description Submits an AJAX request which saves the current animation to the database.
     * @memberof AnimationEditor
     */
    public save(): void {
        const width = this.matrixWidth;
        const height = this.matrixHeight;
        const type = this.typeInt;
        const data = JSON.stringify(this.frames);
        // @ts-ignore
        const id = SESSION.matrix.id;
        // @ts-ignore
        const name = SESSION.matrix.name;
        // @ts-ignore
        const username = SESSION.username;
        
        // @ts-ignore
        $.post("Utils/Forms/saveAnimation", { width, height, type, id, name, data, username }, () => {
            clearTimeout(this.playbackTimeout);
            (document.getElementById("playback") as HTMLDivElement).innerHTML = "";
            (document.getElementById("saveAlert") as HTMLDivElement).innerHTML = `
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Animation Saved!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
        });
    }

    /**
     * @description Creates the controls needed to edit the animation.
     * @param {number} fps The number to set the FPS input to when changing page.
     * @param {(string | null)} colour The colour to set the colour input to when changing page.
     * @memberof AnimationEditor
     */
    public setControls(fps: number, colour: string | null): void {
        const controlsDiv = document.getElementById("controls") as HTMLDivElement;
        let html: string = /*html*/ `
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
                html += /*html*/`
                    <div class="form-floating bg-dark text-white border-dark">
                        <input type="number" class="form-control bg-dark text-white border-dark" id="fps" name="fps" placeholder="FPS" min=1 max=60 value=1>
                        <label for="fps">FPS</label>
                    </div>
                `;
                break; 
            case 1:
                html += /*html*/`
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
                html += /*html*/`
                    <div class="form-floating bg-dark text-white border-dark">
                        <input type="number" class="form-control bg-dark text-white border-dark" id="fps" name="fps" placeholder="FPS" min=1 max=60 value=1>
                        <label for="fps">FPS</label>
                    </div>
                    <input id="colourInput" class="form-control form-control-color bg-dark border-dark" type="color" name="colour" value="${this.defaultOnColour}">
                `;
                break;
        }
        html += /*html*/`
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
        const fpsInput = document.getElementById("fps") as HTMLInputElement;
        fpsInput.value = fps.toString();
        if (colour !== null) {
            const colourInput = document.getElementById("colourInput") as HTMLInputElement;
            if (this instanceof VariableBrightnessAnimationEditor) colourInput.value = parseInt(colour, 2).toString(10);
            else if (this instanceof RGBAnimationEditor) colourInput.value = `#${parseInt(colour, 2).toString(16)}`;
        }
    }

    /**
     * @description Toggle the shiftIsDown property.
     * @memberof AnimationEditor
     */
    public toggleShift(): void {
        this.shiftIsDown = !this.shiftIsDown;
    }

    /**
     * @description Displays the icons for each frame then updates the cookies which store some information.
     * @memberof AnimationEditor
     */
    public updateIcons(): void {
        (document.getElementById("saveAlert") as HTMLDivElement).innerHTML = "";
        this.displayIcons();
        // @ts-ignore
        setCookie("data", JSON.stringify(this.frames));
        // @ts-ignore
        setCookie("fps", this.playbackFPS.toString());
        // @ts-ignore
        setCookie("colour", this.onColour.toString());
    }
}

/**
 * @description Represents the editor used to create an RGB animation.
 * @class RGBAnimationEditor
 * @extends {AnimationEditor}
 */
class RGBAnimationEditor extends AnimationEditor {
    /**
     * @description The colour used to display a pixel as off, in binary.
     * @type {string}
     * @memberof RGBAnimationEditor
     */
    public defaultOnColour: string = "#ff0000";

    /**
     * @description The current colour used to represent an LED which is on.
     * @readonly
     * @type {string}
     * @memberof RGBAnimationEditor
     */
    public get onColour(): string {
        const colourInput = document.getElementById("colourInput") as HTMLInputElement;
        return parseInt(colourInput.value.slice(1), 16).toString(2).padStart(24, "0");
    };

    /**
     * Creates an instance of RGBAnimationEditor.
     * @param {number} width The width of the animation.
     * @param {number} height The height of the animation.
     * @param {string[]} data Any data which already exists for the animation.
     * @memberof RGBAnimationEditor
     */
    public constructor(width: number, height: number, data: string[]) {
        super(width, height, data, 24);
    }

    /**
     * @description Creates the icons to be displayed for each frame.
     * @return {*}  {Array<{ image: string; binary: string }>}
     * @memberof RGBAnimationEditor
     */
    public makeFrameIcons(): Array<{ image: string; binary: string }> {
        return this.frames.map((frame: string) => {
            const canvas: HTMLCanvasElement = document.createElement("canvas");
            canvas.width = this.matrixWidth * pixelSize;
            canvas.height = this.matrixHeight * pixelSize;
            const context = canvas.getContext("2d");
            if (context === null) throw Error();
            (frame.match(/.{1,24}/g) as string[])
                .map((x) => (x.match(/.{1,8}/g) as string[]).map((y) => parseInt(y, 2)))
                .forEach((bytes, i) => {
                    context.fillStyle = bytes.some((byte) => byte > 0)
                        ? `rgb(${bytes[0]}, ${bytes[1]}, ${bytes[2]})`
                        : this.cssDefaultOffColour;
                    const x: number = (i % this.matrixWidth) * pixelSize;
                    const y: number = Math.floor(i / this.matrixWidth) * pixelSize;
                    context.fillRect(x, y, pixelSize, pixelSize);
                });
            return {
                binary: frame,
                image: canvas.toDataURL(),
            };
        });
    }
    
    /**
     * @description Updates the button elements.
     * @memberof RGBAnimationEditor
     */
    public updateLEDs(): void {
        this.LEDs.forEach((button, i) => {
            const offColour = this.cssDefaultOffColour;
            let colour = offColour;
            const bits = this.currentLEDBitPatterns[i];
            const num = parseInt(bits, 2);
            const bitsArr = bits.match(/.{1,8}/g) as string[];
            const numArr = bitsArr.map((x) => parseInt(x, 2));
            colour = num ? "rgb(" + numArr.join(", ") + ")" : offColour;
            button.style.background = colour;
        });
    }
}

/**
 * @description Represents the editor used to create a variable brightness animation.
 * @class VariableBrightnessAnimationEditor
 * @extends {AnimationEditor}
 */
class VariableBrightnessAnimationEditor extends AnimationEditor {
    /**
     * @description The colour used to display a pixel as off, in binary.
     * @type {string}
     * @memberof VariableBrightnessAnimationEditor
     */
    public defaultOnColour: string = "255";

    /**
     * @description The current colour used to represent an LED which is on.
     * @readonly
     * @type {string}
     * @memberof VariableBrightnessAnimationEditor
     */
    public get onColour(): string {
        const colourInput = document.getElementById("colourInput") as HTMLInputElement;
        return parseInt(colourInput.value).toString(2).padStart(8, "0");
    };
    
    /**
     * Creates an instance of VariableBrightnessAnimationEditor.
     * @param {number} width The width of the animation.
     * @param {number} height The height of the animation.
     * @param {string[]} data Any data which already exists for the animation.
     * @memberof VariableBrightnessAnimationEditor
     */
    public constructor(width: number, height: number, data: string[]) {
        super(width, height, data, 8);
    }

    /**
     * @description Creates the icons to be displayed for each frame.
     * @return {*}  {Array<{ image: string; binary: string }>}
     * @memberof VariableBrightnessAnimationEditor
     */
    public makeFrameIcons(): Array<{ image: string; binary: string }> {
        const map = (
            x: number,
            inMin: number,
            inMax: number,
            outMin: number,
            outMax: number
        ) => ((x - inMin) * (outMax - outMin)) / (inMax - inMin) + outMin;
        return this.frames.map((frame: string) => {
            const canvas: HTMLCanvasElement = document.createElement("canvas");
            canvas.width = this.matrixWidth * pixelSize;
            canvas.height = this.matrixHeight * pixelSize;
            const context = canvas.getContext("2d");
            if (context === null) throw Error();
            (frame.match(/.{1,8}/g) as string[])
                .map((x) => parseInt(x, 2))
                .forEach((byte, i) => {
                    context.fillStyle =
                        byte > 0
                            ? `rgba(255, 0, 0, ${map(byte, 0, 255, 0, 1)})`
                            : this.cssDefaultOffColour;
                    const x: number = (i % this.matrixWidth) * pixelSize;
                    const y: number =
                        Math.floor(i / this.matrixWidth) * pixelSize;
                    context.fillRect(x, y, pixelSize, pixelSize);
                });
            return {
                binary: frame,
                image: canvas.toDataURL(),
            };
        });
    }
    
    /**
     * @description Updates the button elements.
     * @memberof VariableBrightnessAnimationEditor
     */
    public updateLEDs(): void {
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

/**
 * @description Represents the editor used to create a monochromatic animation.
 * @class MonochromaticAnimationEditor
 * @extends {AnimationEditor}
 */
class MonochromaticAnimationEditor extends AnimationEditor {
    /**
     * @description The colour used to display a pixel as off, in binary.
     * @type {string}
     * @memberof MonochromaticAnimationEditor
     */
    public defaultOnColour: string = "1";
    
    /**
     * @description The current colour used to represent an LED which is on.
     * @readonly
     * @type {string}
     * @memberof MonochromaticAnimationEditor
     */
    public get onColour(): string {
        return this.defaultOnColour;
    };
    
    /**
     * Creates an instance of MonochromaticAnimationEditor.
     * @param {number} width The width of the animation.
     * @param {number} height The height of the animation.
     * @param {string[]} data Any data which already exists for the animation.
     * @memberof MonochromaticAnimationEditor
     */
    public constructor(width: number, height: number, data: string[]) {
        super(width, height, data, 1);
    }

    /**
     * @description Creates the icons to be displayed for each frame.
     * @return {*}  {Array<{ image: string; binary: string }>}
     * @memberof MonochromaticAnimationEditor
     */
    public makeFrameIcons(): Array<{ image: string; binary: string }> {
        return this.frames.map((frame: string) => {
            const canvas: HTMLCanvasElement = document.createElement("canvas");
            canvas.width = this.matrixWidth * pixelSize;
            canvas.height = this.matrixHeight * pixelSize;
            const context: CanvasRenderingContext2D | null = canvas.getContext(
                "2d"
            );
            if (context === null) throw Error();
            frame
                .split("")
                .map((x) => parseInt(x, 2))
                .forEach((bit, i) => {
                    context.fillStyle = bit === 1 ? "red" : this.cssDefaultOffColour;
                    const x: number = (i % this.matrixWidth) * pixelSize;
                    const y: number =
                        Math.floor(i / this.matrixWidth) * pixelSize;
                    context.fillRect(x, y, pixelSize, pixelSize);
                });
            return {
                binary: frame,
                image: canvas.toDataURL(),
            };
        });
    }
    
    /**
     * @description Updates the button elements.
     * @memberof MonochromaticAnimationEditor
     */
    public updateLEDs(): void {
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

/**
 * @description Creates a new AnimationEditor object of the given type.
 * @param {(0 | 1 | 2)} type The numeric type for the animation.
 * @param {number} width The width of the animation.
 * @param {number} height The height of the animation.
 * @param {string[]} [data=[]] Any data which already exists for the animation.
 * @return {*}  {AnimationEditor}
 */
const createAnimationEditor = (type: 0 | 1 | 2, width: number, height: number, data: string[] = []): AnimationEditor =>
    new [MonochromaticAnimationEditor, VariableBrightnessAnimationEditor, RGBAnimationEditor][type](width, height, data);