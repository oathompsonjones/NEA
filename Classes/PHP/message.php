<?php
class Message
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
            case "user":
                return new User($db->select("Username", "ChatMessage", "MessageID = '$id'")[0][0]);
            case "class":
                return new Group($db->select("ClassID", "ChatMessage", "MessageID = '$id'")[0][0]);
            case "content":
                return $db->select("Content", "ChatMessage", "MessageID = '$id'")[0][0];
            case "createdAt":
                return $db->select("CreatedAt", "ChatMessage", "MessageID = '$id'")[0][0];
            default:
                throw new Exception("Property $name does not exist on type Message.");
        }
    }

    public function delete()
    {
        $db = $_SESSION["database"];
        $db->delete("ChatMessage", "MessageID = '$this->id'");
    }

    public function render()
    {
        $user = $this->user;
        $createdAt = gmdate("D, d M Y H:i:s", $this->createdAt);
        return <<<HTML
            <div class="card bg-dark text-light">
                    <div class="card-header" style="display: flex;">
                        <h6>$user->username</h6>
                        <p class="text-muted" style="flex: 25%; text-align: right;">$createdAt GMT</p>
                    </div>
                    <p class="card-body" style="word-break: break-all;">$this->content</p>
            </div>
            <br>
        HTML;
    }
}
