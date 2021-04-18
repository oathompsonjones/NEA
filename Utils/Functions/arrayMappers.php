<?php

/**
 * Takes an object with the property username, and returns that property.
 * @param User|AssignmentWork $value
 * @return string
 */
function mapToUsernames($value)
{
    return $value->username;
}
/**
 * Takes an object with the property id, and returns that id.
 * @param Animation|Assignment|AssignmentWork|Comment|Frame|Group|Message|Post $value
 * @return string
 */
function mapToIDs($value)
{
    return $value->id;
}
/**
 * Takes an object with the property name, and returns that property.
 * @param Animation|Group $value
 * @return string
 */
function mapToNames($value)
{
    return $value->name;
}
/**
 * Takes a post object and returns the animationID property.
 * @param Post $value
 * @return string
 */
function mapToAnimationID($value)
{
    return $value->animationID;
}
/**
 * Takes a frame object and returns the binary property.
 * @param Frame $value
 * @return string
 */
function mapToBinary($value)
{
    return $value->binary;
}
/**
 * Takes an array and returns the first value.
 * @param Array $value
 * @return mixed
 */
function mapToFirstItem($value)
{
    return $value[0];
}
/**
 * Takes a binary string and returns the associated number.
 * @param string $value
 * @return number
 */
function mapBinaryToIntegers($value)
{
    return bindec($value);
}
/**
 * Takes a binary string and adds 0b to the start of it.
 * @param string $value
 * @return string
 */
function mapToJsonBinary($value)
{
    return "0b$value";
}
/**
 * Takes a string and adds a tab to the start.
 * @param string $value
 * @return string
 */
function mapTabToStart($value)
{
    return "\t$value";
}
/**
 * Takes raw png data and formats it for the HTML <img> tag.
 * @param string $value
 * @return string
 */
function mapBase64ToImageSrc($value)
{
    return "data:image/png;base64,$value";
}
/**
 * Takes a username and returns the associated user object.
 * @param string $value
 * @return User
 */
function mapToUserObject($value)
{
    return new User($value);
}
/**
 * Takes a frame ID and returns the associated frame object.
 * @param string $value
 * @return Frame
 */
function mapToFrameObject($value)
{
    return new Frame($value);
}
/**
 * Takes a post ID and returns the associated post object.
 * @param string $value
 * @return Post
 */
function mapToPostObject($value)
{
    return new Post($value);
}
/**
 * Takes a comment ID and returns the associated comment object.
 * @param string $value
 * @return Comment
 */
function mapToCommentObject($value)
{
    return new Comment($value);
}
/**
 * Takes an animation ID and returns the associated animation object.
 * @param string $value
 * @return Animation
 */
function mapToAnimationObject($value)
{
    return new Animation($value);
}
/**
 * Takes a group ID and returns the associated group object.
 * @param string $value
 * @return Group
 */
function mapToGroupObject($value)
{
    return new Group($value);
}
/**
 * Takes a message ID and returns the associated message object.
 * @param string $value
 * @return Message
 */
function mapToMessageObject($value)
{
    return new Message($value);
}
/**
 * Takes an assignment ID and returns the associated assignment object.
 * @param string $value
 * @return Assignment
 */
function mapToAssignmentObject($value)
{
    return new Assignment($value);
}
/**
 * Takes an assignment work ID and returns the associated assignment work object.
 * @param string $value
 * @return AssignmentWork
 */
function mapToAssignmentWorkObject($value)
{
    return new AssignmentWork($value);
}
