<?php
class Assignment
{
    private $id;
    public function __construct($id)
    {
        $this->id = $id;
    }

    public function __get($name)
    {
        $id = $this->id;
        $db = $_SESSION["database"];
        switch ($name) {
            case "id":
                return $id;
            case "class":
                return new Group($db->select("ClassID", "Assignment", "AssignmentID = '$id'")[0][0]);
            case "description":
                return $db->select("Description", "Assignment", "AssignmentID = '$id'")[0][0];
            case "createdAt":
                return $db->select("CreatedAt", "Assignment", "AssignmentID = '$id'")[0][0];
            case "dueAt":
                return $db->select("DueAt", "Assignment", "AssignmentID = '$id'")[0][0];
            case "work":
                $work = $db->select("WorkID", "AssignmentWork", "AssignmentID = '$id'", "Username");
                if (is_null($work)) return NULL;
                return array_map("mapToAssignmentWorkObject", array_map("mapToFirstItem", $work));
            default:
                throw new Exception("Property $name does not exist on type Assignment.");
        }
    }

    public function render($user)
    {
        switch ($user->type) {
            case "teacher":
                $work = "";
                for ($i = 0; $i < count($this->work); ++$i) $work = $work . $this->work[$i]->render();
                return <<<HTML
                    <div id="$this->id">
                        <div class="card bg-dark">
                            <h5 class="card-header">Assignment</h5>
                            <div class="card-body" style="display: flex;">
                                <p style="flex: 75%;">$this->description</p>
                                <div class="btn-group" style="flex: 25%;">
                                    <button class="btn btn-danger" onclick="deleteAssignment('$this->id');">Delete</button>
                                </div>
                            </div>
                            <div class="card-footer text-muted" style="display: flex;">
                                <p>Date Set: <script>document.write(new Date($this->createdAt * 1000).toGMTString());</script></p>
                                <p style="text-align: right; flex: 25%;">Date Due: <script>document.write(new Date($this->dueAt * 1000).toGMTString());</script></p>
                            </div>
                            <div class="accordion-flush" id="work-$this->id">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="workHeader-$this->id">
                                        <button class="accordion-button collapsed bg-dark text-light" type="button" data-bs-toggle="collapse" data-bs-target="#workCollapse-$this->id" aria-expanded="true" aria-controls="workCollapse-$this->id">
                                            Work
                                        </button>
                                    </h2>
                                    <div id="workCollapse-$this->id" class="accordion-collapse collapse" aria-labelledby="workHeader-$this->id" data-bs-parent="#work-$this->id">
                                        <div class="accordion-body" id="workList-$this->id">
                                            <div class="row row-cols-4">$work</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>
                    </div>
                HTML;
            case "student":
                $handedIn = in_array($user->username, array_map("mapToUsernames", $this->work));
                $handedInAnimation = "";
                if ($handedIn) {
                    for ($i = 0; $i < count($this->work); ++$i) {
                        $thisWork = $this->work[$i];
                        if ($thisWork->username === $user->username) {
                            $handedInAnimation = $thisWork->animation->name;
                            break;
                        }
                    }
                }
                if (!$handedIn) {
                    $animationList = <<<HTML
                        <select class="form-control bg-dark text-light" id="animationList" name="setup">
                    HTML;
                    for ($i = 0; $i < count($user->animations); ++$i) {
                        $animation = $user->animations[$i];
                        $animationList = $animationList . <<<HTML
                            <option value="$i">$animation->name</option>
                        HTML;
                    }
                    $animations = $user->animations;
                    $animationIDs = array_map("mapToIDs", $animations);
                    $jsonAnimationIDs = json_encode($animationIDs);
                    $animationNames = array_map("mapToNames", $animations);
                    $jsonAnimationNames = json_encode($animationNames);
                    $animationList = $animationList . <<<HTML
                        </select>
                    HTML;
                    $animationList = $animationList
                        . "<button class='btn btn-outline-light' onclick='handInAssignment(`$this->id`,"
                        . $jsonAnimationIDs
                        . '[$("#animationList").val()],'
                        . $jsonAnimationNames
                        . '[$("#animationList").val()]'
                        . ");'>Hand In</button>";
                }
                return <<<HTML
                    <div id="$this->id">
                        <div class="card bg-dark">
                            <h5 class="card-header">Assignment</h5>
                            <div class="card-body" style="display: flex;">
                                <p style="flex: 75%;">$this->description</p>
                            </div>
                            <div class="card-footer text-muted" style="display: flex;">
                                <p>Date Set: <script>document.write(new Date($this->createdAt * 1000).toGMTString());</script></p>
                                <p style="text-align: right; flex: 25%;">Date Due: <script>document.write(new Date($this->dueAt * 1000).toGMTString());</script></p>
                            </div>
                HTML . ($handedIn
                    ? <<<HTML
                        <div class="accordion-flush" id="work-$this->id">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="workHeader-$this->id">
                                    <button class="accordion-button collapsed bg-dark text-light text-muted" type="button" data-bs-toggle="collapse" data-bs-target="#workCollapse-$this->id" aria-expanded="true" aria-controls="workCollapse-$this->id" disabled>
                                        Handed In - $handedInAnimation
                                    </button>
                                </h2>
                            </div>
                        </div>
                    HTML
                    : <<<HTML
                        <div class="accordion-flush" id="work-$this->id">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="workHeader-$this->id">
                                    <button class="accordion-button collapsed bg-dark text-light" type="button" data-bs-toggle="collapse" data-bs-target="#workCollapse-$this->id" aria-expanded="true" aria-controls="workCollapse-$this->id">
                                        Hand In
                                    </button>
                                </h2>
                                <div id="workCollapse-$this->id" class="accordion-collapse collapse" aria-labelledby="workHeader-$this->id" data-bs-parent="#work-$this->id">
                                    <div class="accordion-body" id="workList-$this->id">
                                        <div class="input-group">$animationList</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    HTML) . <<<HTML
                        </div>
                        <br>
                    </div>
                HTML;
        }
    }

    public function delete()
    {
        $db = $_SESSION["database"];
        $db->delete("Assignment", "AssignmentID = '$this->id'");
        $db->delete("AssignmentWork", "AssignmentID = '$this->id");
    }
}
