<?php
class Group
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
                return $db->select("Name", "Class", "ClassID = '$id'")[0][0];
            case "chatEnabled":
                return $db->select("ChatEnabled", "Class", "ClassID = '$id'")[0][0];
            case "students":
                $members = $db->select("ClassMember.Username", "ClassMember", "ClassMember.ClassID = '$id'", NULL, "User", "User.Username = ClassMember.Username AND User.Type = 2");
                if (is_null($members)) return NULL;
                return array_map("mapToUserObject", array_map("mapToFirstItem", $members));
            case "teachers":
                $members = $db->select("ClassMember.Username", "ClassMember", "ClassMember.ClassID = '$id'", NULL, "User", "User.Username = ClassMember.Username AND User.Type = 1");
                if (is_null($members)) return NULL;
                return array_map("mapToUserObject", array_map("mapToFirstItem", $members));
            case "chatMessages":
                $messages = $db->select("MessageID", "ChatMessage", "ClassID = '$id'", "CreatedAt");
                if (is_null($messages)) return NULL;
                return array_map("mapToMessageObject", array_map("mapToFirstItem", $messages));
            case "mutedUsers":
                $users = $db->select("Username", "MutedUser", "ClassID = '$id'");
                if (is_null($users)) return NULL;
                return array_map("mapToUserObject", array_map("mapToFirstItem", $users));
            case "assignments":
                return [];
            default:
                throw new Exception("Property $name does not exist on type Group.");
        }
    }

    public function delete()
    {
        $db = $_SESSION["database"];
        $db->delete("Class", "ClassID = '$this->id'");
        $db->delete("ClassMember", "ClassID = '$this->id'");
        $db->delete("MutedUser", "ClassID = '$this->id'");
        $db->delete("ChatMessage", "ClassID = '$this->id'");
        for ($i = 0; $i < count($this->assignments); ++$i) $this->assignments[$i]->delete();
    }

    public function addUser($username)
    {
        $db = $_SESSION["database"];
        $students = $this->students;
        $teachers = $this->teachers;
        if (in_array($username, array_map("mapToUsernames", $students)) || in_array($username, array_map("mapToUsernames", $teachers))) return;
        $db->insert("ClassMember", "Username, ClassID", "'$username', '$this->id'");
    }

    public function muteUser($username)
    {
        $db = $_SESSION["database"];
        $db->insert("MutedUser", "ClassID, Username", "'$this->id', '$username'");
    }

    public function unMuteUser($username)
    {
        $db = $_SESSION["database"];
        $db->delete("MutedUser", "ClassID = '$this->id' AND Username = '$username'");
    }

    public function kickUser($username)
    {
        $db = $_SESSION["database"];
        $db->delete("ClassMember", "ClassID = '$this->id' AND Username = '$username'");
    }

    public function render()
    {
        $id = $this->id;
        $name = $this->name;
        $studentCount = count($this->students);
        $teacherCount = count($this->teachers);
        return <<<HTML
            <div class="card bg-dark text-light">
                <div class="card-body">
                    <h5 class="card-title"><a href="class?classID=$id">$name</a></h5>
                    <p><strong>Students:</strong> $studentCount</p>
                    <p><strong>Teachers:</strong> $teacherCount</p>
                </div>
            </div>
        HTML;
    }
}
