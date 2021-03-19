<?php
$animations = unserialize($_SESSION["user"])->animations;
echo <<<HTML
    <h1>My Animations</h1>
    <div class="accordion accordion-flush" id="accordion">
HTML;
for ($i = 0; $i < count($animations); ++$i) {
    $id = $animations[$i]->id;
    $name = $animations[$i]->name;
    echo <<<HTML
        <div class="accordion-item">
            <h2 class="accordion-header" id="flush-heading-$i">
                <button id="button-$i" class="accordion-button collapsed text-light" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-$i" aria-expanded="false" aria-controls="flush-collapse-$i">
                    $name
                </button>
            </h2>
            <div id="flush-collapse-$i" class="accordion-collapse collapse" aria-labelledby="flush-heading-$i" data-bs-parent="#accordion">
                <div class="accordion-body">
                    <form method="post" action="editor">
                        <input style="display: none;" name="preMade" type="text" value="$id">
                        <button class="btn btn-dark btn-md" type="submit">Edit</button>
                    </form>
                </div>
            </div>
        </div>
        <script>
            $("#button-$i").on("click", () => {
                let button = document.getElementById("button-$i");
                if (!button.className.includes("collapsed")) button.className += " bg-dark";
                let i = 0;
                button = document.getElementById("button-" + i);
                while (button) {
                    if (i !== $i) button.className = "accordion-button collapsed text-light";
                    button = document.getElementById("button-" + ++i);
                }
            });
        </script>
    HTML;
}
echo <<<HTML
    </div>
HTML;
