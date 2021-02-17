const animation = createAnimation(TYPE, WIDTH, HEIGHT);

const allButtons = [];
const singleLEDBitLength = TYPE === 0 ? 1 : TYPE === 1 ? 8 : 24;
const bitCount = WIDTH * HEIGHT * singleLEDBitLength;
let binaryString = "0".repeat(bitCount);

const defaultOnColour = TYPE === 0 ? "1" : TYPE === 1 ? "255" : "#ff0000";
const offColour = (TYPE === 0
    ? "0"
    : TYPE === 1
    ? "0".repeat(8)
    : "0".repeat(24)
).padStart(singleLEDBitLength, "0");
const getOnColour = () =>
    (TYPE === 0
        ? "1"
        : TYPE === 1
        ? parseInt(document.getElementById("colourInput").value).toString(2)
        : parseInt(
              document.getElementById("colourInput").value.slice(1),
              16
          ).toString(2)
    ).padStart(singleLEDBitLength, "0");

const map = (x, inMin, inMax, outMin, outMax) =>
    ((x - inMin) * (outMax - outMin)) / (inMax - inMin) + outMin;
const getBitsArray = () =>
    TYPE === 0
        ? binaryString.split("")
        : TYPE === 1
        ? binaryString.match(/.{1,8}/g)
        : binaryString.match(/.{1,24}/g);
