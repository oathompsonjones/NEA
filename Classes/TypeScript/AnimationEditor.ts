const pixelSize = 100;
const map = (x: number, inMin: number, inMax: number, outMin: number, outMax: number): number =>
    ((x - inMin) * (outMax - outMin)) / (inMax - inMin) + outMin;

abstract class AnimationEditor {
    private _binaryString: string = "";
    private _shiftIsDown: boolean = false;
    public bitCount: number;
    public cssDefaultOffColour: string = "rgba(255, 255, 255, 0.1)";
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
    public get currentLEDBitPatterns(): string[] {
        return this.binaryString.match(new RegExp(`.{1,${this.LEDBitLength}}`, "g")) as string[];
    };
    public get matrix(): Matrix {
        const matrixData = Matrix.create0Array(this.matrixWidth, this.matrixHeight);
        this.currentLEDBitPatterns.forEach((bit, i) => matrixData[Math.floor(i / this.matrixWidth)][i % this.matrixHeight] = parseInt(bit, 2));
        return new Matrix(matrixData);
    }
    public get playbackFPS(): number {
        const fps = document.getElementById("fps") as HTMLInputElement;
        return parseInt(fps.value, 10);
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

    public abstract get onColour(): string;

    protected constructor(protected matrixWidth: number, protected matrixHeight: number, data: string[], public LEDBitLength: number) {
        this.bitCount = this.matrixWidth * this.matrixHeight * this.LEDBitLength;
        this.defaultOffColour = "0".repeat(this.LEDBitLength);
        
        this.frames = data;
        
        this.clearScreen();
    }

    public abstract makeFrameIcons(): Array<{ image: string; binary: string }>;
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

    public displayShiftState(): void {
        const shiftButton = document.getElementById("shiftBtn") as HTMLButtonElement;
        shiftButton.className = this.shiftIsDown ? "btn btn-dark active btn-sm" : "btn btn-dark btn-sm";
    }

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

    public drawCircle(full = false): void {
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

    public new(): void {
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

    public rotate(angle: 90 | 180 | 270): void {
        this.binaryString = this.convertMatrixToString(this.matrix.rotate(angle));
    }

    public save(): void {
        // @ts-ignore
        const width = SESSION.matrix.width;
        // @ts-ignore
        const height = SESSION.matrix.height;
        // @ts-ignore
        const type = SESSION.matrix.type;
        // @ts-ignore
        const id = SESSION.matrix.id;
        // @ts-ignore
        const name = SESSION.matrix.name;
        // @ts-ignore
        const data = SESSION.editor.data;
        // @ts-ignore
        const username = SESSION.username;
        
        // @ts-ignore
        $.post("Utils/Forms/saveAnimation", { width, height, type, id, name, data, username }, () => document.getElementById("saveAlert").innerHTML = `
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Animation Saved!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `);
    }

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
        if (this instanceof MonochromaticAnimationEditor) {
            html += /*html*/`
                <div class="form-floating bg-dark text-white border-dark">
                    <input type="number" class="form-control bg-dark text-white border-dark" id="fps" name="fps" placeholder="FPS" min=1 max=60 value=1>
                    <label for="fps">FPS</label>
                </div>
            `;
        } else if (this instanceof VariableBrightnessAnimationEditor) {
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
        } else if (this instanceof RGBAnimationEditor) {
            html += /*html*/`
                <div class="form-floating bg-dark text-white border-dark">
                    <input type="number" class="form-control bg-dark text-white border-dark" id="fps" name="fps" placeholder="FPS" min=1 max=60 value=1>
                    <label for="fps">FPS</label>
                </div>
                <input id="colourInput" class="form-control form-control-color bg-dark border-dark" type="color" name="colour" value="${this.defaultOnColour}">
            `;
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

    public toggleShift(): void {
        this.shiftIsDown = !this.shiftIsDown;
    }

    public updateIcons(): void {
        const form: HTMLFormElement = document.createElement("form");
        form.style.display = "none";
        form.setAttribute("method", "post");
        const data: HTMLInputElement = document.createElement("input");
        data.setAttribute("type", "text");
        data.setAttribute("name", "data");
        data.setAttribute("value", JSON.stringify(this.frames));
        form.appendChild(data);
        const fps: HTMLInputElement = document.createElement("input");
        fps.setAttribute("type", "text");
        fps.setAttribute("name", "fps");
        fps.setAttribute("value", this.playbackFPS.toString());
        form.appendChild(fps);
        const colour: HTMLInputElement = document.createElement("input");
        colour.setAttribute("type", "text");
        colour.setAttribute("name", "colour");
        colour.setAttribute("value", this.onColour);
        form.appendChild(colour);
        document.getElementsByTagName("body")[0].appendChild(form);
        form.submit();
    }
}

class RGBAnimationEditor extends AnimationEditor {
    public defaultOnColour: string = "#ff0000";

    public get onColour(): string {
        const colourInput = document.getElementById("colourInput") as HTMLInputElement;
        return parseInt(colourInput.value.slice(1), 16).toString(2).padStart(24, "0");
    };

    public constructor(width: number, height: number, data: string[]) {
        super(width, height, data, 24);
    }

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

class VariableBrightnessAnimationEditor extends AnimationEditor {
    public defaultOnColour: string = "255";

    public get onColour(): string {
        const colourInput = document.getElementById("colourInput") as HTMLInputElement;
        return parseInt(colourInput.value).toString(2).padStart(8, "0");
    };
    
    public constructor(width: number, height: number, data: string[]) {
        super(width, height, data, 8);
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

class MonochromaticAnimationEditor extends AnimationEditor {
    public defaultOnColour: string = "1";
    
    public get onColour(): string {
        return this.defaultOnColour;
    };
    
    public constructor(width: number, height: number, data: string[]) {
        super(width, height, data, 1);
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

function createAnimationEditor(
    type: 0 | 1 | 2,
    width: number,
    height: number,
    data: string[] = []
): AnimationEditor {
    return new [
        MonochromaticAnimationEditor,
        VariableBrightnessAnimationEditor,
        RGBAnimationEditor
    ][type](width, height, data);
}
