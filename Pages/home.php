<?php
// Get the required session variables.
$db = $_SESSION["database"];
$user = unserialize($_SESSION["user"]);

/**
 * Renders all Users from the database.
 * @return string
 */
function renderUsers()
{
    // Get the required session variables.
    $db = $_SESSION["database"];
    $user = unserialize($_SESSION["user"]);
    // Get an object for each user.
    $users = array_map("mapToUserObject", array_map("mapToFirstItem", $db->select("Username", "User", NULL, "Username")));
    // Create the table and headings to display each user.
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
    // Count the users so that it doesn't need recalculating on each iteration.
    $userCount = count($users);
    // Loop through each user.
    for ($i = 0; $i < $userCount; ++$i) {
        // Render the basic information.
        $username = $users[$i]->username;
        $passwordHash = $users[$i]->passwordHash;
        $resetPasswordHash = md5($username);
        $type = $users[$i]->type;
        $animations = count($users[$i]->animations);
        $posts = count($users[$i]->posts);
        $followers = count($users[$i]->followers);
        $following = count($users[$i]->following);
        $html .= <<<HTML
            <tr id="$username-row">
                <!-- Links to the user's profile. -->
                <td><a href="profile?user=$username">$username</a></td>
                <td id="$username-password">$passwordHash</td>
                <td id="$username-type">$type</td>
                <td>$animations</td>
                <td>$posts</td>
                <td>$followers</td>
                <td>$following</td>
        HTML;
        // Do not allow an admin to delete themselves.
        if ($username === $user->username) {
            $html .= <<<HTML
                <td>
                    <button class="btn btn-sm btn-danger disabled">Delete</button>
                </td>
            HTML;
        } else {
            // Creates a button to delete the user.
            $html .= <<<HTML
                <script>
                    // Changes the delete button to a confirm button.
                    const delete_$username = () => document.getElementById("$username-delete").innerHTML = `<button onclick="deleteConfirm_$username();" class="btn btn-sm btn-danger">Confirm</button>`;
                    // Runs an AJAX request to delete the user.
                    const deleteConfirm_$username = () => {
                        const username = "$username";
                        $.post("Utils/Forms/deleteUser.php", { username }, () => {
                            document.getElementById("$username-row").style.display = "none";
                            document.getElementById("$username-delete").innerHTML = `<button onclick="delete_$username();" class="btn btn-sm btn-danger">Delete</button>`;
                        });
                    };
                </script>
                <!-- Adds the delete button to the table. -->
                <td id="$username-delete">
                    <button onclick="delete_$username();" class="btn btn-sm btn-danger">Delete</button>
                </td>
            HTML;
        }
        // Reset password button.
        $html .= <<<HTML
            <script>
                // Changes the reset button to a confirm button.
                const resetPassword_$username = () => document.getElementById("$username-resetPassword").innerHTML = `<button onclick="resetPasswordConfirm_$username();" class="btn btn-sm btn-warning">Confirm</button>`;
                // Runs an AJAX request to reset the password.
                const resetPasswordConfirm_$username = () => {
                    const username = "$username";
                    $.post("Utils/Forms/resetPassword.php", { username }, () => {
                        document.getElementById("$username-password").innerHTML = "$resetPasswordHash";
                        document.getElementById("$username-resetPassword").innerHTML = `<button onclick="resetPassword_$username();" class="btn btn-sm btn-warning">Reset Password</button>`;
                    });
                };
            </script>
            <!-- Adds the reset button to the table. -->
            <td id="$username-resetPassword">
                <button onclick="resetPassword_$username();" class="btn btn-sm btn-warning">Reset Password</button>
            </td>
        HTML;
        // Can't make an admin into an admin.
        if ($type === "admin") {
            $html .= <<<HTML
                <td>
                    <button class="btn btn-sm btn-dark disabled">Make Admin</button>
                </td>
            HTML;
        } else {
            // Make admin button.
            $html .= <<<HTML
                <script>
                    // Changes the make admin button to a confirm button.
                    const makeAdmin_$username = () => document.getElementById("$username-makeAdmin").innerHTML = `<button onclick="makeAdminConfirm_$username();" class="btn btn-sm btn-dark">Confirm</button>`;
                    // Runs an AJAX request to make the user an admin.
                    const makeAdminConfirm_$username = () => {
                        const username = "$username";
                        $.post("Utils/Forms/makeAdmin.php", { username }, () => {
                            document.getElementById("$username-type").innerHTML = "admin";
                            document.getElementById("$username-makeAdmin").innerHTML = `<button class="btn btn-sm btn-dark disabled">Make Admin</button>`;
                        });
                    };
                </script>
                <!-- Adds the admin button to the table. -->
                <td id="$username-makeAdmin">
                    <button onclick="makeAdmin_$username();" class="btn btn-sm btn-dark">Make Admin</button>
                </td>
            HTML;
        }
        $html .= <<<HTML
            </tr>
        HTML;
    }
    $html .= <<<HTML
        </table>
    HTML;
    return $html;
}

