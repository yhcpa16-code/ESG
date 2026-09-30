<?php include SITE_ROOT . '/app/views/layouts/header.php'; ?>

<div class="content-container">
    <div class="card">
        <div class="card-header">
            <h3>損益表</h3>
            <p class="text-muted">期間：<?php echo date(DISPLAY_DATE_FORMAT, strtotime($start_date)); ?> 至 <?php echo date(DISPLAY_DATE_FORMAT, strtotime($end_date)); ?></p>
        </div>
        <div class="card-body">
            <table class="table-bordered">
                <thead>
                    <tr>
                        <th>項目</th>
                        <th class="text-right">金額</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="font-weight: bold; background: #f9f9f9;">
                        <td colspan="2">收入</td>
                    </tr>
                    <?php foreach ($revenue_accounts as $account): ?>
                    <tr>
                        <td style="padding-left: 20px;">
                            <?php echo htmlspecialchars($account['account_code'] . ' - ' . $account['account_name']); ?>
                        </td>
                        <td class="text-right">
                            <?php echo CURRENCY_SYMBOL . number_format($account['period_amount'], DECIMAL_PLACES); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr style="font-weight: bold; background: #f9f9f9;">
                        <td style="padding-left: 20px;">收入合計</td>
                        <td class="text-right"><?php echo CURRENCY_SYMBOL . number_format($total_revenue, DECIMAL_PLACES); ?></td>
                    </tr>

                    <tr style="font-weight: bold; background: #f9f9f9;">
                        <td colspan="2">支出</td>
                    </tr>
                    <?php foreach ($expense_accounts as $account): ?>
                    <tr>
                        <td style="padding-left: 20px;">
                            <?php echo htmlspecialchars($account['account_code'] . ' - ' . $account['account_name']); ?>
                        </td>
                        <td class="text-right">
                            <?php echo CURRENCY_SYMBOL . number_format($account['period_amount'], DECIMAL_PLACES); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr style="font-weight: bold; background: #f9f9f9;">
                        <td style="padding-left: 20px;">支出合計</td>
                        <td class="text-right"><?php echo CURRENCY_SYMBOL . number_format($total_expense, DECIMAL_PLACES); ?></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr style="font-weight: bold; border-top: 2px solid #333;">
                        <td>淨收入</td>
                        <td class="text-right" style="color: <?php echo $net_income >= 0 ? '#10b981' : '#ef4444'; ?>;">
                            <?php echo CURRENCY_SYMBOL . number_format($net_income, DECIMAL_PLACES); ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php include SITE_ROOT . '/app/views/layouts/footer.php'; ?>
