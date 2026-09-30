# 會計帳務管理系統

![Version](https://img.shields.io/badge/version-1.0-blue)
![License](https://img.shields.io/badge/license-MIT-green)

## 📋 系統概述

會計帳務管理系統是一個基於PHP和MySQL的輕量級財務管理工具，用於幫助中小型企業進行基本的帳務管理。系統提供簡潔易用的界面，支持會計科目管理、會計憑證、日記帳、分類帳、試算表等基本會計功能。

## 🎯 主要功能

- ✅ **使用者管理** - 帳戶管理、權限控制、密碼管理
- ✅ **會計科目管理** - 新增、編輯、刪除科目
- ✅ **會計憑證管理** - 記錄進項、銷項等交易
- ✅ **日記帳** - 時間序列的交易明細
- ✅ **分類帳** - 按科目分類的交易明細
- ✅ **試算表** - 驗證帳務的完整性
- ✅ **財務報表** - 損益表、資產負債表、科目餘額表

## 🔧 技術棧

| 元件 | 技術 |
|------|------|
| 後端開發 | PHP 7.4+ |
| 資料庫 | MySQL 5.7+ |
| 前端開發 | HTML5 / CSS3 / JavaScript |
| 開發環境 | XAMPP |

## 📦 安裝步驟

### 1. 系統環境要求

- PHP 7.4 以上版本
- MySQL 5.7 以上版本
- XAMPP 開發環境
- 現代化瀏覽器（Chrome、Firefox、Edge）

### 2. 部署步驟

#### 步驟1：複製文件到XAMPP

```bash
# 複製項目到 XAMPP/htdocs 目錄
cp -r accounting_system C:\xampp\htdocs\
```

#### 步驟2：配置資料庫

1. 打開 `config/config.php`
2. 修改資料庫連接設定：

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // 根據您的XAMPP配置修改
define('DB_NAME', 'accounting_system');
```

#### 步驟3：建立資料庫

1. 打開 phpMyAdmin: http://localhost/phpmyadmin
2. 建立新資料庫，命名為 `accounting_system`
3. 導入 `database/init.sql` 文件

```bash
# 或使用命令行
mysql -u root accounting_system < database/init.sql
```

#### 步驟4：訪問系統

```
http://localhost/accounting_system/public/index.php
```

## 👤 預設帳號

| 項目 | 值 |
|------|-----|
| 帳號 | admin |
| 密碼 | admin123 |
| 角色 | 管理員 |

**⚠️ 重要：首次登入後請立即修改密碼**

## 📁 項目結構

```
accounting_system/
├── public/                  # Web根目錄
│   ├── index.php           # 主入口
│   ├── css/
│   │   ├── style.css       # 全域樣式
│   │   └── layout.css      # 佈局樣式
│   └── js/
│       └── main.js         # 主JavaScript
├── app/
│   ├── controllers/        # 控制器層
│   │   ├── AuthController.php
│   │   ├── AccountController.php
│   │   ├── JournalController.php
│   │   ├── ReportController.php
│   │   └── DashboardController.php
│   ├── models/             # 模型層（資料訪問）
│   │   ├── Database.php
│   │   ├── User.php
│   │   ├── Account.php
│   │   └── JournalEntry.php
│   └── views/              # 視圖層（表現層）
│       ├── layouts/
│       ├── users/
│       ├── accounts/
│       ├── journal/
│       ├── reports/
│       └── dashboard/
├── config/
│   └── config.php          # 系統配置
├── database/
│   └── init.sql            # 資料庫初始化
└── README.md               # 本文件
```

## 🔑 使用者角色

### 管理員 (admin)
- 管理所有功能
- 管理系統設定
- 管理使用者帳號

### 使用者 (user)
- 查看儀表板
- 管理會計科目
- 管理會計憑證
- 查看報表

## 📊 主要功能說明

### 1. 儀表板
- 顯示系統統計資訊
- 快速訪問常用功能
- 最近交易記錄

### 2. 會計科目管理
- 支援五種科目類型：資產、負債、權益、收入、支出
- 自動計算科目餘額
- 支援科目刪除

### 3. 日記帳管理
- 記錄所有交易
- 自動生成憑證號
- 自動更新科目餘額
- 支援查詢和編輯

### 4. 報表查詢
- **試算表**：驗證借貸平衡
- **資產負債表**：顯示財務狀況
- **損益表**：計算經營成果
- **分類帳**：查看科目明細

## 🔐 安全性特性

- ✅ 密碼加密存儲（BCrypt）
- ✅ SQL注入防護（預存參數化查詢）
- ✅ XSS防護（HTML實體編碼）
- ✅ Session管理
- ✅ 審計日誌記錄
- ✅ 角色權限控制

## 📝 常見操作

### 添加新科目

1. 點擊「會計科目」→「新增科目」
2. 輸入科目編碼、名稱、類別
3. 點擊「保存」

### 記錄交易

1. 點擊「日記帳」→「新增日記帳」
2. 選擇借貸科目和金額
3. 點擊「保存」
4. 系統自動更新科目餘額

### 查看報表

1. 點擊「報表」菜單
2. 選擇所需的報表類型
3. 系統自動計算並顯示

## 🚀 效能優化

- 資料庫查詢優化（建立索引）
- 頁面加載時間 < 3秒
- 支援至少100個並發使用者
- 報表生成時間 < 5秒

## 🐛 故障排除

### 資料庫連接失敗
- 檢查MySQL是否啟動
- 驗證config.php中的連接參數
- 確認資料庫已建立

### 頁面無法顯示
- 檢查XAMPP的Apache服務是否啟動
- 驗證文件夾權限
- 清除瀏覽器快取

### 密碼錯誤
- 確認Caps Lock未開啟
- 使用預設密碼：admin123
- 如忘記密碼，可直接修改資料庫密碼字段

## 📚 API端點

### 認證
- `GET /public/index.php?controller=auth&action=login` - 登入頁面
- `POST /public/index.php?controller=auth&action=login` - 處理登入
- `GET /public/index.php?controller=auth&action=logout` - 登出

### 會計科目
- `GET /public/index.php?controller=account&action=index` - 科目列表
- `GET /public/index.php?controller=account&action=create` - 新增表單
- `POST /public/index.php?controller=account&action=create` - 儲存科目
- `GET /public/index.php?controller=account&action=edit&id=X` - 編輯表單
- `POST /public/index.php?controller=account&action=edit&id=X` - 更新科目
- `GET /public/index.php?controller=account&action=delete&id=X` - 刪除科目

### 日記帳
- `GET /public/index.php?controller=journal&action=index` - 日記帳列表
- `GET /public/index.php?controller=journal&action=create` - 新增表單
- `POST /public/index.php?controller=journal&action=create` - 儲存記錄
- `GET /public/index.php?controller=journal&action=edit&id=X` - 編輯表單
- `POST /public/index.php?controller=journal&action=edit&id=X` - 更新記錄
- `GET /public/index.php?controller=journal&action=delete&id=X` - 刪除記錄
- `GET /public/index.php?controller=journal&action=view&id=X` - 查看詳情

### 報表
- `GET /public/index.php?controller=report&action=trialBalance` - 試算表
- `GET /public/index.php?controller=report&action=balanceSheet` - 資產負債表
- `GET /public/index.php?controller=report&action=incomeStatement` - 損益表
- `GET /public/index.php?controller=report&action=ledger` - 分類帳

## 📞 技術支援

如遇問題，請檢查以下文件：
- `database/init.sql` - 資料庫結構
- `config/config.php` - 系統配置
- 瀏覽器開發工具的Console選項卡

## 📄 版本歷史

### v1.0 (2026-09-30)
- 初始版本發佈
- 基本會計功能完成
- 報表系統實現

## ⚖️ 開源許可

本項目採用MIT許可證。詳見LICENSE文件。

## 🙏 致謝

感謝宥鋐會計師事務所提供需求規劃和指導。

---

**宥鋐會計師事務所**

版本：1.0 | 最後更新：2026年09月30日
