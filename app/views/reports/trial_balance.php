<?php include SITE_ROOT . '/app/views/layouts/header.php'; ?>

<div class="content-container">
    <div class="card">
        <div class="card-header">
            <h3>試算表</h3>
            <p class="text-muted">日期：<?php echo date(DISPLAY_DATE_FORMAT); ?></p>
        </div>
        <div class="card-body">
            <table class="table-bordered">
                <thead>
                    <tr>
                        <th>科目編碼</th>
                        <th>科目名稱</th>
                        <th>借方</th>
                        <th>貸方</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($accounts as $account): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($account['account_code']); ?></td>
                        <td><?php echo htmlspecialchars($account['account_name']); ?></td>
                        <td class="text-right">
                            <?php
                            if (in_array($account['account_type'], ['asset', 'expense'])) {
                                echo APP_CURRENCY_SYMBOL . number_format($account['natural_balance'], DECIMAL_PLACES);
                            }
                            ?>
                        </td>
                        <td class="text-right">
                            <?php
                            if (!in_array($account['account_type'], ['asset', 'expense'])) {
                                echo APP_CURRENCY_SYMBOL . number_format($account['natural_balance'], DECIMAL_PLACES);
                            }
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="font-weight: bold; border-top: 2px solid #333;">
                        <td colspan="2">合計</td>
                        <td class="text-right"><?php echo APP_CURRENCY_SYMBOL . number_format($total_debit, DECIMAL_PLACES); ?></td>
                        <td class="text-right"><?php echo APP_CURRENCY_SYMBOL . number_format($total_credit, DECIMAL_PLACES); ?></td>
                    </tr>
                </tfoot>
            </table>

            <?php if (abs($total_debit - $total_credit) < 0.005): ?>
            <div class="alert alert-success" style="margin-top: 20px;">
                ✓ 借貸平衡：<?php echo APP_CURRENCY_SYMBOL . number_format($total_debit, DECIMAL_PLACES); ?>
            </div>
            <?php else: ?>
            <div class="alert alert-danger" style="margin-top: 20px;">
                ✗ 借貸不平衡！差額：<?php echo APP_CURRENCY_SYMBOL . number_format(abs($total_debit - $total_credit), DECIMAL_PLACES); ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include SITE_ROOT . '/app/views/layouts/footer.php'; ?>
