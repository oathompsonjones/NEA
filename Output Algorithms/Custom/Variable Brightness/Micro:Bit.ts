const animation: number[][] = [...];

const plot = (x: number, y: number, value: number) => {
    // Insert your code here
};

const clear = () => {
    // Insert your code here
};

input.onButtonPressed(Button.A, () => {
    if (width * height % 4 === 0) {
        for (let i = 0; i < animation.length; ++i) {
            let bits: number[] = [];
            for (let j = 0; j < width * height / 4; ++j)
                for (let k = 0; k < 4; ++k)
                    bits.push(animation[i][j] >> (3 - k) * 8 & 255);
            for (let j = 0; j < width * height; ++j) {
                const x: number = j % width;
                const y: number = Math.floor(j / width);
                plot(x, y, bits[j]);
            }
            basic.pause(1000 / fps);
            clear();
        }
    } else {
        for (let i = 0; i < animation.length; ++i) {
            let bits: number[] = [];
            for (let j = 0; j < width * height % 4; ++j)
                bits.push(animation[i][0] >> width * height % 4 - 1 - j & 1);
            for (j = 0; j < (width * height - width * height % 4) / 4; ++j)
                for (k = 0; k < 4; ++k)
                    bits.push(animation[i][j + 1] >> (3 - k) * 8 & 255);
            for (let j = 0; j < width * height; ++j) {
                const x: number = j % width;
                const y: number = Math.floor(j / width);
                plot(x, y, bits[j]);
            }
            basic.pause(1000 / fps);
            clear();
        }
    }
});