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
        switch ($name) {
            case "username":
                return $username;
            case "bio":
                return $_SESSION["database"]->select("Bio", "User", "Username = '$username'")[0][0];
            case "passwordHash":
                return $_SESSION["database"]->select("PasswordHash", "User", "Username = '$username'")[0][0];
            case "type":
                switch ($_SESSION["database"]->select("Type", "User", "Username = '$username'")[0][0]) {
                    case 0:
                        return "admin";
                    case 1:
                        return "teacher";
                    case 2:
                        return "student";
                }
            case "following":
                return $_SESSION["database"]->select("FollowingUsername", "UserFollowing", "Username = '$username'")[0];
            case "followers":
                return $_SESSION["database"]->select("Username", "UserFollowing", "FollowingUsername = '$username'")[0];
            case "animations":
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
