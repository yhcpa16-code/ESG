<?php include SITE_ROOT . '/app/views/layouts/header.php'; ?>

<div class="content-container">
    <div class="card">
        <div class="card-header">
            <h3>資產負債表</h3>
            <p class="text-muted">報表日期：<?php echo date(DISPLAY_DATE_FORMAT); ?></p>
        </div>
        <div class="card-body">
            <div class="balance-sheet">
                <div class="bs-section">
                    <h4>資產</h4>
                    <table class="table-bordered">
                        <tbody>
                            <?php foreach ($asset_accounts as $account): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($account['account_code'] . ' ' . $account['account_name']); ?></td>
                                <td class="text-right"><?php echo APP_CURRENCY_SYMBOL . number_format($account['natural_balance'], DECIMAL_PLACES); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr style="font-weight: bold; border-top: 2px solid #333;">
                                <td>總資產</td>
                                <td class="text-right"><?php echo APP_CURRENCY_SYMBOL . number_format($total_assets, DECIMAL_PLACES); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="bs-section">
                    <h4>負債與權益</h4>
                    <table class="table-bordered">
                        <tbody>
                            <tr style="font-weight: bold; background: #f9f9f9;">
                                <td colspan="2">負債</td>
                            </tr>
                            <?php foreach ($liability_accounts as $account): ?>
                            <tr>
                                <td style="padding-left: 20px;"><?php echo htmlspecialchars($account['account_code'] . ' ' . $account['account_name']); ?></td>
                                <td class="text-right"><?php echo APP_CURRENCY_SYMBOL . number_format($account['natural_balance'], DECIMAL_PLACES); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr style="font-weight: bold; background: #f9f9f9;">
                                <td style="padding-left: 20px;">負債小計</td>
                                <td class="text-right"><?php echo APP_CURRENCY_SYMBOL . number_format($total_liabilities, DECIMAL_PLACES); ?></td>
                            </tr>

                            <tr style="font-weight: bold; background: #f9f9f9;">
                                <td colspan="2">權益</td>
                            </tr>
                            <?php foreach ($equity_accounts as $account): ?>
                            <tr>
                                <td style="padding-left: 20px;"><?php echo htmlspecialchars($account['account_code'] . ' ' . $account['account_name']); ?></td>
                                <td class="text-right"><?php echo APP_CURRENCY_SYMBOL . number_format($account['natural_balance'], DECIMAL_PLACES); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td style="padding-left: 20px;">本期損益（收入－費用，尚未結轉）</td>
                                <td class="text-right"><?php echo APP_CURRENCY_SYMBOL . number_format($current_income, DECIMAL_PLACES); ?></td>
                            </tr>
                            <tr style="font-weight: bold; background: #f9f9f9;">
                                <td style="padding-left: 20px;">權益小計</td>
                                <td class="text-right"><?php echo APP_CURRENCY_SYMBOL . number_format($total_equity, DECIMAL_PLACES); ?></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr style="font-weight: bold; border-top: 2px solid #333;">
                                <td>負債與權益合計</td>
                                <td class="text-right"><?php echo APP_CURRENCY_SYMBOL . number_format($total_liabilities + $total_equity, DECIMAL_PLACES); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <?php if (abs($total_assets - ($total_liabilities + $total_equity)) < 0.005): ?>
                <div class="alert alert-success" style="margin-top: 20px;">
                    ✓ 資產等於負債加權益（平衡）
                </div>
                <?php else: ?>
                <div class="alert alert-danger" style="margin-top: 20px;">
                    ✗ 不平衡！差額：<?php echo APP_CURRENCY_SYMBOL . number_format(abs($total_assets - ($total_liabilities + $total_equity)), DECIMAL_PLACES); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.balance-sheet .bs-section {
    margin-bottom: 30px;
}
.balance-sheet h4 {
    font-size: 18px;
    margin-bottom: 15px;
    border-bottom: 2px solid #667eea;
    padding-bottom: 10px;
}
</style>

<?php include SITE_ROOT . '/app/views/layouts/footer.php'; ?>
