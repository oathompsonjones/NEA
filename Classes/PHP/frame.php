<?php

/**
 * Class to represent a single frame in an animation.
 */
class Frame
{
    /**
     * @var string
     */
    private $id;
    /**
     * @param string $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @param string $name
     * @return mixed
     */
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
            case "width":
                $animationID = $db->select("AnimationID", "Frame", "FrameID = '$id'")[0][0];
                if (is_null($animationID)) return NULL;
                $animation = new Animation($animationID);
                return $animation->width;
            case "height":
                $animationID = $db->select("AnimationID", "Frame", "FrameID = '$id'")[0][0];
                if (is_null($animationID)) return NULL;
                $animation = new Animation($animationID);
                return $animation->height;
            default:
                throw new Exception("Property $name does not exist on type Frame.");
        }
    }

    /**
     * Deletes the frame from the database.
     * @return void
     */
    public function delete()
    {
        $db = $_SESSION["database"];
        $db->delete("Frame", "FrameID = '$this->id'");
    }
}
