<?php
echo <<<HTML
    <h1>Microcontroller Animations</h1>
    <div class="alert alert-info" role="alert">
        Student homepage
    </div>
HTML;

$animations = unserialize($_SESSION["user"])->animations;
$animation = new MicroBitBuiltInAnimation($animations[0]->id);
$json = $animation->getFramesAs32BitIntegersJSON();
echo "<h3>" . $animation->name . " - " . "MicroPython</h3><br><code><pre>"
    . $animation->generateMicroPythonCode($json) . "</pre></code>" . "<br>"
    . "<h3>" . $animation->name . " - " . "TypeScript</h3><br><code><pre>"
    . $animation->generateTypeScriptCode($json) . "</pre></code>";
