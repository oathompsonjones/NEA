<?php
// Fetches the relevant info from the database.
$db = $_SESSION["database"];
$user = unserialize($_SESSION["user"]);
// Check if the user has searched for another user.
if (isset($_GET["searchUser"]) && !is_null($_GET["searchUser"]) && strlen($_GET["searchUser"]) > 0) {
    $getUser = $_GET["searchUser"];
    // Get every username.
    $allUsernames = array_map("mapToFirstItem", $db->Select("Username", "User", "NOT Type = 0"));
    // Search for the correct username.
    function searchForUsernames($value)
    {
        return strpos(strtolower($value), strtolower($_GET["searchUser"])) !== false;
    }
    $searchedUsernames = array_values(array_filter($allUsernames, "searchForUsernames"));
    // Count all of the usernames so this doesn't have to be done on each iteration.
    $searchedUsernameCount = count($searchedUsernames);
    // State if any results were found.
    if ($searchedUsernameCount > 0) echo <<<HTML
        <h2>Results for $getUser...</h2>
    HTML;
    else echo <<<HTML
        <h2>No results were found for $getUser.</h2>
    HTML;
    // Loop through each username.
    for ($i = 0; $i < $searchedUsernameCount; ++$i) {
        $thisUser = new User($searchedUsernames[$i]);
        $thisUsername = $thisUser->username;
        $thisBio = $thisUser->bio;
        // Render each result.
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
}
// Get an object for the requested user.
else if (isset($_GET["user"]) && !is_null($_GET["user"])) $user = new User($_GET["user"]);

// Get the user's basic info.
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

// Clear the get request if it's getting the user that is logged in.
if ($isLoggedInUser && (isset($_GET["user"]) || isset($_GET["searchUser"])) || isset($_GET["user"]) && strlen($_GET["user"]) === 0) echo <<<HTML
    <script>window.location.replace("/profile");</script>
HTML;

// Handle profile saving.
if (isset($_POST["saveProfile"]) && $_POST["saveProfile"] === "true") {
    // Get the new bio.
    $bio = isset($_POST["Bio"]) ? $_POST["Bio"] : $bio;
    // Get the old password input hash.
    $oldPasswordHashed = isset($_POST["OldPassword"]) && strlen($_POST["OldPassword"]) > 0 ? md5($_POST["OldPassword"]) : NULL;
    // Get the new password input hash.
    $newPasswordHashed = isset($_POST["NewPassword"]) && strlen($_POST["NewPassword"]) > 0 ? md5($_POST["NewPassword"]) : NULL;
    // Get the new password confirmation input hash.
    $confirmPasswordHashed = isset($_POST["ConfirmPassword"]) && strlen($_POST["ConfirmPassword"]) > 0 ? md5($_POST["ConfirmPassword"]) : NULL;
    // Check that there are no NULL values.
    $allNotNull = !is_null($oldPasswordHashed) && !is_null($newPasswordHashed) && !is_null($confirmPasswordHashed);
    $newHash = $passwordHash;
    // If the inputs are all given, they are checked.
    if ($allNotNull) {
        // Check that the old password is correct.
        if ($oldPasswordHashed !== $passwordHash) {
            echo <<<HTML
                <div class="alert alert-danger" role="alert">
                    Your old password is incorrect.
                </div>
            HTML;
            $_POST["editProfile"] = "true";
        }
        // Check that the new password is different to the old.
        else if ($oldPasswordHashed === $newPasswordHashed) {
            echo <<<HTML
                <div class="alert alert-danger" role="alert">
                    You must use a new password.
                </div>
            HTML;
            $_POST["editProfile"] = "true";
        }
        // Check that the new password is the same as the new password confirmation.
        else if ($newPasswordHashed !== $confirmPasswordHashed) {
            echo <<<HTML
                <div class="alert alert-danger" role="alert">
                    Your password confirmation does not match.
                </div>
            HTML;
            $_POST["editProfile"] = "true";
        }
        // Sets the hash.
        else $newHash = $confirmPasswordHashed;
    }
    // Updates the db.
    $db->update("User", ["PasswordHash", "Bio"], [$newHash, $bio], "Username = '$username'");
}
// Checks if the user wants to edit their profile.
if (isset($_POST["editProfile"]) && $_POST["editProfile"] === "true" && $isLoggedInUser) {
    // Escapes all double quotes.
    $bioValue = str_replace('"', '\"', $bio);
    // Outputs the editing form.
    echo <<<HTML
        <!-- Edit profile form. -->
        <form method="post">
            <!-- Bio input. -->
            <div class="form-floating">
                <input id="inputBio" name="Bio" type="text" class="form-control bg-dark text-light border-dark" placeholder="Bio" aria-label="Bio" aria-describedby="basic-addon1">
                <label for="inputBio">Bio</label>
                <!-- Fill in the original bio value. -->
                <script>document.getElementById("inputBio").value = "$bioValue";</script>
            </div>
            <br>
            <br>
            <!-- Old password input. -->
            <div class="form-floating">
                <input id="inputOldPassword" name="OldPassword" type="password" class="passwordInputs form-control bg-dark text-light border-dark" placeholder="Old Password" aria-label="Old Password" aria-describedby="basic-addon1">
                <label for="inputOldPassword">Old Password</label>
            </div>
            <br>
            <!-- New password input. -->
            <div class="form-floating">
                <input id="inputNewPassword" name="NewPassword" type="password" class="passwordInputs form-control bg-dark text-light border-dark" placeholder="New Password" aria-label="New Password" aria-describedby="basic-addon1">
                <label for="inputNewPassword">New Password</label>
            </div>
            <br>
            <!-- Confirm password input. -->
            <div class="form-floating">
                <input id="inputConfirmPassword" name="ConfirmPassword" type="password" class="passwordInputs form-control bg-dark text-light border-dark" placeholder="Confirm Password" aria-label="Confirm Password" aria-describedby="basic-addon1">
                <label for="inputConfirmPassword">Confirm Password</label>
            </div>
            <input name="saveProfile" type="text" style="display: none;" value="true">
            <br>
            <!-- Save button. -->
            <button class="btn btn-dark btn-sm" type="submit">Save</button>
        </form>
        <script>
            // Updates the form to determine if values are required or not.
            const updateRequired = () => {
                const passwordInputs = [...document.getElementsByClassName("passwordInputs")];
                const someHaveValues = passwordInputs.some((input) => input.value !== null && input.value.length > 0);
                passwordInputs.forEach((input) => input.required = someHaveValues);
            };
            // jQuery is cool :)
            $("#inputOldPassword").change(updateRequired);
            $("#inputNewPassword").change(updateRequired);
            $("#inputConfirmPassword").change(updateRequired);
            // Changed the delete button to a confirm button.
            const deleteProfile = () => document.getElementById("deleteProfile").innerHTML = `<button class="btn btn-danger btn-sm" type="button" onclick="deleteProfileConfirm();">Confirm</button>`;
            // Runs an AJAX request to delete the profile.
            const deleteProfileConfirm = () => $.post("Utils/Forms/deleteUser.php", { username: "$username" }, () => window.location.replace("/logout"));
        </script>
        <br>
        <!-- Delete button. -->
        <div id="deleteProfile">
            <button class="btn btn-danger btn-sm" type="button" onclick="deleteProfile();">Delete Account</button>
        </div>
    HTML;
}
// Check if the user wants to display their followers.
else if (isset($_POST["displayFollowers"]) && $_POST["displayFollowers"] === "true") {
    // Get the followers.
    $followers = is_null($user->followers) ? [] : $user->followers;
    // Count the followers.
    $followerCount = count($followers);
    // State if there are any followers.
    if ($followerCount > 0) echo <<<HTML
        <h2><a href="$profile?user=r->username">$user->username</a>'s followers...</h2>
    HTML;
    else echo <<<HTML
        <h2><a href="profile?user=$user->username">$user->username</a> has no followers.</h2>
    HTML;
    // Loop through each follower.
    for ($i = 0; $i < $followerCount; ++$i) {
        $thisUser = $followers[$i];
        $thisUsername = $thisUser->username;
        $thisBio = $thisUser->bio;
        // Render the user.
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
}
// Check if the user wants to display their following.
else if (isset($_POST["displayFollowing"]) && $_POST["displayFollowing"] === "true") {
    // Get the following.
    $following = is_null($user->following) ? [] : $user->following;
    // Count the following.
    $followingCount = count($following);
    // State if there are any following,
    if ($followingCount > 0) echo <<<HTML
        <h2><a href="profile?user=$user->username">$user->username</a> follows...</h2>
    HTML;
    else echo <<<HTML
        <h2><a href="profile?user=$user->username">$user->username</a> does not follow anyone.</h2>
    HTML;
    // Loop through each following.
    for ($i = 0; $i < $followingCount; ++$i) {
        $thisUser = $following[$i];
        $thisUsername = $thisUser->username;
        $thisBio = $thisUser->bio;
        // Render the user.
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
}
// Render the normal profile page.
else {
    // This will store the HTML for the follow user button.
    $followButton = "";
    // Check that the profile is not for the currently logged in user.
    if (!$isLoggedInUser) {
        // Check if the logged in user is following the requested user.
        $isFollowing = in_array($loggedInUser->username, array_map("mapToUsernames", $user->followers));
        // Render the scripts needed to follow and unfollow a user.
        $followButton = <<<HTML
            <!-- Both scripts run AJAX requests to follow/unfollow the user. -->
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
        // Check if the follow or unfollow button should be rendered.
        $followButton .= ($isFollowing ? <<<HTML
            <!-- Unfollow Button -->
            <div id="followUserButton">
                <button onclick="unFollowUser();" class="btn btn-dark btn-sm" type="button" style="padding-left: 10px;">Unfollow</button>
            </div>
        HTML : <<<HTML
            <!-- Follow Button -->
            <div id="followUserButton">
                <button onclick="followUser();" class="btn btn-dark btn-sm" type="button" style="padding-left: 10px;">Follow</button>
            </div>
        HTML);
    }
    echo <<<HTML
        <div class="row row-cols-2">
            <!-- User info. -->
            <div class="col">
                <h3>$username</h3>
                <p>$bio</p>
            </div>
            <div class="col">
                <!-- Display the number of followers and link to a list. -->
                <h3 style="display: flex;">
                    <form method="post" id="followersForm">
                        <input style="display: none;" type="text" name="displayFollowers" value="true">
                        <a id="followersCount" href="javascript:{}" onclick="document.getElementById('followersForm').submit();">$followersCount Followers</a>
                    </form>
                    $followButton
                </h3>
                <!-- Display the number of following and link to a list. -->
                <h3>
                    <form method="post" id="followingForm">
                        <input style="display: none;" type="text" name="displayFollowing" value="true">
                        <a id="followingCount" href="javascript:{}" onclick="document.getElementById('followingForm').submit();">$followingCount Following</a>
                    </form>
                </h3>
                <!-- Display the number of posts. -->
                <h3>$postsCount Posts</h3>
    HTML;
    // Check that the user requested is the logged in user.
    if ($isLoggedInUser) echo <<<HTML
        <!-- Render the edit profile button. -->
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
    // Count the posts so that this isn't recalculated on each iteration.
    $postCount = count($posts);
    // Loop through each post and render it.
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
