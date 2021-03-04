<?php
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
