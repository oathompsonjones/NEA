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

    <!-- Makes the Bootstrap tooltips work. -->
    <script type="text/javascript">
        $(document).ready(function() {
            $('[data-toggle=tooltip]').tooltip();
        });
    </script>

    <!-- Gets the page title from the surrounding php and echo it. -->
    <title><?php echo "Microcontroller Animations | " . $title ?></title>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-dark bg-dark fixed-top navbar-expand-lg">
        <div class="container-fluid" style="padding-left: 30px; padding-right: 30px;">
            <!-- Links to the home page. -->
            <a class="navbar-brand" href="/">Home</a>
            <!-- Dropdown button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <!-- All nav links other than the home page. -->
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <?php
                    // Checks that the auth variable exists and is true.
                    if (isset($_SESSION["auth"]) && $_SESSION["auth"]) {
                        // Get the object for the current user.
                        $user = unserialize($_SESSION["user"]);

                        // Check which navbar needs to be loaded.
                        switch ($user->type) {
                            case "admin":
                                // Render the navbar for admins.
                                echo <<<HTML
                                        <!-- Log Out page. -->
                                        <li class="nav-item">
                                            <a class="nav-link" href="logout">Log Out</a>
                                        </li>
                                    </ul>
                                HTML;
                                break;
                                // Navbar is the same for teachers and students.
                            case "teacher":
                            case "student":
                                // Render the navbar for students and teachers.
                                echo <<<HTML
                                        <!-- Profile page. -->
                                        <li class="nav-item">
                                            <a class="nav-link" href="profile">Profile</a>
                                        </li>
                                        <!-- Animations page. -->
                                        <li class="nav-item">
                                            <a class="nav-link" href="animations">Animations</a>
                                        </li>
                                        <!-- Editor page. -->
                                        <li class="nav-item">
                                            <a class="nav-link" href="editor">Editor</a>
                                        </li>
                                        <!-- Classes page. -->
                                        <li class="nav-item">
                                            <a class="nav-link" href="class">Classes</a>
                                        </li>
                                        <!-- Log out page. -->
                                        <li class="nav-item">
                                            <a class="nav-link" href="logout">Log Out</a>
                                        </li>
                                    </ul>
                                    <!-- User search bar. -->
                                    <form class="d-flex" method="get" action="profile">
                                        <input class="form-control me-2" type="search" placeholder="Find Users..." aria-label="Search" name="searchUser">
                                        <button class="btn btn-outline-success" type="submit">Search</button>
                                    </form>
                                HTML;
                                break;
                        }
                    } else {
                        // Render the navbar for users who are not logged in.
                        echo <<<HTML
                                <!-- Log in page. -->
                                <li class="nav-item">
                                    <a class="nav-link" href="login">Log In</a>
                                </li>
                                <!-- Sign up page. -->
                                <li class="nav-item">
                                    <a class="nav-link" href="signup">Sign Up</a>
                                </li>
                            </ul>
                        HTML;
                    }
                    // Renders the user's username in the top right.
                    echo <<<HTML
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link disabled">$user->username</a>
                            </li>
                        </ul>
                    HTML;
                    ?>
            </div>
        </div>
    </nav>