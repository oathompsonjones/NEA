<?php
$db = $_SESSION["database"];
$user = unserialize($_SESSION["user"]);

echo <<<HTML
    <h1>My Animations</h1>
    <div class="row row-cols-1 row-cols-md-5 g-4">
        <style>.animation { display: none; }</style>
HTML;
$animations = $user->animations;
$posts = $user->posts;
$postedAnimationIDs = array_map("mapToAnimationID", $posts);

for ($i = 0; $i < count($animations); ++$i) {
    $html = $animations[$i]->render();
    echo <<<HTML
        <div class="col">$html</div>
    HTML;
}
echo <<<HTML
        <style>.animation { display: block; }</style>
    </div>
HTML;
