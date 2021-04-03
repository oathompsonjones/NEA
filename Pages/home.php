<?php
$db = $_SESSION["database"];
$user = unserialize($_SESSION["user"]);

switch ($user->type) {
    case "admin":
        $users = array_map("mapToUserObject", array_map("mapToFirstItem", $db->select("Username", "User", NULL, "Username")));
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
            $resetPasswordHash = md5($username);
            $type = $users[$i]->type;
            $animations = count($users[$i]->animations);
            $posts = count($users[$i]->posts);
            $followers = count($users[$i]->followers);
            $following = count($users[$i]->following);
            echo <<<HTML
                <tr id="$username-row">
                    <td>$username</td>
                    <td id="$username-password">$passwordHash</td>
                    <td id="$username-type">$type</td>
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
                    <script>
                        const delete_$username = () => {
                            document.getElementById("$username-delete").innerHTML = `<button onclick="deleteConfirm_$username();" class="btn btn-sm btn-danger">Confirm</button>`;
                        };
                        const deleteConfirm_$username = () => {
                            const username = "$username";
                            $.post("Utils/Forms/deleteUser.php", { username }, () => {
                                document.getElementById("$username-row").style.display = "none";
                                document.getElementById("$username-delete").innerHTML = `<button onclick="delete_$username();" class="btn btn-sm btn-danger">Delete</button>`;
                            });
                        };
                    </script>
                    <td id="$username-delete">
                        <button onclick="delete_$username();" class="btn btn-sm btn-danger">Delete</button>
                    </td>
                HTML;
            }
            echo <<<HTML
                <script>
                    const resetPassword_$username = () => {
                        document.getElementById("$username-resetPassword").innerHTML = `<button onclick="resetPasswordConfirm_$username();" class="btn btn-sm btn-warning">Confirm</button>`;
                    };
                    const resetPasswordConfirm_$username = () => {
                        const username = "$username";
                        $.post("Utils/Forms/resetPassword.php", { username }, () => {
                            document.getElementById("$username-password").innerHTML = "$resetPasswordHash";
                            document.getElementById("$username-resetPassword").innerHTML = `<button onclick="resetPassword_$username();" class="btn btn-sm btn-warning">Reset Password</button>`;
                        });
                    };
                </script>
                <td id="$username-resetPassword">
                    <button onclick="resetPassword_$username();" class="btn btn-sm btn-warning">Reset Password</button>
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
                    <script>
                        const makeAdmin_$username = () => {
                            document.getElementById("$username-makeAdmin").innerHTML = `<button onclick="makeAdminConfirm_$username();" class="btn btn-sm btn-dark">Confirm</button>`;
                        };
                        const makeAdminConfirm_$username = () => {
                            const username = "$username";
                            $.post("Utils/Forms/makeAdmin.php", { username }, () => {
                                document.getElementById("$username-type").innerHTML = "admin";
                                document.getElementById("$username-makeAdmin").innerHTML = `<button class="btn btn-sm btn-dark disabled">Make Admin</button>`;
                            });
                        };
                    </script>
                    <td id="$username-makeAdmin">
                        <button onclick="makeAdmin_$username();" class="btn btn-sm btn-dark">Make Admin</button>
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
                        <style>.post { display: none; }</style>
        HTML;
        for ($i = 0; $i < min(count($feedPosts), 100); ++$i) echo $feedPosts[$i]->render() . "<br>";
        echo <<<HTML
                        <style>.post { display: block; }</style>
                    </div>
                </div>
                <div style="flex: 60%; max-width: 60%; word-wrap: break-word;">
                    Other stuff goes here...
                </div>
            </div>
        HTML;
        break;
}
