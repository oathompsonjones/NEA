<?php
$db = $_SESSION["database"];
$user = unserialize($_SESSION["user"]);

function renderUsers()
{
    $db = $_SESSION["database"];
    $user = unserialize($_SESSION["user"]);
    $users = array_map("mapToUserObject", array_map("mapToFirstItem", $db->select("Username", "User", NULL, "Username")));
    $html = <<<HTML
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
        $html = $html . <<<HTML
            <tr id="$username-row">
                <td><a href="profile?user=$username">$username</a></td>
                <td id="$username-password">$passwordHash</td>
                <td id="$username-type">$type</td>
                <td>$animations</td>
                <td>$posts</td>
                <td>$followers</td>
                <td>$following</td>
        HTML;
        if ($username === $user->username) {
            $html = $html . <<<HTML
                <td>
                    <button class="btn btn-sm btn-danger disabled">Delete</button>
                </td>
            HTML;
        } else {
            $html = $html . <<<HTML
                <script>
                    const delete_$username = () => document.getElementById("$username-delete").innerHTML = `<button onclick="deleteConfirm_$username();" class="btn btn-sm btn-danger">Confirm</button>`;
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
        $html = $html . <<<HTML
            <script>
                const resetPassword_$username = () => document.getElementById("$username-resetPassword").innerHTML = `<button onclick="resetPasswordConfirm_$username();" class="btn btn-sm btn-warning">Confirm</button>`;
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
            $html = $html . <<<HTML
                <td>
                    <button class="btn btn-sm btn-dark disabled">Make Admin</button>
                </td>
            HTML;
        } else {
            $html = $html . <<<HTML
                <script>
                    const makeAdmin_$username = () => document.getElementById("$username-makeAdmin").innerHTML = `<button onclick="makeAdminConfirm_$username();" class="btn btn-sm btn-dark">Confirm</button>`;
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
        $html = $html . <<<HTML
            </tr>
        HTML;
    }
    $html = $html . <<<HTML
        </table>
    HTML;
    return $html;
}

function renderAnimations()
{
    $db = $_SESSION["database"];
    $animations = array_map("mapToAnimationObject", array_map("mapToFirstItem", $db->select("AnimationID", "Animation", NULL, "AnimationID")));
    $html = <<<HTML
        <h3>Animations</h3>
        <table style="width: 100%;">
            <tr>
                <th>Animation ID</th>
                <th>Name</th>
                <th>Width</th>
                <th>Height</th>
                <th>Type</th>
                <th>Frames</th>
                <th>User</th>
                <th></th>
            </tr>
    HTML;
    for ($i = 0; $i < count($animations); ++$i) {
        $id = $animations[$i]->id;
        $name = $animations[$i]->name;
        $width = $animations[$i]->width;
        $height = $animations[$i]->height;
        $type = $animations[$i]->typeString;
        $frames = count($animations[$i]->frames);
        $user = $animations[$i]->user;
        $html = $html . <<<HTML
            <tr id="$id-row">
                <td>$id</td>
                <td>$name</td>
                <td>$width</td>
                <td>$height</td>
                <td>$type</td>
                <td>$frames</td>
                <td><a href="profile?user=$user->username">$user->username</a></td>
                <script>
                    const delete_$id = () => document.getElementById("$id-delete").innerHTML = `<button onclick="deleteConfirm_$id();" class="btn btn-sm btn-danger">Confirm</button>`;
                    const deleteConfirm_$id = () => {
                        const animationID = "$id";
                        $.post("Utils/Forms/deleteAnimation.php", { animationID }, () => {
                            document.getElementById("$id-row").style.display = "none";
                            document.getElementById("$id-delete").innerHTML = `<button onclick="delete_$id();" class="btn btn-sm btn-danger">Delete</button>`;
                        });
                    };
                </script>
                <td id="$id-delete">
                    <button onclick="delete_$id();" class="btn btn-sm btn-danger">Delete</button>
                </td>
            </tr>
        HTML;
    }
    $html = $html . <<<HTML
        </table>
    HTML;
    return $html;
}

