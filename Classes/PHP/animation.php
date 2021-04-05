<?php
class Animation
{
    private $id;
    public function __construct($id)
    {
        $this->id = $id;
    }

    public function __get($name)
    {
        $id = $this->id;
        $db = $_SESSION["database"];
        switch ($name) {
            case "id":
                return $id;
            case "name":
                return $db->select("Name", "Animation", "AnimationID = '$id'")[0][0];
            case "width":
                return $db->select("Width", "Animation", "AnimationID = '$id'")[0][0];
            case "height":
                return $db->select("Height", "Animation", "AnimationID = '$id'")[0][0];
            case "type":
                return $db->select("Type", "Animation", "AnimationID = '$id'")[0][0];
            case "typeString":
                return $this->type == 0 ? "Monochromatic" : ($this->type == 1 ? "Variable Brightness" : "RGB");
            case "frames":
                $frames = $db->select("FrameID", "Frame", "AnimationID = '$id'", "FramePosition ASC");
                if (is_null($frames)) return NULL;
                return array_map("mapToFrameObject", array_map("mapToFirstItem", $frames));
            case "user":
                return new User($db->select("Username", "Animation", "AnimationID = '$id'")[0][0]);
            default:
                throw new Exception("Property $name does not exist on type Animation.");
        }
    }

    public function render($showImages = true)
    {
        $db = $_SESSION["database"];
        $id = $this->id;
        $name = $this->name;
        $icons = array_map("mapBase64ToImageSrc", $this->generateFrameIcons());
        $jsonIcons = json_encode($icons);
        $firstIcon = $icons[0];
        $user = $this->user;
        $posts = $user->posts;
        $postedAnimationIDs = array_map("mapToAnimationID", $posts);
        $postExists = in_array($id, $postedAnimationIDs);
        $shareButton = <<<HTML
            <script>
                const unShare_$id = () => {
                    const animationID = "$id";
                    $.post("Utils/Forms/unShareAnimation.php", { animationID }, () => document.getElementById("$id-shareButton").innerHTML = `<button class="btn btn-dark btn-sm" type="button" onclick="share_$id();" style="width: 100%;">Share</button>`);
                };
                const share_$id = () => {
                    const animationID = "$id";
                    const username = "$user->username";
                    const fps = document.getElementById("$id-inputFPS").value;
                    $.post("Utils/Forms/shareAnimation.php", { animationID, username, fps }, () => document.getElementById("$id-shareButton").innerHTML = `<button class="btn btn-dark btn-sm" type="button" onclick="unShare_$id();" style="width: 100%;">Unshare</button>`);
                };
                const delete_$id = () => {
                    document.getElementById("$id-deleteButton").innerHTML = `<button class="btn btn-danger btn-sm" type="button" onclick="deleteConfirm_$id();" style="width: 100%;">Confirm</button>`;
                };
                const deleteConfirm_$id = () => {
                    const animationID = "$id";
                    $.post("Utils/Forms/deleteAnimation.php", { animationID }, () => document.getElementById("$id-container").style.display = "none");
                };
            </script>
        HTML;
        $shareButton = $shareButton . ($postExists
            ? <<<HTML
                <button class="btn btn-dark btn-sm" type="button" onclick="unShare_$id();" style="width: 100%;">Unshare</button>
            HTML
            : <<<HTML
                <button class="btn btn-dark btn-sm" type="button" onclick="share_$id();" style="width: 100%;">Share</button>
            HTML);
        $fps = $postExists ? (new Post($db->select("PostID", "Post", "AnimationID = '$id'")[0][0]))->fps : 1;
        return <<<HTML
            <div class="col" id="$id-container">
                <script>
                    const _$id = (frames) => {
                        const img = document.getElementById("$id-icon");
                        const div = document.getElementById("$id-div");
                        const fps = document.getElementById("$id-inputFPS")?.value || 1;
                        let i = 0;
                        div.className = "icon";
                        const interval = setInterval(() => img.src = frames[i++], 1000 / fps);
                        setTimeout(() => {
                            clearInterval(interval);
                            img.src = frames[0];
                            div.className = "icon firstIcon";
                        }, 1000 * (frames.length + 1) / fps);
                    };
                </script>
                <div id="$id-card" class="card text-white bg-dark animation">
        HTML . ($showImages ? <<<HTML
            <div id="$id-div" class="icon firstIcon">
                <img src="$firstIcon" class="card-img-top" id="$id-icon">
                <div id="$id-buttons" class="buttons">
                    <button class="btn btn-secondary btn-lg" data-toggle="tooltip" data-placement="top" title="Play the animation" onclick='_$id($jsonIcons);'>▶</button>
                </div>
            </div>
        HTML : "") . <<<HTML
                    <div class="card-body">
                        <h5 class="card-title">$name</h5>
                        <div style="display: flex;">
                            <div id="$id-deleteButton" style="display: flex; width: 50%;">
                                <button class="btn btn-danger btn-sm" type="button" onclick="delete_$id();" style="width: 100%;">Delete</button>
                            </div>
                            <form method="post" action="editor" style="width: 50%;">
                                <input style="display: none;" name="preMade" type="text" value="$id">
                                <button class="btn btn-dark btn-sm" type="submit" style="width: 100%;">Edit</button>
                            </form>
                        </div>
                        <div style="display: flex; width: 100%;">
                            <div id="$id-shareButton" style="display: flex; width: 50%;">
                                $shareButton
                            </div>
                            <div class="form-floating" style="width: 50%;">
                                <input type="number" class="form-control bg-dark text-light border-dark" id="$id-inputFPS" name="fps" placeholder="FPS" min=1 max=60 value=$fps required>
                                <label for="$id-inputFPS" class="form-label">FPS</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        HTML;
    }

