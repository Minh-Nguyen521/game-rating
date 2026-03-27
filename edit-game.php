<?php
require_once 'includes/db-connect.php';
require_once 'includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) redirect('games.php');

// Fetch existing game
$stmt = $conn->prepare("SELECT * FROM games WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$game = $stmt->get_result()->fetch_assoc();
if (!$game) redirect('games.php');

// Fetch existing platforms for this game
$pStmt = $conn->prepare("SELECT platform_id FROM game_platforms WHERE game_id = ?");
$pStmt->bind_param('i', $id);
$pStmt->execute();
$existingPlatforms = array_column($pStmt->get_result()->fetch_all(MYSQLI_ASSOC), 'platform_id');

$pageTitle = 'Edit: ' . $game['title'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ── Server-side validation ──────────────────────────────
    $title        = sanitize($_POST['title']        ?? '');
    $description  = sanitize($_POST['description']  ?? '');
    $genre_id     = (int)($_POST['genre_id']         ?? 0);
    $release_year = (int)($_POST['release_year']     ?? 0);
    $developer    = sanitize($_POST['developer']     ?? '');
    $publisher    = sanitize($_POST['publisher']     ?? '');
    $coverFile    = $game['cover_image'];

    if (strlen($title) < 2)                $errors['title']        = 'Title is required (min 2 chars).';
    if (!$genre_id)                         $errors['genre_id']     = 'Please select a genre.';
    if ($release_year < 1970 || $release_year > date('Y') + 2)
                                            $errors['release_year'] = 'Enter a valid release year.';
    if (strlen($description) < 10)         $errors['description']  = 'Description is required (min 10 chars).';

    // ── New cover upload ────────────────────────────────────
    if (!empty($_FILES['cover_image']['name'])) {
        $uploadError = '';
        $uploaded = uploadCover($_FILES['cover_image'], $uploadError);
        if ($uploaded) {
            // Delete old file if not default
            if ($game['cover_image'] !== 'default-cover.jpg') {
                $oldPath = __DIR__ . '/uploads/' . $game['cover_image'];
                if (file_exists($oldPath)) unlink($oldPath);
            }
            $coverFile = $uploaded;
        } else {
            $errors['cover_image'] = $uploadError;
        }
    }

    if (empty($errors)) {
        $upd = $conn->prepare("
            UPDATE games SET title=?, description=?, genre_id=?, release_year=?, developer=?, publisher=?, cover_image=?
            WHERE id=?
        ");
        $upd->bind_param('ssiisssi', $title, $description, $genre_id, $release_year, $developer, $publisher, $coverFile, $id);
        $upd->execute();

        // Update platforms
        $conn->prepare("DELETE FROM game_platforms WHERE game_id=?")->execute() ||
        (($d = $conn->prepare("DELETE FROM game_platforms WHERE game_id=?")) && $d->bind_param('i',$id) && $d->execute());

        if (!empty($_POST['platforms'])) {
            $ps = $conn->prepare("INSERT INTO game_platforms (game_id, platform_id) VALUES (?,?)");
            foreach ($_POST['platforms'] as $pid) {
                $pid = (int)$pid;
                $ps->bind_param('ii', $id, $pid);
                $ps->execute();
            }
        }

        redirect("game-detail.php?id=$id&updated=1");
    }
    // Re-populate from POST on error
    $game = array_merge($game, compact('title','description','genre_id','release_year','developer','publisher'));
    $existingPlatforms = array_map('intval', $_POST['platforms'] ?? []);
}

$genres    = $conn->query("SELECT * FROM genres ORDER BY name");
$platforms = $conn->query("SELECT * FROM platforms ORDER BY name");

require_once 'includes/header.php';
?>

<div class="page-hero">
    <div class="container">
        <h1 class="page-hero-title">Edit Game</h1>
        <p class="breadcrumb">
            <a href="index.php">Home</a> ›
            <a href="games.php">Browse</a> ›
            <a href="game-detail.php?id=<?= $id ?>"><?= sanitize($game['title']) ?></a> ›
            <span>Edit</span>
        </p>
    </div>
</div>

<div class="container">
<?php if ($errors): ?>
<div class="alert alert-error">❌ Please fix the errors below.</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" id="gameForm" class="form-card">

    <div class="form-grid">

        <div class="form-group full">
            <label for="title">Game Title *</label>
            <input type="text" id="title" name="title"
                   value="<?= sanitize($game['title']) ?>"
                   maxlength="255" required
                   class="<?= isset($errors['title']) ? 'input-error' : '' ?>">
            <?php if (isset($errors['title'])): ?><span class="error-msg"><?= $errors['title'] ?></span><?php endif; ?>
        </div>

        <div class="form-group">
            <label for="genre_id">Genre *</label>
            <select id="genre_id" name="genre_id" required
                    class="<?= isset($errors['genre_id']) ? 'input-error' : '' ?>">
                <option value="">— Select Genre —</option>
                <?php while ($g = $genres->fetch_assoc()): ?>
                <option value="<?= $g['id'] ?>" <?= $game['genre_id'] == $g['id'] ? 'selected' : '' ?>>
                    <?= sanitize($g['name']) ?>
                </option>
                <?php endwhile; ?>
            </select>
            <?php if (isset($errors['genre_id'])): ?><span class="error-msg"><?= $errors['genre_id'] ?></span><?php endif; ?>
        </div>

        <div class="form-group">
            <label for="release_year">Release Year *</label>
            <input type="number" id="release_year" name="release_year"
                   value="<?= (int)$game['release_year'] ?>"
                   min="1970" max="<?= date('Y') + 2 ?>" required
                   class="<?= isset($errors['release_year']) ? 'input-error' : '' ?>">
            <?php if (isset($errors['release_year'])): ?><span class="error-msg"><?= $errors['release_year'] ?></span><?php endif; ?>
        </div>

        <div class="form-group">
            <label for="developer">Developer</label>
            <input type="text" id="developer" name="developer"
                   value="<?= sanitize($game['developer']) ?>" maxlength="255">
        </div>

        <div class="form-group">
            <label for="publisher">Publisher</label>
            <input type="text" id="publisher" name="publisher"
                   value="<?= sanitize($game['publisher']) ?>" maxlength="255">
        </div>

        <div class="form-group full">
            <label for="description">Description *</label>
            <textarea id="description" name="description" required
                      class="<?= isset($errors['description']) ? 'input-error' : '' ?>"><?= sanitize($game['description']) ?></textarea>
            <?php if (isset($errors['description'])): ?><span class="error-msg"><?= $errors['description'] ?></span><?php endif; ?>
        </div>

        <div class="form-group full">
            <label>Cover Image (leave blank to keep current)</label>
            <?php if ($game['cover_image'] && $game['cover_image'] !== 'default-cover.jpg'): ?>
            <div class="image-preview" style="margin-bottom:.75rem;">
                <img src="<?= coverSrc($game['cover_image']) ?>" alt="Current cover">
            </div>
            <?php endif; ?>
            <label class="file-upload-label" for="cover_image">
                <span class="upload-icon">📁</span>
                <span>Click to replace the cover image</span>
            </label>
            <input type="file" id="cover_image" name="cover_image" accept="image/*">
            <?php if (isset($errors['cover_image'])): ?><span class="error-msg"><?= $errors['cover_image'] ?></span><?php endif; ?>
            <div id="imagePreview" class="image-preview mt-1"></div>
        </div>

        <div class="form-group full">
            <label>Platforms</label>
            <select name="platforms[]" multiple style="height:130px;">
                <?php while ($p = $platforms->fetch_assoc()): ?>
                <option value="<?= $p['id'] ?>"
                    <?= in_array((int)$p['id'], $existingPlatforms) ? 'selected' : '' ?>>
                    <?= sanitize($p['name']) ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>

    </div>

    <div style="display:flex;gap:.75rem;margin-top:1.25rem;">
        <button type="submit" class="btn btn-primary">💾 Save Changes</button>
        <a href="game-detail.php?id=<?= $id ?>" class="btn btn-secondary">Cancel</a>
        <a href="delete-game.php?id=<?= $id ?>" class="btn btn-danger confirm-delete" style="margin-left:auto;">🗑️ Delete Game</a>
    </div>

</form>
</div>

<?php require_once 'includes/footer.php'; ?>
