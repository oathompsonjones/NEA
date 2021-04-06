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
    for ($i = 0; $i < count($classes); ++$i) {
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
    $teachers = $class->teachers;
    $teachersHTML = "";
    for ($i = 0; $i < count($teachers); ++$i) {
        $username = $teachers[$i]->username;
        $teachersHTML = $teachersHTML . <<<HTML
            <a href="profile?user=$username">$username</a><br>
        HTML;
    }
    $students = $class->students;
    $studentsHTML = "";
    for ($i = 0; $i < count($students); ++$i) {
        $username = $students[$i]->username;
        $studentsHTML = $studentsHTML . <<<HTML
            <a href="profile?user=$username">$username</a><br>
        HTML;
    }
    echo <<<HTML
        <div style="display: flex; width: 100%; height: 100%;">
            <div style="flex: 85%;">
                <div class="btn-group" style="width: 100%; margin-bottom: 10px;">
                    <button class="btn btn-dark" disabled><h5>$class->name</h5></button>
    HTML;
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
                            $.post("Utils/Forms/sendChatMessage.php", { message, username, classID }, (...args) => { document.getElementById("messageInput").value = ""; console.log(args); });
                        };
                        const updateMessages = () => {
                            const classID = "$class->id";
                            const currentHTML = document.getElementById("messages").innerHTML;
                            const messageCount = currentHTML?.match(/<div class="card bg-dark text-light">/g)?.length ?? 0;
                            $.post("Utils/Forms/updateMessages.php", { classID, currentHTML, messageCount }, (messages) => {
                                if (messages !== currentHTML) {
                                    document.getElementById("messages").innerHTML = messages;
                                    document.getElementById("messages").scrollTop = document.getElementById("messages").scrollHeight;
                                }
                            });
                        };
                        setInterval(updateMessages, 500);
                    </script>
                    <div class="input-group mb-3">
                        <input id="messageInput" class="form-control bg-dark border-dark text-light" type="text" placeholder="Message $class->name">
                        <button type="button" class="btn btn-dark" onclick="sendMessage()">Send</button>
                    </div>
                </div>
            </div>
        HTML;
    }
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
            </div>
        HTML;
    }
    echo <<<HTML
                <div id="assignments" style="height: 90%;">
                    Assignments
                </div>
            </div>
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
