<?php
// ============================================================
// Search & Filter Page
// ============================================================
require_once 'includes/db-connect.php';
require_once 'includes/functions.php';

$pageTitle = 'Search Games';
$PER_PAGE  = 12;

// ── Inputs ─────────────────────────────────────────────────
$q        = trim($_GET['q']        ?? '');
$genre_id = (int)($_GET['genre_id'] ?? 0);
$year     = (int)($_GET['year']     ?? 0);
$min_rate = (float)($_GET['min_rating'] ?? 0);
$sort     = in_array($_GET['sort'] ?? '', ['rating','newest','title','year']) ? $_GET['sort'] : 'rating';
$page     = max(1, (int)($_GET['page'] ?? 1));

$searched = $q || $genre_id || $year || $min_rate;

// ── Build dynamic query ─────────────────────────────────────
$where  = [];
$params = [];
$types  = '';

if ($q) {
    $like     = '%' . $q . '%';
    $where[]  = '(g.title LIKE ? OR g.developer LIKE ? OR g.description LIKE ?)';
    $params   = array_merge($params, [$like, $like, $like]);
    $types   .= 'sss';
}
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
if ($min_rate > 0) {
    $where[] = 'g.avg_rating >= ?';
    $params[] = $min_rate;
    $types   .= 'd';
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$orderSQL = match($sort) {
    'newest' => 'g.created_at DESC',
    'title'  => 'g.title ASC',
    'year'   => 'g.release_year DESC',
    default  => 'g.avg_rating DESC, g.rating_count DESC',
};

// ── Total count ─────────────────────────────────────────────
$total = 0;
$games = null;

if ($searched || true) { // always query so page is useful as a browse fallback
    $cStmt = $conn->prepare("SELECT COUNT(*) AS c FROM games g $whereSQL");
    if ($types) $cStmt->bind_param($types, ...$params);
    $cStmt->execute();
    $total = $cStmt->get_result()->fetch_assoc()['c'];

    [$offset, $totalPages, $page] = paginate($total, $PER_PAGE, $page);

    $sql = "SELECT g.*, gr.name AS genre_name
            FROM games g
            JOIN genres gr ON g.genre_id = gr.id
            $whereSQL
            ORDER BY $orderSQL
            LIMIT ? OFFSET ?";

    $sStmt = $conn->prepare($sql);
    $allParams = array_merge($params, [$PER_PAGE, $offset]);
    $allTypes  = $types . 'ii';
    $sStmt->bind_param($allTypes, ...$allParams);
    $sStmt->execute();
    $games = $sStmt->get_result();
} else {
    $totalPages = 1;
}

$genres = $conn->query("SELECT * FROM genres ORDER BY name");
$years  = $conn->query("SELECT DISTINCT release_year FROM games ORDER BY release_year DESC");

require_once 'includes/header.php';
?>

<div class="page-hero">
    <div class="container">
        <h1 class="page-hero-title">Search Games</h1>
        <p class="breadcrumb"><a href="index.php">Home</a> › <span>Search</span></p>
    </div>
</div>

<div class="container">

    <!-- Search Form -->
    <form method="GET" action="search.php" class="filter-bar">

        <!-- Keyword -->
        <div class="filter-group" style="flex:2;min-width:220px;">
            <label for="q">Keyword</label>
            <input type="search" id="q" name="q"
                   value="<?= sanitize($q) ?>"
                   placeholder="Title, developer, description…">
        </div>

        <!-- Genre -->
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

        <!-- Year -->
        <div class="filter-group">
            <label for="year">Year</label>
            <select name="year" id="year">
                <option value="">Any Year</option>
                <?php while ($y = $years->fetch_assoc()): ?>
                <option value="<?= $y['release_year'] ?>" <?= $year == $y['release_year'] ? 'selected' : '' ?>>
                    <?= $y['release_year'] ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- Min Rating -->
        <div class="filter-group">
            <label for="min_rating">Min Rating</label>
            <select name="min_rating" id="min_rating">
                <option value="0">Any</option>
                <?php foreach ([9,8,7,6,5] as $r): ?>
                <option value="<?= $r ?>" <?= $min_rate == $r ? 'selected' : '' ?>><?= $r ?>+</option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Sort -->
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
                <button type="submit" class="btn btn-primary">Search</button>
                <a href="search.php" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>

    <!-- Results -->
    <?php if ($games !== null): ?>
    <p class="text-muted mb-2" style="font-size:.88rem;">
        <?= $total ?> result<?= $total != 1 ? 's' : '' ?>
        <?= $q ? ' for "<strong>' . sanitize($q) . '</strong>"' : '' ?>
        &nbsp;·&nbsp; Page <?= $page ?> of <?= $totalPages ?>
    </p>

    <?php if ($games->num_rows === 0): ?>
    <div class="empty-state">
        <span class="empty-icon">🔍</span>
        <h3>No games found</h3>
        <p>Try different keywords or adjust your filters.</p>
        <a href="add-game.php" class="btn btn-primary mt-2">+ Add This Game</a>
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
                <span class="text-muted" style="font-size:.78rem;"><?= $g['rating_count'] ?> reviews</span>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <nav class="pagination">
        <?php
        $qBase = http_build_query(array_filter(['q'=>$q,'genre_id'=>$genre_id,'year'=>$year,'min_rating'=>$min_rate,'sort'=>$sort]));
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
    <?php endif; ?>

</div>

<?php require_once 'includes/footer.php'; ?>
