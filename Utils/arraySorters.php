<?php
function sortByCreatedAt($a, $b)
{
    return intval($b->createdAt) - intval($a->createdAt);
}