    public function delete()
    {
        $db = $_SESSION["database"];
        $db->delete("Animation", "AnimationID = '$this->id'");
        for ($i = 0; $i < count($this->frames); ++$i) $this->frames[$i]->delete();
        $posts = $db->select("PostID", "Post", "AnimationID = '$this->id'");
        for ($i = 0; $i < count($posts); ++$i) (new Post($posts[$i]))->delete();
        $db->delete("AssignmentWork", "AnimationID = '$this->id'");
    }

    public function share($username, $fps)
    {
        $db = $_SESSION["database"];
        $timestamp = time();
        $id = md5($username . $timestamp);
        $db->insert("Post", "PostID, Username, AnimationID, CreatedAt, FPS", "'$id', '$username', '$this->id', '$timestamp', $fps");
    }

    public function getFramesAs32BitIntegersJSON()
    {
        function frameToBinaryArray($value)
        {
            $num = strlen($value->binary) + 32 - strlen($value->binary) % 32;
            $binary = str_pad($value->binary, $num, "0", STR_PAD_LEFT);
            $smallerBinaries = [];
            preg_match_all("/.{1,32}/", $binary, $smallerBinaries);
            return array_map("mapToJsonBinary", $smallerBinaries[0]);
        }
        $frames = array_map("frameToBinaryArray", $this->frames);
        return str_replace('"', "", json_encode($frames, JSON_PRETTY_PRINT));
    }

    public function generateFrameIcons()
    {
        $frames = $this->frames;
        $ledWidth = 1024 / $this->width;
        $ledHeight = 1024 / $this->height;
        $binary = array_map("mapToBinary", $frames);
        $images = [];
        for ($i = 0; $i < count($binary); ++$i) {
            $image = imagecreatetruecolor(1024, 1024);
            $bgColour = imagecolorallocatealpha($image, 255, 255, 255, 10);
            imagefill($image, 0, 0, $bgColour);
            $leds = [];
            switch ($this->type) {
                case 0:
                    preg_match_all("/.{1,1}/", $binary[$i], $leds);
                    $leds = array_map("mapBinaryToIntegers", $leds[0]);
                    for ($j = 0; $j < count($leds); ++$j) {
                        $x = $j % $this->width;
                        $y = floor($j / $this->width);
                        $ledColour = imagecolorallocate($image, $leds[$j] * 255, 0, 0);
                        imagefilledrectangle($image, $x * $ledWidth, $y * $ledHeight, ($x + 1) * $ledWidth, ($y + 1) * $ledHeight, $ledColour);
                    }
                    break;
                case 1:
                    preg_match_all("/.{1,8}/", $binary[$i], $leds);
                    $leds = array_map("mapBinaryToIntegers", $leds[0]);
                    for ($j = 0; $j < count($leds); ++$j) {
                        $x = $j % $this->width;
                        $y = floor($j / $this->width);
                        $ledColour = imagecolorallocate($image, $leds[$j], 0, 0);
                        imagefilledrectangle($image, $x * $ledWidth, $y * $ledHeight, ($x + 1) * $ledWidth, ($y + 1) * $ledHeight, $ledColour);
                    }
                    break;
                case 2:
                    preg_match_all("/.{1,24}/", $binary[$i], $leds);
                    $leds = $leds[0];
                    for ($j = 0; $j < count($leds); ++$j) {
                        $rgb = [];
                        preg_match_all("/.{1,8}/", $leds[$j], $rgb);
                        $rgb = array_map("mapBinaryToIntegers", $rgb[0]);
                        $x = $j % $this->width;
                        $y = floor($j / $this->width);
                        $ledColour = imagecolorallocate($image, $rgb[0], $rgb[1], $rgb[2]);
                        imagefilledrectangle($image, $x * $ledWidth, $y * $ledHeight, ($x + 1) * $ledWidth, ($y + 1) * $ledHeight, $ledColour);
                    }
                    break;
            }
            ob_start();
            imagepng($image);
            $contents = ob_get_contents();
            ob_end_clean();
            $images[$i] = base64_encode($contents);
        }
        return $images;
    }

