<?php
$db = $_SESSION["database"];
$user = unserialize($_SESSION["user"]);

if (isset($_POST["deleteUser"]) && !is_null($_POST["deleteUser"])) {
    $tmp = $_POST["deleteUser"];
    echo <<<HTML
        <div class="alert alert-danger" role="alert" style="display: flex;">
            <p>Are you sure you want to permanently delete $tmp's account? This can <strong>not</strong> be undone. All data will be erased.</p>
            <form method="post" style="padding-left: 5px;">
                <input style="display: none;" name="deleteUserConfirmed" type="text" value="$tmp">
                <button class="btn btn-danger btn-sm" type="submit">Yes</button>
            </form>
            <form method="post" style="padding-left: 5px;">
                <button class="btn btn-danger btn-sm" type="submit">No</button>
            </form>
        </div>
    HTML;
} else if (isset($_POST["deleteUserConfirmed"]) && !is_null($_POST["deleteUserConfirmed"])) {
    (new User($_POST["deleteUserConfirmed"]))->delete();
    require "Include/clearPost.inc";
}

if (isset($_POST["resetPassword"]) && !is_null($_POST["resetPassword"])) {
    $tmp = $_POST["resetPassword"];
    echo <<<HTML
        <div class="alert alert-danger" role="alert" style="display: flex;">
            <p>Are you sure you want to reset $tmp's password? This can <strong>not</strong> be undone.</p>
            <form method="post" style="padding-left: 5px;">
                <input style="display: none;" name="resetPasswordConfirmed" type="text" value="$tmp">
                <button class="btn btn-danger btn-sm" type="submit">Yes</button>
            </form>
            <form method="post" style="padding-left: 5px;">
                <button class="btn btn-danger btn-sm" type="submit">No</button>
            </form>
        </div>
    HTML;
} else if (isset($_POST["resetPasswordConfirmed"]) && !is_null($_POST["resetPasswordConfirmed"])) {
    $tmp = $_POST["resetPasswordConfirmed"];
    $db->update("User", ["PasswordHash"], [md5($tmp)], "Username = '$tmp'");
    require "Include/clearPost.inc";
}

if (isset($_POST["makeAdmin"]) && !is_null($_POST["makeAdmin"])) {
    $tmp = $_POST["makeAdmin"];
    echo <<<HTML
        <div class="alert alert-danger" role="alert" style="display: flex;">
            <p>Are you sure you want to make $tmp an admin? This can <strong>not</strong> be undone.</p>
            <form method="post" style="padding-left: 5px;">
                <input style="display: none;" name="makeAdminConfirmed" type="text" value="$tmp">
                <button class="btn btn-danger btn-sm" type="submit">Yes</button>
            </form>
            <form method="post" style="padding-left: 5px;">
                <button class="btn btn-danger btn-sm" type="submit">No</button>
            </form>
        </div>
    HTML;
} else if (isset($_POST["makeAdminConfirmed"]) && !is_null($_POST["makeAdminConfirmed"])) {
    $tmp = $_POST["makeAdminConfirmed"];
    $db->update("User", ["type"], [0], "Username = '$tmp'");
    require "Include/clearPost.inc";
}

switch ($user->type) {
    case "admin":
        function mapUsers($value)
        {
            return new User($value[0]);
        }
        $users = array_map("mapUsers", $db->select("Username", "User", NULL, "Username"));
        echo <<<HTML
            <h1>Microcontroller Animations</h1>
            <h3>Users</h3>
            <table style="width: 100%;">
                <tr>
                    <th>Username</th>
                    <th>Password Hash</th>
                    <th>Type</th>
                    <th>Animations</th>
                    <th>Posts</th>
                    <th>Followers</th>
                    <th>Following</th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
        HTML;
        for ($i = 0; $i < count($users); ++$i) {
            $username = $users[$i]->username;
            $passwordHash = $users[$i]->passwordHash;
            $type = $users[$i]->type;
            $animations = count($users[$i]->animations);
            $posts = count($users[$i]->posts);
            $followers = count($users[$i]->followers);
            $following = count($users[$i]->following);
            echo <<<HTML
                <tr>
                    <td>$username</td>
                    <td>$passwordHash</td>
                    <td>$type</td>
                    <td>$animations</td>
                    <td>$posts</td>
                    <td>$followers</td>
                    <td>$following</td>
            HTML;
            if ($username === $user->username) {
                echo <<<HTML
                        <td>
                            <button class="btn btn-sm btn-danger disabled">Delete</button>
                        </td>
                    HTML;
            } else {
                echo <<<HTML
                    <td>
                        <form method="post">
                            <input style="display: none;" type="text" name="deleteUser" value="$username">
                            <button class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                HTML;
            }
            echo <<<HTML
                <td>
                    <form method="post">
                        <input style="display: none;" type="text" name="resetPassword" value="$username">
                        <button class="btn btn-sm btn-warning">Reset Password</button>
                    </form>
                </td>
            HTML;
            if ($type === "admin") {
                echo <<<HTML
                        <td>
                            <button class="btn btn-sm btn-dark disabled">Make Admin</button>
                        </td>
                    HTML;
            } else {
                echo <<<HTML
                    <td>
                        <form method="post">
                            <input style="display: none;" type="text" name="makeAdmin" value="$username">
                            <button class="btn btn-sm btn-dark">Make Admin</button>
                        </form>
                    </td>
                HTML;
            }
            echo <<<HTML
                </tr>
            HTML;
        }
        echo <<<HTML
            </table>
        HTML;
        break;
    case "teacher":
    case "student":
        $feedPosts = [];
        $feedUsers = $user->following;
        array_push($feedUsers, $user);
        for ($i = 0; $i < count($feedUsers); ++$i) {
            $thisUser = $feedUsers[$i];
            $thisPosts = $thisUser->posts;
            for ($j = 0; $j < count($thisPosts); ++$j) array_push($feedPosts, $thisPosts[$j]);
        }
        usort($feedPosts, "sortByCreatedAt");
        echo <<<HTML
            <div style="display: flex; height: 100%;">
                <div style="flex: 40%; max-width: 40%; word-wrap: break-word;">
                    <h1>Microcontroller Animations</h1>
                    <div class="card-group" style="display: block; overflow: auto; height: 95%;">
        HTML;
        for ($i = 0; $i < min(count($feedPosts), 100); ++$i) echo generatePost($feedPosts[$i]->id, $i);
        echo <<<HTML
                    </div>
                </div>
                <div style="flex: 60%; max-width: 60%; word-wrap: break-word;">
                    Other stuff goes here...
                </div>
            </div>
        HTML;
        break;
}
