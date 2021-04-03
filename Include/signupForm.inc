<!-- form for user signup -->
<form action="signup?submit=signup" method="post" style="padding: 10px;">
    <h1>Sign Up</h1>
    <div class="form-floating">
        <input type="text" class="form-control bg-dark text-light border-dark" id="inputUsername" aria-describedby="usernameHelp" name="username" placeholder="Username" required>
        <label for="inputUsername">Username</label>
    </div>
    <script>
        $("#inputUsername").on("keypress", (e) => {
            if (e.which === 32) return false;
        });
    </script>
    <br>
    <div class="form-floating">
        <input type="password" class="form-control bg-dark text-light border-dark" id="inputPassword" name="password" placeholder="Password" required>
        <label for="inputPassword">Password</label>
    </div>
    <br>
    <div class="form-floating">
        <input type="password" class="form-control bg-dark text-light border-dark" id="inputPasswordConfirmation" name="passwordConfirmation" placeholder="Confirm Password" required>
        <label for="inputPasswordConfirmation">Confirm Password</label>
    </div>
    <br>
    <div class="form-floating">
        <select class="form-control bg-dark text-light border-dark" id="inputType" name="type" placeholder="User Type" required>
            <option value="2">Student</option>
            <option value="1">Teacher</option>
        </select>
        <label for="inputType">User Type</label>
    </div>
    <br>
    <button type="submit" class="btn btn-dark">Sign Up</button>
</form>