    // BBC Micro:Bit
    public function generateMicroPythonCode($animationJSON)
    {
        return "Error: MicroPython is only valid for the BBC Micro:Bit.";
    }

    public function generateTypeScriptCode($animationJSON)
    {
        return "Error: TypeScript is only valid for the BBC MicroBit.";
    }

    public function generateHexFile($animationJSON)
    {
        return "Error: Hex files are only valid for the BBC MicroBit.";
    }

    // Arduino
    public function generateArduinoCode($animationJSON)
    {
        return "Error: Arduino code is only valid for the Arduino.";
    }
}

class MicroBitBuiltInAnimation extends Animation
{
    public function __construct($id)
    {
        parent::__construct($id);
    }

    public function generateMicroPythonCode($animationJSON)
    {
        $fps = 1;
        $code = "from microbit import *"
            . "\n"
            . "\ndef play(animation):"
            . "\n\tfor frame in animation:"
            . "\n\t\tbits = [255 & frame[0]]"
            . "\n\t\tfor i in range(len(frame) - 1):"
            . "\n\t\t\tfor j in range(4):"
            . "\n\t\t\t\tbits.append(255 & frame[i + 1] >> 24 - j * 8)"
            . "\n\t\tfor i in range(len(bits)):"
            . "\n\t\t\tx = i % 5"
            . "\n\t\t\ty = i // 5"
            . "\n\t\t\tdisplay.set_pixel(x, y, bits[i] * 9 // 255)"
            . "\n\t\tsleep(1000 / $fps)"
            . "\n\t\tdisplay.clear()"
            . "\n"
            . "\nwhile True:"
            . "\n\tif button_a.is_pressed():"
            . "\n\t\tplay(";

        $lines = explode("\n", $animationJSON);
        for ($i = 0; $i < count($lines); ++$i) {
            $line = trim($lines[$i]);
            $nextLine = trim($lines[$i + 1]);
            $nextNextLine = trim($lines[$i + 2]);
            if (!$nextLine) $code = "$code$line";
            else if (!$nextNextLine) $code = "$code$line\n\t\t";
            else if ($nextLine[0] === "[" || $nextLine[0] === "]") $code = "$code$line\n\t\t\t";
            else if ($nextLine[1] === "b") $code = "$code$line\n\t\t\t\t";
        }

        $code = "$code)";
        return $code;
    }

    public function generateTypeScriptCode($animationJSON)
    {
        $fps = 1;
        $code = "const play = (animation: number[][]) => {"
            . "\n\tanimation.forEach((frame: number[]) => {"
            . "\n\t\tconst bits: number[] = [255 & frame[0]];"
            . "\n\t\tfor (let i = 0; i < frame.length - 1; ++i)"
            . "\n\t\t\tfor (let j = 0; j < 4; ++j)"
            . "\n\t\t\t\tbits.push(255 & frame[i + 1] >> 24 - j * 8);"
            . "\n\t\tfor (let i = 0; i < bits.length; ++i) {"
            . "\n\t\t\tconst x = i % 5;"
            . "\n\t\t\tconst y = Math.floor(i / 5);"
            . "\n\t\t\tled.plotBrightness(x, y, bits[i]);"
            . "\n\t\t}"
            . "\n\t\tbasic.pause(1000 / $fps);"
            . "\n\t\tbasic.clearScreen();"
            . "\n\t});"
            . "\n}"
            . "\n"
            . "\ninput.onButtonPressed(Button.A, () => {"
            . "\n\tplay(";

        $lines = explode("\n", $animationJSON);
        for ($i = 0; $i < count($lines); ++$i) {
            $line = trim($lines[$i]);
            $nextLine = trim($lines[$i + 1]);
            $nextNextLine = trim($lines[$i + 2]);
            if (!$nextLine) $code = "$code$line";
            else if (!$nextNextLine) $code = "$code$line\n\t";
            else if ($nextLine[0] === "[" || $nextLine[0] === "]") $code = "$code$line\n\t\t";
            else if ($nextLine[1] === "b") $code = "$code$line\n\t\t\t";
        }

        $code = "$code);"
            . "\n});";
        return $code;
    }

    public function generateHexFile($animationJSON)
    {
        return "Error: I haven't done this yet.";
    }
}
