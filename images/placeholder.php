<?php
// ============================================================
// Placeholder image generator (serves a dark gradient PNG)
// Save this as images/placeholder.php and use it as fallback,
// OR just drop a real placeholder.jpg into images/.
//
// This file generates a simple SVG placeholder inline.
// ============================================================
header('Content-Type: image/svg+xml');
echo '<svg xmlns="http://www.w3.org/2000/svg" width="300" height="400" viewBox="0 0 300 400">
  <defs>
    <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="#161b26"/>
      <stop offset="100%" stop-color="#1a1040"/>
    </linearGradient>
  </defs>
  <rect width="300" height="400" fill="url(#g)"/>
  <text x="150" y="180" font-family="sans-serif" font-size="48" fill="#2a3147" text-anchor="middle">🎮</text>
  <text x="150" y="230" font-family="sans-serif" font-size="14" fill="#8892a4" text-anchor="middle">No Image</text>
</svg>';
