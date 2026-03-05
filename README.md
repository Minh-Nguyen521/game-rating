# GameVault – Game Discovery & Rating Platform

A full-stack web app built with **PHP**, **MySQL**, **HTML5**, **CSS3**, and **JavaScript** — similar to IMDb but for video games.

## 🚀 Setup

### 1. Requirements
- PHP 8.0+
- MySQL 8.0+
- Apache / Nginx (or `php -S localhost:8000`)

### 2. Database
```sql
-- In MySQL client or phpMyAdmin:
SOURCE schema.sql;
SOURCE sample-data.sql;
```

### 3. Configure DB connection
Edit `includes/db-connect.php` and set your MySQL credentials:
```php
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
```

### 4. Run
```bash
# With PHP built-in server:
php -S localhost:8000

# Then open:
http://localhost:8000
```

### 5. Uploads folder permissions
```bash
chmod 755 uploads/
```

---

## 📁 Structure

```
game-rating/
├── css/style.css          # All styles + responsive
├── js/main.js             # Nav, validation, star picker, file preview
├── images/placeholder.php # Auto-generated SVG placeholder
├── uploads/               # User-uploaded covers
├── includes/
│   ├── db-connect.php     # MySQL connection
│   ├── functions.php      # Helpers: sanitize, upload, paginate, stars
│   ├── header.php         # HTML head + sticky nav
│   └── footer.php         # Footer + scripts
├── index.php              # Home: hero, top-rated, latest
├── games.php              # Browse with genre/year filter + pagination
├── game-detail.php        # Game info + review form + review list
├── add-game.php           # Add game (with cover upload)
├── edit-game.php          # Edit game
├── delete-game.php        # Delete confirmation
├── search.php             # Full search + advanced filters
├── schema.sql             # Table definitions + triggers
└── sample-data.sql        # 8 genres, 12 games, 25+ reviews
```

---

## ✅ Features

| Feature | Implementation |
|---------|---------------|
| **CRUD** | Add / Edit / Delete games; submit reviews |
| **Search** | Full-text search on title, developer, description |
| **Filter** | Genre, year, min rating, sort order |
| **Pagination** | 12 per page with page links |
| **File Upload** | Cover images (JPG/PNG/WebP, max 2 MB) |
| **Form Validation** | Client-side (JS) + server-side (PHP) |
| **Responsive** | Mobile-first CSS with hamburger nav |
| **Database** | 5 related tables with FK constraints + triggers |