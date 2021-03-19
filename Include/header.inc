<!doctype html>
<html lang="en">
    <head>
        <!-- Meta Tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
        <link rel="stylesheet" href="CSS/main.css">

        <!-- jQuery -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
        
        <!--Tooltips-->
        <script type="text/javascript">
            $(document).ready(function() {
                $('[data-toggle=tooltip]').tooltip();
            }); 
        </script>
        
        <title><?php echo "Microcontroller Animations | " . $title ?></title>
    </head>
    <body>
        <nav class="navbar navbar-dark bg-dark fixed-top navbar-expand-lg">
            <div class="container-fluid">
                <a class="navbar-brand" href="/">Home</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <?php
                    // Checks that the auth variable exists and is true.
                    if(isset($_SESSION["auth"]) && $_SESSION["auth"]) {
                        // Get the object for the current user.
                        $user = unserialize($_SESSION["user"]);

                        // Check which navbar needs to be loaded.
                        switch ($user->type) {
                            case "admin":
                                // Render the navbar for admins.
                                echo <<<HTML
                                    <li class="nav-item">
                                        <a class="nav-link" href="logout">Log Out</a>
                                    </li>
                                HTML;
                                break;
                            case "teacher":
                            case "student":
                                // Render the navbar for students and teachers.
                                echo <<<HTML
                                    <li class="nav-item">
                                        <a class="nav-link" href="animations">Animations</a>
                                    </li>

                                    <li class="nav-item">
                                        <a class="nav-link" href="editor">Editor</a>
                                    </li>

                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Classes</a>
                                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                            <li><a class="dropdown-item" href="class?classID=1">Class 1</a></li>
                                            <li><a class="dropdown-item" href="class?classID=2">Class 2</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item" href="class">Join new class</a></li>
                                        </ul>
                                    </li>

                                    <li class="nav-item">
                                        <a class="nav-link" href="profile">Profile</a>
                                    </li>

                                    <li class="nav-item">
                                        <a class="nav-link" href="logout">Log Out</a>
                                    </li>
                                HTML;
                                break;
                        }
                    } else {
                        // Render the navbar for users who are not logged in.
                        echo <<<HTML
                            <li class="nav-item">
                                <a class="nav-link" href="login">Log In</a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="signup">Sign Up</a>
                            </li>
                        HTML;
                    }
                    ?>
                    </ul>
                </div>
            </div>
        </nav>