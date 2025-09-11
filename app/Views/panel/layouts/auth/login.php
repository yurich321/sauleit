<div class="main-container">
    <div class="auth-container">
        <div class="auth-card">
            <div>Admin panel</div>
            <form method="post" action="panel/login" class="form">
                <?= csrf()::field();?>
                <div class="input-container">
                    <label for="email">Email</label>
                    <input name="email" type="text" id="email" required>
                </div>

                <div class="input-container">
                    <label for="pass">Password</label>
                    <input name="password" type="password" id="pass" required>
                </div>

                <button type="submit" class="btn primary">Sign in</button>
            </form>
        </div>
    </div>
</div>

