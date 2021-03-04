<?php
function mapFrame($value)
{
    return new Frame($value[0]);
}

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
            case "frames":
                $frames = $db->select("FrameID", "Frame", "AnimationID = '$id'", "FramePosition ASC");
                if (is_null($frames)) return NULL;
                return array_map("mapFrame", $frames);
            default:
                throw new Exception("Property $name does not exist on type Animation.");
        }
    }

    public function getFramesAs32BitIntegersJSON()
    {
        function mapBinary($val)
        {
            return "0b" . $val;
        }
        function frameToBinaryArray($value)
        {
            $num = strlen($value->binary) + 32 - strlen($value->binary) % 32;
            $binary = str_pad($value->binary, $num, "0", STR_PAD_LEFT);
            $smallerBinaries = [];
            preg_match_all("/.{1,32}/", $binary, $smallerBinaries);
            return array_map("mapBinary", $smallerBinaries[0]);
        }
        $frames = array_map("frameToBinaryArray", $this->frames);
        return str_replace('"', "", json_encode($frames, JSON_PRETTY_PRINT));
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
            . "\nimport math"
            . "\n"
            . "\ndef play(animation):"
            . "\n\tfor frame in animation:"
            . "\n\t\tbits = [255 & frame[0]]"
            . "\n\t\tfor i in range(len(frame) - 1):"
            . "\n\t\t\tfor j in range(4):"
            . "\n\t\t\t\tbits.append(255 & frame[i + 1] << j * 8 >> 24)"
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
        return "Error: I haven't done this yet.";
    }

    public function generateHexFile($animationJSON)
    {
        return "Error: I haven't done this yet.";
    }
}
