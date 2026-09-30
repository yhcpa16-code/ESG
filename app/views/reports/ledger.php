<?php include SITE_ROOT . '/app/views/layouts/header.php'; ?>

<div class="content-container">
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-body">
            <form method="GET" style="display: flex; gap: 10px; align-items: flex-end;">
                <div style="flex: 1;">
                    <label for="account_id" style="display: block; margin-bottom: 5px;">選擇科目</label>
                    <select name="account_id" id="account_id" onchange="this.form.submit()">
                        <option value="">-- 請選擇科目 --</option>
                        <?php foreach ($accounts as $acc): ?>
                        <option value="<?php echo $acc['account_id']; ?>"
                                <?php echo $account_id == $acc['account_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($acc['account_code'] . ' - ' . $acc['account_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <?php if ($account): ?>
    <div class="card">
        <div class="card-header">
            <h3>分類帳 - <?php echo htmlspecialchars($account['account_code'] . ' ' . $account['account_name']); ?></h3>
        </div>
        <div class="card-body">
            <?php if (count($entries) > 0): ?>
            <table class="table-bordered">
                <thead>
                    <tr>
                        <th>日期</th>
                        <th>憑證號</th>
                        <th>對方科目</th>
                        <th>說明</th>
                        <th class="text-right">借方</th>
                        <th class="text-right">貸方</th>
                        <th class="text-right">餘額</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $running_balance = $account['balance'];
                    foreach ($entries as $entry):
                        $is_debit = $entry['debit_account'] == $account_id;
                        $opposite_code = $is_debit ? $entry['credit_code'] : $entry['debit_code'];
                        $opposite_name = $is_debit ? $entry['credit_name'] : $entry['debit_name'];
                    ?>
                    <tr>
                        <td><?php echo date(DISPLAY_DATE_FORMAT, strtotime($entry['entry_date'])); ?></td>
                        <td><?php echo htmlspecialchars($entry['voucher_no']); ?></td>
                        <td><?php echo htmlspecialchars($opposite_code . ' ' . $opposite_name); ?></td>
                        <td><?php echo htmlspecialchars(substr($entry['description'], 0, 20)); ?></td>
                        <td class="text-right">
                            <?php echo $is_debit ? CURRENCY_SYMBOL . number_format($entry['amount'], DECIMAL_PLACES) : ''; ?>
                        </td>
                        <td class="text-right">
                            <?php echo !$is_debit ? CURRENCY_SYMBOL . number_format($entry['amount'], DECIMAL_PLACES) : ''; ?>
                        </td>
                        <td class="text-right">
                            <?php
                            if ($is_debit) {
                                $running_balance -= $entry['amount'];
                            } else {
                                $running_balance += $entry['amount'];
                            }
                            echo CURRENCY_SYMBOL . number_format($running_balance, DECIMAL_PLACES);
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="font-weight: bold; border-top: 2px solid #333;">
                        <td colspan="6" class="text-right">期末餘額：</td>
                        <td class="text-right"><?php echo CURRENCY_SYMBOL . number_format($account['balance'], DECIMAL_PLACES); ?></td>
                    </tr>
                </tfoot>
            </table>
            <?php else: ?>
            <p class="text-center text-muted">此科目暫無交易記錄</p>
            <?php endif; ?>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-info">
        請選擇一個科目以查看其分類帳
    </div>
    <?php endif; ?>
</div>

<?php include SITE_ROOT . '/app/views/layouts/footer.php'; ?>
