<?php
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | GameVault' : 'GameVault – Discover & Rate Games' ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
</head>
<body>

<header class="site-header">
    <div class="container header-inner">
        <a href="index.php" class="logo">
            <span class="logo-icon">🎮</span>
            <span class="logo-text">GameVault</span>
        </a>

        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
            <span></span><span></span><span></span>
        </button>

        <nav class="main-nav" id="mainNav">
            <ul>
                <li><a href="index.php"    class="<?= $currentPage === 'index'    ? 'active' : '' ?>">Home</a></li>
                <li><a href="games.php"    class="<?= $currentPage === 'games'    ? 'active' : '' ?>">Browse</a></li>
                <li><a href="search.php"   class="<?= $currentPage === 'search'   ? 'active' : '' ?>">Search</a></li>
                <li><a href="add-game.php" class="<?= $currentPage === 'add-game' ? 'active' : '' ?> btn btn-primary">+ Add Game</a></li>
            </ul>
        </nav>
    </div>
</header>

<main class="site-main">
