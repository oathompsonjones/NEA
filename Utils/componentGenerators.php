<?php
function generatePost($postID, $i = 0)
{
    // Get DB and User variables.
    $user = unserialize($_SESSION["user"]);
    // Get post variables.
    $post = new Post($postID);
    $postCreatedAt = $post->createdAt;
    $postFps = $post->fps;
    $postLikedBy = $post->likedBy;
    $postLikedByUsernames = array_map("mapToUsernames", $post->likedBy);
    $postLikedByUser = in_array($user->username, $postLikedByUsernames);
    $postLikedByCount = count($postLikedBy);
    // Get animation variables.
    $postAnimation = $post->animation;
    $postAnimationName = $postAnimation->name;
    $postAnimationWidth = $postAnimation->width;
    $postAnimationHeight = $postAnimation->height;
    $postAnimationType = $postAnimation->typeString;
    $postAnimationIcons = array_map("mapBase64ToImageSrc", $postAnimation->generateFrameIcons());
    $postAnimationIconsJSON = json_encode($postAnimationIcons);
    $postAnimationIconCount = count($postAnimationIcons);
    $postAnimationFirstIcon = $postAnimationIcons[0];
    // Get user variables.
    $postUser = $post->user;
    $postUserUsername = $postUser->username;
    // Generate HTML.
    $likeButton = $postLikedByUser
        ? <<<HTML
            <form method="post">
                <input style="display: none;" type="text" name="unLikePost" value="$post->id">
                <button type="submit" class="btn btn-danger">❤</button>
            </form>
        HTML : <<<HTML
            <form method="post">
                <input style="display: none;" type="text" name="likePost" value="$post->id">
                <button type="submit" class="btn btn-secondary">❤</button>
            </form>
        HTML;
    $commentButton = <<<HTML
        <form method="post">
            <input style="display: none;" type="text" name="commentOnPost" value="$post->id">
            <button type="submit" class="btn btn-secondary">Comment</button>
        </form>
    HTML;
    return <<<HTML
        <div class="card text-white bg-dark">
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
            <div class="card-header">
                $postUserUsername
            </div>
            <div id="$i-card" class="icon">
                <img src="$postAnimationFirstIcon" loading="lazy" class="card-img-top" id="$i-icon">
                <div id="$i-buttons" class="buttons">
                    <button class="btn btn-secondary btn-lg" data-toggle="tooltip" data-placement="top" title="Play the animation" onclick='playback($i, $postAnimationIconsJSON, $postFps);'>▶</button>
                </div>
            </div>
            <div class="card-body">
                <h5 class="card-title">$postAnimationName</h5>
                <p>
                    <strong>Type:</strong> $postAnimationType
                    <br>
                    <strong>Frames:</strong> $postAnimationIconCount
                    <br>
                    <strong>FPS:</strong> $postFps
                    <br>
                    <strong>Dimensions:</strong> $postAnimationWidth x $postAnimationHeight
                    <br>
                    <strong>Likes:</strong> $postLikedByCount
                </p>
                <div style="display: flex;">
                    $likeButton
                    $commentButton
                </div>
            </div>
            <div class="card-footer text-muted">
                <script>document.write(new Date($postCreatedAt * 1000).toGMTString());</script>
            </div>
        </div>
    HTML;
}
