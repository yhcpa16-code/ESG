<?php include SITE_ROOT . '/app/views/layouts/header.php'; ?>

<div class="content-container">
    <div class="card" style="max-width: 500px; margin: 0 auto;">
        <div class="card-header">
            <h3>修改密碼</h3>
        </div>
        <div class="card-body">
            <form method="POST" class="form">
                <div class="form-group">
                    <label for="old_password">舊密碼 *</label>
                    <input type="password" id="old_password" name="old_password" required>
                </div>

                <div class="form-group">
                    <label for="new_password">新密碼 *</label>
                    <input type="password" id="new_password" name="new_password" required>
                    <small style="color: #9ca3af;">密碼長度至少 6 個字符</small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">確認新密碼 *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">修改密碼</button>
                    <a href="<?php echo SITE_URL; ?>index.php?controller=dashboard" class="btn-secondary">返回</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include SITE_ROOT . '/app/views/layouts/footer.php'; ?>
