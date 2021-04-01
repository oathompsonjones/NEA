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
