<?php
// ============================================================
// Home Page
// ============================================================
require_once 'includes/db-connect.php';
require_once 'includes/functions.php';

$pageTitle = 'Home';

// Stats
$stats = [];
$res = $conn->query("SELECT COUNT(*) AS c FROM games");   $stats['games']   = $res->fetch_assoc()['c'];
$res = $conn->query("SELECT COUNT(*) AS c FROM ratings");  $stats['ratings'] = $res->fetch_assoc()['c'];
$res = $conn->query("SELECT COUNT(*) AS c FROM genres");   $stats['genres']  = $res->fetch_assoc()['c'];

// Top-rated games (min 2 ratings)
$topGames = $conn->query("
    SELECT g.*, gr.name AS genre_name
    FROM games g
    JOIN genres gr ON g.genre_id = gr.id
    WHERE g.rating_count >= 1
    ORDER BY g.avg_rating DESC, g.rating_count DESC
    LIMIT 8
");

// Latest additions
$latestGames = $conn->query("
    SELECT g.*, gr.name AS genre_name
    FROM games g
    JOIN genres gr ON g.genre_id = gr.id
    ORDER BY g.created_at DESC
    LIMIT 4
");

require_once 'includes/header.php';
?>

<!-- Hero -->
<section class="hero">
    <div class="container">
        <h1 class="hero-title">Discover &amp; Rate Games</h1>
        <p class="hero-sub">Your ultimate platform for exploring, rating, and reviewing video games from every era.</p>
        <div class="hero-actions">
            <a href="games.php"    class="btn btn-primary">Browse All Games</a>
            <a href="search.php"   class="btn btn-secondary">Search</a>
            <a href="add-game.php" class="btn btn-secondary">+ Add a Game</a>
        </div>
    </div>
</section>

<div class="container" style="padding-top:2rem;">

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-box">
            <div class="stat-num"><?= $stats['games'] ?></div>
            <div class="stat-label">Games</div>
        </div>
        <div class="stat-box">
            <div class="stat-num"><?= $stats['ratings'] ?></div>
            <div class="stat-label">Reviews</div>
        </div>
        <div class="stat-box">
            <div class="stat-num"><?= $stats['genres'] ?></div>
            <div class="stat-label">Genres</div>
        </div>
    </div>

    <!-- Top Rated -->
    <section class="mb-3">
        <div class="section-header">
            <h2 class="section-title">Top Rated</h2>
            <a href="games.php?sort=rating" class="btn btn-secondary btn-sm">See All →</a>
        </div>
        <div class="games-grid">
            <?php while ($g = $topGames->fetch_assoc()): ?>
            <a href="game-detail.php?id=<?= $g['id'] ?>" class="card" style="display:block;text-decoration:none;">
                <img src="<?= coverSrc($g['cover_image']) ?>" alt="<?= sanitize($g['title']) ?>" class="card-cover">
                <div class="card-body">
                    <div class="card-title"><?= sanitize($g['title']) ?></div>
                    <div class="card-meta"><?= (int)$g['release_year'] ?> · <span class="genre-badge"><?= sanitize($g['genre_name']) ?></span></div>
                </div>
                <div class="card-footer">
                    <span class="rating-badge"><span class="icon">★</span><?= number_format($g['avg_rating'],1) ?></span>
                    <span class="text-muted" style="font-size:.78rem;"><?= $g['rating_count'] ?> reviews</span>
                </div>
            </a>
            <?php endwhile; ?>
        </div>
    </section>

    <!-- Latest -->
    <section class="mb-3">
        <div class="section-header">
            <h2 class="section-title">Recently Added</h2>
            <a href="games.php?sort=newest" class="btn btn-secondary btn-sm">See All →</a>
        </div>
        <div class="games-grid">
            <?php while ($g = $latestGames->fetch_assoc()): ?>
            <a href="game-detail.php?id=<?= $g['id'] ?>" class="card" style="display:block;text-decoration:none;">
                <img src="<?= coverSrc($g['cover_image']) ?>" alt="<?= sanitize($g['title']) ?>" class="card-cover">
                <div class="card-body">
                    <div class="card-title"><?= sanitize($g['title']) ?></div>
                    <div class="card-meta"><?= (int)$g['release_year'] ?> · <span class="genre-badge"><?= sanitize($g['genre_name']) ?></span></div>
                </div>
                <div class="card-footer">
                    <span class="rating-badge"><span class="icon">★</span><?= number_format($g['avg_rating'],1) ?></span>
                    <span class="text-muted" style="font-size:.78rem;"><?= $g['rating_count'] ?> reviews</span>
                </div>
            </a>
            <?php endwhile; ?>
        </div>
    </section>

</div>

<?php require_once 'includes/footer.php'; ?>
