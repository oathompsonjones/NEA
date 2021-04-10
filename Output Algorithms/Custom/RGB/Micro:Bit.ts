const animation: number[][] = [...];

const plot = (x: number, y: number, r: number, g: number, b: number) => {
    // Insert your code here
};

const clear = () => {
    // Insert your code here
};

input.onButtonPressed(Button.A, () => {
    for (let i = 0; i < animation.length; ++i) {
        let bits: number[][] = [];
        for (let j = 0; j < width * height; ++j) {
            bits[j] = [];
            bits[j][0] = animation[i][j] >> 16 & 255;
            bits[j][1] = animation[i][j] >> 8 & 255;
            bits[j][2] = animation[i][j] & 255;
        }
        for (let j = 0; j < width * height; ++j) {
            const x: number = j % width;
            const y: number = Math.floor(j / width);
            plot(x, y, bits[j][0], bits[j][1], bits[j][2]);
        }
        basic.pause(1000 / fps);
        clear();
    }
});