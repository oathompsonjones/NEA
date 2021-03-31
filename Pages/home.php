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
        $user = unserialize($_SESSION["user"]);
        $feedPosts = [];
        $feedUsers = $user->following;
        array_push($feedUsers, $user);
        for ($i = 0; $i < count($feedUsers); ++$i) {
            $thisUser = $feedUsers[$i];
            $thisPosts = $thisUser->posts;
            for ($j = 0; $j < count($thisPosts); ++$j) array_push($feedPosts, $thisPosts[$j]);
        }
        function sortPostsByTimestamp($a, $b)
        {
            return max($a->createdAt, $b->createdAt);
        }
        function mapIconsSrc($value)
        {
            return "data:image/png;base64,$value";
        }
        usort($feedPosts, "sortPostsByTimestamp");
        echo <<<HTML
            <div style="display: flex; height: 95%;">
                <div style="flex: 66%; max-width: 66%; word-wrap: break-word;">
                    Stuff...
                </div>
                <div style="flex: 33%; max-width: 33%; word-wrap: break-word;">
                    <h2>Your Feed</h2>
                    <script>
                        const playback = (index, frames, fps) => {
                            const img = document.getElementById(index.toString() + "-icon");
                            const buttons = document.getElementById(index.toString() + "-buttons");
                            let i = 0;
                            buttons.style.display = "none";
                            const interval = setInterval(() => img.src = frames[i++], 1000 / fps);
                            setTimeout(() => {
                                clearInterval(interval);
                                img.src = frames[0];
                                buttons.style.display = "block";
                            }, 1000 * (frames.length + 1) / fps);
                        };
                    </script>
                    <div class="card-group" style="display: block; overflow: auto; height: 90%;">
        HTML;
        for ($i = 0; $i < count($feedPosts); ++$i) {
            $post = $feedPosts[$i];
            $username = $post->user->username;
            $name = $post->animation->name;
            $type = $post->animation->typeString;
            $timestamp = $post->createdAt;
            $fps = $post->fps;
            $icons = !is_null($post)
                ? array_map("mapIconsSrc", $post->animation->generateFrameIcons())
                : [];
            $jsonIcons = json_encode($icons);
            $firstIcon = $icons[0];
            echo <<<HTML
                <div class="card text-white bg-dark">
                    <div class="card-header">
                        $username
                    </div>
                    <div id="$i-card" class="icon">
                        <img src="$firstIcon" class="card-img-top" id="$i-icon">
                        <div id="$i-buttons" class="buttons">
                            <button class="btn btn-secondary btn-lg" data-toggle="tooltip" data-placement="top" title="Play the animation" onclick='playback($i, $jsonIcons, $fps);'>Play</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">
                            $name<br><span class='badge rounded-pill bg-secondary'>$type - $fps FPS</span>
                        </h5>
                    </div>
                    <div class="card-footer text-muted">
                        <script>document.write(new Date(Date.now() - $timestamp).toGMTString());</script>
                    </div>
                </div>
            HTML;
        }
        echo <<<HTML
                    </div>
                </div>
            </div>
        HTML;
        break;
}
