<?php include SITE_ROOT . '/app/views/layouts/header.php'; ?>

<div class="dashboard-container">
    <!-- 統計卡片 -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-content">
                <div class="stat-label">科目總數</div>
                <div class="stat-value"><?php echo $account_count; ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📈</div>
            <div class="stat-content">
                <div class="stat-label">本月交易筆數</div>
                <div class="stat-value"><?php echo $month_count; ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-content">
                <div class="stat-label">本月交易金額</div>
                <div class="stat-value"><?php echo APP_CURRENCY_SYMBOL . number_format($month_amount, DECIMAL_PLACES); ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📉</div>
            <div class="stat-content">
                <div class="stat-label">今日交易</div>
                <div class="stat-value"><?php echo $today_count; ?></div>
            </div>
        </div>
    </div>

    <!-- 財務概況 -->
    <div class="dashboard-row">
        <div class="dashboard-col-6">
            <div class="card">
                <div class="card-header">
                    <h3>資產負債權益概況</h3>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <td>總資產</td>
                            <td class="text-right"><?php echo APP_CURRENCY_SYMBOL . number_format($total_assets, DECIMAL_PLACES); ?></td>
                        </tr>
                        <tr>
                            <td>總負債</td>
                            <td class="text-right"><?php echo APP_CURRENCY_SYMBOL . number_format($total_liabilities, DECIMAL_PLACES); ?></td>
                        </tr>
                        <tr>
                            <td>總權益（含本期損益）</td>
                            <td class="text-right"><?php echo APP_CURRENCY_SYMBOL . number_format($total_equity, DECIMAL_PLACES); ?></td>
                        </tr>
                        <tr style="border-top: 2px solid #ddd; font-weight: bold;">
                            <td>資負合計</td>
                            <td class="text-right"><?php echo APP_CURRENCY_SYMBOL . number_format($total_liabilities + $total_equity, DECIMAL_PLACES); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="dashboard-col-6">
            <div class="card">
                <div class="card-header">
                    <h3>快速連結</h3>
                </div>
                <div class="card-body">
                    <div class="quick-links">
                        <a href="<?php echo SITE_URL; ?>index.php?controller=journal&action=create" class="btn-link">➕ 新增日記帳</a>
                        <a href="<?php echo SITE_URL; ?>index.php?controller=account&action=index" class="btn-link">📋 管理科目</a>
                        <a href="<?php echo SITE_URL; ?>index.php?controller=report&action=trialBalance" class="btn-link">📊 試算表</a>
                        <a href="<?php echo SITE_URL; ?>index.php?controller=report&action=balanceSheet" class="btn-link">💼 資產負債表</a>
                        <a href="<?php echo SITE_URL; ?>index.php?controller=report&action=incomeStatement" class="btn-link">📈 損益表</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 最近交易 -->
    <div class="card" style="margin-top: 20px;">
        <div class="card-header">
            <h3>最近交易</h3>
            <a href="<?php echo SITE_URL; ?>index.php?controller=journal&action=index" class="btn-small">查看全部</a>
        </div>
        <div class="card-body">
            <?php if (count($recent_entries) > 0): ?>
            <table class="table-bordered">
                <thead>
                    <tr>
                        <th>憑證號</th>
                        <th>日期</th>
                        <th>借方科目</th>
                        <th>貸方科目</th>
                        <th>金額</th>
                        <th>說明</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_entries as $entry): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($entry['voucher_no']); ?></td>
                        <td><?php echo date(DISPLAY_DATE_FORMAT, strtotime($entry['entry_date'])); ?></td>
                        <td><?php echo htmlspecialchars($entry['debit_code'] . ' ' . $entry['debit_name']); ?></td>
                        <td><?php echo htmlspecialchars($entry['credit_code'] . ' ' . $entry['credit_name']); ?></td>
                        <td class="text-right"><?php echo APP_CURRENCY_SYMBOL . number_format($entry['amount'], DECIMAL_PLACES); ?></td>
                        <td><?php echo htmlspecialchars(mb_substr((string)$entry['description'], 0, 20)); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p class="text-center text-muted">尚無交易記錄</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include SITE_ROOT . '/app/views/layouts/footer.php'; ?>
