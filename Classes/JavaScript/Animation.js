"use strict";
const pixelSize = 100;
class AnimationBase {
    matrixWidth;
    matrixHeight;
    frames = [];
    constructor(matrixWidth, matrixHeight) {
        this.matrixWidth = matrixWidth;
        this.matrixHeight = matrixHeight;
    }
    get framesAsInts() {
        const intFrames = [];
        this.frames.forEach((frame) => {
            const subStrings = frame.match(/.{1,32}/g);
            intFrames.push(...subStrings.map((str) => parseInt(str, 2)));
        });
        return intFrames;
    }
}
class RGBAnimation extends AnimationBase {
    constructor(width, height) {
        super(width, height);
    }
    get framesAsInts() {
        const intFrames = [];
        this.frames.forEach((frame) => {
            const subStrings = frame.match(/.{1,24}/g);
            intFrames.push(...subStrings.map((str) => parseInt(str, 2)));
        });
        return intFrames;
    }
    makeFrameIcons() {
        return this.frames.map((frame) => {
            const canvas = document.createElement("canvas");
            canvas.width = this.matrixWidth * pixelSize;
            canvas.height = this.matrixHeight * pixelSize;
            const context = canvas.getContext("2d");
            if (context === null)
                throw Error();
            frame.match(/.{1,24}/g).map((x) => x.match(/.{1,8}/g).map((y) => parseInt(y, 2))).forEach((bytes, i) => {
                context.fillStyle = bytes.some((byte) => byte > 0) ? `rgb(${bytes[0]}, ${bytes[1]}, ${bytes[2]})` : "rgba(0, 0, 0, 0.1)";
                const x = i % this.matrixWidth * pixelSize;
                const y = Math.floor(i / this.matrixWidth) * pixelSize;
                context.fillRect(x, y, pixelSize, pixelSize);
            });
            return {
                binary: frame,
                image: canvas.toDataURL()
            };
        });
    }
    convertToArduino() { return ""; }
    convertToMicroPython() { return ""; }
    convertToTypeScript() { return ""; }
    createHexFile() { return ""; }
}
class VariableBrightnessAnimation extends AnimationBase {
    constructor(width, height) {
        super(width, height);
    }
    makeFrameIcons() {
        const map = (x, inMin, inMax, outMin, outMax) => (x - inMin) * (outMax - outMin) / (inMax - inMin) + outMin;
        return this.frames.map((frame) => {
            const canvas = document.createElement("canvas");
            canvas.width = this.matrixWidth * pixelSize;
            canvas.height = this.matrixHeight * pixelSize;
            const context = canvas.getContext("2d");
            if (context === null)
                throw Error();
            frame.match(/.{1,8}/g).map((x) => parseInt(x, 2)).forEach((byte, i) => {
                context.fillStyle = byte > 0 ? `rgba(255, 0, 0, ${map(byte, 0, 255, 0, 1)})` : "rgba(0, 0, 0, 0.1)";
                const x = i % this.matrixWidth * pixelSize;
                const y = Math.floor(i / this.matrixWidth) * pixelSize;
                context.fillRect(x, y, pixelSize, pixelSize);
            });
            return {
                binary: frame,
                image: canvas.toDataURL()
            };
        });
    }
    convertToArduino() { return ""; }
    convertToMicroPython() { return ""; }
    convertToTypeScript() { return ""; }
    createHexFile() { return ""; }
}
class MonochromaticAnimation extends AnimationBase {
    constructor(width, height) {
        super(width, height);
    }
    makeFrameIcons() {
        return this.frames.map((frame) => {
            const canvas = document.createElement("canvas");
            canvas.width = this.matrixWidth * pixelSize;
            canvas.height = this.matrixHeight * pixelSize;
            const context = canvas.getContext("2d");
            if (context === null)
                throw Error();
            frame.split("").map((x) => parseInt(x, 2)).forEach((bit, i) => {
                context.fillStyle = bit === 1 ? "red" : "rgba(0, 0, 0, 0.1)";
                const x = i % this.matrixWidth * pixelSize;
                const y = Math.floor(i / this.matrixWidth) * pixelSize;
                context.fillRect(x, y, pixelSize, pixelSize);
            });
            return {
                binary: frame,
                image: canvas.toDataURL()
            };
        });
    }
    convertToArduino() { return ""; }
    convertToMicroPython() { return ""; }
    convertToTypeScript() { return ""; }
    createHexFile() { return ""; }
}
function createAnimation(type, width, height) {
    if (type === 0)
        return new MonochromaticAnimation(width, height);
    if (type === 1)
        return new VariableBrightnessAnimation(width, height);
    if (type === 2)
        return new RGBAnimation(width, height);
    return;
}