/**
 * Renders all Animations from the database.
 * @return string
 */
function renderAnimations()
{
    // Get the required session variables.
    $db = $_SESSION["database"];
    // Get an object for each animation.
    $animations = array_map("mapToAnimationObject", array_map("mapToFirstItem", $db->select("AnimationID", "Animation", NULL, "AnimationID")));
    // Create the table and headings to display each animation.
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
    // Count the animations so that it doesn't need recalculating on each iteration.
    $animationCount = count($animations);
    // Loop through each animation.
    for ($i = 0; $i < $animationCount; ++$i) {
        // Render the basic information.
        $id = $animations[$i]->id;
        $name = $animations[$i]->name;
        $width = $animations[$i]->width;
        $height = $animations[$i]->height;
        $type = $animations[$i]->typeString;
        $frames = count($animations[$i]->frames);
        $user = $animations[$i]->user;
        $html .= <<<HTML
            <tr id="$id-row">
                <td>$id</td>
                <td>$name</td>
                <td>$width</td>
                <td>$height</td>
                <td>$type</td>
                <td>$frames</td>
                <!-- Links to the user's profile. -->
                <td><a href="profile?user=$user->username">$user->username</a></td>
                <script>
                    // Changes the delete button to a confirm button.
                    const delete_$id = () => document.getElementById("$id-delete").innerHTML = `<button onclick="deleteConfirm_$id();" class="btn btn-sm btn-danger">Confirm</button>`;
                    // Runs an AJAX request to delete the animation.
                    const deleteConfirm_$id = () => {
                        const animationID = "$id";
                        $.post("Utils/Forms/deleteAnimation.php", { animationID }, () => {
                            document.getElementById("$id-row").style.display = "none";
                            document.getElementById("$id-delete").innerHTML = `<button onclick="delete_$id();" class="btn btn-sm btn-danger">Delete</button>`;
                        });
                    };
                </script>
                <!-- Delete button. -->
                <td id="$id-delete">
                    <button onclick="delete_$id();" class="btn btn-sm btn-danger">Delete</button>
                </td>
            </tr>
        HTML;
    }
    $html .= <<<HTML
        </table>
    HTML;
    return $html;
}

/**
 * Renders all Posts from the database.
 * @return string
 */
function renderPosts()
{
    // Get the required session variables.
    $db = $_SESSION["database"];
    // Get an object for each post.
    $posts = array_map("mapToPostObject", array_map("mapToFirstItem", $db->select("PostID", "Post", NULL, "PostID")));
    // Create the table and headings to display each post.
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
    // Count the posts so that it doesn't need recalculating on each iteration.
    $postCount = count($posts);
    // Loop through each post.
    for ($i = 0; $i < $postCount; ++$i) {
        $id = $posts[$i]->id;
        $animation = $posts[$i]->animationID;
        $fps = $posts[$i]->fps;
        $comments = count($posts[$i]->comments);
        $likes = count($posts[$i]->likedBy);
        $createdAt = $posts[$i]->createdAt;
        $user = $posts[$i]->user;
        $html .= <<<HTML
            <tr id="$id-row">
                <td>$id</td>
                <td>$animation</td>
                <td>$fps</td>
                <td>$comments</td>
                <td>$likes</td>
                <td>$createdAt</td>
                <!-- Links to the user's profile. -->
                <td><a href="profile?user=$user->username">$user->username</a></td>
                <script>
                    // Changes the delete button to a confirm button.
                    const delete_$id = () => document.getElementById("$id-delete").innerHTML = `<button onclick="deleteConfirm_$id();" class="btn btn-sm btn-danger">Confirm</button>`;
                    // Runs an AJAX request to delete the post.
                    const deleteConfirm_$id = () => {
                        const postID = "$id";
                        $.post("Utils/Forms/unShareAnimation.php", { postID }, () => {
                            document.getElementById("$id-row").style.display = "none";
                            document.getElementById("$id-delete").innerHTML = `<button onclick="delete_$id();" class="btn btn-sm btn-danger">Delete</button>`;
                        });
                    };
                </script>
                <!-- Delete button. -->
                <td id="$id-delete">
                    <button onclick="delete_$id();" class="btn btn-sm btn-danger">Delete</button>
                </td>
            </tr>
        HTML;
    }
    $html .= <<<HTML
        </table>
    HTML;
    return $html;
}