const updateLEDs = () => {
    allButtons.forEach((button, i) => {
        const offColour = "rgba(0, 0, 0, 0.1)";
        let colour = offColour;
        if (TYPE === 0) {
            const bits = binaryString[i];
            const num = parseInt(bits, 2);
            colour = num ? "rgb(255, 0, 0)" : offColour;
        } else if (TYPE === 1) {
            const bits = binaryString.match(/.{1,8}/g)[i];
            const num = parseInt(bits, 2);
            colour = num
                ? `rgba(255, 0, 0, ${map(num, 0, 255, 0, 1)})`
                : offColour;
        } else if (TYPE === 2) {
            const bits = binaryString.match(/.{1,24}/g)[i];
            const num = parseInt(bits, 2);
            const bitsArr = bits.match(/.{1,8}/g);
            const numArr = bitsArr.map((x) => parseInt(x, 2));
            colour = num ? "rgb(" + numArr.join(", ") + ")" : offColour;
        }
        button.style.background = colour;
    });
};
const updateControls = () => {
    document.write(/*html*/ `
        <br>
        <div class="btn-group">
            <button class="btn btn-primary btn-sm" onclick="clearScreen();    updateLEDs();" data-toggle="tooltip" data-placement="top" title="Turn off all LEDs">Clear</button>
            <button class="btn btn-primary btn-sm" onclick="fillScreen();     updateLEDs();" data-toggle="tooltip" data-placement="top" title="Turn on all LEDs">Fill</button>
            <button class="btn btn-primary btn-sm" onclick="invertScreen();   updateLEDs();" data-toggle="tooltip" data-placement="top" title="Invert all LEDs">Invert</button>
            <button class="btn btn-primary btn-sm" onclick="toggleShift();"                  data-toggle="tooltip" data-placement="top" title="Hold shift to draw straight lines" id="shiftBtn" >Shift</button>
            <button class="btn btn-primary btn-sm" onclick="flip(false);      updateLEDs();" data-toggle="tooltip" data-placement="top" title="Flip the image vertically">↕</button>
            <button class="btn btn-primary btn-sm" onclick="flip(true);       updateLEDs();" data-toggle="tooltip" data-placement="top" title="Flip the image horizontally">↔</button>
    `);
    if (WIDTH === HEIGHT)
        document.write(/*html*/ `
            <button class="btn btn-primary btn-sm" onclick="move('left');     updateLEDs();" data-toggle="tooltip" data-placement="top" title="Move the image to the left">⬅</button>
            <button class="btn btn-primary btn-sm" onclick="move('right');    updateLEDs();" data-toggle="tooltip" data-placement="top" title="Move the image to the right">➡</button>
            <button class="btn btn-primary btn-sm" onclick="move('up');       updateLEDs();" data-toggle="tooltip" data-placement="top" title="Move the image up">⬆</button>
            <button class="btn btn-primary btn-sm" onclick="move('down');     updateLEDs();" data-toggle="tooltip" data-placement="top" title="Move the image down">⬇</button>
            <button class="btn btn-primary btn-sm" onclick="rotate(270);      updateLEDs();" data-toggle="tooltip" data-placement="top" title="Rotate the image anticlockwise">↪</button>
            <button class="btn btn-primary btn-sm" onclick="rotate(90);       updateLEDs();" data-toggle="tooltip" data-placement="top" title="Rotate the image clockwise">↩</button>
        `);
    else
        document.write(/*html*/ `
            <button class="btn btn-primary btn-sm" onclick="move('left');     updateLEDs();" disabled>⬅</button>
            <button class="btn btn-primary btn-sm" onclick="move('right');    updateLEDs();" disabled>➡</button>
            <button class="btn btn-primary btn-sm" onclick="move('up');       updateLEDs();" disabled>⬆</button>
            <button class="btn btn-primary btn-sm" onclick="move('down');     updateLEDs();" disabled>⬇</button>
            <button class="btn btn-primary btn-sm" onclick="rotate(270);      updateLEDs();" disabled>↪</button>
            <button class="btn btn-primary btn-sm" onclick="rotate(90);       updateLEDs();" disabled>↩</button>
        `);
    document.write(/*html*/ `
        </div>
        <br><br>
        <div class="btn-group">
            <button class="btn btn-primary btn-sm" onclick="drawPlus();       updateLEDs();" data-toggle="tooltip" data-placement="top" title="Draw a plus sign">➕</button>
            <button class="btn btn-primary btn-sm" onclick="drawCross();      updateLEDs();" data-toggle="tooltip" data-placement="top" title="Draw a diagonal cross">❌</button>
            <button class="btn btn-primary btn-sm" onclick="drawBorder();     updateLEDs();" data-toggle="tooltip" data-placement="top" title="Draw a border">🔲</button>
    `);
    if (WIDTH === HEIGHT)
        document.write(/*html*/ `
            <button class="btn btn-primary btn-sm" onclick="drawCircle();     updateLEDs();" data-toggle="tooltip" data-placement="top" title="Draw a circle outline">⭕</button>
            <button class="btn btn-primary btn-sm" onclick="drawCircle(true); updateLEDs();" data-toggle="tooltip" data-placement="top" title="Draw a circle">🔴</button>
            <button class="btn btn-primary btn-sm" onclick="drawNoEntry();    updateLEDs();" data-toggle="tooltip" data-placement="top" title="Draw a no entry sign">🚫</button>
        `);
    else
        document.write(/*html*/ `
            <button class="btn btn-primary btn-sm" onclick="drawCircle();     updateLEDs();" disabled>⭕</button>
            <button class="btn btn-primary btn-sm" onclick="drawCircle(true); updateLEDs();" disabled>🔴</button>
            <button class="btn btn-primary btn-sm" onclick="drawNoEntry();    updateLEDs();" disabled>🚫</button>
        `);
    document.write(/*html*/ `
        </div>
        <br><br>
    `);
    if (TYPE === 1)
        document.write(/*html*/ `
        <label for="colour">Brightness:</label>
        <input id="colourInput" type="number" name="colour" min="0" max="255" value="${defaultOnColour}">
    `);
    else if (TYPE === 2)
        document.write(/*html*/ `
        <label for="colour">Colour:</label><input id="colourInput" type="color" name="colour" value="${defaultOnColour}">
    `);
    document.write(/*html*/ `
        <button class="btn btn-primary btn-sm" onclick="animation.frames.push(binaryString); updateIcons(); clearScreen(); updateLEDs();" data-toggle="tooltip" data-placement="top" title="Save this frame and make a new one">➕</button>
        <button class="btn btn-primary btn-sm" onclick="playback();                                                                     " data-toggle="tooltip" data-placement="top" title="Play the animaiton">Play</button>
    `);
};
const updateIcons = () => {
    // Update frame icons.
    const frameIconsDiv = document.getElementById("frameIcons");
    frameIconsDiv.innerHTML = animation
        .makeFrameIcons()
        .map(
            (icon, i) => /*html*/ `
            <div class="icon">
                <img src="${icon.image}">
                <div class="buttons">
                    <button class="btn btn-secondary btn-sm" data-toggle="tooltip" data-placement="top" title="Copy this frame to the editor" onclick="binaryString = '${icon.binary}';updateLEDs();">Copy</button><br>
                    <button class="btn btn-secondary btn-sm" data-toggle="tooltip" data-placement="top" title="Delete this frame" onclick="animation.frames = animation.frames.filter((_, i) => i !== ${i});updateIcons();">Delete</button>
                </div>
                <p>${icon.binary}</p>
            </div>
        `
        )
        .join("");
};

