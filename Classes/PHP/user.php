<?php
function mapAnimation($value)
{
    return new Animation($value[0]);
}

function mapPosts($value)
{
    return new Post($value[0]);
}

class User
{
    private $username;
    public function __construct($username)
    {
        $this->username = $username;
    }

    public function __get($name)
    {
        $username = $this->username;
        $db = $_SESSION["database"];
        switch ($name) {
            case "username":
                return $username;
            case "bio":
                return $db->select("Bio", "User", "Username = '$username'")[0][0];
            case "passwordHash":
                return $db->select("PasswordHash", "User", "Username = '$username'")[0][0];
            case "type":
                switch ($db->select("Type", "User", "Username = '$username'")[0][0]) {
                    case 0:
                        return "admin";
                    case 1:
                        return "teacher";
                    case 2:
                        return "student";
                }
            case "following":
                return $db->select("FollowingUsername", "UserFollowing", "Username = '$username'");
            case "followers":
                return $db->select("Username", "UserFollowing", "FollowingUsername = '$username'");
            case "animations":
                $animations = $db->select("AnimationID", "Animation", "Username = '$username'");
                if (is_null($animations)) return NULL;
                return array_map("mapAnimation", $animations);
            case "posts":
                $posts = $db->select("PostID", "Post", "Username = '$username'");
                if (is_null($posts)) return NULL;
                return array_map("mapPosts", $posts);
            default:
                throw new Exception("Property $name does not exist on type User.");
        }
    }

    public function delete()
    {
        $db = $_SESSION["database"];
        $db->delete("User", "Username = '$this->username'");
        for ($i = 0; $i < count($this->animations); ++$i) $this->animations[$i]->delete();
        for ($i = 0; $i < count($this->posts); ++$i) $this->posts[$i]->delete();
        $db->delete("PostLike", "Username = '$this->username'");
        $db->delete("Comment", "Username = '$this->username'");
        $db->delete("UserFollowing", "Username = '$this->username' OR FollowingUsername = '$this->username'");
        $db->delete("ChatMessage", "Username = '$this->username'");
        $db->delete("MutedUser", "Username = '$this->username'");
        $db->delete("AssignmentWork", "Username = '$this->username'");
    }

    public function followUser($username)
    {
    }

    public function unfollowUser($username)
    {
    }
}
