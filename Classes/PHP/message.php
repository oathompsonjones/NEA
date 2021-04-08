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

    public function render($loggedInUser)
    {
        $id = $this->id;
        $user = $this->user;
        $createdAt = gmdate("D, d M Y H:i:s", $this->createdAt);
        $html = <<<HTML
            <div id="$id" class="card bg-dark text-light">
                <div class="card-header" style="display: flex;">
                    <h6>$user->username</h6>
                    <p class="text-muted" style="flex: 25%; text-align: right;">$createdAt GMT</p>
                </div>
                <div class="card-body" style="display: flex; word-break: break-all;">
                    <p style="flex: 100%;">$this->content</p>
        HTML;
        if ($loggedInUser->username === $user->username || $loggedInUser->type === "teacher") $html = $html . <<<HTML
            <div style="flex: 10%; text-align: right;">
                <a class="btn btn-sm btn-danger" onclick="deleteMessage('$id');">Delete</a>
            </div>
        HTML;
        $html = $html . <<<HTML
                </div>
            </div>
            <br>
        HTML;
        return $html;
    }
}
