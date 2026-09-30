# 會計帳務系統 - 安裝指南

## 📦 系統需求

### 最低配置
- **PHP**: 7.4 或更高版本
- **MySQL**: 5.7 或更高版本
- **Web伺服器**: Apache 2.4 或更高版本
- **瀏覽器**: 支援HTML5的現代瀏覽器

### 建議配置
- PHP 8.0+
- MySQL 8.0+
- Apache 2.4.41+
- 4GB以上RAM

## 🚀 快速安裝

### 方式一：使用XAMPP（推薦用於開發環境）

#### 第1步：安裝XAMPP

1. 下載XAMPP（適用於Windows/Mac/Linux）
   - 訪問 https://www.apachefriends.org/
   - 選擇PHP 7.4+版本
   - 安裝到預設目錄

2. 啟動XAMPP Control Panel
   ```
   Windows: C:\xampp\xampp-control.exe
   Mac: /Applications/XAMPP/xamppfiles/manager-osx.app
   Linux: sudo /opt/lampp/manager-linux.app
   ```

3. 啟動以下服務：
   - ✓ Apache
   - ✓ MySQL

#### 第2步：複製項目文件

1. 打開文件管理器
2. 導航到XAMPP安裝目錄下的 `htdocs` 資料夾
   ```
   Windows: C:\xampp\htdocs\
   Mac: /Applications/XAMPP/xamppfiles/htdocs/
   Linux: /opt/lampp/htdocs/
   ```

3. 將 `accounting_system` 資料夾複製到此位置

#### 第3步：建立資料庫

1. 打開瀏覽器，訪問 phpMyAdmin
   ```
   http://localhost/phpmyadmin
   ```

2. 登入（預設無密碼）
   - 使用者名稱：`root`
   - 密碼：空白

3. 建立新資料庫
   - 在左側面板，輸入數據庫名稱：`accounting_system`
   - 選擇排序規則：`utf8mb4_unicode_ci`
   - 點擊「建立」

4. 導入初始化SQL
   - 選擇剛建立的 `accounting_system` 資料庫
   - 點擊「匯入」選項卡
   - 點擊「選擇文件」
   - 選擇 `database/init.sql`
   - 點擊「執行」

#### 第4步：驗證安裝

1. 打開瀏覽器，訪問系統
   ```
   http://localhost/accounting_system/public/index.php
   ```

2. 使用預設帳號登入
   - 帳號：`admin`
   - 密碼：`admin123`

3. 如果成功看到儀表板，安裝完成！

---

### 方式二：使用Docker（適用於生產環境）

#### 前置條件
- 安裝Docker和Docker Compose

#### 建立Dockerfile

在項目根目錄建立 `Dockerfile`：

```dockerfile
FROM php:7.4-apache

# 安裝必要的擴展
RUN docker-php-ext-install mysqli pdo pdo_mysql

# 啟用Apache的mod_rewrite
RUN a2enmod rewrite

# 複製項目文件
COPY . /var/www/html/

# 設定文件夾權限
RUN chown -R www-data:www-data /var/www/html/
```

#### 建立docker-compose.yml

```yaml
version: '3.8'

services:
  web:
    build: .
    ports:
      - "80:80"
    volumes:
      - .:/var/www/html/
    environment:
      DB_HOST: db
      DB_USER: root
      DB_PASS: accounting123
      DB_NAME: accounting_system
    depends_on:
      - db

  db:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: accounting123
      MYSQL_DATABASE: accounting_system
    volumes:
      - ./database/init.sql:/docker-entrypoint-initdb.d/init.sql
      - mysql_data:/var/lib/mysql

volumes:
  mysql_data:
```

#### 啟動服務

```bash
# 構建並啟動容器
docker-compose up -d

# 訪問系統
http://localhost/accounting_system/public/index.php
```

---

## ⚙️ 配置說明

### config/config.php

根據您的環境修改以下設定：

