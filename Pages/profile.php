<?php
$db = $_SESSION["database"];
$user = unserialize($_SESSION["user"]);
if (isset($_GET["searchUser"]) && !is_null($_GET["searchUser"]) && strlen($_GET["searchUser"]) > 0) {
    $getUser = $_GET["searchUser"];
    $allUsernames = array_map("mapToFirstItem", $db->Select("Username", "User", "NOT Type = 0"));
    function searchForUsernames($value)
    {
        return strpos(strtolower($value), strtolower($_GET["searchUser"])) !== false;
    }
    $searchedUsernames = array_values(array_filter($allUsernames, "searchForUsernames"));
    $searchedUsernameCount = count($searchedUsernames);
    if ($searchedUsernameCount > 0) echo <<<HTML
        <h2>Results for $getUser...</h2>
    HTML;
    else echo <<<HTML
        <h2>No results were found for $getUser.</h2>
    HTML;
    for ($i = 0; $i < $searchedUsernameCount; ++$i) {
        $thisUser = new User($searchedUsernames[$i]);
        $thisUsername = $thisUser->username;
        $thisBio = $thisUser->bio;
        echo <<<HTML
            <a href="profile?user=$thisUsername">
                <div class="card bg-dark">
                    <div class="card-body">
                        <h5 class="card-title">$thisUsername</h5>
                        $thisBio
                    </div>
                </div>
            </a>
            <br>
        HTML;
    }
    exit;
} else if (isset($_GET["user"]) && !is_null($_GET["user"])) $user = new User($_GET["user"]);
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

if ($isLoggedInUser && (isset($_GET["user"]) || isset($_GET["searchUser"])) || isset($_GET["user"]) && strlen($_GET["user"]) === 0) echo <<<HTML
    <script>window.location.replace("/profile");</script>
HTML;

if (isset($_POST["saveProfile"]) && $_POST["saveProfile"] === "true") {
    $bio = isset($_POST["Bio"]) ? $_POST["Bio"] : $bio;
    $oldPasswordHashed = isset($_POST["OldPassword"]) && strlen($_POST["OldPassword"]) > 0 ? md5($_POST["OldPassword"]) : NULL;
    $newPasswordHashed = isset($_POST["NewPassword"]) && strlen($_POST["NewPassword"]) > 0 ? md5($_POST["NewPassword"]) : NULL;
    $confirmPasswordHashed = isset($_POST["ConfirmPassword"]) && strlen($_POST["ConfirmPassword"]) > 0 ? md5($_POST["ConfirmPassword"]) : NULL;
    $allNotNull = !is_null($oldPasswordHashed) && !is_null($newPasswordHashed) && !is_null($confirmPasswordHashed);
    $newHash = $passwordHash;
    if ($allNotNull) {
        if ($oldPasswordHashed !== $passwordHash) {
            echo <<<HTML
                <div class="alert alert-danger" role="alert">
                    Your old password is incorrect.
                </div>
            HTML;
            $_POST["editProfile"] = "true";
        } else if ($oldPasswordHashed === $newPasswordHashed) {
            echo <<<HTML
                <div class="alert alert-danger" role="alert">
                    You must use a new password.
                </div>
            HTML;
            $_POST["editProfile"] = "true";
        } else if ($newPasswordHashed !== $confirmPasswordHashed) {
            echo <<<HTML
                <div class="alert alert-danger" role="alert">
                    Your password confirmation does not match.
                </div>
            HTML;
            $_POST["editProfile"] = "true";
        } else $newHash = $confirmPasswordHashed;
    }
    $db->update("User", ["PasswordHash", "Bio"], [$newHash, $bio], "Username = '$username'");
}

