<?php
// Gets the object for the logged in user.
$user = unserialize($_SESSION["user"]);
// Renders the heading.
echo <<<HTML
    <h1>My Animations</h1>
    <div class="row row-cols-1 row-cols-md-5 g-4">
        <!-- Does not display animation until fully rendered. -->
        <style>.animation { display: none; }</style>
HTML;
// Gets all of the user's animations.
$animations = $user->animations;
// Counts the animations so that it does not need to be recalculated on every loop iteration.
$animationCount = count($animations);
// Loops through each animation.
for ($i = 0; $i < $animationCount; ++$i) {
    // Gets the HTML to display.
    $html = $animations[$i]->render();
    // Displays the HTML.
    echo <<<HTML
        <div class="col">$html</div>
    HTML;
}
echo <<<HTML
        <!-- Now that all animations are rendered, they are displayed. -->
        <style>.animation { display: block; }</style>
    </div>
HTML;
