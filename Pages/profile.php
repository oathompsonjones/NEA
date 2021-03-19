<?php
$db = $_SESSION["database"];
$user = unserialize($_SESSION["user"]);
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

$saveProfile = isset($_POST["saveProfile"]);
if ($saveProfile) {
    $bio = isset($_POST["Bio"]) ? $_POST["Bio"] : $bio;
    $passwordHash = isset($_POST["Password"]) && strlen($_POST["Password"]) > 0 ? md5($_POST["Password"]) : $passwordHash;
    $db->update("User", ["PasswordHash", "Bio"], [$passwordHash, $bio], "Username = '$username'");
}

$editProfile = isset($_POST["editProfile"]) && $_POST["editProfile"] === "true";
$deleteProfile = isset($_POST["deleteProfile"]) && $_POST["deleteProfile"] === "true";
$deleteProfileConfirm = isset($_POST["deleteProfileConfirm"]) && $_POST["deleteProfileConfirm"] === "true";
if ($editProfile) {
    echo <<<HTML
        <form method="post">
            <div class="input-group">
                <span class="input-group-text bg-dark text-light border-dark">Bio</span>
                <input name="Bio" type="text" class="form-control bg-dark text-light border-dark" placeholder="Bio" aria-label="Bio" aria-describedby="basic-addon1" value="$bio">
            </div>
            <br>
            <div class="input-group">
                <span class="input-group-text bg-dark text-light border-dark">Password</span>
                <input name="Password" type="password" class="form-control bg-dark text-light border-dark" placeholder="Password" aria-label="Password" aria-describedby="basic-addon1">
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
    echo <<<HTML
        <style>
            .row { display: flex; }
            .column { 
                flex: 50%; 
                max-width: 50%; 
                word-wrap: break-word;
            }
        </style>
        <div class="row">
            <div class="column">
                <h3>$username</h3>
                <p>$bio</p>
            </div>
            <div class="column">
                <h3>$followersCount Followers</h3>
                <h3>$followingCount Following</h3>
                <h3>$postsCount Posts</h3>
                <form method="post">
                    <input name="editProfile" type="boolean" style="display: none;" value="true">
                    <button class="btn btn-dark btn-sm" type="submit">Edit Profile</button>
                </form>
            </div>
        </div>
        <hr>
        <div class="row">
            Insert posts here.
        </div>
    HTML;
}
