#!/usr/bin/env bash
# Codespaces 建立後執行：寫入設定、等待資料庫啟動、匯入範例資料
set -e
cd "$(dirname "$0")/.."

cp .devcontainer/local.php config/local.php

echo "等待資料庫啟動..."
for i in $(seq 1 60); do
  if php -r 'require "config/local.php"; new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);' 2>/dev/null; then
    break
  fi
  sleep 2
done

CODESPACES=true php database/demo_reset.php

echo ""
echo "完成！請開啟「連接埠」分頁中的 8080（會計帳務系統）。"
echo "管理員：admin / admin123　　示範帳號：demo / demo1234"
