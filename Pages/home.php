<?php
$db = $_SESSION["database"];
$user = unserialize($_SESSION["user"]);

if (isset($_POST["deleteUser"]) && !is_null($_POST["deleteUser"])) {
    $tmp = $_POST["deleteUser"];
    echo <<<HTML
        <div class="alert alert-danger" role="alert">
            Are you sure you want to permanently delete $tmp's account? This can <strong>not</strong> be undone. All data will be erased.
        </div>
    HTML;
} else if (isset($_POST["deleteUserConfirmed"]) && !is_null($_POST["deleteUserConfirmed"])) {
    (new User($_POST["deleteUserConfirmed"]))->delete();
}

if (isset($_POST["resetPassword"]) && !is_null($_POST["resetPassword"])) {
    $tmp = $_POST["resetPassword"];
    echo <<<HTML
        <div class="alert alert-warning" role="alert">
            Are you sure you want to reset $tmp's password? This can <strong>not</strong> be undone.
        </div>
    HTML;
} else if (isset($_POST["resetPasswordConfirmed"]) && !is_null($_POST["resetPasswordConfirmed"])) {
    $tmp = $_POST["resetPasswordConfirmed"];
    $db->update("User", ["PasswordHash"], [md5($tmp)], "Username = '$tmp'");
}

echo <<<HTML
    <h1>Microcontroller Animations</h1>
HTML;

switch ($user->type) {
    case "admin":
        function mapUsers($value)
        {
            return new User($value[0]);
        }
        $users = array_map("mapUsers", $db->select("Username", "User", NULL, "Username"));
        echo <<<HTML
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
            if (isset($_POST["deleteUser"]) && !is_null($_POST["deleteUser"])) {
                echo <<<HTML
                    <td>
                        <form method="post">
                            <input style="display: none;" type="text" name="deleteUserConfirmed" value="$username">
                            <button class="btn btn-sm btn-danger">Delete</button>
                        </form>
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
            if (isset($_POST["resetPassword"]) && !is_null($_POST["resetPassword"])) {
                echo <<<HTML
                    <td>
                        <form method="post">
                            <input style="display: none;" type="text" name="resetPasswordConfirmed" value="$username">
                            <button class="btn btn-sm btn-warning">Reset Password</button>
                        </form>
                    </td>
                HTML;
            } else {
                echo <<<HTML
                    <td>
                        <form method="post">
                            <input style="display: none;" type="text" name="resetPassword" value="$username">
                            <button class="btn btn-sm btn-warning">Reset Password</button>
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
    default:
        echo "Hello, world!";
}
