-- ============================================================
-- Game Rating Platform - Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS game_rating CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE game_rating;

-- ------------------------------------------------------------
-- Table 1: genres
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS genres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- Table 2: games
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    genre_id INT NOT NULL,
    release_year YEAR NOT NULL,
    developer VARCHAR(255),
    publisher VARCHAR(255),
    cover_image VARCHAR(255) DEFAULT 'default-cover.jpg',
    avg_rating DECIMAL(3,1) DEFAULT 0.0,
    rating_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (genre_id) REFERENCES genres(id) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- ------------------------------------------------------------
-- Table 3: ratings
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL,
    reviewer_name VARCHAR(100) NOT NULL,
    score TINYINT NOT NULL CHECK (score BETWEEN 1 AND 10),
    review_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- ------------------------------------------------------------
-- Table 4: platforms
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS platforms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

-- ------------------------------------------------------------
-- Table 5: game_platforms (junction)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS game_platforms (
    game_id INT NOT NULL,
    platform_id INT NOT NULL,
    PRIMARY KEY (game_id, platform_id),
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE,
    FOREIGN KEY (platform_id) REFERENCES platforms(id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- Trigger: auto-update avg_rating and rating_count on INSERT
-- ------------------------------------------------------------
DELIMITER $$
CREATE TRIGGER after_rating_insert
AFTER INSERT ON ratings
FOR EACH ROW
BEGIN
    UPDATE games
    SET avg_rating  = (SELECT ROUND(AVG(score),1) FROM ratings WHERE game_id = NEW.game_id),
        rating_count = (SELECT COUNT(*) FROM ratings WHERE game_id = NEW.game_id)
    WHERE id = NEW.game_id;
END$$

CREATE TRIGGER after_rating_delete
AFTER DELETE ON ratings
FOR EACH ROW
BEGIN
    UPDATE games
    SET avg_rating  = COALESCE((SELECT ROUND(AVG(score),1) FROM ratings WHERE game_id = OLD.game_id), 0.0),
        rating_count = (SELECT COUNT(*) FROM ratings WHERE game_id = OLD.game_id)
    WHERE id = OLD.game_id;
END$$
DELIMITER ;
