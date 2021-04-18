<?php

/**
 * Class to represent a comment made on a post.
 */
class Comment
{
    /**
     * @var string
     */
    private $id;
    /**
     * @param string $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        $id = $this->id;
        $db = $_SESSION["database"];
        switch ($name) {
            case "id":
                return $id;
            case "createdAt":
                return $db->select("CreatedAt", "Comment", "CommentID = '$id'")[0][0];
            case "post":
                return new Post($db->select("PostID", "Comment", "CommentID = '$id'")[0][0]);
            case "user":
                return new User($db->select("Username", "Comment", "CommentID = '$id'")[0][0]);
            case "content":
                return $db->select("Content", "Comment", "CommentID = '$id'")[0][0];
            default:
                throw new Exception("Property $name does not exist on type Comment.");
        }
    }

    /**
     * Deletes the comment from the database.
     * @return void
     */
    public function delete()
    {
        $db = $_SESSION["database"];
        $db->delete("Comment", "CommentID = '$this->id'");
    }

    /**
     * Creates the HTML required to render the comment.
     * @return string
     */
    public function render()
    {
        $username = $this->user->username;
        $content = $this->content;
        $createdAt = $this->createdAt;
        $commentID = $this->id;
        $loggedInUser = unserialize($_SESSION["user"]);
        $deleteButton = "";
        if ($loggedInUser->username === $username) $deleteButton = <<<HTML
            <div style="float: right;">
                <script>
                    const deleteConfirm_$commentID = () => {
                        const commentID = "$commentID";
                        $.post("Utils/Forms/deleteComment.php", { commentID }, () => document.getElementById(commentID).style.display = "none");
                    };
                    const delete_$commentID = () => {
                        document.getElementById("delete-$commentID").innerHTML = "Confirm";
                        document.getElementById("delete-$commentID").onclick = deleteConfirm_$commentID;
                    };
                </script>
                <button onclick="delete_$commentID();" class="btn btn-danger btn-sm" type="button" id="delete-$commentID">Delete</button>
            </div>
        HTML;
        return <<<HTML
            <div class="card bg-dark text-light" id="$commentID">
                <div class="card-header">$username $deleteButton</div>
                <div class="card-body">$content</div>
                <div class="card-footer text-muted"><script>document.write(new Date($createdAt * 1000).toGMTString());</script></div>
            </div>
        HTML;
    }
}
