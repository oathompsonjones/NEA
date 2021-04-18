<?php
// Gets the database object from the session.
$db = $_SESSION["database"];
// Gets the logged in user object from the session.
$user = unserialize($_SESSION["user"]);
// Gets an array of classes that the user is in.
$classes = is_null($user->classes) ? [] : $user->classes;

// Gets an array containing the IDs of every class.
$validClassIDs = array_map("mapToFirstItem", $db->select("ClassID", "Class"));
// Converts these IDs to JSON.
$validClassIDsJSON = json_encode($validClassIDs);

// Checks that the user has not selected a class.
if (!isset($_GET["classID"]) || is_null($_GET["classID"])) {
    echo <<<HTML
        <!-- This will contain a warning if there is an invalid class ID. -->
        <div id="invalidCodeWarning"></div>
        <h1>Classes</h1>
        <div class="row row-cols-2">
            <!-- Form to allow the user to join a new class. -->
            <div>
                <h5>Join a new class</h5>
                <script>
                    // Function runs an AJAX request to update the db in the background, then redirects to the correct class.
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
    // Allows teachers to create new classes.
    if ($user->type === "teacher") echo <<<HTML
        <div>
            <h5>Create a new class</h5>
            <script>
                // Runs AJAX request then redirects.
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
    // Stores the HTML that will render all classes.
    $html = "";
    // Calculates how many classes the user is in so that this doesn't need to be done on every loop iteration.
    $classCount = count($classes);
    // Loops through every class the user is in.
    for ($i = 0; $i < $classCount; ++$i) {
        // Gets the HTML to render this class.
        $classHTML = $classes[$i]->render();
        // Wraps the class HTML in a column div.
        $html .= <<<HTML
            <div class="col">$classHTML</div>
        HTML;
    }
    // Outputs the HTML.
    echo <<<HTML
        <h5>Your Classes</h5>
        <div class="row row-cols-1 row-cols-md-3 g-4">$html</div>
    HTML;
}
// This renders a class once the ID has been given.
else {
    // Stores the classID.
    $classID = $_GET["classID"];
    // Checks that a class exists with this ID.
    $classExits = in_array($classID, $validClassIDs);
    // Gets the class' Group object.
    $class = new Group($classID);
    // Checks that the logged in user is a member of the class.
    $userIsInClass = in_array($user->username, array_map("mapToUsernames", $class->students)) || in_array($user->username, array_map("mapToUsernames", $class->teachers));
    // If the class does not exists, or if the user isn't in the class, redirects to the classes page.
    if (!($classExits && $userIsInClass)) echo <<<HTML
        <script>window.location.replace("class")</script>
    HTML;
    // Rendering the class page starts here.
    echo <<<HTML
        <!-- Page wrapper. -->
        <div style="display: flex; width: 100%; height: 100%;">
            <!-- Main section of the page. -->
            <div style="flex: 85%;">
                <!-- Class navbar. -->
                <div class="btn-group" style="width: 100%; margin-bottom: 10px;">
                    <!-- Uses a disabled button to display the class name. -->
                    <button class="btn btn-dark" disabled><h5>$class->name</h5></button>
    HTML;
    // Chat button
    if ($class->chatEnabled) echo <<<HTML
        <script>
            // Stops displaying the other sections, displays the chat, then scrolls the chat to the bottom.
            const showChat = () => {
                if (document.getElementById("settings")) document.getElementById("settings").style.display = "none";
                if (document.getElementById("assignments")) document.getElementById("assignments").style.display = "none";
                if (document.getElementById("chat")) document.getElementById("chat").style.display = "";
                document.getElementById("messages").scrollTop = document.getElementById("messages").scrollHeight;
            };
        </script>
        <button onclick="showChat();" class="btn btn-dark">Chat</button>
    HTML;
    // Chat can be disabled.
    else echo <<<HTML
        <button class="btn btn-dark" disabled>Chat</button>
    HTML;
    // Settings Button. Only exists if user is a teacher.
    if ($user->type === "teacher") echo <<<HTML
        <script>
            // Stops displaying the other sections, then displays the settings.
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
                // Stops displaying the other sections, then displays the assignments.
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
            <!-- Chat wrapper. -->
            <div id="chat" style="display: none; height: 90%;">
                <!-- Stores the content of all messages. -->
                <div id="messages" style="height: 95%; width: 100%; overflow: auto;"></div>
                <!-- Allows the user to send messages. -->
                <div id="input" style="height: 5%; width: 100%;">
                    <script>
                        // Gets the message content, exits if it has no content, sends AJAX request to update DB, then clears the user's input.
                        const sendMessage = () => {
                            const message = document.getElementById("messageInput").value?.trim();
                            if (!message.length) return false;
                            const username = "$user->username";
                            const classID = "$class->id";
                            $.post("Utils/Forms/sendChatMessage.php", { message, username, classID }, () => document.getElementById("messageInput").value = "");
                        };
                        // Sends AJAX request to update DB without refreshing page.
                        const deleteMessage = (messageID) => $.post("Utils/Forms/deleteChatMessage.php", { messageID }, () => $("#" + messageID).hide());
                        // Sends AJAX request to request data from DB without refreshing, then updates the messages div and scrolls down.
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
                        // Sends AJAX request to check if the user is muted orr not, then hides/shows the input field accordingly.
                        const updateInput = () => {
                            const classID = "$class->id";
                            const username = "$user->username";
                            $.post("Utils/Forms/updateClassChatInput.php", { classID, username }, (isMuted) => {
                                if (isMuted === "true") $("#userInput").hide();
                                else $("#userInput").show();
                            });
                        };
                        // Updates the messages and input section twice per second.
                        setInterval(() => {
                            updateMessages();
                            updateInput();
                        }, 500);
                    </script>
                    <!-- The input field. -->
                    <div id="userInput" class="input-group mb-3">
                        <input id="messageInput" class="form-control bg-dark border-dark text-light" type="text" placeholder="Message $class->name">
                        <button id="messageSend" type="button" class="btn btn-dark" onclick="sendMessage()">Send</button>
                        <script>
                            // Allows user to press enter to send the message.
                            $("#messageInput").keydown((event) => {
                                if (event.keyCode === 13) $("#messageSend").click();
                            });
                        </script>
                    </div>
                </div>
            </div>
        HTML;
    }
    // Settings Page - only accessible by teachers.
    if ($user->type === "teacher") {
        echo <<<HTML
            <div id="settings" style="display: none; height: 90%;">
                <div class="form-floating">
                    <input id="inputClassName" name="className" type="text" class="form-control bg-dark text-light border-dark" placeholder="Class Name" aria-label="Class Name" value="$class->name">
                    <label for="inputClassName">Class Name</label>
                </div>
                <div class="form-check">
        HTML;
        // Allows teachers to toggle chat.
        if ($class->chatEnabled) echo <<<HTML
            <input class="form-check-input" type="checkbox" value="" id="inputChatEnabled" checked>
        HTML;
        else echo <<<HTML
            <input class="form-check-input" type="checkbox" value="" id="inputChatEnabled">
        HTML;
        // Gets the current URL.
        $url = $_SERVER["REQUEST_URI"];
        echo <<<HTML
                    <label class="form-check-label" for="inputChatEnabled">Chat Enabled</label>
                </div>
                <script>
                    // Updates DB via AJAX, then redirects to current URL, which will display the default page for the class.
                    const saveClass = () => {
                        const name = document.getElementById("inputClassName").value;
                        const chatEnabled = Number(document.getElementById("inputChatEnabled").checked);
                        const classID = "$class->id";
                        $.post("Utils/Forms/saveClass.php", { name, chatEnabled, classID }, () => window.location.replace("$url"));
                    };
                    // Updates DB via AJAX request, then redirects back to classes page.
                    const deleteClass = () => {
                        const classID = "$class->id";
                        $.post("Utils/Forms/deleteClass.php", { classID }, () => window.location.replace("class"));
                    };
                </script>
                <button onclick="saveClass();" class="btn btn-dark">Save</button>
                <button onclick="deleteClass();" class="btn btn-danger">Delete</button>
                <!-- Allows teachers to access and share the class code. -->
                <p class="text-muted">Class ID: $class->id (Send this code to any students you wish to invite.)</p>
            </div>
        HTML;
    }
    // Assignments Page
    echo <<<HTML
        <div id="assignments" style="height: 90%; overflow-y: auto;">
    HTML;
    // Teachers can create and delete assignments.
    if ($user->type === "teacher") echo <<<HTML
        <script>
            // Uses AJAX because refreshing is bad.
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
        <!-- Form to create assignment. -->
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
    // Students can hand in assignments.
    else echo <<<HTML
        <script>
            // AJAX, then update page.
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
    // Gets all assignments for the class.
    $assignments = $class->assignments;
    // Sorts assignments into past and future due dates.
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
    // Loops through each teacher in the class and renders them at the top of the list.
    for ($i = 0; $i < $teacherCount; ++$i) {
        $username = $teachers[$i]->username;
        $teachersHTML .= <<<HTML
            <div id="$username" style="display: flex;">
                <a class="btn btn-dark" href="profile?user=$username">$username</a>
        HTML;
        $teachersHTML .= <<<HTML
            </div>
            <br>
        HTML;
    }
    // Student List
    $students = $class->students;
    $studentsHTML = "";
    $studentCount = count($students);
    // Loops through each student and renders them below teachers.
    for ($i = 0; $i < $studentCount; ++$i) {
        $username = $students[$i]->username;
        $studentsHTML .= <<<HTML
            <div id="$username" style="display: flex;">
                <a class="btn btn-dark" href="profile?user=$username">$username</a>
        HTML;
        // If the current user is a teacher, they can kick and mute students.
        if ($user->type === "teacher") {
            $studentsHTML .= <<<HTML
                <script>
                    // Sends an AJAX request to update the db.
                    const kick_$username = () => {
                        const username = "$username";
                        const classID = "$class->id";
                        $.post("Utils/Forms/kickUserFromClass.php", { username, classID }, () => {
                            console.log(document.getElementById("$username"))
                            document.getElementById("$username").style.display = "none";
                        });
                    };
                    // Sends an AJAX request to update the db.
                    const mute_$username = () => {
                        const username = "$username";
                        const classID = "$class->id";
                        $.post("Utils/Forms/muteUserInClass.php", { username, classID }, () => {
                            document.getElementById("muteButton-$username").innerHTML = `<button class="btn btn-sm btn-secondary" onclick="unMute_$username();">Unmute</button>`;
                        });
                    };
                    // Sends an AJAX request to update the db.
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
                $studentsHTML .= <<<HTML
                    <button class="btn btn-sm btn-secondary" onclick="unMute_$username();">Unmute</button>
                HTML;
            } else {
                $studentsHTML .= <<<HTML
                    <button class="btn btn-sm btn-secondary" onclick="mute_$username();">Mute</button>
                HTML;
            }
            $studentsHTML .= <<<HTML
                    </div>
                </div>
            HTML;
        }
        $studentsHTML .= <<<HTML
            </div>
            <br>
        HTML;
    }
    // Side Bar - displays class members.
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
