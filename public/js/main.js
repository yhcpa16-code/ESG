/**
 * 會計帳務系統 - 主要JavaScript文件
 */

document.addEventListener('DOMContentLoaded', function() {
    // 自動隱藏警告訊息
    const alerts = document.querySelectorAll('.alert:not(.alert-static)');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.3s ease-out';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 300);
        }, 3000);
    });

    // 表單驗證
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#ef4444';
                } else {
                    field.style.borderColor = '';
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('請填寫所有必填欄位');
            }
        });
    });

    // 數字格式化
    formatCurrency();
});

/**
 * 格式化貨幣顯示
 */
function formatCurrency() {
    const currencyElements = document.querySelectorAll('[data-currency]');
    currencyElements.forEach(elem => {
        const amount = parseFloat(elem.textContent);
        if (!isNaN(amount)) {
            elem.textContent = 'NT$' + amount.toLocaleString('zh-TW', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    });
}

/**
 * 確認刪除操作
 */
function confirmDelete(message = '確定要刪除此項目嗎?') {
    return confirm(message);
}

/**
 * 日期格式化
 */
function formatDate(dateString) {
    const options = { year: 'numeric', month: '2-digit', day: '2-digit' };
    return new Date(dateString).toLocaleDateString('zh-TW', options);
}

/**
 * 打印頁面
 */
function printPage() {
    window.print();
}

/**
 * 導出CSV
 */
function exportTableToCSV(filename = 'export.csv') {
    const table = document.querySelector('.table-bordered');
    if (!table) return;

    let csv = [];
    const rows = table.querySelectorAll('tr');

    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const csvRow = [];
        cols.forEach(col => {
            csvRow.push('"' + col.textContent.trim().replace(/"/g, '""') + '"');
        });
        csv.push(csvRow.join(','));
    });

    downloadCSV(csv.join('\n'), filename);
}

/**
 * 下載CSV文件
 */
function downloadCSV(csv, filename) {
    const link = document.createElement('a');
    const blob = new Blob(['﻿' + csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);

    link.setAttribute('href', url);
    link.setAttribute('download', filename);
    link.style.visibility = 'hidden';

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

/**
 * 金額計算器
 */
function calculateAmount(value) {
    const amount = parseFloat(value);
    return isNaN(amount) ? 0 : parseFloat(amount.toFixed(2));
}

/**
 * 驗證電子郵件
 */
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

/**
 * 驗證日期格式
 */
function validateDate(dateString) {
    const date = new Date(dateString);
    return date instanceof Date && !isNaN(date);
}