if (isset($_POST["editProfile"]) && $_POST["editProfile"] === "true" && $isLoggedInUser) {
    $bioValue = str_replace('"', '\"', $bio);
    echo <<<HTML
        <form method="post">
            <div class="form-floating">
                <input id="inputBio" name="Bio" type="text" class="form-control bg-dark text-light border-dark" placeholder="Bio" aria-label="Bio" aria-describedby="basic-addon1">
                <label for="inputBio">Bio</label>
                <script>document.getElementById("inputBio").value = "$bioValue";</script>
            </div>
            <br>
            <br>
            <div class="form-floating">
                <input id="inputOldPassword" name="OldPassword" type="password" class="passwordInputs form-control bg-dark text-light border-dark" placeholder="Old Password" aria-label="Old Password" aria-describedby="basic-addon1">
                <label for="inputOldPassword">Old Password</label>
            </div>
            <br>
            <div class="form-floating">
                <input id="inputNewPassword" name="NewPassword" type="password" class="passwordInputs form-control bg-dark text-light border-dark" placeholder="New Password" aria-label="New Password" aria-describedby="basic-addon1">
                <label for="inputNewPassword">New Password</label>
            </div>
            <br>
            <div class="form-floating">
                <input id="inputConfirmPassword" name="ConfirmPassword" type="password" class="passwordInputs form-control bg-dark text-light border-dark" placeholder="Confirm Password" aria-label="Confirm Password" aria-describedby="basic-addon1">
                <label for="inputConfirmPassword">Confirm Password</label>
            </div>
            <input name="saveProfile" type="text" style="display: none;" value="true">
            <br>
            <button class="btn btn-dark btn-sm" type="submit">Save</button>
        </form>
        <script>
            const updateRequired = () => {
                const passwordInputs = [...document.getElementsByClassName("passwordInputs")];
                const someHaveValues = passwordInputs.some((input) => input.value !== null && input.value.length > 0);
                passwordInputs.forEach((input) => input.required = someHaveValues);
            };
            $("#inputOldPassword").change(updateRequired);
            $("#inputNewPassword").change(updateRequired);
            $("#inputConfirmPassword").change(updateRequired);
            
            const deleteProfile = () => document.getElementById("deleteProfile").innerHTML = `<button class="btn btn-danger btn-sm" type="button" onclick="deleteProfileConfirm();">Confirm</button>`;
            const deleteProfileConfirm = () => $.post("Utils/Forms/deleteUser.php", { username: "$username" }, () => window.location.replace("/logout"));
        </script>
        <br>
        <div id="deleteProfile">
            <button class="btn btn-danger btn-sm" type="button" onclick="deleteProfile();">Delete Account</button>
        </div>
    HTML;
} else if (isset($_POST["deleteProfile"]) && $_POST["deleteProfile"] === "true" && $isLoggedInUser) {
    if (isset($_POST["deleteProfileConfirm"]) && $_POST["deleteProfileConfirm"] === "true" && $isLoggedInUser) {
        $user->delete();
        require_once "Pages/logout.php";
    } else {
        echo <<<HTML
            <div class="alert alert-danger" role="alert" style="display: flex;">
                <p>Are you sure you want to permanently delete your account? This can <strong>not</strong> be undone. All data will be erased.</p>
                <form method="post" style="padding-left: 5px;">
                    <input name="deleteProfile" type="text" style="display: none;" value="true">
                    <input name="deleteProfileConfirm" type="text" style="display: none;" value="true">
                    <button class="btn btn-danger btn-sm" type="submit" style="float: right;">Yes</button>
                </form>
                <form method="post" style="padding-left: 5px;">
                    <button class="btn btn-danger btn-sm" type="submit" style="float: right;">No</button>
                </form>
            </div>
        HTML;
    }
} else if (isset($_POST["displayFollowers"]) && $_POST["displayFollowers"] === "true") {
    $followers = is_null($user->followers) ? [] : $user->followers;
    $followerCount = count($followers);
    if ($followerCount > 0) echo <<<HTML
        <h2><a href="$profile?user=r->username">$user->username</a>'s followers...</h2>
    HTML;
    else echo <<<HTML
        <h2><a href="profile?user=$user->username">$user->username</a> has no followers.</h2>
    HTML;
    for ($i = 0; $i < $followerCount; ++$i) {
        $thisUser = $followers[$i];
        $thisUsername = $thisUser->username;
        $thisBio = $thisUser->bio;
        echo <<<HTML
            <a href="profile?user=$thisUsername">
                <div class="card bg-dark">
                    <div class="card-body">
                        <h5 class="card-title">$thisUsername</h5>
                        $thisBio
                    </div>
                </div>
            </a>
            <br>
        HTML;
    }
} else if (isset($_POST["displayFollowing"]) && $_POST["displayFollowing"] === "true") {
    $following = is_null($user->following) ? [] : $user->following;
    $followingCount = count($following);
    if ($followingCount > 0) echo <<<HTML
        <h2><a href="profile?user=$user->username">$user->username</a> follows...</h2>
    HTML;
    else echo <<<HTML
        <h2><a href="profile?user=$user->username">$user->username</a> does not follow anyone.</h2>
    HTML;
    for ($i = 0; $i < $followingCount; ++$i) {
        $thisUser = $following[$i];
        $thisUsername = $thisUser->username;
        $thisBio = $thisUser->bio;
        echo <<<HTML
            <a href="profile?user=$thisUsername">
                <div class="card bg-dark">
                    <div class="card-body">
                        <h5 class="card-title">$thisUsername</h5>
                        $thisBio
                    </div>
                </div>
            </a>
            <br>
        HTML;
    }
} else {
    $followButton = "";
    if (!$isLoggedInUser) {
        $isFollowing = in_array($loggedInUser->username, array_map("mapToUsernames", $user->followers));
        $followButton = <<<HTML
            <script>
                const unFollowUser = () => {
                    const loggedInUsername = '$loggedInUser->username';
                    const username = '$username';
                    $.post("Utils/Forms/unFollowUser.php", { loggedInUsername, username }, () => {
                        document.getElementById("followUserButton").innerHTML = `<button onclick="followUser();" class="btn btn-dark btn-sm" type="button" style="padding-left: 10px;">Follow</button>`;
                        document.getElementById("followersCount").innerHTML = (parseInt(document.getElementById("followersCount").innerHTML.split(" ")[0]) - 1) + " Followers";
                    });
                };
                const followUser = () => {
                    const loggedInUsername = '$loggedInUser->username';
                    const username = '$username';
                    $.post("Utils/Forms/followUser.php", { loggedInUsername, username }, () => {
                        document.getElementById("followUserButton").innerHTML = `<button onclick="unFollowUser();" class="btn btn-dark btn-sm" type="button" style="padding-left: 10px;">Unfollow</button>`;
                        document.getElementById("followersCount").innerHTML = (parseInt(document.getElementById("followersCount").innerHTML.split(" ")[0]) + 1) + " Followers";
                    });
                };
            </script>
        HTML;
        if ($isFollowing) {
            $followButton .= ($isFollowing ? <<<HTML
                <div id="followUserButton">
                    <button onclick="unFollowUser();" class="btn btn-dark btn-sm" type="button" style="padding-left: 10px;">Unfollow</button>
                </div>
            HTML : <<<HTML
                <div id="followUserButton">
                    <button onclick="followUser();" class="btn btn-dark btn-sm" type="button" style="padding-left: 10px;">Follow</button>
                </div>
            HTML);
        }
    }
    echo <<<HTML
        <div class="row row-cols-2">
            <div class="col">
                <h3>$username</h3>
                <p>$bio</p>
            </div>
            <div class="col">
                <h3 style="display: flex;">
                    <form method="post" id="followersForm">
                        <input style="display: none;" type="text" name="displayFollowers" value="true">
                        <a id="followersCount" href="javascript:{}" onclick="document.getElementById('followersForm').submit();">$followersCount Followers</a>
                    </form>
                    $followButton
                </h3>
                <h3>
                    <form method="post" id="followingForm">
                        <input style="display: none;" type="text" name="displayFollowing" value="true">
                        <a id="followingCount" href="javascript:{}" onclick="document.getElementById('followingForm').submit();">$followingCount Following</a>
                    </form>
                </h3>
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
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <style>.post { display: none; }</style>
    HTML;
    $postCount = count($posts);
    for ($i = 0; $i < $postCount; ++$i) {
        $html = $posts[$i]->render();
        echo <<<HTML
            <div class="col">$html</div>
        HTML;
    }
    echo <<<HTML
            <style>.post { display: block; }</style>
        </div>
    HTML;
}
