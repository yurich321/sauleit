<div class="container">
    <div class="auth-card">
        <?php if (!empty($error)): ?>
            <div class="alert error"><?= htmlspecialchars($error,ENT_QUOTES,'UTF-8') ?></div>
        <?php endif; ?>
        <form method="post" action="/login" class="form">
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
