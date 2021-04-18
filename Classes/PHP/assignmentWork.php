<?php

/**
 * Class to represent a piece of work submitted for an assignment.
 */
class AssignmentWork
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

    /**
     * Creates the HTML required to render the work.
     * @return string
     */
    public function render()
    {
        $animationHTML = $this->animation->render(true, false);
        return <<<HTML
            <div class="col">
                <h5 >$this->username</h5>
                $animationHTML
            </div>
        HTML;
    }

    /**
     * Deletes the work from the database.
     * @return void
     */
    public function delete()
    {
        $db = $_SESSION["database"];
        $db->delete("AssignmentWork", "WorkID = '$this->id");
    }
}
