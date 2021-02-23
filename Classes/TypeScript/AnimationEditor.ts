const pixelSize = 100;
const map = (x: number, inMin: number, inMax: number, outMin: number, outMax: number): number =>
    ((x - inMin) * (outMax - outMin)) / (inMax - inMin) + outMin;

abstract class AnimationEditor {
    private _binaryString: string = "";
    private _shiftIsDown: boolean = false;
    public bitCount: number;
    public defaultOffColour: string;
    public frames: string[] = [];
    public LEDs: HTMLButtonElement[] = [];
    public playbackTimeout: number = 0;
    public shiftedLEDs: number[] = [];

    public get binaryString(): string {
        return this._binaryString;
    }
    public set binaryString(val: string) {
        this._binaryString = val;
        this.updateLEDs();
    }
    public get matrix(): Matrix {
        const matrixData = Matrix.create0Array(this.matrixWidth, this.matrixHeight);
        this.LEDBitPatterns.forEach((bit, i) => matrixData[Math.floor(i / this.matrixWidth)][i % this.matrixHeight] = parseInt(bit, 2));
        return new Matrix(matrixData);
    }
    public get playbackFPS(): number {
        const fpsInput = document.getElementById("fpsInput") as HTMLInputElement;
        return parseInt(fpsInput.value, 10);
    }
    public get shiftIsDown(): boolean {
        return this._shiftIsDown;
    }
    public set shiftIsDown(val: boolean) {
        this._shiftIsDown = val;
        if (val === false) this.shiftedLEDs = [];
        this.displayShiftState();
    }

    public abstract defaultOnColour: string;

    public abstract get LEDBitPatterns(): string[];
    public abstract get onColour(): string;

    protected constructor(protected matrixWidth: number, protected matrixHeight: number, frames: string[], public LEDBitLength: number) {
        this.bitCount = this.matrixWidth * this.matrixHeight * this.LEDBitLength;
        this.defaultOffColour = "0".repeat(this.LEDBitLength);
        this.frames = frames;
        this.clearScreen();
    }

    public abstract makeFrameIcons(): Array<{ image: string; binary: string }>;
    public abstract setControls(): void;
    public abstract updateLEDs(): void;

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

    public clearScreen(): void {
        this.binaryString = "0".repeat(this.bitCount);
    }

    public convertMatrixToString(M: Matrix): string {
        return M.value.map((row) => row.map((x) => x.toString(2).padStart(this.LEDBitLength, "0")).join("")).join("");
    }

    public displayIcons(): void {
        const frameIconsDiv = document.getElementById("frameIcons") as HTMLDivElement;
        frameIconsDiv.innerHTML = this.makeFrameIcons().map((icon, i) => /*html*/ `
            <div class="icon">
                <img src="${icon.image}">
                <div class="buttons">
                    <button class="btn btn-secondary btn-sm" data-toggle="tooltip" data-placement="top" title="Copy this frame to the editor" onclick="editor.onFrameCopy('${icon.binary}')">Copy</button><br>
                    <button class="btn btn-secondary btn-sm" data-toggle="tooltip" data-placement="top" title="Delete this frame" onclick="editor.onFrameDelete(${i})">Delete</button>
                </div>
                <p>${icon.binary}</p>
            </div>
        `).join("");
    }

    public displayShiftState(): void {
        const shiftButton = document.getElementById("shiftBtn") as HTMLButtonElement;
        shiftButton.className = this.shiftIsDown ? "btn btn-primary active btn-sm" : "btn btn-primary btn-sm";
    }

    public drawPlus(): void {
        const bitPatterns: string[] = this.LEDBitPatterns;
        for (let y = 0; y < this.matrixHeight; ++y) {
            for (let x = 0; x < this.matrixWidth; ++x) {
                const validX = x === Math.floor(this.matrixWidth / 2) || x === Math.floor((this.matrixWidth - 1) / 2);
                const validY = y === Math.floor(this.matrixHeight / 2) || y === Math.floor((this.matrixHeight - 1) / 2);
                if (validX || validY) bitPatterns[this.matrixWidth * y + x] = this.onColour;
            }
        }
        this.binaryString = bitPatterns.join("");
    }