```php
// 資料庫連接
define('DB_HOST', 'localhost');    // MySQL伺服器位址
define('DB_USER', 'root');         // MySQL使用者
define('DB_PASS', '');             // MySQL密碼
define('DB_NAME', 'accounting_system');  // 資料庫名稱
define('DB_PORT', 3306);           // MySQL埠號

// 系統設定
define('SITE_URL', 'http://localhost/accounting_system/');
define('COMPANY_NAME', '宥鋐會計師事務所');

// 時區
date_default_timezone_set('Asia/Taipei');

// 功能開關
define('ENABLE_AUDIT_LOG', true);  // 審計日誌
define('ENABLE_AUTO_BACKUP', true); // 自動備份
```

### 檔案夾權限

確保以下資料夾有寫入權限：

```bash
# Linux/Mac
chmod 755 public/
chmod 755 uploads/
chmod 755 app/

# Windows使用者需要在資料夾屬性中設定「修改」權限
```

---

## 🔐 安全配置

### 第1步：修改預設密碼

1. 登入系統
2. 導航到 「修改密碼」
3. 輸入新密碼
4. 保存

### 第2步：建立新使用者

由於目前系統不支援使用者介面註冊，請通過資料庫直接插入：

```sql
-- 連接到accounting_system資料庫
USE accounting_system;

-- 插入新使用者
INSERT INTO users (username, password, user_name, role) VALUES 
('newuser', '$2y$10$...', '新用戶名稱', 'user');
```

要生成密碼雜湊，可使用線上工具或PHP：

```php
<?php
echo password_hash('你的密碼', PASSWORD_BCRYPT);
?>
```

### 第3步：設定HTTPS（生產環境）

1. 獲取SSL證書
   - 使用Let's Encrypt（免費）
   - 或購買商業證書

2. 配置Apache SSL
   ```apache
   <VirtualHost *:443>
       ServerName yourdomain.com
       SSLEngine on
       SSLCertificateFile /path/to/certificate.crt
       SSLCertificateKeyFile /path/to/private.key
       DocumentRoot /var/www/html
   </VirtualHost>
   ```

3. 強制重定向HTTP到HTTPS
   ```apache
   <VirtualHost *:80>
       ServerName yourdomain.com
       Redirect permanent / https://yourdomain.com/
   </VirtualHost>
   ```

---

## 📋 資料庫備份

### 自動備份

系統會自動每日備份，檔案保存在 `/backups/` 目錄。

### 手動備份

#### 使用phpMyAdmin

1. 打開 http://localhost/phpmyadmin
2. 選擇 `accounting_system` 資料庫
3. 點擊「匯出」
4. 選擇「快速」或「自訂」
5. 點擊「執行」

#### 使用命令列

```bash
# 備份資料庫
mysqldump -u root -p accounting_system > backup_$(date +%Y%m%d).sql

# 恢復資料庫
mysql -u root -p accounting_system < backup_20240101.sql
```

---

## 🐛 故障排除

### Q: 無法連接到資料庫

**症狀**：出現「連接失敗」錯誤

**解決方案**：
1. 確認MySQL已啟動
2. 檢查 `config/config.php` 中的連接參數
3. 驗證資料庫已建立
4. 檢查用戶名和密碼是否正確

### Q: 頁面無法加載

**症狀**：顯示空白頁面或錯誤

**解決方案**：
1. 檢查Apache是否啟動
2. 驗證文件夾權限（755）
3. 檢查PHP版本 >= 7.4
4. 查看PHP錯誤日誌

### Q: 無法登入

**症狀**：顯示「帳號或密碼錯誤」

**解決方案**：
1. 確認帳號密碼正確（預設：admin/admin123）
2. 檢查是否啟用了大小寫鎖定
3. 嘗試重置密碼（見 常見操作 > 重置密碼）

### Q: 性能緩慢

**症狀**：頁面加載緩慢

**解決方案**：
1. 檢查系統RAM是否充足
2. 優化資料庫（建立索引）
3. 啟用PHP OPcache
4. 檢查硬碟空間

---

## 📞 常見操作

### 重置管理員密碼

如果忘記管理員密碼，可通過資料庫重置：

