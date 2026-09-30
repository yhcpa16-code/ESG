<?php include SITE_ROOT . '/app/views/layouts/header.php'; ?>

<div class="content-container">
    <div class="card">
        <div class="card-body">
            <form method="POST" class="form">
                <div class="form-row">
                    <div class="form-col-6">
                        <div class="form-group">
                            <label for="voucher_no">憑證號 *</label>
                            <input type="text" id="voucher_no" name="voucher_no"
                                   value="<?php echo isset($entry) ? htmlspecialchars($entry['voucher_no']) : htmlspecialchars($voucher_no); ?>"
                                   <?php echo isset($entry) ? 'readonly' : ''; ?> required>
                        </div>
                    </div>
                    <div class="form-col-6">
                        <div class="form-group">
                            <label for="entry_date">日期 *</label>
                            <input type="date" id="entry_date" name="entry_date"
                                   value="<?php echo isset($entry) ? $entry['entry_date'] : date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col-6">
                        <div class="form-group">
                            <label for="debit_account">借方科目 *</label>
                            <select id="debit_account" name="debit_account" required>
                                <option value="">-- 請選擇 --</option>
                                <?php foreach ($accounts as $acc): ?>
                                <option value="<?php echo $acc['account_id']; ?>"
                                        <?php echo (isset($entry) && $entry['debit_account'] == $acc['account_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($acc['account_code'] . ' - ' . $acc['account_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-col-6">
                        <div class="form-group">
                            <label for="credit_account">貸方科目 *</label>
                            <select id="credit_account" name="credit_account" required>
                                <option value="">-- 請選擇 --</option>
                                <?php foreach ($accounts as $acc): ?>
                                <option value="<?php echo $acc['account_id']; ?>"
                                        <?php echo (isset($entry) && $entry['credit_account'] == $acc['account_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($acc['account_code'] . ' - ' . $acc['account_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="amount">金額 *</label>
                    <input type="number" id="amount" name="amount" step="0.01" min="0"
                           value="<?php echo isset($entry) ? $entry['amount'] : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">說明</label>
                    <textarea id="description" name="description" rows="4"><?php echo isset($entry) ? htmlspecialchars($entry['description']) : ''; ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">保存</button>
                    <a href="<?php echo SITE_URL; ?>index.php?controller=journal&action=index" class="btn-secondary">返回</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include SITE_ROOT . '/app/views/layouts/footer.php'; ?>
