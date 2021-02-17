<!-- form for user signup -->
<form action="signup?submit=signup" method="post" style="padding: 10px;">
    <h1>Sign Up</h1>
    <div class="form-group">
        <label for="inputUsername">Username</label>
        <input type="text" class="form-control" id="inputUsername" aria-describedby="usernameHelp" name="username">
    </div>
    <div class="form-group">
        <label for="inputPassword">Password</label>
        <input type="password" class="form-control" id="inputPassword" name="password">
    </div>
    <div class="form-group">
        <label for="inputPasswordConfirmation">Confirm Password</label>
        <input type="password" class="form-control" id="inputPasswordConfirmation" name="passwordConfirmation">
    </div>
    <div class="form-group">
        <label for="inputType">User Type</label>
        <select class="form-control" id="inputType" name="type">
            <option value="1">Teacher</option>
            <option value="2">Student</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Sign Up</button>
</form>