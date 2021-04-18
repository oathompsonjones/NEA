<!-- Form for user login. -->
<form action="login?submit=login" method="post" style="padding: 10px;">
    <h1>Log In</h1>
    <!-- Username field. -->
    <div class="form-floating">
        <input type="text" class="form-control bg-dark text-light border-dark" id="inputUsername" name="username" placeholder="Username" required>
        <label for="inputUsername">Username</label>
    </div>
    <br>
    <!-- Password field. -->
    <div class="form-floating">
        <input type="password" class="form-control bg-dark text-light border-dark" id="inputPassword" name="password" placeholder="Password" required>
        <label for="inputPassword">Password</label>
    </div>
    <br>
    <!-- Login button. -->
    <button type="submit" class="btn btn-dark">Log In</button>
</form>