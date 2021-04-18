<?php

/**
 * Class to represent a user.
 */
class User
{
    /**
     * @var string
     */
    private $username;
    /**
     * @param string $username
     */
    public function __construct($username)
    {
        $this->username = $username;
    }

    /**
     * @param string $name
     * @return mixed
     */
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

    /**
     * Deletes the user from the database.
     * @return void
     */
    public function delete()
    {
        $db = $_SESSION["database"];
        $db->delete("User", "Username = '$this->username'");
        $animationCount = count($this->animations);
        for ($i = 0; $i < $animationCount; ++$i) $this->animations[$i]->delete();
        $postCount = count($this->posts);
        for ($i = 0; $i < $postCount; ++$i) $this->posts[$i]->delete();
        $db->delete("PostLike", "Username = '$this->username'");
        $db->delete("Comment", "Username = '$this->username'");
        $db->delete("UserFollowing", "Username = '$this->username' OR FollowingUsername = '$this->username'");
        $db->delete("ChatMessage", "Username = '$this->username'");
        $db->delete("MutedUser", "Username = '$this->username'");
        $db->delete("AssignmentWork", "Username = '$this->username'");
    }

    /**
     * Sets the user's password to be the same as their username.
     * @return void
     */
    public function resetPassword()
    {
        $db = $_SESSION["database"];
        $db->update("User", ["PasswordHash"], [md5($this->username)], "Username = '$this->username'");
    }

    /**
     * Turns the user into an admin.
     * @return void
     */
    public function makeAdmin()
    {
        $db = $_SESSION["database"];
        $db->update("User", ["type"], [0], "Username = '$this->username'");
    }

    /**
     * Adds the given user to list of users who follow this user.
     * @param string $username
     * @return void
     */
    public function followUser($username)
    {
        $db = $_SESSION["database"];
        $user = new User($username);
        $db->insert("UserFollowing", "Username, FollowingUsername", "'$this->username', '$user->username'");
    }

    /**
     * Removes the given user from list of users who follow this user.
     * @param string $username
     * @return void
     */
    public function unfollowUser($username)
    {
        $db = $_SESSION["database"];
        $user = new User($username);
        $db->delete("UserFollowing", "Username = '$this->username' AND FollowingUsername = '$user->username'");
    }
}
