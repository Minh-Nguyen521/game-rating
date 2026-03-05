<?php
// ============================================================
// Helper functions
// ============================================================

/**
 * Sanitise a string from user input.
 */
function sanitize(string $value): string {
    return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to a URL.
 */
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

/**
 * Render star rating HTML (out of 10 mapped to 5 stars).
 */
function starRating(float $score): string {
    $full  = (int) round($score / 2);
    $empty = 5 - $full;
    $html  = '<span class="stars" title="' . number_format($score, 1) . '/10">';
    $html .= str_repeat('<i class="star full">★</i>', $full);
    $html .= str_repeat('<i class="star empty">☆</i>', $empty);
    $html .= '</span>';
    return $html;
}

/**
 * Handle cover image upload. Returns filename on success, false on failure.
 */
function uploadCover(array $file, string &$error): string|false {
    $allowed   = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $maxBytes  = 2 * 1024 * 1024; // 2 MB
    $uploadDir = __DIR__ . '/../uploads/';

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Upload error code: ' . $file['error'];
        return false;
    }
    if (!in_array($file['type'], $allowed)) {
        $error = 'Only JPG, PNG, WebP or GIF images are allowed.';
        return false;
    }
    if ($file['size'] > $maxBytes) {
        $error = 'Image must be smaller than 2 MB.';
        return false;
    }

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('cover_', true) . '.' . strtolower($ext);
    $dest     = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        $error = 'Failed to move uploaded file. Check folder permissions.';
        return false;
    }

    return $filename;
}

/**
 * Return a cover image path (falls back to placeholder).
 */
function coverSrc(string $filename): string {
    $path = __DIR__ . '/../uploads/' . $filename;
    if ($filename && file_exists($path)) {
        return 'uploads/' . htmlspecialchars($filename);
    }
    return 'images/placeholder.php';
}

/**
 * Paginate helper – returns [offset, totalPages].
 */
function paginate(int $total, int $perPage, int $page): array {
    $totalPages = max(1, (int) ceil($total / $perPage));
    $page       = max(1, min($page, $totalPages));
    $offset     = ($page - 1) * $perPage;
    return [$offset, $totalPages, $page];
}
