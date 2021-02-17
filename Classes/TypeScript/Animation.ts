const pixelSize = 100;

abstract class AnimationBase {
    public frames: string[] = [];

    constructor(
        protected readonly matrixWidth: number,
        protected readonly matrixHeight: number
    ) {}

    public get framesAsInts(): number[] {
        const intFrames: number[] = [];
        this.frames.forEach((frame: string) => {
            const subStrings: string[] = frame.match(/.{1,32}/g) as string[];
            intFrames.push(...subStrings.map((str) => parseInt(str, 2)));
        });
        return intFrames;
    }

    public abstract makeFrameIcons(): Array<{ image: string; binary: string }>;
    public abstract convertToArduino(): string;
    public abstract convertToMicroPython(): string;
    public abstract convertToTypeScript(): string;
    public abstract createHexFile(): string;
}

class RGBAnimation extends AnimationBase {
    public constructor(width: number, height: number) {
        super(width, height);
    }

    public get framesAsInts(): number[] {
        const intFrames: number[] = [];
        this.frames.forEach((frame: string) => {
            const subStrings: string[] = frame.match(/.{1,24}/g) as string[];
            intFrames.push(...subStrings.map((str) => parseInt(str, 2)));
        });
        return intFrames;
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

    public convertToArduino(): string {
        return "";
    }
    public convertToMicroPython(): string {
        return "";
    }
    public convertToTypeScript(): string {
        return "";
    }
    public createHexFile(): string {
        return "";
    }
}

class VariableBrightnessAnimation extends AnimationBase {
    public constructor(width: number, height: number) {
        super(width, height);
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

    public convertToArduino(): string {
        return "";
    }
    public convertToMicroPython(): string {
        return "";
    }
    public convertToTypeScript(): string {
        return "";
    }
    public createHexFile(): string {
        return "";
    }
}

class MonochromaticAnimation extends AnimationBase {
    public constructor(width: number, height: number) {
        super(width, height);
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

    public convertToArduino(): string {
        return "";
    }
    public convertToMicroPython(): string {
        return "";
    }
    public convertToTypeScript(): string {
        return "";
    }
    public createHexFile(): string {
        return "";
    }
}

function createAnimation(
    type: 0 | 1 | 2,
    width: number,
    height: number
): AnimationBase | undefined {
    if (type === 0) return new MonochromaticAnimation(width, height);
    if (type === 1) return new VariableBrightnessAnimation(width, height);
    if (type === 2) return new RGBAnimation(width, height);
    return;
}
