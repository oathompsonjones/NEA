<?php

/**
 * Takes two objects with the createdAt property and returns the difference between these properties.
 * @param Assignment|AssignmentWork|Comment|Message|Post $a
 * @param Assignment|AssignmentWork|Comment|Message|Post $b
 * @return number
 */
function sortByCreatedAt($a, $b)
{
    return intval($b->createdAt) - intval($a->createdAt);
}
