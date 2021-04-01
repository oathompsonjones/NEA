<?php
$db = $_SESSION["database"];
$user = unserialize($_SESSION["user"]);

echo <<<HTML
    <h1>My Animations</h1>
    <div class="row row-cols-1 row-cols-md-5 g-4">
        <style>.animation { display: none; }</style>
HTML;
$animations = $user->animations;
$posts = $user->posts;
$postedAnimationIDs = array_map("mapToAnimationID", $posts);

for ($i = 0; $i < count($animations); ++$i) {
    $animation = $animations[$i];
    $id = $animation->id;
    $name = $animation->name;
    $frameCount = count($animation->frames);
    $type = $animation->typeString;
    $icons = array_map("mapBase64ToImageSrc", $animation->generateFrameIcons());
    $jsonIcons = json_encode($icons);
    $firstIcon = $icons[0];

    $postExists = in_array($id, $postedAnimationIDs);
    $shareButton = <<<HTML
        <script>
            const unShare_$id = () => {
                const animationID = "$id";
                $.post("Utils/Forms/unShareAnimation.php", { animationID }, () => document.getElementById("$id-shareButton").innerHTML = `<button class="btn btn-dark btn-sm" type="button" onclick="share_$id();" style="width: 100%;">Share</button>`);
            };
            const share_$id = () => {
                const animationID = "$id";
                const username = "$user->username";
                const fps = document.getElementById("$id-inputFPS").value;
                $.post("Utils/Forms/shareAnimation.php", { animationID, username, fps }, () => document.getElementById("$id-shareButton").innerHTML = `<button class="btn btn-dark btn-sm" type="button" onclick="unShare_$id();" style="width: 100%;">Unshare</button>`);
            };
            const delete_$id = () => {
                document.getElementById("$id-deleteButton").innerHTML = `<button class="btn btn-danger btn-sm" type="button" onclick="deleteConfirm_$id();" style="width: 100%;">Confirm</button>`;
            };
            const deleteConfirm_$id = () => {
                const animationID = "$id";
                $.post("Utils/Forms/deleteAnimation.php", { animationID }, () => document.getElementById("$id-container").style.display = "none");
            };
        </script>
    HTML;
    $shareButton = $shareButton . ($postExists
        ? <<<HTML
            <button class="btn btn-dark btn-sm" type="button" onclick="unShare_$id();" style="width: 100%;">Unshare</button>
        HTML
        : <<<HTML
            <button class="btn btn-dark btn-sm" type="button" onclick="share_$id();" style="width: 100%;">Share</button>
        HTML);
    echo <<<HTML
        <div class="col" id="$id-container">
            <script>
                const _$id = (frames) => {
                    const img = document.getElementById("$id-icon");
                    const buttons = document.getElementById("$id-buttons");
                    const fps = document.getElementById("$id-inputFPS")?.value || 1;
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
            <div class="card text-white bg-dark animation">
                <div id="$id-card" class="icon">
                    <img src="$firstIcon" class="card-img-top" id="$id-icon">
                    <div id="$id-buttons" class="buttons">
                        <button class="btn btn-secondary btn-lg" data-toggle="tooltip" data-placement="top" title="Play the animation" onclick='_$id($jsonIcons);'>Play</button>
                    </div>
                </div>
                <div class="card-body">
                    <h5 class="card-title">$name</h5>
                    <div style="display: flex;">
                        <div id="$id-deleteButton" style="display: flex; width: 50%;">
                            <button class="btn btn-danger btn-sm" type="button" onclick="delete_$id();" style="width: 100%;">Delete</button>
                        </div>
                        <form method="post" action="editor" style="width: 50%;">
                            <input style="display: none;" name="preMade" type="text" value="$id">
                            <button class="btn btn-dark btn-sm" type="submit" style="width: 100%;">Edit</button>
                        </form>
                    </div>
                    <div style="display: flex; width: 100%;">
                        <div id="$id-shareButton" style="display: flex; width: 50%;">
                            $shareButton
                        </div>
                        <div class="form-floating" style="width: 50%;">
                            <input type="number" class="form-control bg-dark text-light border-dark" id="$id-inputFPS" name="fps" placeholder="FPS" min=1 max=60 value=1 required>
                            <label for="$id-inputFPS" class="form-label">FPS</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    HTML;
}
echo <<<HTML
        <style>.animation { display: block; }</style>
    </div>
HTML;
