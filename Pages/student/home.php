<?php
echo <<<HTML
    <h1>Microcontroller Animations</h1>
    <div class="alert alert-info" role="alert">
        Student homepage
    </div>
HTML;

$animations = unserialize($_SESSION["user"])->animations;
echo "<br>Animations: ";
function map($value)
{
    return "ID: " . $value->id . ", "
        . "Name: " . $value->name . ", "
        . "Width: " . $value->width . ", "
        . "Height: " . $value->height . ", "
        . "Type: " . $value->type . ", "
        . "Frames: " . count($value->frames);
}
print_r(array_map("map", $animations));
