<?php
function mapToUsernames($value)
{
    return $value->username;
}
function mapToFirstItem($value)
{
    return $value[0];
}
function mapBase64ToImageSrc($value)
{
    return "data:image/png;base64,$value";
}
function mapToUserObject($value)
{
    return new User($value);
}
function mapToFrameObject($value)
{
    return new Frame($value);
}
function mapToPostObject($value)
{
    return new Post($value);
}
function mapToAnimationObject($value)
{
    return new Animation($value);
}
function mapToAnimationID($value)
{
    return $value->animationID;
}
function mapToBinary($value)
{
    return $value->binary;
}
function mapToJsonBinary($value)
{
    return "0b$value";
}
function mapBinaryToIntegers($value)
{
    return bindec($value);
}
