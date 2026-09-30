<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>css/style.css?v=1.1">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>css/layout.css?v=1.1">
</head>
<body>
    <div class="container-fluid">
        <?php if (isset($_SESSION['user_id'])): ?>
        <!-- 導航欄 -->
        <nav class="navbar">
            <div class="navbar-container">
                <div class="navbar-brand">
                    <a href="<?php echo SITE_URL; ?>index.php?controller=dashboard">
                        <?php echo SITE_NAME; ?>
                    </a>
                </div>
                <div class="navbar-menu">
                    <ul class="navbar-menu-list">
                        <li class="navbar-item">
                            <a href="<?php echo SITE_URL; ?>index.php?controller=dashboard" class="navbar-link">儀表板</a>
                        </li>
                        <li class="navbar-item">
                            <a href="<?php echo SITE_URL; ?>index.php?controller=account&action=index" class="navbar-link">會計科目</a>
                        </li>
                        <li class="navbar-item">
                            <a href="<?php echo SITE_URL; ?>index.php?controller=journal&action=index" class="navbar-link">日記帳</a>
                        </li>
                        <li class="navbar-item">
                            <a href="<?php echo SITE_URL; ?>index.php?controller=ai&action=index" class="navbar-link">AI 記帳</a>
                        </li>
                        <li class="navbar-item dropdown">
                            <a href="#" class="navbar-link">報表</a>
                            <ul class="dropdown-menu">
                                <li><a href="<?php echo SITE_URL; ?>index.php?controller=report&action=trialBalance">試算表</a></li>
                                <li><a href="<?php echo SITE_URL; ?>index.php?controller=report&action=balanceSheet">資產負債表</a></li>
                                <li><a href="<?php echo SITE_URL; ?>index.php?controller=report&action=incomeStatement">損益表</a></li>
                                <li><a href="<?php echo SITE_URL; ?>index.php?controller=report&action=ledger">分類帳</a></li>
                                <li><a href="<?php echo SITE_URL; ?>index.php?controller=report&action=accountBalance">科目餘額表</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
                <div class="navbar-user">
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <?php if (($_SESSION['role'] ?? '') === USER_ROLE_ADMIN): ?>
                    <a href="<?php echo SITE_URL; ?>index.php?controller=settings&action=index" class="btn-small">AI 設定</a>
                    <?php endif; ?>
                    <a href="<?php echo SITE_URL; ?>index.php?controller=auth&action=changePassword" class="btn-small">修改密碼</a>
                    <a href="<?php echo SITE_URL; ?>index.php?controller=auth&action=logout" class="btn-small btn-danger">登出</a>
                </div>
            </div>
        </nav>

        <!-- 頁面內容 -->
        <div class="main-content">
            <div class="page-header">
                <h1><?php echo isset($pageTitle) ? $pageTitle : SITE_NAME; ?></h1>
            </div>

            <?php if (!empty($GLOBALS['messages'])): ?>
            <div class="alert alert-success">
                <?php foreach ($GLOBALS['messages'] as $msg): ?>
                    <p><?php echo htmlspecialchars($msg); ?></p>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php
            $layoutErrors = array_merge($GLOBALS['errors'] ?? [], $_SESSION['flash_errors'] ?? []);
            unset($_SESSION['flash_errors']);
            ?>
            <?php if (!empty($layoutErrors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($layoutErrors as $err): ?>
                    <p><?php echo htmlspecialchars($err); ?></p>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
