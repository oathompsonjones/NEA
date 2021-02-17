<!-- form for user login -->
<form action="login?submit=login" method="post" style="padding: 10px;">
    <h1>Log In</h1>
    <div class="form-group">
        <label for="inputUsername">Username</label>
        <input type="text" class="form-control" id="inputUsername" aria-describedby="usernameHelp" name="username">
    </div>
    <div class="form-group">
        <label for="inputPassword">Password</label>
        <input type="password" class="form-control" id="inputPassword" name="password">
    </div>
    <button type="submit" class="btn btn-primary">Log In</button>
</form>