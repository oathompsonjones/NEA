<?php
function mapAnimation($value)
{
    return new Animation($value[0]);
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
            default:
                throw new Exception("Property $name does not exist on type User.");
        }
    }

    public function followUser($username)
    {
    }

    public function unfollowUser($username)
    {
    }
}
