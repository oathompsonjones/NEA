<!-- Form for user signup. -->
<form action="signup?submit=signup" method="post" style="padding: 10px;">
    <h1>Sign Up</h1>
    <!-- Username field. -->
    <div class="form-floating">
        <input type="text" class="form-control bg-dark text-light border-dark" id="inputUsername" aria-describedby="usernameHelp" name="username" placeholder="Username" required>
        <label for="inputUsername">Username (max. 32 characters)</label>
    </div>
    <script>
        // Ensures that the username only contains letters and numbers and is no more than 32 characters.
        $("#inputUsername").on("keypress", (e) => {
            const isValid = (n) => (/[0-9A-Za-z]/).test(String.fromCharCode(n)) && $("#inputUsername").val().length < 32;
            if (!isValid(e.which)) return false;
        });
    </script>
    <br>
    <!-- Password field. -->
    <div class="form-floating">
        <input type="password" class="form-control bg-dark text-light border-dark" id="inputPassword" name="password" placeholder="Password" required>
        <label for="inputPassword">Password</label>
    </div>
    <br>
    <!-- Password confirmation field. -->
    <div class="form-floating">
        <input type="password" class="form-control bg-dark text-light border-dark" id="inputPasswordConfirmation" name="passwordConfirmation" placeholder="Confirm Password" required>
        <label for="inputPasswordConfirmation">Confirm Password</label>
    </div>
    <br>
    <!-- User type field. -->
    <div class="form-floating">
        <select class="form-control bg-dark text-light border-dark" id="inputType" name="type" placeholder="User Type" required>
            <option value="2">Student</option>
            <option value="1">Teacher</option>
        </select>
        <label for="inputType">User Type</label>
    </div>
    <br>
    <!-- Sign up button. -->
    <button type="submit" class="btn btn-dark">Sign Up</button>
</form>