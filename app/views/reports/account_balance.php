<?php include SITE_ROOT . '/app/views/layouts/header.php'; ?>

<div class="content-container">
    <div class="card">
        <div class="card-header">
            <h3>科目餘額表</h3>
            <p class="text-muted">報表日期：<?php echo date(DISPLAY_DATE_FORMAT); ?></p>
        </div>
        <div class="card-body">
            <table class="table-bordered">
                <thead>
                    <tr>
                        <th>科目編碼</th>
                        <th>科目名稱</th>
                        <th>科目類別</th>
                        <th class="text-right">餘額</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $type_groups = [
                        'asset' => ['name' => '資產', 'accounts' => [], 'total' => 0],
                        'liability' => ['name' => '負債', 'accounts' => [], 'total' => 0],
                        'equity' => ['name' => '權益', 'accounts' => [], 'total' => 0],
                        'revenue' => ['name' => '收入', 'accounts' => [], 'total' => 0],
                        'expense' => ['name' => '支出', 'accounts' => [], 'total' => 0],
                    ];

                    // 按類型分組
                    foreach ($accounts as $account) {
                        $type = $account['account_type'];
                        if (isset($type_groups[$type])) {
                            $type_groups[$type]['accounts'][] = $account;
                            $type_groups[$type]['total'] += $account['balance'];
                        }
                    }

                    // 顯示分組
                    foreach ($type_groups as $type => $group):
                        if (count($group['accounts']) > 0):
                    ?>
                    <tr style="font-weight: bold; background: #f9f9f9;">
                        <td colspan="4"><?php echo $group['name']; ?></td>
                    </tr>
                    <?php foreach ($group['accounts'] as $account): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($account['account_code']); ?></td>
                        <td><?php echo htmlspecialchars($account['account_name']); ?></td>
                        <td><?php echo $group['name']; ?></td>
                        <td class="text-right"><?php echo CURRENCY_SYMBOL . number_format($account['balance'], DECIMAL_PLACES); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr style="font-weight: bold; background: #f9f9f9;">
                        <td colspan="3"><?php echo $group['name']; ?>小計</td>
                        <td class="text-right"><?php echo CURRENCY_SYMBOL . number_format($group['total'], DECIMAL_PLACES); ?></td>
                    </tr>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </tbody>
                <tfoot>
                    <tr style="font-weight: bold; border-top: 2px solid #333;">
                        <td colspan="3">總計</td>
                        <td class="text-right">
                            <?php
                            $grand_total = array_sum(array_column($type_groups, 'total'));
                            echo CURRENCY_SYMBOL . number_format($grand_total, DECIMAL_PLACES);
                            ?>
                        </td>
                    </tr>
                </tfoot>
            </table>

            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd;">
                <h4>科目統計</h4>
                <table class="table-bordered">
                    <tr>
                        <td>資產</td>
                        <td class="text-right"><?php echo CURRENCY_SYMBOL . number_format($type_groups['asset']['total'], DECIMAL_PLACES); ?></td>
                    </tr>
                    <tr>
                        <td>負債</td>
                        <td class="text-right"><?php echo CURRENCY_SYMBOL . number_format($type_groups['liability']['total'], DECIMAL_PLACES); ?></td>
                    </tr>
                    <tr>
                        <td>權益</td>
                        <td class="text-right"><?php echo CURRENCY_SYMBOL . number_format($type_groups['equity']['total'], DECIMAL_PLACES); ?></td>
                    </tr>
                    <tr>
                        <td>收入</td>
                        <td class="text-right"><?php echo CURRENCY_SYMBOL . number_format($type_groups['revenue']['total'], DECIMAL_PLACES); ?></td>
                    </tr>
                    <tr>
                        <td>支出</td>
                        <td class="text-right"><?php echo CURRENCY_SYMBOL . number_format($type_groups['expense']['total'], DECIMAL_PLACES); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include SITE_ROOT . '/app/views/layouts/footer.php'; ?>
