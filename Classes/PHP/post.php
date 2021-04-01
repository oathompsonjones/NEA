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
        $likeButton = $postLikedByUser
            ? <<<HTML
            <form method="post">
                <input style="display: none;" type="text" name="unLikePost" value="$postID">
                <button type="submit" class="btn btn-danger">❤</button>
            </form>
        HTML : <<<HTML
            <form method="post">
                <input style="display: none;" type="text" name="likePost" value="$postID">
                <button type="submit" class="btn btn-secondary">❤</button>
            </form>
        HTML;
        $commentButton = <<<HTML
            <form method="post">
                <input style="display: none;" type="text" name="commentOnPost" value="$postID">
                <button type="submit" class="btn btn-secondary">Comment</button>
            </form>
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
}
