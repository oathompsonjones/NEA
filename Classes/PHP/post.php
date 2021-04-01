<?php
function mapLikes($value)
{
    return new User($value[0]);
}

class Post
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
            case "createdAt":
                return $db->select("CreatedAt", "Post", "PostID = '$id'")[0][0];
            case "user":
                return new User($db->select("Username", "Post", "PostID = '$id'")[0][0]);
            case "animation":
                return new Animation($db->select("AnimationID", "Post", "PostID = '$id'")[0][0]);
            case "animationID":
                return $db->select("AnimationID", "Post", "PostID = '$id'")[0][0];
            case "fps":
                return $db->select("FPS", "Post", "PostID = '$id'")[0][0];
            case "likedBy":
                $likes = $db->select("Username", "PostLike", "PostID = '$id'");
                if (is_null($likes)) return NULL;
                return array_map("mapLikes", $likes);
            default:
                throw new Exception("Property $name does not exist on type Post.");
        }
    }

    public function delete()
    {
        $db = $_SESSION["database"];
        $db->delete("Post", "PostID = '$this->id'");
        $db->delete("PostLike", "PostID = '$this->id'");
        $db->delete("Comment", "PostID = '$this->id'");
    }

    public function like($username)
    {
        $db = $_SESSION["database"];
        $db->insert("PostLike", "PostID, Username", "'$this->id', '$username'");
    }

    public function unlike($username)
    {
        $db = $_SESSION["database"];
        $db->delete("PostLike", "PostID = '$this->id' AND Username = '$username'");
    }

    public function comment($username, $content)
    {
        $db = $_SESSION["database"];
        $timestamp = time();
        $id = md5("$timestamp-$this->id");
        $db->insert("Comment", "CommentID, PostID, Username, Content, CreatedAt", "'$id', '$this->id', '$username', '$content', $timestamp");
    }

    public function uncomment($id)
    {
        $db = $_SESSION["database"];
        $db->delete("Comment", "CommentID = '$id'");
    }
}
