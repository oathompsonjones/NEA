<?php
function mapIconsSrc($value)
{
    return "data:image/png;base64,$value";
}
function flattenUsernamesArray($value)
{
    return $value[0];
}
$db = $_SESSION["database"];
$user = unserialize($_SESSION["user"]);
if (isset($_GET["user"]) && !is_null($_GET["user"])) {
    $getUser = $_GET["user"];
    $dbUser = $db->select("*", "User", "Username = '$getUser'")[0][0];
    if (!is_null($dbUser)) $user = new User($_GET["user"]);
    else {
        $allUsernames = array_map("flattenUsernamesArray", $db->Select("Username", "User"));
        function searchForUsernames($value)
        {
            return strpos(strtolower($value), strtolower($_GET["user"])) !== false;
        }
        $searchedUsernames = array_values(array_filter($allUsernames, "searchForUsernames"));
        echo <<<HTML
            <h2>User not found</h2>
        HTML;
        if (count($searchedUsernames) > 0) echo <<<HTML
            <h5>Here are some other suggestions...</h5>
        HTML;
        else echo <<<HTML
            <h5>No search results were found...</h5>
        HTML;
        for ($i = 0; $i < count($searchedUsernames); ++$i) {
            echo <<<HTML
                <h4><a href="profile?user=$searchedUsernames[$i]" style="text-decoration: none;">$searchedUsernames[$i]</a></h4><br>
            HTML;
        }
        exit;
    }
}
$passwordHash = $user->passwordHash;
$username = $user->username;
$bio = $user->bio;
$type = $user->type;
$followers = $user->followers;
$followersCount = count($user->followers);
$following = $user->following;
$followingCount = count($user->following);
$posts = $user->posts;
$postsCount = count($user->posts);
$loggedInUser = unserialize($_SESSION["user"]);
$isLoggedInUser = $loggedInUser->username === $username;

if ($isLoggedInUser && isset($_GET["user"])) echo <<<HTML
    <script>window.location.replace("/profile");</script>
HTML;

$saveProfile = isset($_POST["saveProfile"]) && $_POST["saveProfile"] === "true";
if ($saveProfile) {
    $bio = isset($_POST["Bio"]) ? $_POST["Bio"] : $bio;
    $passwordHash = isset($_POST["Password"]) && strlen($_POST["Password"]) > 0 ? md5($_POST["Password"]) : $passwordHash;
    $db->update("User", ["PasswordHash", "Bio"], [$passwordHash, $bio], "Username = '$username'");
}

$followUser = isset($_POST["followUser"]) && $_POST["followUser"] === "true";
if ($followUser) {
    $loggedInUser->followUser($user->username);
    require "Include/clearPost.inc";
}

$unFollowUser = isset($_POST["unFollowUser"]) && $_POST["unFollowUser"] === "true";
if ($unFollowUser) {
    $loggedInUser->unfollowUser($user->username);
    require "Include/clearPost.inc";
}