/**
 * Renders all Comments from the database.
 * @return string
 */
function renderComments()
{
    // Get the required session variables.
    $db = $_SESSION["database"];
    // Get an object for each comment.
    $comments = array_map("mapToCommentObject", array_map("mapToFirstItem", $db->select("CommentID", "Comment", NULL, "CommentID")));
    // Create the table and headings to display each comment.
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
    // Count the posts so that it doesn't need recalculating on each iteration.
    $commentCount = count($comments);
    // Loop through each comment.
    for ($i = 0; $i < $commentCount; ++$i) {
        $id = $comments[$i]->id;
        $content = $comments[$i]->content;
        $post = $comments[$i]->post->id;
        $createdAt = $comments[$i]->createdAt;
        $user = $comments[$i]->user;
        $html .= <<<HTML
            <tr id="$id-row">
                <td>$id</td>
                <td style="max-width: 500px; word-wrap: break-word;">$content</td>
                <td>$post</td>
                <td>$createdAt</td>
                <!-- Links to the user's profile. -->
                <td><a href="profile?user=$user->username">$user->username</a></td>
                <script>
                    // Changes the delete button to a confirm button.
                    const delete_$id = () => document.getElementById("$id-delete").innerHTML = `<button onclick="deleteConfirm_$id();" class="btn btn-sm btn-danger">Confirm</button>`;
                    // Runs an AJAX request to delete the comment.
                    const deleteConfirm_$id = () => {
                        const commentID = "$id";
                        $.post("Utils/Forms/deleteComment.php", { commentID }, () => {
                            document.getElementById("$id-row").style.display = "none";
                            document.getElementById("$id-delete").innerHTML = `<button onclick="delete_$id();" class="btn btn-sm btn-danger">Delete</button>`;
                        });
                    };
                </script>
                <!-- Delete button. -->
                <td id="$id-delete">
                    <button onclick="delete_$id();" class="btn btn-sm btn-danger">Delete</button>
                </td>
            </tr>
        HTML;
    }
    $html .= <<<HTML
        </table>
    HTML;
    return $html;
}

/**
 * Renders all Classes from the database.
 * @return string
 */
function renderClasses()
{
    // Get the required session variables.
    $db = $_SESSION["database"];
    // Get an object for each class.
    $classes = array_map("mapToGroupObject", array_map("mapToFirstItem", $db->select("ClassID", "Class", NULL, "ClassID")));
    // Create the table and headings to display each class.
    $html = <<<HTML
        <h3>Classes</h3>
        <table style="width: 100%;">
            <tr>
                <th>Class ID</th>
                <th>Name</th>
                <th>Chat Enabled</th>
                <th>Students</th>
                <th>Teachers</th>
                <th>Chat Messages</th>
                <th>Muted Users</th>
                <th>Assignments</th>
                <th></th>
            </tr>
    HTML;
    // Count the posts so that it doesn't need recalculating on each iteration.
    $classCount = count($classes);
    // Loop through each class.
    for ($i = 0; $i < $classCount; ++$i) {
        $id = $classes[$i]->id;
        $name = $classes[$i]->name;
        $chatEnabled = $classes[$i]->chatEnabled;
        $students = count($classes[$i]->students);
        $teachers = count($classes[$i]->teachers);
        $chatMessages = count($classes[$i]->chatMessages);
        $mutedUsers = count($classes[$i]->mutedUsers);
        $assignments = count($classes[$i]->assignments);
        $html .= <<<HTML
            <tr id="$id-row">
                <td>$id</td>
                <td style="max-width: 500px; word-wrap: break-word;">$name</td>
                <td>$chatEnabled</td>
                <td>$students</td>
                <td>$teachers</td>
                <td>$chatMessages</td>
                <td>$mutedUsers</td>
                <td>$assignments</td>
                <script>
                    // Changes the delete button to a confirm button.
                    const delete_$id = () => document.getElementById("$id-delete").innerHTML = `<button onclick="deleteConfirm_$id();" class="btn btn-sm btn-danger">Confirm</button>`;
                    // Runs an AJAX request to delete the class.
                    const deleteConfirm_$id = () => {
                        const classID = "$id";
                        $.post("Utils/Forms/deleteClass.php", { classID }, () => {
                            document.getElementById("$id-row").style.display = "none";
                            document.getElementById("$id-delete").innerHTML = `<button onclick="delete_$id();" class="btn btn-sm btn-danger">Delete</button>`;
                        });
                    };
                </script>
                <!-- Delete button. -->
                <td id="$id-delete">
                    <button onclick="delete_$id();" class="btn btn-sm btn-danger">Delete</button>
                </td>
            </tr>
        HTML;
    }
    $html .= <<<HTML
        </table>
    HTML;
    return $html;
}

