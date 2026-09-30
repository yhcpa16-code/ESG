<?php include SITE_ROOT . '/app/views/layouts/header.php'; ?>

<div class="content-container">
    <div class="content-header">
        <a href="<?php echo SITE_URL; ?>index.php?controller=journal&action=create" class="btn-primary">➕ 新增日記帳</a>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (count($entries) > 0): ?>
            <table class="table-bordered">
                <thead>
                    <tr>
                        <th>憑證號</th>
                        <th>日期</th>
                        <th>借方科目</th>
                        <th>貸方科目</th>
                        <th>金額</th>
                        <th>說明</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($entries as $entry): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($entry['voucher_no']); ?></td>
                        <td><?php echo date(DISPLAY_DATE_FORMAT, strtotime($entry['entry_date'])); ?></td>
                        <td><?php echo htmlspecialchars($entry['debit_code'] . ' ' . $entry['debit_name']); ?></td>
                        <td><?php echo htmlspecialchars($entry['credit_code'] . ' ' . $entry['credit_name']); ?></td>
                        <td class="text-right"><?php echo CURRENCY_SYMBOL . number_format($entry['amount'], DECIMAL_PLACES); ?></td>
                        <td><?php echo htmlspecialchars(substr($entry['description'], 0, 20)); ?></td>
                        <td>
                            <a href="<?php echo SITE_URL; ?>index.php?controller=journal&action=view&id=<?php echo $entry['entry_id']; ?>" class="btn-small">查看</a>
                            <a href="<?php echo SITE_URL; ?>index.php?controller=journal&action=edit&id=<?php echo $entry['entry_id']; ?>" class="btn-small">編輯</a>
                            <a href="<?php echo SITE_URL; ?>index.php?controller=journal&action=delete&id=<?php echo $entry['entry_id']; ?>" class="btn-small btn-danger" onclick="return confirm('確定要刪除此記錄嗎?');">刪除</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- 分頁 -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination" style="text-align: center; margin-top: 20px;">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="<?php echo SITE_URL; ?>index.php?controller=journal&action=index&page=<?php echo $i; ?>"
                       class="pagination-link <?php echo $i == $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
            <?php else: ?>
            <p class="text-center text-muted">尚無記錄</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include SITE_ROOT . '/app/views/layouts/footer.php'; ?>