function renderPosts()
{
    $db = $_SESSION["database"];
    $posts = array_map("mapToPostObject", array_map("mapToFirstItem", $db->select("PostID", "Post", NULL, "PostID")));
    $html = <<<HTML
        <h3>Posts</h3>
        <table style="width: 100%;">
            <tr>
                <th>Post ID</th>
                <th>Animation</th>
                <th>FPS</th>
                <th>Comments</th>
                <th>Likes</th>
                <th>Created At</th>
                <th>User</th>
                <th></th>
            </tr>
    HTML;
    for ($i = 0; $i < count($posts); ++$i) {
        $id = $posts[$i]->id;
        $animation = $posts[$i]->animationID;
        $fps = $posts[$i]->fps;
        $comments = count($posts[$i]->comments);
        $likes = count($posts[$i]->likedBy);
        $createdAt = $posts[$i]->createdAt;
        $user = $posts[$i]->user;
        $html = $html . <<<HTML
            <tr id="$id-row">
                <td>$id</td>
                <td>$animation</td>
                <td>$fps</td>
                <td>$comments</td>
                <td>$likes</td>
                <td>$createdAt</td>
                <td><a href="profile?user=$user->username">$user->username</a></td>
                <script>
                    const delete_$id = () => document.getElementById("$id-delete").innerHTML = `<button onclick="deleteConfirm_$id();" class="btn btn-sm btn-danger">Confirm</button>`;
                    const deleteConfirm_$id = () => {
                        const postID = "$id";
                        $.post("Utils/Forms/unShareAnimation.php", { postID }, () => {
                            document.getElementById("$id-row").style.display = "none";
                            document.getElementById("$id-delete").innerHTML = `<button onclick="delete_$id();" class="btn btn-sm btn-danger">Delete</button>`;
                        });
                    };
                </script>
                <td id="$id-delete">
                    <button onclick="delete_$id();" class="btn btn-sm btn-danger">Delete</button>
                </td>
            </tr>
        HTML;
    }
    $html = $html . <<<HTML
        </table>
    HTML;
    return $html;
}

function renderComments()
{
    $db = $_SESSION["database"];
    $comments = array_map("mapToCommentObject", array_map("mapToFirstItem", $db->select("CommentID", "Comment", NULL, "CommentID")));
    $html = <<<HTML
        <h3>Comments</h3>
        <table style="width: 100%;">
            <tr>
                <th>Comment ID</th>
                <th>Content</th>
                <th>Post</th>
                <th>Created At</th>
                <th>User</th>
                <th></th>
            </tr>
    HTML;
    for ($i = 0; $i < count($comments); ++$i) {
        $id = $comments[$i]->id;
        $content = $comments[$i]->content;
        $post = $comments[$i]->post->id;
        $createdAt = $comments[$i]->createdAt;
        $user = $comments[$i]->user;
        $html = $html . <<<HTML
            <tr id="$id-row">
                <td>$id</td>
                <td style="max-width: 500px; word-wrap: break-word;">$content</td>
                <td>$post</td>
                <td>$createdAt</td>
                <td><a href="profile?user=$user->username">$user->username</a></td>
                <script>
                    const delete_$id = () => document.getElementById("$id-delete").innerHTML = `<button onclick="deleteConfirm_$id();" class="btn btn-sm btn-danger">Confirm</button>`;
                    const deleteConfirm_$id = () => {
                        const commentID = "$id";
                        $.post("Utils/Forms/deleteComment.php", { commentID }, () => {
                            document.getElementById("$id-row").style.display = "none";
                            document.getElementById("$id-delete").innerHTML = `<button onclick="delete_$id();" class="btn btn-sm btn-danger">Delete</button>`;
                        });
                    };
                </script>
                <td id="$id-delete">
                    <button onclick="delete_$id();" class="btn btn-sm btn-danger">Delete</button>
                </td>
            </tr>
        HTML;
    }
    $html = $html . <<<HTML
        </table>
    HTML;
    return $html;
}

switch ($user->type) {
    case "admin":
        echo <<<HTML
            <h1>Microcontroller Animations</h1>
        HTML;
        echo renderUsers();
        echo renderAnimations();
        echo renderPosts();
        echo renderComments();
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
