<?php
function mapIconsSrc($value)
{
    return "data:image/png;base64,$value";
}

$db = $_SESSION["database"];
$user = unserialize($_SESSION["user"]);

if (isset($_POST["deleteAnimation"]) && !is_null($_POST["deleteAnimation"])) {
    $tmp = new Animation($_POST["deleteAnimation"]);
    $name = $tmp->name;
    $id = $tmp->id;
    echo <<<HTML
        <div class="alert alert-danger" role="alert" style="display: flex;">
            <p>Are you sure you want to permanently delete the animation $name? This can <strong>not</strong> be undone. All data will be erased.<p>
            <form method="post" style="padding-left: 5px;">
                <input style="display: none;" name="deleteAnimationConfirmed" type="text" value="$id">
                <button class="btn btn-danger btn-sm" type="submit">Yes</button>
            </form>
            <form method="post" style="padding-left: 5px;">
                <button class="btn btn-danger btn-sm" type="submit">No</button>
            </form>
        </div>
    HTML;
} else if (isset($_POST["deleteAnimationConfirmed"]) && !is_null($_POST["deleteAnimationConfirmed"])) {
    (new Animation($_POST["deleteAnimationConfirmed"]))->delete();
    require "Include/clearPost.inc";
}

if (isset($_POST["shareAnimation"]) && !is_null($_POST["shareAnimation"])) {
    $timestamp = time();
    $id = md5($user->username . $timestamp);
    $animationID = $_POST["shareAnimation"];
    $fps = $_POST["fps"];
    $db->insert("Post", "PostID, Username, AnimationID, CreatedAt, FPS", "'$id', '$user->username', '$animationID', '$timestamp', $fps");
    require "Include/clearPost.inc";
}

if (isset($_POST["unShareAnimation"]) && !is_null($_POST["unShareAnimation"])) {
    $id = $_POST["unShareAnimation"];
    (new Post($db->select("PostID", "Post", "AnimationID = '$id'")[0][0]))->delete();
    require "Include/clearPost.inc";
}

$animations = $user->animations;
$posts = $user->posts;
function postAnimationIDs($val)
{
    return $val->animationID;
}
$postedAnimationIDs = array_map("postAnimationIDs", $posts);

echo <<<HTML
    <h1>My Animations</h1>
    <div class="accordion accordion-flush" id="accordion">
        <script>
            const playback = (index, frames) => {
                const img = document.getElementById(index.toString() + "-icon");
                const buttons = document.getElementById(index.toString() + "-buttons");
                const fps = document.getElementById(index.toString() + "-inputFPS")?.value || 1;
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
        <div class="row row-cols-1 row-cols-md-5 g-4">
HTML;
for ($i = 0; $i < count($animations); ++$i) {
    $animation = $animations[$i];
    $id = $animation->id;
    $name = $animation->name;
    $frameCount = count($animation->frames);
    $type = $animation->typeString;
    $icons = array_map("mapIconsSrc", $animation->generateFrameIcons());
    $jsonIcons = json_encode($icons);
    $firstIcon = $icons[0];

    if (in_array($id, $postedAnimationIDs)) {
        $shareButton = <<<HTML
            <form method="post" style="display: flex; width: 100%;">
                <input style="display: none;" name="unShareAnimation" type="text" value="$id">
                <button class="btn btn-dark btn-sm" type="submit" style="width: 50%;">Unshare</button>
                <div class="form-floating" style="width: 50%;">
                    <input type="number" class="form-control bg-dark text-light border-dark" id="$i-inputFPS" name="fps" placeholder="FPS" min=1 max=60 value=1 required>
                    <label for="$i-inputFPS" class="form-label">FPS</label>
                </div>
            </form>
        HTML;
    } else {
        $shareButton = <<<HTML
            <form method="post" style="display: flex; width: 100%;">
                <input style="display: none;" name="shareAnimation" type="text" value="$id">
                <button class="btn btn-dark btn-sm" type="submit" style="width: 50%;">Share</button>
                <div class="form-floating" style="width: 50%;">
                    <input type="number" class="form-control bg-dark text-light border-dark" id="$i-inputFPS" name="fps" placeholder="FPS" min=1 max=60 value=1 required>
                    <label for="$i-inputFPS" class="form-label">FPS</label>
                </div>
            </form>
        HTML;
    }

    echo <<<HTML
        <div class="col">
            <div class="card text-white bg-dark">
                <div id="$i-card" class="icon">
                    <img src="$firstIcon" class="card-img-top" id="$i-icon">
                    <div id="$i-buttons" class="buttons">
                        <button class="btn btn-secondary btn-lg" data-toggle="tooltip" data-placement="top" title="Play the animation" onclick='playback($i, $jsonIcons);'>Play</button>
                    </div>
                </div>
                <div class="card-body">
                    <h5 class="card-title">$name</h5>
                    <div style="display: flex;">
                        <form method="post" style="width: 50%;">
                            <input style="display: none;" name="deleteAnimation" type="text" value="$id">
                            <button class="btn btn-danger btn-sm" type="submit" style="width: 100%;">Delete</button>
                        </form>
                        <form method="post" action="editor" style="width: 50%;">
                            <input style="display: none;" name="preMade" type="text" value="$id">
                            <button class="btn btn-dark btn-sm" type="submit" style="width: 100%;">Edit</button>
                        </form>
                    </div>
                    $shareButton
                </div>
            </div>
        </div>
    HTML;
}
echo <<<HTML
        </div>
    </div>
HTML;
