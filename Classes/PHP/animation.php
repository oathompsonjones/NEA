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
                $frames = $db->select("FrameID", "Frame", "AnimationID = '$id'");
                if (is_null($frames)) return NULL;
                return array_map("mapFrame", $frames);
            default:
                throw new Exception("Property $name does not exist on type Animation.");
        }
    }
}

class Frame
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
            case "position":
                return $db->select("FramePosition", "Frame", "FrameID = '$id'")[0][0];
            case "binary":
                return $db->select("BinaryString", "Frame", "FrameID = '$id'")[0][0];
            default:
                throw new Exception("Property $name does not exist on type Frame.");
        }
    }
}
