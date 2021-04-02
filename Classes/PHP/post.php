<?php
class Post
{
    private $id;
    public function __construct($id)
    {
        $this->id = $id;
    }

    public function __get($name)
    {
        $id = $this->id;
        $db = $_SESSION["database"];
        switch ($name) {
            case "id":
                return $id;
            case "createdAt":
                return $db->select("CreatedAt", "Post", "PostID = '$id'")[0][0];
            case "user":
                return new User($db->select("Username", "Post", "PostID = '$id'")[0][0]);
            case "animation":
                return new Animation($db->select("AnimationID", "Post", "PostID = '$id'")[0][0]);
            case "animationID":
                return $db->select("AnimationID", "Post", "PostID = '$id'")[0][0];
            case "fps":
                return $db->select("FPS", "Post", "PostID = '$id'")[0][0];
            case "likedBy":
                $likes = $db->select("Username", "PostLike", "PostID = '$id'");
                if (is_null($likes)) return NULL;
                return array_map("mapToUserObject", array_map("mapToFirstItem", $likes));
            default:
                throw new Exception("Property $name does not exist on type Post.");
        }
    }

    public function delete()
    {
        $db = $_SESSION["database"];
        $db->delete("Post", "PostID = '$this->id'");
        $db->delete("PostLike", "PostID = '$this->id'");
        $db->delete("Comment", "PostID = '$this->id'");
    }

    public function like($username)
    {
        $db = $_SESSION["database"];
        $db->insert("PostLike", "PostID, Username", "'$this->id', '$username'");
    }

    public function unlike($username)
    {
        $db = $_SESSION["database"];
        $db->delete("PostLike", "PostID = '$this->id' AND Username = '$username'");
    }

    public function comment($username, $content)
    {
        $db = $_SESSION["database"];
        $timestamp = time();
        $id = md5("$timestamp-$this->id");
        $db->insert("Comment", "CommentID, PostID, Username, Content, CreatedAt", "'$id', '$this->id', '$username', '$content', $timestamp");
    }

    public function uncomment($id)
    {
        $db = $_SESSION["database"];
        $db->delete("Comment", "CommentID = '$id'");
    }

    public function render()
    {
        // Get the current user.
        $user = unserialize($_SESSION["user"]);
        // Get post variables.
        $postID = $this->id;
        $postCreatedAt = $this->createdAt;
        $postFps = $this->fps;
        $postLikedBy = $this->likedBy;
        $postLikedByUsernames = array_map("mapToUsernames", $this->likedBy);
        $postLikedByUser = in_array($user->username, $postLikedByUsernames);
        $postLikedByCount = count($postLikedBy);
        // Get animation variables.
        $postAnimation = $this->animation;
        $postAnimationName = $postAnimation->name;
        $postAnimationWidth = $postAnimation->width;
        $postAnimationHeight = $postAnimation->height;
        $postAnimationType = $postAnimation->typeString;
        $postAnimationIcons = array_map("mapBase64ToImageSrc", $postAnimation->generateFrameIcons());
        $postAnimationIconsJSON = json_encode($postAnimationIcons);
        $postAnimationIconCount = count($postAnimationIcons);
        $postAnimationFirstIcon = $postAnimationIcons[0];
        // Get user variables.
        $postUser = $this->user;
        $postUserUsername = $postUser->username;
        // Generate HTML.
        $likeButton = <<<HTML
            <script>
                const like_$postID = () => {
                    const postID = "$postID";
                    const username = "$user->username";
                    $.post("Utils/Forms/likePost.php", { postID, username }, () => {
                        document.getElementById("$postID-likeButton").innerHTML = `<button type="button" onclick="unLike_$postID()" class="btn btn-danger">❤</button>`
                        document.getElementById("$postID-likeCount").innerHTML = "<strong>Likes:</strong> " + (parseInt(document.getElementById("$postID-likeCount").innerHTML.split(" ")[1]) + 1).toString();
                    });
                };
                const unLike_$postID = () => {
                    const postID = "$postID";
                    const username = "$user->username";
                    $.post("Utils/Forms/unLikePost.php", { postID, username }, () => {
                        document.getElementById("$postID-likeButton").innerHTML = `<button type="button" onclick="like_$postID()" class="btn btn-secondary">❤</button>`
                        document.getElementById("$postID-likeCount").innerHTML = "<strong>Likes:</strong> " + (parseInt(document.getElementById("$postID-likeCount").innerHTML.split(" ")[1]) - 1).toString();
                    });
                };
            </script>
        HTML;
        $likeButton = $likeButton . ($postLikedByUser
            ? <<<HTML
                <button type="button" onclick="unLike_$postID()" class="btn btn-danger">❤</button>
            HTML
            : <<<HTML
                <button type="button" onclick="like_$postID()" class="btn btn-secondary">❤</button>
            HTML);
        $commentButton = <<<HTML
            <button type="button" class="btn btn-secondary">Comment</button>
        HTML;
        return <<<HTML
            <div class="card text-white bg-dark post">
                <script>
                    const _$postID = (frames, fps) => {
                        const img = document.getElementById("$postID-icon");
                        const buttons = document.getElementById("$postID-buttons");
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
                <div class="icon">
                    <img src="$postAnimationFirstIcon" loading="lazy" class="card-img-top" id="$postID-icon">
                    <div id="$postID-buttons" class="buttons">
                        <button class="btn btn-secondary btn-lg" data-toggle="tooltip" data-placement="top" title="Play the animation" onclick='_$postID($postAnimationIconsJSON, $postFps);'>▶</button>
                    </div>
                </div>
                <div class="card-body">
                    <h5 class="card-title">$postAnimationName</h5>
                    <p><strong>Type:</strong> $postAnimationType</p>
                    <p><strong>Frames:</strong> $postAnimationIconCount</p>
                    <p><strong>FPS:</strong> $postFps</p>
                    <p><strong>Dimensions:</strong> $postAnimationWidth x $postAnimationHeight</p>
                    <p id="$postID-likeCount"><strong>Likes:</strong> $postLikedByCount</p>
                    <div style="display: flex;">
                        <div id="$postID-likeButton">$likeButton</div>
                        <div id="$postID-commentButton">$commentButton</div>
                    </div>
                </div>
                <div class="card-footer text-muted">
                    <script>document.write(new Date($postCreatedAt * 1000).toGMTString());</script>
                </div>
            </div>
        HTML;
    }
}
