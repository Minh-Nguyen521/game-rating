<?php
require_once 'includes/db-connect.php';
require_once 'includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) redirect('games.php');

$stmt = $conn->prepare("SELECT * FROM games WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$game = $stmt->get_result()->fetch_assoc();
if (!$game) redirect('games.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    // Delete uploaded cover
    if ($game['cover_image'] && $game['cover_image'] !== 'default-cover.jpg') {
        $path = __DIR__ . '/uploads/' . $game['cover_image'];
        if (file_exists($path)) unlink($path);
    }

    // Delete game (ratings + platforms cascade automatically)
    $del = $conn->prepare("DELETE FROM games WHERE id = ?");
    $del->bind_param('i', $id);
    $del->execute();

    redirect('games.php?deleted=1');
}

$pageTitle = 'Delete: ' . $game['title'];
require_once 'includes/header.php';
?>

<div class="page-hero">
    <div class="container">
        <h1 class="page-hero-title">Delete Game</h1>
        <p class="breadcrumb">
            <a href="index.php">Home</a> ›
            <a href="games.php">Browse</a> ›
            <a href="game-detail.php?id=<?= $id ?>"><?= sanitize($game['title']) ?></a> ›
            <span>Delete</span>
        </p>
    </div>
</div>

<div class="container" style="padding-top:2rem;">
    <div class="danger-zone">
        <div style="font-size:3rem;margin-bottom:1rem;">⚠️</div>
        <h2>Delete "<?= sanitize($game['title']) ?>"?</h2>
        <p style="color:var(--text-muted);margin-bottom:1.5rem;line-height:1.6;">
            This will permanently delete the game, all its reviews and all platform links.
            <strong>This action cannot be undone.</strong>
        </p>

        <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
            <!-- Confirm delete form -->
            <form method="POST">
                <button type="submit" name="confirm_delete" class="btn btn-danger">Yes, Delete Game</button>
            </form>
            <a href="game-detail.php?id=<?= $id ?>" class="btn btn-secondary">No, Go Back</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
