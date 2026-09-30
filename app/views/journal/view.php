<?php include SITE_ROOT . '/app/views/layouts/header.php'; ?>

<div class="content-container">
    <div class="card" style="max-width: 700px; margin: 0 auto;">
        <div class="card-header">
            <h3>日記帳詳情</h3>
        </div>
        <div class="card-body">
            <div class="detail-container">
                <div class="detail-row">
                    <div class="detail-label">憑證號</div>
                    <div class="detail-value"><?php echo htmlspecialchars($entry['voucher_no']); ?></div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">日期</div>
                    <div class="detail-value"><?php echo date(DISPLAY_DATE_FORMAT, strtotime($entry['entry_date'])); ?></div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">借方科目</div>
                    <div class="detail-value">
                        <?php echo htmlspecialchars($entry['debit_code'] . ' - ' . $entry['debit_name']); ?>
                    </div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">貸方科目</div>
                    <div class="detail-value">
                        <?php echo htmlspecialchars($entry['credit_code'] . ' - ' . $entry['credit_name']); ?>
                    </div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">金額</div>
                    <div class="detail-value">
                        <?php echo CURRENCY_SYMBOL . number_format($entry['amount'], DECIMAL_PLACES); ?>
                    </div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">說明</div>
                    <div class="detail-value"><?php echo htmlspecialchars($entry['description'] ?? '無'); ?></div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">建檔人員</div>
                    <div class="detail-value"><?php echo htmlspecialchars($entry['user_name']); ?></div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">建檔時間</div>
                    <div class="detail-value"><?php echo date(DATETIME_FORMAT, strtotime($entry['created_at'])); ?></div>
                </div>

                <div style="margin-top: 30px; display: flex; gap: 10px;">
                    <a href="<?php echo SITE_URL; ?>index.php?controller=journal&action=edit&id=<?php echo $entry['entry_id']; ?>" class="btn-primary">編輯</a>
                    <a href="<?php echo SITE_URL; ?>index.php?controller=journal&action=delete&id=<?php echo $entry['entry_id']; ?>" class="btn-danger" onclick="return confirm('確定要刪除此記錄嗎?');">刪除</a>
                    <a href="<?php echo SITE_URL; ?>index.php?controller=journal&action=index" class="btn-secondary">返回</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include SITE_ROOT . '/app/views/layouts/footer.php'; ?>