$editProfile = isset($_POST["editProfile"]) && $_POST["editProfile"] === "true";
$deleteProfile = isset($_POST["deleteProfile"]) && $_POST["deleteProfile"] === "true";
$deleteProfileConfirm = isset($_POST["deleteProfileConfirm"]) && $_POST["deleteProfileConfirm"] === "true";
if ($editProfile) {
    echo <<<HTML
        <form method="post">
            <div class="form-floating">
                <input id="inputBio" name="Bio" type="text" class="form-control bg-dark text-light border-dark" placeholder="Bio" aria-label="Bio" aria-describedby="basic-addon1" value="$bio">
                <label for="inputBio">Bio</label>
            </div>
            <br>
            <div class="form-floating">
                <input id="inputPassword" name="Password" type="password" class="form-control bg-dark text-light border-dark" placeholder="Password" aria-label="Password" aria-describedby="basic-addon1">
                <label for="inputPassword">Change Password</label>
            </div>
            <input name="saveProfile" type="text" style="display: none;" value="true">
            <br>
            <button class="btn btn-dark btn-sm" type="submit">Save</button>
        </form>
        <form method="post">
            <input name="deleteProfile" type="text" style="display: none;" value="true">
            <button class="btn btn-danger btn-sm" type="submit">Delete Account</button>
        </form>
    HTML;
} else if ($deleteProfile) {
    if ($deleteProfileConfirm) {
        $user->delete();
        require_once "Pages/logout.php";
    } else {
        echo <<<HTML
            <div class="alert alert-danger" role="alert">
                Are you sure you want to permanently delete your account? This can <strong>not</strong> be undone. All data will be erased.
            </div>
            <form method="post">
                <input name="deleteProfile" type="text" style="display: none;" value="true">
                <input name="deleteProfileConfirm" type="text" style="display: none;" value="true">
                <button class="btn btn-danger btn-sm" type="submit">Delete Account</button>
            </form>
        HTML;
    }
} else {
    $followButton = "";
    if (!$isLoggedInUser) {
        $dbRes = $db->select("*", "UserFollowing", "Username = '$loggedInUser->username' AND FollowingUsername = '$user->username'")[0][0];
        if (is_null($dbRes)) {
            $followButton = <<<HTML
                <form method="post" style="padding-left: 10px;">
                    <input name="followUser" type="boolean" style="display: none;" value="true">
                    <button class="btn btn-dark btn-sm" type="submit">Follow</button>
                </form>
            HTML;
        } else {
            $followButton = <<<HTML
                <form method="post" style="padding-left: 10px;">
                    <input name="unFollowUser" type="boolean" style="display: none;" value="true">
                    <button class="btn btn-dark btn-sm" type="submit">Unfollow</button>
                </form>
            HTML;
        }
    }
    echo <<<HTML
        <div style="display: flex;">
            <div style="flex: 50%; max-width: 50%; word-wrap: break-word;">
                <h3>$username</h3>
                <p>$bio</p>
            </div>
            <div style="flex: 50%; max-width: 50%; word-wrap: break-word;">
                <h3 style="display: flex;">$followersCount Followers $followButton</h3>
                <h3>$followingCount Following</h3>
                <h3>$postsCount Posts</h3>
    HTML;
    if ($isLoggedInUser) echo <<<HTML
        <form method="post">
            <input name="editProfile" type="boolean" style="display: none;" value="true">
            <button class="btn btn-dark btn-sm" type="submit">Edit Profile</button>
        </form>
    HTML;
    echo <<<HTML
            </div>
        </div>
        <hr>
    HTML;
    echo <<<HTML
        <div class="row row-cols-1 row-cols-md-5 g-4">
            <script>
                const playback = (index, frames, fps) => {
                    const img = document.getElementById(index.toString() + "-icon");
                    const card = document.getElementById(index.toString() + "-card");
                    const buttons = document.getElementById(index.toString() + "-buttons");
                    let i = 0;
                    card.className = "";
                    buttons.style.display = "none";
                    const interval = setInterval(() => img.src = frames[i++], 1000 / fps);
                    setTimeout(() => {
                        clearInterval(interval);
                        img.src = frames[0];
                        card.className = "icon";
                        buttons.style.display = "block";
                    }, 1000 * (frames.length + 1) / fps);
                };
            </script>
    HTML;
    for ($i = 0; $i < count($posts); ++$i) {
        $post = $posts[$i];
        $name = $post->animation->name;
        $type = $post->animation->typeString;
        $fps = $post->fps;
        $title = !is_null($name)
            ? <<<HTML
                $name<br><span class='badge rounded-pill bg-secondary'>$type - $fps FPS</span>
            HTML : "";
        $icons = !is_null($post)
            ? array_map("mapIconsSrc", $post->animation->generateFrameIcons())
            : [];
        $jsonIcons = json_encode($icons);
        $firstIcon = $icons[0];
        echo <<<HTML
            <div class="col">
                <div class="card text-white bg-dark">
                    <div id="$i-card" class="icon">
                        <img src="$firstIcon" class="card-img-top" id="$i-icon">
                        <div id="$i-buttons" class="buttons">
                            <button class="btn btn-secondary btn-lg" data-toggle="tooltip" data-placement="top" title="Play the animation" onclick='playback($i, $jsonIcons, $fps);'>Play</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">$title</h5>
                    </div>
                </div>
            </div>
        HTML;
    }
    echo <<<HTML
        </div>
    HTML;
}
