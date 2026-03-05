<?php
// ============================================================
// Game Detail Page + Review submission
// ============================================================
require_once 'includes/db-connect.php';
require_once 'includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { redirect('games.php'); }

// ── Fetch game ──────────────────────────────────────────────
$stmt = $conn->prepare("
    SELECT g.*, gr.name AS genre_name
    FROM games g
    JOIN genres gr ON g.genre_id = gr.id
    WHERE g.id = ?
");
$stmt->bind_param('i', $id);
$stmt->execute();
$game = $stmt->get_result()->fetch_assoc();
if (!$game) { redirect('games.php'); }

// ── Fetch platforms ─────────────────────────────────────────
$pStmt = $conn->prepare("
    SELECT p.name FROM platforms p
    JOIN game_platforms gp ON p.id = gp.platform_id
    WHERE gp.game_id = ?
    ORDER BY p.name
");
$pStmt->bind_param('i', $id);
$pStmt->execute();
$platforms = $pStmt->get_result();

// ── Handle review form POST ─────────────────────────────────
$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $reviewer = sanitize($_POST['reviewer_name'] ?? '');
    $score    = (int)($_POST['score'] ?? 0);
    $text     = sanitize($_POST['review_text'] ?? '');

    if (strlen($reviewer) < 2) $errors[] = 'Name must be at least 2 characters.';
    if ($score < 1 || $score > 10) $errors[] = 'Score must be between 1 and 10.';
    if (strlen($text) < 5)         $errors[] = 'Review must be at least 5 characters.';

    if (empty($errors)) {
        $ins = $conn->prepare("INSERT INTO ratings (game_id, reviewer_name, score, review_text) VALUES (?,?,?,?)");
        $ins->bind_param('isis', $id, $reviewer, $score, $text);
        $ins->execute();
        $success = 'Your review has been submitted!';

        // Refresh game data
        $stmt->execute();
        $game = $stmt->get_result()->fetch_assoc();
    }
}

// ── Fetch reviews ───────────────────────────────────────────
$revStmt = $conn->prepare("
    SELECT * FROM ratings WHERE game_id = ? ORDER BY created_at DESC
");
$revStmt->bind_param('i', $id);
$revStmt->execute();
$reviews = $revStmt->get_result();

$pageTitle = $game['title'];
require_once 'includes/header.php';
?>

<div class="page-hero">
    <div class="container">
        <p class="breadcrumb">
            <a href="index.php">Home</a> ›
            <a href="games.php">Browse</a> ›
            <span><?= sanitize($game['title']) ?></span>
        </p>
    </div>
</div>

<div class="container">

    <!-- Game Detail -->
    <div class="detail-layout mb-3">

        <!-- Cover -->
        <div>
            <img src="<?= coverSrc($game['cover_image']) ?>"
                 alt="<?= sanitize($game['title']) ?>"
                 class="detail-cover">
        </div>

        <!-- Info -->
        <div>
            <h1 class="detail-title"><?= sanitize($game['title']) ?></h1>

            <div class="detail-meta-row">
                <span class="genre-badge"><?= sanitize($game['genre_name']) ?></span>
                <span><?= (int)$game['release_year'] ?></span>
                <?php if ($game['developer']): ?>
                <span>· <?= sanitize($game['developer']) ?></span>
                <?php endif; ?>
                <?php if ($game['publisher'] && $game['publisher'] !== $game['developer']): ?>
                <span>· Published by <?= sanitize($game['publisher']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Score Box -->
            <div class="score-box">
                <div class="detail-score"><?= number_format($game['avg_rating'],1) ?></div>
                <div style="margin:.25rem 0;"><?= starRating((float)$game['avg_rating']) ?></div>
                <div class="detail-score-label"><?= $game['rating_count'] ?> review<?= $game['rating_count'] != 1 ? 's' : '' ?></div>
            </div>

            <!-- Description -->
            <p style="color:var(--text-muted);font-size:.95rem;line-height:1.7;margin-bottom:1.25rem;">
                <?= nl2br(sanitize($game['description'])) ?>
            </p>

            <!-- Platforms -->
            <?php if ($platforms->num_rows > 0): ?>
            <div style="margin-bottom:1.25rem;">
                <p style="font-size:.78rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.4rem;">Platforms</p>
                <div class="platforms-list">
                    <?php while ($p = $platforms->fetch_assoc()): ?>
                    <span class="platform-tag"><?= sanitize($p['name']) ?></span>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Actions -->
            <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
                <a href="edit-game.php?id=<?= $id ?>" class="btn btn-secondary">✏️ Edit Game</a>
                <a href="delete-game.php?id=<?= $id ?>" class="btn btn-danger confirm-delete">🗑️ Delete Game</a>
            </div>
        </div>
    </div>

    <!-- Review Form -->
    <section style="max-width:720px;margin-bottom:3rem;">
        <h2 class="section-title mb-2" style="margin-bottom:1.25rem;">Write a Review</h2>

        <?php if ($success): ?>
        <div class="alert alert-success" data-auto-dismiss>✅ <?= $success ?></div>
        <?php endif; ?>

        <?php if ($errors): ?>
        <div class="alert alert-error">
            ❌ <?= implode('<br>', array_map('sanitize', $errors)) ?>
        </div>
        <?php endif; ?>

        <form method="POST" id="reviewForm" class="form-card" style="padding:1.5rem;">
            <div class="form-grid">
                <div class="form-group">
                    <label for="reviewer_name">Your Name *</label>
                    <input type="text" id="reviewer_name" name="reviewer_name"
                           placeholder="e.g. Alex M"
                           value="<?= sanitize($_POST['reviewer_name'] ?? '') ?>"
                           maxlength="100" required>
                </div>

                <div class="form-group">
                    <label for="score">Score (1 – 10) *</label>
                    <div id="starPicker" style="display:flex;gap:2px;margin-bottom:.3rem;"></div>
                    <input type="number" id="score" name="score" min="1" max="10"
                           value="<?= (int)($_POST['score'] ?? 7) ?>" required>
                </div>

                <div class="form-group full">
                    <label for="review_text">Review *</label>
                    <textarea id="review_text" name="review_text"
                              placeholder="Share your thoughts about this game…"
                              required><?= sanitize($_POST['review_text'] ?? '') ?></textarea>
                </div>
            </div>

            <button type="submit" name="submit_review" class="btn btn-primary mt-2">Submit Review</button>
        </form>
    </section>

    <!-- Reviews List -->
    <section style="max-width:720px;margin-bottom:3rem;">
        <h2 class="section-title" style="margin-bottom:1.25rem;">
            Reviews <span style="color:var(--text-muted);font-size:.85rem;font-weight:500;">(<?= $reviews->num_rows ?>)</span>
        </h2>

        <?php if ($reviews->num_rows === 0): ?>
        <div class="empty-state" style="padding:2rem 0;">
            <span class="empty-icon">📝</span>
            <p>No reviews yet. Be the first!</p>
        </div>
        <?php else: ?>
        <div class="review-list">
            <?php while ($r = $reviews->fetch_assoc()): ?>
            <div class="review-card">
                <div class="review-header">
                    <div style="display:flex;align-items:center;gap:.6rem;">
                        <span class="reviewer-name"><?= sanitize($r['reviewer_name']) ?></span>
                        <?= starRating((float)$r['score']) ?>
                    </div>
                    <div style="display:flex;align-items:center;gap:.5rem;">
                        <span class="review-score-badge"><?= $r['score'] ?>/10</span>
                        <span class="review-date"><?= date('M j, Y', strtotime($r['created_at'])) ?></span>
                    </div>
                </div>
                <?php if ($r['review_text']): ?>
                <p class="review-text"><?= nl2br(sanitize($r['review_text'])) ?></p>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
    </section>

</div>

<?php require_once 'includes/footer.php'; ?>
