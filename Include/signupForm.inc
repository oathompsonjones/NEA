<!-- form for user signup -->
<form action="signup?submit=signup" method="post" style="padding: 10px;">
    <h1>Sign Up</h1>
    <div class="form-floating">
        <input type="text" class="form-control bg-dark text-white" id="inputUsername" aria-describedby="usernameHelp" name="username" placeholder="Username">
        <label for="inputUsername">Username</label>
    </div>
    <br>
    <div class="form-floating">
        <input type="password" class="form-control bg-dark text-white" id="inputPassword" name="password" placeholder="Password">
        <label for="inputPassword">Password</label>
    </div>
    <br>
    <div class="form-floating">
        <input type="password" class="form-control bg-dark text-white" id="inputPasswordConfirmation" name="passwordConfirmation" placeholder="Confirm Password">
        <label for="inputPasswordConfirmation">Confirm Password</label>
    </div>
    <br>
    <div class="form-floating">
        <select class="form-control bg-dark text-white" id="inputType" name="type" placeholder="User Type">
            <option value="1">Teacher</option>
            <option value="2">Student</option>
        </select>
        <label for="inputType">User Type</label>
    </div>
    <br>
    <button type="submit" class="btn btn-dark">Sign Up</button>
</form>