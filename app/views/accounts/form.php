<?php include SITE_ROOT . '/app/views/layouts/header.php'; ?>

<div class="content-container">
    <div class="card">
        <div class="card-body">
            <form method="POST" class="form">
                <div class="form-group">
                    <label for="account_code">科目編碼 *</label>
                    <input type="text" id="account_code" name="account_code"
                           value="<?php echo isset($account['account_code']) ? htmlspecialchars($account['account_code']) : ''; ?>"
                           <?php echo isset($account) ? 'readonly' : ''; ?> required>
                </div>

                <div class="form-group">
                    <label for="account_name">科目名稱 *</label>
                    <input type="text" id="account_name" name="account_name"
                           value="<?php echo isset($account['account_name']) ? htmlspecialchars($account['account_name']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="account_type">科目類別 *</label>
                    <select id="account_type" name="account_type" required>
                        <option value="">-- 請選擇 --</option>
                        <?php foreach ($types as $key => $name): ?>
                        <option value="<?php echo $key; ?>"
                                <?php echo (isset($account['account_type']) && $account['account_type'] === $key) ? 'selected' : ''; ?>>
                            <?php echo $name; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="description">說明</label>
                    <textarea id="description" name="description" rows="4"><?php echo isset($account['description']) ? htmlspecialchars($account['description']) : ''; ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">保存</button>
                    <a href="<?php echo SITE_URL; ?>index.php?controller=account&action=index" class="btn-secondary">返回</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include SITE_ROOT . '/app/views/layouts/footer.php'; ?>
