<?php
$db = $_SESSION["database"];
$user = unserialize($_SESSION["user"]);
$classes = is_null($user->classes) ? [] : $user->classes;

$validClassIDs = array_map("mapToFirstItem", $db->select("ClassID", "Class"));
$validClassIDsJSON = json_encode($validClassIDs);

if (!isset($_GET["classID"]) || is_null($_GET["classID"])) {
    echo <<<HTML
        <div id="invalidCodeWarning"></div>
        <h1>Classes</h1>
        <div class="row row-cols-2">
            <div>
                <h5>Join a new class</h5>
                <script>
                    const joinClass = () => {
                        const classID = document.getElementById("inputClassCode").value;
                        const validIDs = $validClassIDsJSON;
                        if (!validIDs.includes(classID)) return document.getElementById("invalidCodeWarning").innerHTML = `
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                Invalid class code.
                                <button style="float: right;" type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        `;
                        const username = "$user->username";
                        $.post("Utils/Forms/joinClass.php", { classID, username }, () => {
                            document.getElementById("inputClassCode").value = "";
                            window.location.replace("class?classID=" + classID);
                        });
                    };
                </script>
                <div class="input-group mb-3" id="joinClassInput">
                    <div class="form-floating">
                        <input type="text" class="form-control bg-dark text-light border-dark" id="inputClassCode" name="classCode" placeholder="Class Code" required>
                        <label for="inputClassCode">Class Code</label>
                    </div>
                    <button type="button" class="btn btn-dark" onclick="joinClass()">Join</button>
                </div>
            </div>
    HTML;
    if ($user->type === "teacher") echo <<<HTML
        <div>
            <h5>Create a new class</h5>
            <script>
                const createClass = () => {
                    const className = document.getElementById("inputClassName").value;
                    const username = "$user->username";
                    $.post("Utils/Forms/createClass.php", { className, username }, (classID) => {
                        document.getElementById("inputClassName").value = "";
                        window.location.replace("class?classID=" + classID);
                    });
                };
            </script>
            <div class="input-group mb-3" id="createClassInput">
                <div class="form-floating">
                    <input type="text" class="form-control bg-dark text-light border-dark" id="inputClassName" name="className" placeholder="Class Name" required>
                    <label for="inputClassName">Class Name</label>
                </div>
                <button type="button" class="btn btn-dark" onclick="createClass()">Create</button>
            </div>
        </div>
    HTML;
    echo <<<HTML
        </div>
    HTML;
    $html = "";
    $classCount = count($classes);
    for ($i = 0; $i < $classCount; ++$i) {
        $classHTML = $classes[$i]->render();
        $html = $html . <<<HTML
            <div class="col">$classHTML</div>
        HTML;
    }
    echo <<<HTML
        <h5>Your Classes</h5>
        <div class="row row-cols-1 row-cols-md-3 g-4">$html</div>
    HTML;
} else {
    $classID = $_GET["classID"];
    $classExits = in_array($classID, $validClassIDs);
    $class = new Group($classID);
    $userIsInClass = in_array($user->username, array_map("mapToUsernames", $class->students)) || in_array($user->username, array_map("mapToUsernames", $class->teachers));
    if (!($classExits && $userIsInClass)) echo <<<HTML
        <script>window.location.replace("class")</script>
    HTML;
    // Wrapper
    echo <<<HTML
        <div style="display: flex; width: 100%; height: 100%;">
            <div style="flex: 85%;">
                <div class="btn-group" style="width: 100%; margin-bottom: 10px;">
                    <button class="btn btn-dark" disabled><h5>$class->name</h5></button>
    HTML;
    // Chat button
    if ($class->chatEnabled) echo <<<HTML
        <script>
            const showChat = () => {
                if (document.getElementById("settings")) document.getElementById("settings").style.display = "none";
                if (document.getElementById("assignments")) document.getElementById("assignments").style.display = "none";
                if (document.getElementById("chat")) document.getElementById("chat").style.display = "";
                document.getElementById("messages").scrollTop = document.getElementById("messages").scrollHeight;
            };
        </script>
        <button onclick="showChat();" class="btn btn-dark">Chat</button>
    HTML;
    else echo <<<HTML
        <button class="btn btn-dark" disabled>Chat</button>
    HTML;
    // Settings Button
    if ($user->type === "teacher") echo <<<HTML
        <script>
            const showSettings = () => {
                if (document.getElementById("chat")) document.getElementById("chat").style.display = "none";
                if (document.getElementById("assignments")) document.getElementById("assignments").style.display = "none";
                if (document.getElementById("settings")) document.getElementById("settings").style.display = "";
            };
        </script>
        <button onclick="showSettings();" class="btn btn-dark">Settings</button>
    HTML;
    // Assignments Button
    echo <<<HTML
            <script>
                const showAssignments = () => {
                    if (document.getElementById("settings")) document.getElementById("settings").style.display = "none";
                    if (document.getElementById("chat")) document.getElementById("chat").style.display = "none";
                    if (document.getElementById("assignments")) document.getElementById("assignments").style.display = "";
                };
            </script>
            <button onclick="showAssignments();" class="btn btn-dark">Assignments</button>
        </div>
    HTML;
    // Chat Page
    if ($class->chatEnabled) {
        echo <<<HTML
            <div id="chat" style="display: none; height: 90%;">
                <div id="messages" style="height: 95%; width: 100%; overflow: auto;"></div>
                <div id="input" style="height: 5%; width: 100%;">
                    <script>
                        const sendMessage = () => {
                            const message = document.getElementById("messageInput").value?.trim();
                            if (!message.length) return false;
                            const username = "$user->username";
                            const classID = "$class->id";
                            $.post("Utils/Forms/sendChatMessage.php", { message, username, classID }, () => document.getElementById("messageInput").value = "");
                        };
                        const deleteMessage = (messageID) => $.post("Utils/Forms/deleteChatMessage.php", { messageID }, () => $("#" + messageID).hide());
                        const updateMessages = () => {
                            const classID = "$class->id";
                            const currentHTML = document.getElementById("messages").innerHTML;
                            const messageCount = currentHTML?.match(/<div class="card bg-dark text-light">/g)?.length ?? 0;
                            const username = "$user->username";
                            $.post("Utils/Forms/updateMessages.php", { classID, currentHTML, messageCount, username }, (messages) => {
                                if (messages !== currentHTML) {
                                    document.getElementById("messages").innerHTML = messages;
                                    document.getElementById("messages").scrollTop = document.getElementById("messages").scrollHeight;
                                }
                            });
                        };
                        const updateInput = () => {
                            const classID = "$class->id";
                            const username = "$user->username";
                            $.post("Utils/Forms/updateClassChatInput.php", { classID, username }, (isMuted) => {
                                if (isMuted === "true") $("#userInput").hide();
                                else $("#userInput").show();
                            });
                        };      
                        setInterval(() => {
                            updateMessages();
                            updateInput();
                        }, 500);
                    </script>
                    <div id="userInput" class="input-group mb-3">
                        <input id="messageInput" class="form-control bg-dark border-dark text-light" type="text" placeholder="Message $class->name">
                        <button id="messageSend" type="button" class="btn btn-dark" onclick="sendMessage()">Send</button>
                        <script>
                            $("#messageInput").keydown((event) => {
                                if (event.keyCode === 13) $("#messageSend").click();
                            });
                        </script>
                    </div>
                </div>
            </div>
        HTML;
    }
    // Settings Page
    if ($user->type === "teacher") {
        echo <<<HTML
            <div id="settings" style="display: none; height: 90%;">
                <div class="form-floating">
                    <input id="inputClassName" name="className" type="text" class="form-control bg-dark text-light border-dark" placeholder="Class Name" aria-label="Class Name" value="$class->name">
                    <label for="inputClassName">Class Name</label>
                </div>
                <div class="form-check">
        HTML;
        if ($class->chatEnabled) echo <<<HTML
            <input class="form-check-input" type="checkbox" value="" id="inputChatEnabled" checked>
        HTML;
        else echo <<<HTML
            <input class="form-check-input" type="checkbox" value="" id="inputChatEnabled">
        HTML;
        $url = $_SERVER["REQUEST_URI"];
        echo <<<HTML
                    <label class="form-check-label" for="inputChatEnabled">Chat Enabled</label>
                </div>
                <script>
                    const saveClass = () => {
                        const name = document.getElementById("inputClassName").value;
                        const chatEnabled = Number(document.getElementById("inputChatEnabled").checked);
                        const classID = "$class->id";
                        $.post("Utils/Forms/saveClass.php", { name, chatEnabled, classID }, () => window.location.replace("$url"));
                    };
                    const deleteClass = () => {
                        const classID = "$class->id";
                        $.post("Utils/Forms/deleteClass.php", { classID }, () => window.location.replace("class"));
                    };
                </script>
                <button onclick="saveClass();" class="btn btn-dark">Save</button>
                <button onclick="deleteClass();" class="btn btn-danger">Delete</button>
                <p class="text-muted">Class ID: $class->id (Send this code to any students you wish to invite.)</p>
            </div>
        HTML;
    }
    // Assignments Page
    echo <<<HTML
        <div id="assignments" style="height: 90%; overflow-y: auto;">
    HTML;
    if ($user->type === "teacher") echo <<<HTML
        <script>
            const deleteAssignment = (assignmentID) => $.post("Utils/Forms/deleteAssignment.php", { assignmentID }, () => $("#" + assignmentID).hide());
            const createAssignment = (assignmentDescription, assignmentDueAt) => {
                const dueAtInput = document.getElementById("assignmentDueAt");
                if (assignmentDueAt.match(/\d\d\/\d\d\/\d\d\d\d/g) === null || new Date(assignmentDueAt = [assignmentDueAt.split("/")[1], assignmentDueAt.split("/")[0], assignmentDueAt.split("/")[2]].join("/")).valueOf() < Date.now() || new Date(assignmentDueAt).year > 2030) {
                    dueAtInput.setCustomValidity("Invalid date.");
                    dueAtInput.reportValidity();
                } else {
                    const classID = "$classID";
                    $.post("Utils/Forms/createAssignment.php", { assignmentDescription, assignmentDueAt, classID }, () => window.location.replace("class?classID=$classID"));
                }
            };
        </script>
        <div>
            <h4>Create New Assignment</h4>
            <div class="form-floating">
                <input class="form-control bg-dark border-dark text-light" type="text" name="Description" id="assignmentDesc" placeholder="Description">
                <label for="assignmentDesc">Description</label>
            </div>
            <div class="form-floating">
                <input class="form-control bg-dark border-dark text-light" type="date" name="Date Due" id="assignmentDueAt" placeholder="dd/mm/yyyy">
                <label for="assignmentDueAt">Date Due (dd/mm/yyyy)</label>
            </div>
            <button class="btn btn-dark" type="button" onclick="createAssignment($('#assignmentDesc').val(), $('#assignmentDueAt').val());">Create</button>
        </div>
        <br>
    HTML;
    else echo <<<HTML
        <script>
            const handInAssignment = (assignmentID, animationID, animationName) => {
                const username = '$user->username';
                $.post("Utils/Forms/handInAssignment.php", { assignmentID, animationID, username }, () => {
                    document.getElementById("work-" + assignmentID).innerHTML = `<div class="accordion-item">
                        <h2 class="accordion-header" id="workHeader-` + assignmentID + `">
                            <button class="accordion-button collapsed bg-dark text-light text-muted" type="button" data-bs-toggle="collapse" data-bs-target="#workCollapse-` + assignmentID + `" aria-expanded="true" aria-controls="workCollapse-` + assignmentID + `" disabled>
                                Handed In - ` + animationName + `
                            </button>
                        </h2>
                    </div>`;
                });
            };
        </script>
    HTML;
    $assignments = $class->assignments;
    $lastAssignmentWasPast = false;
    $assignmentCount = count($assignments);
    for ($i = 0; $i < $assignmentCount; ++$i) {
        $assignment = $assignments[$i];
        if ($i === 0) echo <<<HTML
            <h4>Upcoming Assignments</h4>
        HTML;
        if ($assignment->dueAt < time() && !$lastAssignmentWasPast) {
            $lastAssignmentWasPast = true;
            echo <<<HTML
                <h4>Past Assignments</h4>
            HTML;
        }
        echo $assignment->render($user);
    }
    echo <<<HTML
            </div>
        </div>
    HTML;
    // Teacher List
    $teachers = $class->teachers;
    $teachersHTML = "";
    $teacherCount = count($teachers);
    for ($i = 0; $i < $teacherCount; ++$i) {
        $username = $teachers[$i]->username;
        $teachersHTML = $teachersHTML . <<<HTML
            <div id="$username" style="display: flex;">
                <a class="btn btn-dark" href="profile?user=$username">$username</a>
        HTML;
        if ($user->type === "teacher" && $teachers[$i]->type === "student") {
            $teachersHTML = $teachersHTML . <<<HTML
                <script>
                    const kick_$username = () => {
                        const username = "$username";
                        const classID = "$class->id";
                        $.post("Utils/Forms/kickUserFromClass.php", { username, classID }, () => {
                            console.log(document.getElementById("$username"))
                            document.getElementById("$username").style.display = "none";
                        });
                    };
                    const mute_$username = () => {
                        const username = "$username";
                        const classID = "$class->id";
                        $.post("Utils/Forms/muteUserInClass.php", { username, classID }, () => {
                            document.getElementById("muteButton-$username").innerHTML = `<button class="btn btn-sm btn-secondary" onclick="unMute_$username();">Unmute</button>`;
                        });
                    };
                    const unMute_$username = () => {
                        const username = "$username";
                        const classID = "$class->id";
                        $.post("Utils/Forms/unMuteUserInClass.php", { username, classID }, () => {
                            document.getElementById("muteButton-$username").innerHTML = `<button class="btn btn-sm btn-secondary" onclick="mute_$username();">Mute</button>`;
                        });
                    };
                </script>
                <div class="btn-group">
                    <button class="btn btn-sm btn-danger" onclick="kick_$username();">Kick</button>
                    <div class="btn-group" id="muteButton-$username">
            HTML;
            if (in_array($username, array_map("mapToUsernames", $class->mutedUsers))) {
                $teachersHTML = $teachersHTML . <<<HTML
                    <button class="btn btn-sm btn-secondary" onclick="unMute_$username();">Unmute</button>
                HTML;
            } else {
                $teachersHTML = $teachersHTML . <<<HTML
                    <button class="btn btn-sm btn-secondary" onclick="mute_$username();">Mute</button>
                HTML;
            }
            $teachersHTML = $teachersHTML . <<<HTML
                    </div>
                </div>
            HTML;
        }
        $teachersHTML = $teachersHTML . <<<HTML
            </div>
            <br>
        HTML;
    }
    // Student List
    $students = $class->students;
    $studentsHTML = "";
    $studentCount = count($students);
    for ($i = 0; $i < $studentCount; ++$i) {
        $username = $students[$i]->username;
        $studentsHTML = $studentsHTML . <<<HTML
            <div id="$username" style="display: flex;">
                <a class="btn btn-dark" href="profile?user=$username">$username</a>
        HTML;
        if ($user->type === "teacher" && $students[$i]->type === "student") {
            $studentsHTML = $studentsHTML . <<<HTML
                <script>
                    const kick_$username = () => {
                        const username = "$username";
                        const classID = "$class->id";
                        $.post("Utils/Forms/kickUserFromClass.php", { username, classID }, () => {
                            console.log(document.getElementById("$username"))
                            document.getElementById("$username").style.display = "none";
                        });
                    };
                    const mute_$username = () => {
                        const username = "$username";
                        const classID = "$class->id";
                        $.post("Utils/Forms/muteUserInClass.php", { username, classID }, () => {
                            document.getElementById("muteButton-$username").innerHTML = `<button class="btn btn-sm btn-secondary" onclick="unMute_$username();">Unmute</button>`;
                        });
                    };
                    const unMute_$username = () => {
                        const username = "$username";
                        const classID = "$class->id";
                        $.post("Utils/Forms/unMuteUserInClass.php", { username, classID }, () => {
                            document.getElementById("muteButton-$username").innerHTML = `<button class="btn btn-sm btn-secondary" onclick="mute_$username();">Mute</button>`;
                        });
                    };
                </script>
                <div class="btn-group" role="group">
                    <button class="btn btn-sm btn-danger" onclick="kick_$username();">Kick</button>
                    <div class="btn-group" id="muteButton-$username">
            HTML;
            if (in_array($username, array_map("mapToUsernames", $class->mutedUsers))) {
                $studentsHTML = $studentsHTML . <<<HTML
                    <button class="btn btn-sm btn-secondary" onclick="unMute_$username();">Unmute</button>
                HTML;
            } else {
                $studentsHTML = $studentsHTML . <<<HTML
                    <button class="btn btn-sm btn-secondary" onclick="mute_$username();">Mute</button>
                HTML;
            }
            $studentsHTML = $studentsHTML . <<<HTML
                    </div>
                </div>
            HTML;
        }
        $studentsHTML = $studentsHTML . <<<HTML
            </div>
            <br>
        HTML;
    }
    // Side Bar
    echo <<<HTML
            <div class="bg-dark" style="
                flex: 15%;
                margin: -30px;
                margin-top: -60px;
                margin-left: 30px;
                padding: 30px;
                padding-top: 60px;
            ">
                <h5>Teachers</h5>
                $teachersHTML
                <br>
                <h5>Students</h5>
                $studentsHTML
            </div>
        </div>
    HTML;
}