// Controls.
let shift = false;
let shiftButtons = [];
const ledClicked = (index) => {
    const bitsArr = getBitsArray();
    const onColour = getOnColour();
    if (shift) {
        if (shiftButtons.length === 0) {
            shiftButtons.push(index);
            bitsArr[index] = parseInt(bitsArr[index], 2) ? offColour : onColour;
        } else if (shiftButtons.length === 1) {
            shiftButtons.push(index);
            const coords = bresenhamLine(
                shiftButtons[0] % WIDTH,
                Math.floor(shiftButtons[0] / WIDTH),
                shiftButtons[1] % WIDTH,
                Math.floor(shiftButtons[1] / WIDTH)
            );
            coords.forEach((button) => {
                const buttonCoords = button.split(",").map((x) => parseInt(x));
                const i = buttonCoords[0] + buttonCoords[1] * WIDTH;
                bitsArr[i] = onColour;
            });
            shiftButtons = [];
        }
    } else bitsArr[index] = parseInt(bitsArr[index], 2) ? offColour : onColour;
    binaryString = bitsArr.join("");
};
const clearScreen = () => (binaryString = "0".repeat(bitCount));
const fillScreen = () => (binaryString = getOnColour().repeat(WIDTH * HEIGHT));
const invertScreen = () =>
    (binaryString = binaryString
        .split("")
        .map((x) => (parseInt(x) ? "0" : "1"))
        .join(""));
const createMatrix = () => {
    const bitsArr = getBitsArray();
    const matrixData = Matrix.create0Array(WIDTH, HEIGHT);
    bitsArr.forEach(
        (bit, i) =>
            (matrixData[Math.floor(i / WIDTH)][i % WIDTH] = parseInt(bit, 2))
    );
    return new Matrix(matrixData);
};
const convertMatrixToString = (M) =>
    M.value
        .map((row) =>
            row
                .map((x) => x.toString(2).padStart(singleLEDBitLength, "0"))
                .join("")
        )
        .join("");
const rotate = (a) =>
    (binaryString = convertMatrixToString(createMatrix().rotate(a)));
const flip = (reverseRows) =>
    (binaryString = convertMatrixToString(
        reverseRows
            ? createMatrix().reversedRows
            : createMatrix().reversedColumns
    ));
const move = (direction) =>
    (binaryString = convertMatrixToString(createMatrix().translate(direction)));

// Keyboard.
window.onkeydown = (e) => {
    if (e.code === "ShiftLeft") shift = true;
    displayShift();
};
window.onkeyup = (e) => {
    if (e.code === "ShiftLeft") {
        shift = false;
        shiftButtons = [];
    }
    displayShift();
};
const toggleShift = () => {
    shift = !shift;
    displayShift();
};
const displayShift = () =>
    (document.getElementById("shiftBtn").className = shift
        ? "btn btn-primary active btn-sm"
        : "btn btn-primary btn-sm");