// Check which kind of user it is.
switch ($user->type) {
    case "admin":
        // Render the heading.
        echo <<<HTML
            <h1>Microcontroller Animations</h1>
        HTML;
        // Call each render function from above.
        echo renderUsers();
        echo renderAnimations();
        echo renderPosts();
        echo renderComments();
        echo renderClasses();
        break;
    case "teacher":
    case "student":
        // Teachers and students get the same site.
        // Get an array containing each post to show in the feed.
        $feedPosts = [];
        $feedUsers = $user->following;
        array_push($feedUsers, $user);
        // Count each user so this doesn't need to be recalculated on each iteration.
        $feedUserCount = count($feedUsers);
        // Loop through each user.
        for ($i = 0; $i < $feedUserCount; ++$i) {
            $thisUser = $feedUsers[$i];
            $thisPosts = $thisUser->posts;
            // Count each post so this
            $thisPostCount = count($thisPosts);
            // Add each post to the array.
            for ($j = 0; $j < $thisPostCount; ++$j) array_push($feedPosts, $thisPosts[$j]);
        }
        // Sort the post by when they were created.
        usort($feedPosts, "sortByCreatedAt");
        echo <<<HTML
            <div style="display: flex; height: 100%;">
                <div style="flex: 40%; max-width: 40%; word-wrap: break-word;">
                    <div class="card-group" style="display: block; overflow: auto; height: 100%;">
                        <style>.post { display: none; }</style>
        HTML;
        // Render each post.
        for ($i = 0; $i < min(count($feedPosts), 100); ++$i) echo $feedPosts[$i]->render() . "<br>";
        echo <<<HTML
                        <style>.post { display: block; }</style>
                    </div>
                </div>
                <div style="flex: 3%; max-width: 3%;"></div>
        HTML;
        // Get all of the user's animations.
        $allAnimations = is_null($user->animations) ? [] : $user->animations;
        $animations = "";
        // Render the first 3.
        for ($i = 0; $i < min(3, count($allAnimations)); ++$i) {
            $html = $allAnimations[$i]->render(false);
            $animations .= <<<HTML
                <div class="col">$html</div>
            HTML;
        }
        // If there are less than 3 animations, render a button to create a new one.
        if (count($allAnimations) < 3) $animations .= <<<HTML
            <div class="col">
                <div class="card bg-dark text-light" style="height: 100%; min-height: 150px">
                    <div class="card-body" style="display: flex; height: 100%">
                        <form action="editor" style="width: 100%; height: 100%;">
                            <button type="submit" class="btn btn-lg btn-dark" style="width: 100%; height: 100%;">New</button>
                        </form>
                    </div>
                </div>
            </div>
        HTML;
        // Get all of the user's classes.
        $allClasses = is_null($user->classes) ? [] : $user->classes;
        $classes = "";
        // Render the first 3.
        for ($i = 0; $i < min(3, count($allClasses)); ++$i) {
            $html = $allClasses[$i]->render();
            $classes .= <<<HTML
                <div class="col">$html</div>
            HTML;
        }
        // If there are less than 3 classes, render a button to create/join a new one.
        if (count($allClasses) < 3) $classes .= <<<HTML
            <div class="col">
                <div class="card bg-dark text-light" style="height: 100%; min-height: 100px">
                    <div class="card-body" style="display: flex; height: 100%">
                        <form action="class" style="width: 100%; height: 100%;">
                            <button type="submit" class="btn btn-lg btn-dark" style="width: 100%; height: 100%;">New</button>
                        </form>
                    </div>
                </div>
            </div>
        HTML;
        // Render the user's info.
        echo <<<HTML
                <div style="flex: 57%; max-width: 57%; word-wrap: break-word;">
                    <h1>Microcontroller Animations</h1>
                    <div style="height: 15%; overflow: hidden;">
                        <h2><a href="profile">$user->username</a></h2>
                        <p>$user->bio</p>
                    </div>
                    <hr>
                    <div>
                        <h2><a href="class">Classes</a></h2>
                        <div class="row row-cols-3" style="width: 100%;">$classes</div>
                    </div>
                    <hr>
                    <div>
                        <h2><a href="animations">Animations</a></h2>
                        <div class="row row-cols-3" style="width: 100%;">$animations</div>
                    </div>
                </div>
            </div>
        HTML;
        break;
    default:
        require_once "Page/logout.php";
}
