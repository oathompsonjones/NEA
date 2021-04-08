<?php
class AssignmentWork
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
            case "assignment":
                return new Assignment($db->select("AssignmentID", "AssignmentWork", "WorkID = '$id'")[0][0]);
            case "username":
                return $db->select("Username", "AssignmentWork", "WorkID = '$id'")[0][0];
            case "user":
                return new User($db->select("Username", "AssignmentWork", "WorkID = '$id'")[0][0]);
            case "animation":
                return new Animation($db->select("AnimationID", "AssignmentWork", "WorkID = '$id'")[0][0]);
            case "createdAt":
                return $db->select("CreatedAt", "AssignmentWork", "WorkID = '$id'")[0][0];
            default:
                throw new Exception("Property $name does not exist on type AssignmentWork.");
        }
    }

    public function render()
    {
        $db = $_SESSION["database"];
        return "";
    }

    public function delete()
    {
        $db = $_SESSION["database"];
        $db->delete("AssignmentWork", "WorkID = '$this->id");
    }
}
