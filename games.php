<?php
require_once 'includes/db-connect.php';
require_once 'includes/functions.php';

$pageTitle = 'Browse Games';
$PER_PAGE  = 12;

// ── Input sanitisation ──────────────────────────────────────
$genre_id = isset($_GET['genre_id']) ? (int)$_GET['genre_id'] : 0;
$year     = isset($_GET['year'])     ? (int)$_GET['year']     : 0;
$sort     = in_array($_GET['sort'] ?? '', ['rating','newest','title','year']) ? $_GET['sort'] : 'rating';
$page     = max(1, (int)($_GET['page'] ?? 1));

// ── Build dynamic WHERE ─────────────────────────────────────
$where  = [];
$params = [];
$types  = '';

if ($genre_id) {
    $where[] = 'g.genre_id = ?';
    $params[] = $genre_id;
    $types   .= 'i';
}
if ($year) {
    $where[] = 'g.release_year = ?';
    $params[] = $year;
    $types   .= 'i';
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$orderSQL = match($sort) {
    'newest' => 'g.created_at DESC',
    'title'  => 'g.title ASC',
    'year'   => 'g.release_year DESC',
    default  => 'g.avg_rating DESC, g.rating_count DESC',
};

// ── Total count ─────────────────────────────────────────────
$countSQL  = "SELECT COUNT(*) AS c FROM games g $whereSQL";
$countStmt = $conn->prepare($countSQL);
if ($types) $countStmt->bind_param($types, ...$params);
$countStmt->execute();
$total = $countStmt->get_result()->fetch_assoc()['c'];

[$offset, $totalPages, $page] = paginate($total, $PER_PAGE, $page);

// ── Fetch games ─────────────────────────────────────────────
$sql  = "SELECT g.*, gr.name AS genre_name
         FROM games g
         JOIN genres gr ON g.genre_id = gr.id
         $whereSQL
         ORDER BY $orderSQL
         LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$allParams  = [...$params, $PER_PAGE, $offset];
$allTypes   = $types . 'ii';
$stmt->bind_param($allTypes, ...$allParams);
$stmt->execute();
$games = $stmt->get_result();

// ── Genres for dropdown ─────────────────────────────────────
$genres = $conn->query("SELECT * FROM genres ORDER BY name");

// ── Year range for dropdown ─────────────────────────────────
$years  = $conn->query("SELECT DISTINCT release_year FROM games ORDER BY release_year DESC");

require_once 'includes/header.php';
?>

<div class="page-hero">
    <div class="container">
        <h1 class="page-hero-title">Browse Games</h1>
        <p class="breadcrumb"><a href="index.php">Home</a> › <span>Browse</span></p>
    </div>
</div>

<div class="container">

    <!-- Filter Bar -->
    <form method="GET" action="games.php" class="filter-bar">
        <div class="filter-group">
            <label for="genre_id">Genre</label>
            <select name="genre_id" id="genre_id">
                <option value="">All Genres</option>
                <?php while ($g = $genres->fetch_assoc()): ?>
                <option value="<?= $g['id'] ?>" <?= $genre_id == $g['id'] ? 'selected' : '' ?>>
                    <?= sanitize($g['name']) ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="filter-group">
            <label for="year">Year</label>
            <select name="year" id="year">
                <option value="">All Years</option>
                <?php while ($y = $years->fetch_assoc()): ?>
                <option value="<?= $y['release_year'] ?>" <?= $year == $y['release_year'] ? 'selected' : '' ?>>
                    <?= $y['release_year'] ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="filter-group">
            <label for="sort">Sort By</label>
            <select name="sort" id="sort">
                <option value="rating"  <?= $sort === 'rating'  ? 'selected' : '' ?>>Top Rated</option>
                <option value="newest"  <?= $sort === 'newest'  ? 'selected' : '' ?>>Newest</option>
                <option value="title"   <?= $sort === 'title'   ? 'selected' : '' ?>>Title A–Z</option>
                <option value="year"    <?= $sort === 'year'    ? 'selected' : '' ?>>Year</option>
            </select>
        </div>

        <div class="filter-group" style="flex:0;min-width:auto;">
            <label>&nbsp;</label>
            <div style="display:flex;gap:.5rem;">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="games.php" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>

    <!-- Results Info -->
    <p class="text-muted mb-2" style="font-size:.88rem;">
        Showing <?= $total ?> game<?= $total != 1 ? 's' : '' ?>
        <?= $genre_id || $year ? ' (filtered)' : '' ?>
        &nbsp;·&nbsp; Page <?= $page ?> of <?= $totalPages ?>
    </p>

    <!-- Games Grid -->
    <?php if ($games->num_rows === 0): ?>
    <div class="empty-state">
        <span class="empty-icon">🎮</span>
        <h3>No games found</h3>
        <p>Try adjusting your filters or <a href="add-game.php">add a game</a>.</p>
    </div>
    <?php else: ?>
    <div class="games-grid">
        <?php while ($g = $games->fetch_assoc()): ?>
        <div class="card">
            <a href="game-detail.php?id=<?= $g['id'] ?>">
                <img src="<?= coverSrc($g['cover_image']) ?>" alt="<?= sanitize($g['title']) ?>" class="card-cover">
            </a>
            <div class="card-body">
                <div class="card-title">
                    <a href="game-detail.php?id=<?= $g['id'] ?>" style="color:inherit;"><?= sanitize($g['title']) ?></a>
                </div>
                <div class="card-meta">
                    <?= (int)$g['release_year'] ?> · <span class="genre-badge"><?= sanitize($g['genre_name']) ?></span>
                </div>
            </div>
            <div class="card-footer">
                <span class="rating-badge"><span class="icon">★</span><?= number_format($g['avg_rating'],1) ?></span>
                <div style="display:flex;gap:.4rem;">
                    <a href="edit-game.php?id=<?= $g['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                    <a href="delete-game.php?id=<?= $g['id'] ?>" class="btn btn-danger btn-sm confirm-delete">Del</a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <nav class="pagination" aria-label="Page navigation">
        <?php
        $qBase = http_build_query(array_filter(['genre_id'=>$genre_id,'year'=>$year,'sort'=>$sort]));
        $qBase = $qBase ? "&$qBase" : '';
        ?>
        <a href="?page=<?= $page-1 ?><?= $qBase ?>" class="page-btn <?= $page <= 1 ? 'disabled' : '' ?>">&#8592;</a>
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <a href="?page=<?= $p ?><?= $qBase ?>" class="page-btn <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
        <?php endfor; ?>
        <a href="?page=<?= $page+1 ?><?= $qBase ?>" class="page-btn <?= $page >= $totalPages ? 'disabled' : '' ?>">&#8594;</a>
    </nav>
    <?php endif; ?>

    <?php endif; ?>

</div>

<?php require_once 'includes/footer.php'; ?>
