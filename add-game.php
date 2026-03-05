<?php
// ============================================================
// Add Game Page
// ============================================================
require_once 'includes/db-connect.php';
require_once 'includes/functions.php';

$pageTitle = 'Add Game';
$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ── Server-side validation ──────────────────────────────
    $title       = sanitize($_POST['title']       ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $genre_id    = (int)($_POST['genre_id']        ?? 0);
    $release_year= (int)($_POST['release_year']    ?? 0);
    $developer   = sanitize($_POST['developer']    ?? '');
    $publisher   = sanitize($_POST['publisher']    ?? '');
    $coverFile   = 'default-cover.jpg';

    if (strlen($title) < 2)               $errors['title']        = 'Title is required (min 2 chars).';
    if (!$genre_id)                        $errors['genre_id']     = 'Please select a genre.';
    if ($release_year < 1970 || $release_year > date('Y') + 2)
                                           $errors['release_year'] = 'Enter a valid release year.';
    if (strlen($description) < 10)        $errors['description']  = 'Description is required (min 10 chars).';

    // ── File upload ─────────────────────────────────────────
    if (!empty($_FILES['cover_image']['name'])) {
        $uploadError = '';
        $uploaded = uploadCover($_FILES['cover_image'], $uploadError);
        if ($uploaded) {
            $coverFile = $uploaded;
        } else {
            $errors['cover_image'] = $uploadError;
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("
            INSERT INTO games (title, description, genre_id, release_year, developer, publisher, cover_image)
            VALUES (?,?,?,?,?,?,?)
        ");
        $stmt->bind_param('ssiisss', $title, $description, $genre_id, $release_year, $developer, $publisher, $coverFile);
        $stmt->execute();
        $newId = $conn->insert_id;

        // Platforms (multi-select)
        if (!empty($_POST['platforms'])) {
            $ps = $conn->prepare("INSERT INTO game_platforms (game_id, platform_id) VALUES (?,?)");
            foreach ($_POST['platforms'] as $pid) {
                $pid = (int)$pid;
                $ps->bind_param('ii', $newId, $pid);
                $ps->execute();
            }
        }

        redirect("game-detail.php?id=$newId&added=1");
    }
}

$genres    = $conn->query("SELECT * FROM genres ORDER BY name");
$platforms = $conn->query("SELECT * FROM platforms ORDER BY name");

require_once 'includes/header.php';
?>

<div class="page-hero">
    <div class="container">
        <h1 class="page-hero-title">Add New Game</h1>
        <p class="breadcrumb"><a href="index.php">Home</a> › <a href="games.php">Browse</a> › <span>Add Game</span></p>
    </div>
</div>

<div class="container">
<?php if ($errors): ?>
<div class="alert alert-error">❌ Please fix the errors below before submitting.</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" id="gameForm" class="form-card">

    <div class="form-grid">

        <!-- Title -->
        <div class="form-group full">
            <label for="title">Game Title *</label>
            <input type="text" id="title" name="title"
                   value="<?= sanitize($_POST['title'] ?? '') ?>"
                   placeholder="e.g. Elden Ring" maxlength="255" required
                   class="<?= isset($errors['title']) ? 'input-error' : '' ?>">
            <?php if (isset($errors['title'])): ?><span class="error-msg"><?= $errors['title'] ?></span><?php endif; ?>
        </div>

        <!-- Genre -->
        <div class="form-group">
            <label for="genre_id">Genre *</label>
            <select id="genre_id" name="genre_id" required
                    class="<?= isset($errors['genre_id']) ? 'input-error' : '' ?>">
                <option value="">— Select Genre —</option>
                <?php while ($g = $genres->fetch_assoc()): ?>
                <option value="<?= $g['id'] ?>" <?= (isset($_POST['genre_id']) && $_POST['genre_id'] == $g['id']) ? 'selected' : '' ?>>
                    <?= sanitize($g['name']) ?>
                </option>
                <?php endwhile; ?>
            </select>
            <?php if (isset($errors['genre_id'])): ?><span class="error-msg"><?= $errors['genre_id'] ?></span><?php endif; ?>
        </div>

        <!-- Release Year -->
        <div class="form-group">
            <label for="release_year">Release Year *</label>
            <input type="number" id="release_year" name="release_year"
                   value="<?= (int)($_POST['release_year'] ?? date('Y')) ?>"
                   min="1970" max="<?= date('Y') + 2 ?>" required
                   class="<?= isset($errors['release_year']) ? 'input-error' : '' ?>">
            <?php if (isset($errors['release_year'])): ?><span class="error-msg"><?= $errors['release_year'] ?></span><?php endif; ?>
        </div>

        <!-- Developer -->
        <div class="form-group">
            <label for="developer">Developer</label>
            <input type="text" id="developer" name="developer"
                   value="<?= sanitize($_POST['developer'] ?? '') ?>"
                   placeholder="e.g. FromSoftware" maxlength="255">
        </div>

        <!-- Publisher -->
        <div class="form-group">
            <label for="publisher">Publisher</label>
            <input type="text" id="publisher" name="publisher"
                   value="<?= sanitize($_POST['publisher'] ?? '') ?>"
                   placeholder="e.g. Bandai Namco" maxlength="255">
        </div>

        <!-- Description -->
        <div class="form-group full">
            <label for="description">Description *</label>
            <textarea id="description" name="description"
                      placeholder="Describe the game…" required
                      class="<?= isset($errors['description']) ? 'input-error' : '' ?>"><?= sanitize($_POST['description'] ?? '') ?></textarea>
            <?php if (isset($errors['description'])): ?><span class="error-msg"><?= $errors['description'] ?></span><?php endif; ?>
        </div>

        <!-- Cover Image -->
        <div class="form-group full">
            <label>Cover Image (JPG / PNG / WebP · max 2 MB)</label>
            <label class="file-upload-label" for="cover_image">
                <span class="upload-icon">📁</span>
                <span>Click to choose a cover image</span>
                <span style="font-size:.75rem;opacity:.6;">JPG, PNG, WebP or GIF</span>
            </label>
            <input type="file" id="cover_image" name="cover_image" accept="image/*">
            <?php if (isset($errors['cover_image'])): ?><span class="error-msg"><?= $errors['cover_image'] ?></span><?php endif; ?>
            <div id="imagePreview" class="image-preview"></div>
        </div>

        <!-- Platforms -->
        <div class="form-group full">
            <label>Platforms (hold Ctrl/Cmd to select multiple)</label>
            <select name="platforms[]" multiple style="height:130px;">
                <?php while ($p = $platforms->fetch_assoc()): ?>
                <option value="<?= $p['id'] ?>"
                    <?= (!empty($_POST['platforms']) && in_array($p['id'], $_POST['platforms'])) ? 'selected' : '' ?>>
                    <?= sanitize($p['name']) ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>

    </div><!-- /.form-grid -->

    <div style="display:flex;gap:.75rem;margin-top:1.25rem;">
        <button type="submit" class="btn btn-primary">💾 Add Game</button>
        <a href="games.php" class="btn btn-secondary">Cancel</a>
    </div>

</form>
</div>

<?php require_once 'includes/footer.php'; ?>