    public drawCross(): void {
        const bitPatterns: string[] = this.LEDBitPatterns;
        const coords = this.calculateBresenhamLine(0, 0, this.matrixWidth - 1, this.matrixHeight - 1)
            .concat(this.calculateBresenhamLine(this.matrixWidth - 1, 0, 0, this.matrixHeight - 1));
        for (let y = 0; y < this.matrixHeight; ++y)
            for (let x = 0; x < this.matrixWidth; ++x)
                if (coords.filter((c) => c.x === x && c.y === y).length > 0)
                    bitPatterns[this.matrixWidth * y + x] = this.onColour;
        this.binaryString = bitPatterns.join("");
    }

    public drawBorder(): void {
        const bitPatterns: string[] = this.LEDBitPatterns;
        for (let y = 0; y < this.matrixHeight; ++y) {
            for (let x = 0; x < this.matrixWidth; ++x) {
                const validX = x === 0 || x === this.matrixWidth - 1;
                const validY = y === 0 || y === this.matrixHeight - 1;
                if (validX || validY) bitPatterns[this.matrixWidth * y + x] = this.onColour;
            }
        }
        this.binaryString = bitPatterns.join("");
    }

    public drawCircle(full = false): void {
        const bitPatterns: string[] = this.LEDBitPatterns;
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

    public drawNoEntry(): void {
        const bitPatterns: string[] = this.LEDBitPatterns;
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

    public fillScreen(): void {
        this.binaryString = this.onColour.repeat(this.matrixWidth * this.matrixHeight);
    }

    public flip(reverseRows: boolean) {
        this.binaryString = this.convertMatrixToString(reverseRows ? this.matrix.reversedRows : this.matrix.reversedColumns);
    }

    public invertScreen(): void {
        this.binaryString = this.binaryString.split("").map((x) => (parseInt(x) ? "0" : "1")).join("");
    }

    public move(direction: "left" | "right" | "up" | "down"): void {
        this.binaryString = this.convertMatrixToString(this.matrix.translate(direction));
    }

    public playback(): void {
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

    public onFrameCopy(binary: string): void {
        this.binaryString = binary;
    }

    public onFrameDelete(index: number): void {
        this.frames = this.frames.filter((_, i) => i !== index);
        this.updateIcons();
    }

    public onLEDClicked(index: number): void {
        const bitPatterns: string[] = this.LEDBitPatterns;
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

    public rotate(angle: 90 | 180 | 270): void {
        this.binaryString = this.convertMatrixToString(this.matrix.rotate(angle));
    }

    public toggleShift(): void {
        this.shiftIsDown = !this.shiftIsDown;
    }

    public updateIcons(): void {
        const data: string = JSON.stringify(this.frames);
        document.location.assign(`${document.URL.split("?")[0]}?frames=${encodeURIComponent(data)}`);
    }
}

class RGBAnimationEditor extends AnimationEditor {
    public defaultOnColour: string = "#ff0000";
    
    public get LEDBitPatterns(): string[] {
        return this.binaryString.match(/.{1,24}/g) as string[];
    };
    public get onColour(): string {
        const colourInput = document.getElementById("colourInput") as HTMLInputElement;
        return parseInt(colourInput.value.slice(1), 16).toString(2).padStart(24, "0");
    };

    public constructor(width: number, height: number, frames: string[]) {
        super(width, height, frames, 24);
    }

    public makeFrameIcons(): Array<{ image: string; binary: string }> {
        return this.frames.map((frame: string) => {
            const canvas: HTMLCanvasElement = document.createElement("canvas");
            canvas.width = this.matrixWidth * pixelSize;
            canvas.height = this.matrixHeight * pixelSize;
            const context = canvas.getContext("2d");
            if (context === null) throw Error();
            (frame.match(/.{1,24}/g) as string[])
                .map((x) =>
                    (x.match(/.{1,8}/g) as string[]).map((y) => parseInt(y, 2))
                )
                .forEach((bytes, i) => {
                    context.fillStyle = bytes.some((byte) => byte > 0)
                        ? `rgb(${bytes[0]}, ${bytes[1]}, ${bytes[2]})`
                        : "rgba(0, 0, 0, 0.1)";
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

    public setControls(): void {
        let html: string = "";
        html += /*html*/ `
            <br>
            <div class="btn-group">
                <button class="btn btn-primary btn-sm" onclick="editor.clearScreen();   " data-toggle="tooltip" data-placement="top" title="Turn off all LEDs">Clear</button>
                <button class="btn btn-primary btn-sm" onclick="editor.fillScreen();    " data-toggle="tooltip" data-placement="top" title="Turn on all LEDs">Fill</button>
                <button class="btn btn-primary btn-sm" onclick="editor.invertScreen();  " data-toggle="tooltip" data-placement="top" title="Invert all LEDs">Invert</button>
                <button class="btn btn-primary btn-sm" onclick="editor.toggleShift();   " data-toggle="tooltip" data-placement="top" title="Hold shift to draw straight lines" id="shiftBtn" >Shift</button>
                <button class="btn btn-primary btn-sm" onclick="editor.flip(false);     " data-toggle="tooltip" data-placement="top" title="Flip the image vertically">↕</button>
                <button class="btn btn-primary btn-sm" onclick="editor.flip(true);      " data-toggle="tooltip" data-placement="top" title="Flip the image horizontally">↔</button>
        `;
        if (this.matrixWidth === this.matrixHeight)
            html += /*html*/ `
                <button class="btn btn-primary btn-sm" onclick="editor.move('left');    " data-toggle="tooltip" data-placement="top" title="Move the image to the left">⬅</button>
                <button class="btn btn-primary btn-sm" onclick="editor.move('right');   " data-toggle="tooltip" data-placement="top" title="Move the image to the right">➡</button>
                <button class="btn btn-primary btn-sm" onclick="editor.move('up');      " data-toggle="tooltip" data-placement="top" title="Move the image up">⬆</button>
                <button class="btn btn-primary btn-sm" onclick="editor.move('down');    " data-toggle="tooltip" data-placement="top" title="Move the image down">⬇</button>
                <button class="btn btn-primary btn-sm" onclick="editor.rotate(270);     " data-toggle="tooltip" data-placement="top" title="Rotate the image anticlockwise">↪</button>
                <button class="btn btn-primary btn-sm" onclick="editor.rotate(90);      " data-toggle="tooltip" data-placement="top" title="Rotate the image clockwise">↩</button>
            `;
        else
            html += /*html*/ `
                <button class="btn btn-primary btn-sm" onclick="editor.move('left');    " disabled>⬅</button>
                <button class="btn btn-primary btn-sm" onclick="editor.move('right');   " disabled>➡</button>
                <button class="btn btn-primary btn-sm" onclick="editor.move('up');      " disabled>⬆</button>
                <button class="btn btn-primary btn-sm" onclick="editor.move('down');    " disabled>⬇</button>
                <button class="btn btn-primary btn-sm" onclick="editor.rotate(270);     " disabled>↪</button>
                <button class="btn btn-primary btn-sm" onclick="editor.rotate(90);      " disabled>↩</button>
            `;
        html += /*html*/ `
            </div>
            <br><br>
            <div class="btn-group">
                <button class="btn btn-primary btn-sm" onclick="editor.drawPlus();      " data-toggle="tooltip" data-placement="top" title="Draw a plus sign">➕</button>
                <button class="btn btn-primary btn-sm" onclick="editor.drawCross();     " data-toggle="tooltip" data-placement="top" title="Draw a diagonal cross">❌</button>
                <button class="btn btn-primary btn-sm" onclick="editor.drawBorder();    " data-toggle="tooltip" data-placement="top" title="Draw a border">🔲</button>
        `;
        if (this.matrixWidth === this.matrixHeight)
            html += /*html*/ `
                <button class="btn btn-primary btn-sm" onclick="editor.drawCircle();    " data-toggle="tooltip" data-placement="top" title="Draw a circle outline">⭕</button>
                <button class="btn btn-primary btn-sm" onclick="editor.drawCircle(true);" data-toggle="tooltip" data-placement="top" title="Draw a circle">🔴</button>
                <button class="btn btn-primary btn-sm" onclick="editor.drawNoEntry();   " data-toggle="tooltip" data-placement="top" title="Draw a no entry sign">🚫</button>
            `;
        else
            html += /*html*/ `
                <button class="btn btn-primary btn-sm" onclick="editor.drawCircle();    " disabled>⭕</button>
                <button class="btn btn-primary btn-sm" onclick="editor.drawCircle(true);" disabled>🔴</button>
                <button class="btn btn-primary btn-sm" onclick="editor.drawNoEntry();   " disabled>🚫</button>
            `;
        html += /*html*/ `
            </div>
            <br><br>
            <label for="colour">Colour:</label>
            <input id="colourInput" type="color" name="colour" value="${this.defaultOnColour}">
            <br>
            <button class="btn btn-primary btn-sm" onclick="editor.frames.push(editor.binaryString); editor.updateIcons(); editor.clearScreen();" data-toggle="tooltip" data-placement="top" title="Save this frame and make a new one">➕</button>
            <button class="btn btn-primary btn-sm" onclick="editor.playback();" data-toggle="tooltip" data-placement="top" title="Play the animation">Play</button>
            <label for="colour">FPS:</label>
            <input id="fpsInput" type="number" name="fps" min="1" max="120" value="1">
        `;
        const controlsDiv = document.getElementById("controls") as HTMLDivElement;
        controlsDiv.innerHTML = html;
    }
    
    public updateLEDs(): void {
        this.LEDs.forEach((button, i) => {
            const offColour = "rgba(0, 0, 0, 0.1)";
            let colour = offColour;
            const bits = this.LEDBitPatterns[i];
            const num = parseInt(bits, 2);
            const bitsArr = bits.match(/.{1,8}/g) as string[];
            const numArr = bitsArr.map((x) => parseInt(x, 2));
            colour = num ? "rgb(" + numArr.join(", ") + ")" : offColour;
            button.style.background = colour;
        });
    }
}

class VariableBrightnessAnimationEditor extends AnimationEditor {
    public defaultOnColour: string = "255";
    
    public get LEDBitPatterns(): string[] {
        return this.binaryString.match(/.{1,8}/g) as string[];
    };
    public get onColour(): string {
        const colourInput = document.getElementById("colourInput") as HTMLInputElement;
        return parseInt(colourInput.value).toString(2).padStart(8, "0");
    };
    
    public constructor(width: number, height: number, frames: string[]) {
        super(width, height, frames, 8);
    }

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
                            : "rgba(0, 0, 0, 0.1)";
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

    public setControls(): void {
        let html: string = "";
        html += /*html*/ `
            <br>
            <div class="btn-group">
                <button class="btn btn-primary btn-sm" onclick="editor.clearScreen();   " data-toggle="tooltip" data-placement="top" title="Turn off all LEDs">Clear</button>
                <button class="btn btn-primary btn-sm" onclick="editor.fillScreen();    " data-toggle="tooltip" data-placement="top" title="Turn on all LEDs">Fill</button>
                <button class="btn btn-primary btn-sm" onclick="editor.invertScreen();  " data-toggle="tooltip" data-placement="top" title="Invert all LEDs">Invert</button>
                <button class="btn btn-primary btn-sm" onclick="editor.toggleShift();   " data-toggle="tooltip" data-placement="top" title="Hold shift to draw straight lines" id="shiftBtn" >Shift</button>
                <button class="btn btn-primary btn-sm" onclick="editor.flip(false);     " data-toggle="tooltip" data-placement="top" title="Flip the image vertically">↕</button>
                <button class="btn btn-primary btn-sm" onclick="editor.flip(true);      " data-toggle="tooltip" data-placement="top" title="Flip the image horizontally">↔</button>
        `;
        if (this.matrixWidth === this.matrixHeight)
            html += /*html*/ `
                <button class="btn btn-primary btn-sm" onclick="editor.move('left');    " data-toggle="tooltip" data-placement="top" title="Move the image to the left">⬅</button>
                <button class="btn btn-primary btn-sm" onclick="editor.move('right');   " data-toggle="tooltip" data-placement="top" title="Move the image to the right">➡</button>
                <button class="btn btn-primary btn-sm" onclick="editor.move('up');      " data-toggle="tooltip" data-placement="top" title="Move the image up">⬆</button>
                <button class="btn btn-primary btn-sm" onclick="editor.move('down');    " data-toggle="tooltip" data-placement="top" title="Move the image down">⬇</button>
                <button class="btn btn-primary btn-sm" onclick="editor.rotate(270);     " data-toggle="tooltip" data-placement="top" title="Rotate the image anticlockwise">↪</button>
                <button class="btn btn-primary btn-sm" onclick="editor.rotate(90);      " data-toggle="tooltip" data-placement="top" title="Rotate the image clockwise">↩</button>
            `;
        else
            html += /*html*/ `
                <button class="btn btn-primary btn-sm" onclick="editor.move('left');    " disabled>⬅</button>
                <button class="btn btn-primary btn-sm" onclick="editor.move('right');   " disabled>➡</button>
                <button class="btn btn-primary btn-sm" onclick="editor.move('up');      " disabled>⬆</button>
                <button class="btn btn-primary btn-sm" onclick="editor.move('down');    " disabled>⬇</button>
                <button class="btn btn-primary btn-sm" onclick="editor.rotate(270);     " disabled>↪</button>
                <button class="btn btn-primary btn-sm" onclick="editor.rotate(90);      " disabled>↩</button>
            `;
        html += /*html*/ `
            </div>
            <br><br>
            <div class="btn-group">
                <button class="btn btn-primary btn-sm" onclick="editor.drawPlus();      " data-toggle="tooltip" data-placement="top" title="Draw a plus sign">➕</button>
                <button class="btn btn-primary btn-sm" onclick="editor.drawCross();     " data-toggle="tooltip" data-placement="top" title="Draw a diagonal cross">❌</button>
                <button class="btn btn-primary btn-sm" onclick="editor.drawBorder();    " data-toggle="tooltip" data-placement="top" title="Draw a border">🔲</button>
        `;
        if (this.matrixWidth === this.matrixHeight)
            html += /*html*/ `
                <button class="btn btn-primary btn-sm" onclick="editor.drawCircle();    " data-toggle="tooltip" data-placement="top" title="Draw a circle outline">⭕</button>
                <button class="btn btn-primary btn-sm" onclick="editor.drawCircle(true);" data-toggle="tooltip" data-placement="top" title="Draw a circle">🔴</button>
                <button class="btn btn-primary btn-sm" onclick="editor.drawNoEntry();   " data-toggle="tooltip" data-placement="top" title="Draw a no entry sign">🚫</button>
            `;
        else
            html += /*html*/ `
                <button class="btn btn-primary btn-sm" onclick="editor.drawCircle();    " disabled>⭕</button>
                <button class="btn btn-primary btn-sm" onclick="editor.drawCircle(true);" disabled>🔴</button>
                <button class="btn btn-primary btn-sm" onclick="editor.drawNoEntry();   " disabled>🚫</button>
            `;
        html += /*html*/ `
            </div>
            <br><br>
            <label for="colour">Brightness:</label>
            <input id="colourInput" type="number" name="colour" min="0" max="255" value="${this.defaultOnColour}">
            <br>
            <button class="btn btn-primary btn-sm" onclick="editor.frames.push(editor.binaryString); editor.updateIcons(); editor.clearScreen();" data-toggle="tooltip" data-placement="top" title="Save this frame and make a new one">➕</button>
            <button class="btn btn-primary btn-sm" onclick="editor.playback();" data-toggle="tooltip" data-placement="top" title="Play the animation">Play</button>
            <label for="colour">FPS:</label>
            <input id="fpsInput" type="number" name="fps" min="1" max="120" value="1">
        `;
        const controlsDiv = document.getElementById("controls") as HTMLDivElement;
        controlsDiv.innerHTML = html;
    }
    
    public updateLEDs(): void {
        this.LEDs.forEach((button, i) => {
            const offColour = "rgba(0, 0, 0, 0.1)";
            let colour = offColour;
            const bits = this.LEDBitPatterns[i];
            const num = parseInt(bits, 2);
            colour = num
                ? `rgba(255, 0, 0, ${map(num, 0, 255, 0, 1)})`
                : offColour;
            button.style.background = colour;
        });
    }
}

class MonochromaticAnimationEditor extends AnimationEditor {
    public defaultOnColour: string = "1";
    
    public get LEDBitPatterns(): string[] {
        return this.binaryString.split("");
    };
    public get onColour(): string {
        return this.defaultOnColour;
    };
    
    public constructor(width: number, height: number, frames: string[]) {
        super(width, height, frames, 1);
    }

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
                    context.fillStyle =
                        bit === 1 ? "red" : "rgba(0, 0, 0, 0.1)";
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

    public setControls(): void {
        let html: string = "";
        html += /*html*/ `
            <br>
            <div class="btn-group">
                <button class="btn btn-primary btn-sm" onclick="editor.clearScreen();   " data-toggle="tooltip" data-placement="top" title="Turn off all LEDs">Clear</button>
                <button class="btn btn-primary btn-sm" onclick="editor.fillScreen();    " data-toggle="tooltip" data-placement="top" title="Turn on all LEDs">Fill</button>
                <button class="btn btn-primary btn-sm" onclick="editor.invertScreen();  " data-toggle="tooltip" data-placement="top" title="Invert all LEDs">Invert</button>
                <button class="btn btn-primary btn-sm" onclick="editor.toggleShift();   " data-toggle="tooltip" data-placement="top" title="Hold shift to draw straight lines" id="shiftBtn" >Shift</button>
                <button class="btn btn-primary btn-sm" onclick="editor.flip(false);     " data-toggle="tooltip" data-placement="top" title="Flip the image vertically">↕</button>
                <button class="btn btn-primary btn-sm" onclick="editor.flip(true);      " data-toggle="tooltip" data-placement="top" title="Flip the image horizontally">↔</button>
        `;
        if (this.matrixWidth === this.matrixHeight)
            html += /*html*/ `
                <button class="btn btn-primary btn-sm" onclick="editor.move('left');    " data-toggle="tooltip" data-placement="top" title="Move the image to the left">⬅</button>
                <button class="btn btn-primary btn-sm" onclick="editor.move('right');   " data-toggle="tooltip" data-placement="top" title="Move the image to the right">➡</button>
                <button class="btn btn-primary btn-sm" onclick="editor.move('up');      " data-toggle="tooltip" data-placement="top" title="Move the image up">⬆</button>
                <button class="btn btn-primary btn-sm" onclick="editor.move('down');    " data-toggle="tooltip" data-placement="top" title="Move the image down">⬇</button>
                <button class="btn btn-primary btn-sm" onclick="editor.rotate(270);     " data-toggle="tooltip" data-placement="top" title="Rotate the image anticlockwise">↪</button>
                <button class="btn btn-primary btn-sm" onclick="editor.rotate(90);      " data-toggle="tooltip" data-placement="top" title="Rotate the image clockwise">↩</button>
            `;
        else
            html += /*html*/ `
                <button class="btn btn-primary btn-sm" onclick="editor.move('left');    " disabled>⬅</button>
                <button class="btn btn-primary btn-sm" onclick="editor.move('right');   " disabled>➡</button>
                <button class="btn btn-primary btn-sm" onclick="editor.move('up');      " disabled>⬆</button>
                <button class="btn btn-primary btn-sm" onclick="editor.move('down');    " disabled>⬇</button>
                <button class="btn btn-primary btn-sm" onclick="editor.rotate(270);     " disabled>↪</button>
                <button class="btn btn-primary btn-sm" onclick="editor.rotate(90);      " disabled>↩</button>
            `;
        html += /*html*/ `
            </div>
            <br><br>
            <div class="btn-group">
                <button class="btn btn-primary btn-sm" onclick="editor.drawPlus();      " data-toggle="tooltip" data-placement="top" title="Draw a plus sign">➕</button>
                <button class="btn btn-primary btn-sm" onclick="editor.drawCross();     " data-toggle="tooltip" data-placement="top" title="Draw a diagonal cross">❌</button>
                <button class="btn btn-primary btn-sm" onclick="editor.drawBorder();    " data-toggle="tooltip" data-placement="top" title="Draw a border">🔲</button>
        `;
        if (this.matrixWidth === this.matrixHeight)
            html += /*html*/ `
                <button class="btn btn-primary btn-sm" onclick="editor.drawCircle();    " data-toggle="tooltip" data-placement="top" title="Draw a circle outline">⭕</button>
                <button class="btn btn-primary btn-sm" onclick="editor.drawCircle(true);" data-toggle="tooltip" data-placement="top" title="Draw a circle">🔴</button>
                <button class="btn btn-primary btn-sm" onclick="editor.drawNoEntry();   " data-toggle="tooltip" data-placement="top" title="Draw a no entry sign">🚫</button>
            `;
        else
            html += /*html*/ `
                <button class="btn btn-primary btn-sm" onclick="editor.drawCircle();    " disabled>⭕</button>
                <button class="btn btn-primary btn-sm" onclick="editor.drawCircle(true);" disabled>🔴</button>
                <button class="btn btn-primary btn-sm" onclick="editor.drawNoEntry();   " disabled>🚫</button>
            `;
        html += /*html*/ `
            </div>
            <br><br>
            <button class="btn btn-primary btn-sm" onclick="editor.frames.push(editor.binaryString); editor.updateIcons(); editor.clearScreen();" data-toggle="tooltip" data-placement="top" title="Save this frame and make a new one">➕</button>
            <button class="btn btn-primary btn-sm" onclick="editor.playback();" data-toggle="tooltip" data-placement="top" title="Play the animation">Play</button>
            <label for="colour">FPS:</label>
            <input id="fpsInput" type="number" name="fps" min="1" max="120" value="1">
        `;
        const controlsDiv = document.getElementById("controls") as HTMLDivElement;
        controlsDiv.innerHTML = html;
    }
    
    public updateLEDs(): void {
        this.LEDs.forEach((button, i) => {
            const offColour = "rgba(0, 0, 0, 0.1)";
            let colour = offColour;
            const bits = this.LEDBitPatterns[i];
            const num = parseInt(bits, 2);
            colour = num ? "rgb(255, 0, 0)" : offColour;
            button.style.background = colour;
        });
    }
}

function createAnimationEditor(type: 0 | 1 | 2, width: number, height: number, frames: string[] = []): AnimationEditor {
    return new [MonochromaticAnimationEditor, VariableBrightnessAnimationEditor, RGBAnimationEditor][type](width, height, frames);
}