// Graphics - Key Algorithms.
const bresenhamLine = (x0, y0, x1, y1) => {
    // https://en.wikipedia.org/wiki/Bresenham%27s_line_algorithm#Line_equation
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
            coords.push(`${x},${y}`);
            if (D > 0) {
                y += yi;
                D += 2 * (dy - dx);
            } else D += 2 * dy;
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
            coords.push(`${x},${y}`);
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
};
// Graphics - Any Size.
const drawPlus = () => {
    const bitsArr = getBitsArray();
    const onColour = getOnColour();
    for (let y = 0; y < HEIGHT; ++y) {
        for (let x = 0; x < WIDTH; ++x) {
            const validX =
                x === Math.floor(WIDTH / 2) ||
                x === Math.floor((WIDTH - 1) / 2);
            const validY =
                y === Math.floor(HEIGHT / 2) ||
                y === Math.floor((HEIGHT - 1) / 2);
            if (validX || validY) bitsArr[WIDTH * y + x] = onColour;
        }
    }
    binaryString = bitsArr.join("");
};
const drawCross = () => {
    const bitsArr = getBitsArray();
    const onColour = getOnColour();
    const coords = bresenhamLine(0, 0, WIDTH - 1, HEIGHT - 1).concat(
        bresenhamLine(WIDTH - 1, 0, 0, HEIGHT - 1)
    );
    for (let y = 0; y < HEIGHT; ++y)
        for (let x = 0; x < WIDTH; ++x)
            if (coords.includes(x.toString() + "," + y.toString()))
                bitsArr[WIDTH * y + x] = onColour;
    binaryString = bitsArr.join("");
};
const drawBorder = () => {
    const bitsArr = getBitsArray();
    const onColour = getOnColour();
    for (let y = 0; y < HEIGHT; ++y) {
        for (let x = 0; x < WIDTH; ++x) {
            const validX = x === 0 || x === WIDTH - 1;
            const validY = y === 0 || y === HEIGHT - 1;
            if (validX || validY) bitsArr[WIDTH * y + x] = onColour;
        }
    }
    binaryString = bitsArr.join("");
};
// Graphics - Square only.
const drawCircle = (full = false) => {
    const bitsArr = getBitsArray();
    const onColour = getOnColour();
    const size = Math.min(WIDTH, HEIGHT);
    const radius = (size % 2 === 0 ? size : size - 1) / 2;
    for (let y = 0; y < HEIGHT; ++y) {
        for (let x = 0; x < WIDTH; ++x) {
            // Pythagoras.
            const xDistance = size % 2 === 0 ? radius - x - 0.5 : radius - x;
            const yDistance = size % 2 === 0 ? radius - y - 0.5 : radius - y;
            const distance = Math.round(
                Math.sqrt(xDistance ** 2 + yDistance ** 2)
            );
            if ((full && distance <= radius) || distance === radius)
                bitsArr[WIDTH * y + x] = onColour;
        }
    }
    binaryString = bitsArr.join("");
};
const drawNoEntry = () => {
    const bitsArr = getBitsArray();
    const onColour = getOnColour();
    const size = Math.min(WIDTH, HEIGHT);
    const radius = (size % 2 === 0 ? size : size - 1) / 2;
    for (let y = 0; y < HEIGHT; ++y) {
        for (let x = 0; x < WIDTH; ++x) {
            // Pythagoras.
            const xDistance = size % 2 === 0 ? radius - x - 0.5 : radius - x;
            const yDistance = size % 2 === 0 ? radius - y - 0.5 : radius - y;
            const distance = Math.round(
                Math.sqrt(xDistance ** 2 + yDistance ** 2)
            );
            if ((x === y && distance <= radius) || distance === radius)
                bitsArr[WIDTH * y + x] = onColour;
        }
    }
    binaryString = bitsArr.join("");
};
// Playback
let playbackTimeout;
const playback = () => {
    clearTimeout(playbackTimeout);
    const playbackDiv = document.getElementById("playback");
    const frames = animation.makeFrameIcons();
    const fps = 1;
    let i = 0;
    const renderFrame = () => {
        const currentFrame = frames[i];
        playbackDiv.innerHTML = `<img src=${currentFrame.image}>`;
        i++;
        playbackTimeout = setTimeout(renderFrame, 1000 / fps);
    };
    renderFrame();
};
