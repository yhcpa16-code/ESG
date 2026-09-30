<?php include SITE_ROOT . '/app/views/layouts/header.php'; ?>

<div class="content-container">
    <div class="content-header">
        <a href="<?php echo SITE_URL; ?>index.php?controller=account&action=create" class="btn-primary">➕ 新增科目</a>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (count($accounts) > 0): ?>
            <table class="table-bordered">
                <thead>
                    <tr>
                        <th>科目編碼</th>
                        <th>科目名稱</th>
                        <th>科目類別</th>
                        <th>餘額</th>
                        <th>說明</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($accounts as $account): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($account['account_code']); ?></td>
                        <td><?php echo htmlspecialchars($account['account_name']); ?></td>
                        <td><?php
                            $types = ['asset' => '資產', 'liability' => '負債', 'equity' => '權益', 'revenue' => '收入', 'expense' => '支出'];
                            echo $types[$account['account_type']] ?? $account['account_type'];
                        ?></td>
                        <td class="text-right"><?php echo APP_CURRENCY_SYMBOL . number_format($account['natural_balance'], DECIMAL_PLACES); ?></td>
                        <td><?php echo htmlspecialchars(mb_substr((string)$account['description'], 0, 30)); ?></td>
                        <td>
                            <a href="<?php echo SITE_URL; ?>index.php?controller=account&action=edit&id=<?php echo $account['account_id']; ?>" class="btn-small">編輯</a>
                            <a href="<?php echo SITE_URL; ?>index.php?controller=account&action=delete&id=<?php echo $account['account_id']; ?>" class="btn-small btn-danger" onclick="return confirm('確定要刪除此科目嗎?');">刪除</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p class="text-center text-muted">尚無科目記錄</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include SITE_ROOT . '/app/views/layouts/footer.php'; ?>
