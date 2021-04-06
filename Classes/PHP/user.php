<?php
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
                $users = $db->select("FollowingUsername", "UserFollowing", "Username = '$username'");
                if (is_null($users)) return NULL;
                return array_map("mapToUserObject", array_map("mapToFirstItem", $users));
            case "followers":
                $users = $db->select("Username", "UserFollowing", "FollowingUsername = '$username'");
                if (is_null($users)) return NULL;
                return array_map("mapToUserObject", array_map("mapToFirstItem", $users));
            case "animations":
                $animations = $db->select("AnimationID", "Animation", "Username = '$username'", "AnimationID");
                if (is_null($animations)) return NULL;
                return array_map("mapToAnimationObject", array_map("mapToFirstItem", $animations));
            case "posts":
                $posts = $db->select("PostID", "Post", "Username = '$username'", "CreatedAt DESC");
                if (is_null($posts)) return NULL;
                return array_map("mapToPostObject", array_map("mapToFirstItem", $posts));
            case "classes":
                $classes = $db->select("ClassID", "ClassMember", "Username = '$username'");
                if (is_null($classes)) return NULL;
                return array_map("mapToGroupObject", array_map("mapToFirstItem", $classes));
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

    public function resetPassword()
    {
        $db = $_SESSION["database"];
        $db->update("User", ["PasswordHash"], [md5($this->username)], "Username = '$this->username'");
    }

    public function makeAdmin()
    {
        $db = $_SESSION["database"];
        $db->update("User", ["type"], [0], "Username = '$this->username'");
    }

    public function followUser($username)
    {
        $db = $_SESSION["database"];
        $user = new User($username);
        $db->insert("UserFollowing", "Username, FollowingUsername", "'$this->username', '$user->username'");
    }

    public function unfollowUser($username)
    {
        $db = $_SESSION["database"];
        $user = new User($username);
        $db->delete("UserFollowing", "Username = '$this->username' AND FollowingUsername = '$user->username'");
    }
}
