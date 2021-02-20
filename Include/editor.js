const editor = createAnimationEditor(TYPE, WIDTH, HEIGHT);
// Keyboard.
window.onkeydown = (e) => e.code === "ShiftLeft" ? editor.shiftIsDown = true : void 0;
window.onkeyup = (e) => e.code === "ShiftLeft" ? editor.shiftIsDown = false : void 0;
// Playback
let playbackTimeout;
const playback = () => {
    clearTimeout(playbackTimeout);
    const playbackDiv = document.getElementById("playback");
    const frames = editor.makeFrameIcons();
    const fps = 1;
    let i = 0;
    const renderFrame = () => {
        const currentFrame = frames[i++];
        playbackDiv.innerHTML = `<img src=${currentFrame.image}>`;
        playbackTimeout = setTimeout(renderFrame, 1000 / fps);
    };
    renderFrame();
};