```sql
USE accounting_system;

-- 將管理員密碼重設為 admin123
UPDATE users SET password = '$2y$10$L6bDcGEmlU7wyV8eLt49FeCjm2vZUbvWCyr5HbQYRRDgEbrwBYGDq' 
WHERE username = 'admin';
```

### 清除審計日誌

```sql
-- 清除所有審計日誌
DELETE FROM audit_logs;

-- 只保留最近30天的日誌
DELETE FROM audit_logs WHERE DATE(created_at) < DATE_SUB(NOW(), INTERVAL 30 DAY);
```

### 導出資料

系統支援將表格導出為CSV格式，點擊表格下方的「導出」按鈕。

---

## ✅ 安裝檢查清單

- [ ] PHP版本 >= 7.4
- [ ] MySQL版本 >= 5.7
- [ ] XAMPP已安裝並啟動
- [ ] Apache和MySQL服務已啟動
- [ ] 項目文件已複製到htdocs
- [ ] 資料庫已建立
- [ ] init.sql已導入
- [ ] 可以訪問登入頁面
- [ ] 可以使用預設帳號登入
- [ ] 儀表板正常顯示
- [ ] 所有菜單項目可訪問
- [ ] 可以新增會計科目
- [ ] 可以記錄日記帳
- [ ] 報表可以正確生成

---

## 📚 進階配置

### 啟用多語言支援

編輯 `config/config.php`：

```php
define('DEFAULT_LANGUAGE', 'zh-TW');  // zh-TW, zh-CN, en
```

### 自訂公司資訊

編輯 `config/config.php`：

```php
define('COMPANY_NAME', '您的公司名稱');
define('COMPANY_ADDRESS', '地址');
define('COMPANY_PHONE', '電話');
define('COMPANY_EMAIL', '信箱');
```

### 啟用SMTP郵件發送

編輯 `config/config.php`：

```php
define('ENABLE_EMAIL', true);
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-password');
```

---

## 🔄 升級指南

### 從v0.9升級到v1.0

1. 備份現有資料庫和文件
2. 下載最新版本
3. 覆蓋現有文件（保留 `config/config.php`）
4. 執行資料庫升級腳本

```bash
# 位置：database/migrations/upgrade_0.9_to_1.0.sql
mysql -u root -p accounting_system < database/migrations/upgrade_0.9_to_1.0.sql
```

5. 清除快取

```bash
rm -rf /tmp/accounting_cache/*
```

---

## 📞 技術支援

如遇問題，請檢查：

1. **安裝指南** - 本文件
2. **README.md** - 系統概述
3. **配置文件** - config/config.php
4. **資料庫結構** - database/init.sql
5. **PHP錯誤日誌** - XAMPP/php/logs/

---

**宥鋐會計師事務所**  
版本：1.0 | 最後更新：2026年09月30日

---

## 🤖 AI 語音／拍照記帳設定

AI 記帳使用 OpenAI API，需先設定 API 金鑰：

1. 以管理員帳號登入，點右上角「**AI 設定**」。
2. 在「設定金鑰」貼上 OpenAI API 金鑰（`sk-` 開頭），按「**驗證並儲存**」。
3. 系統會先向 OpenAI 確認金鑰可用才儲存；看到「金鑰已驗證並儲存」即完成，不需重新啟動 Apache。

注意事項：
- 金鑰存放在 `config/ai_key.php`，瀏覽器無法直接讀取，網頁只顯示遮罩值。請勿分享或備份此檔案到其他位置。
- 也可改用 Windows 環境變數 `OPENAI_API_KEY`（需重新開啟 XAMPP Control Panel）；網頁設定的金鑰優先。
- 建議在 OpenAI 後台設定每月用量上限。
- 模型名稱可在 `config/config.php` 的 `OPENAI_TRANSCRIBE_MODEL`、`OPENAI_PARSE_MODEL` 調整。
- 網頁錄音只能在 `localhost` 或 HTTPS 下使用；手機以區網 IP 開啟時請改用「上傳音檔」。
- 語音與單據影像會傳送至 OpenAI 處理，請依個資法事先告知相關人員。
