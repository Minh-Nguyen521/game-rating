-- ============================================================
-- Game Rating Platform - Sample Data
-- ============================================================
USE game_rating;

-- ------------------------------------------------------------
-- Genres
-- ------------------------------------------------------------
INSERT INTO genres (name, description) VALUES
('Action',      'Fast-paced games focused on combat and reflexes'),
('RPG',         'Role-playing games with story, progression and character builds'),
('FPS',         'First-person shooter games'),
('Adventure',   'Story-driven exploration games'),
('Strategy',    'Tactical and resource management games'),
('Sports',      'Sports and racing simulations'),
('Horror',      'Survival horror and psychological terror'),
('Platformer',  'Jump-and-run side-scrolling games');

-- ------------------------------------------------------------
-- Platforms
-- ------------------------------------------------------------
INSERT INTO platforms (name) VALUES
('PC'),('PlayStation 5'),('PlayStation 4'),('Xbox Series X'),('Nintendo Switch'),('Xbox One');

-- ------------------------------------------------------------
-- Games
-- ------------------------------------------------------------
INSERT INTO games (title, description, genre_id, release_year, developer, publisher, cover_image) VALUES
('Elden Ring',
 'An action RPG set in a vast open world crafted by Hidetaka Miyazaki and George R.R. Martin. Explore the Lands Between, battle fearsome bosses, and uncover the mystery of the Elden Ring.',
 1, 2022, 'FromSoftware', 'Bandai Namco', 'elden-ring.jpg'),

('The Witcher 3: Wild Hunt',
 'A story-driven open-world RPG set in a war-torn fantasy universe. Play as Geralt of Rivia, a monster hunter, and make choices that shape the world around you.',
 2, 2015, 'CD Projekt Red', 'CD Projekt', 'witcher3.jpg'),

('Cyberpunk 2077',
 'An open-world action-adventure set in Night City. Play as V, a mercenary outlaw going after a one-of-a-kind implant that holds the key to immortality.',
 1, 2020, 'CD Projekt Red', 'CD Projekt', 'cyberpunk2077.jpg'),

('Halo Infinite',
 'Master Chief returns to confront the most ruthless foe he has ever faced — the Banished — on a mysterious ringworld known as Zeta Halo.',
 3, 2021, '343 Industries', 'Xbox Game Studios', 'halo-infinite.jpg'),

('The Legend of Zelda: Breath of the Wild',
 'Step into a world of discovery and exploration in this open-air adventure. Explore the wilds of Hyrule and face the darkness of Calamity Ganon.',
 4, 2017, 'Nintendo EPD', 'Nintendo', 'botw.jpg'),

('Civilization VI',
 'Build an empire to stand the test of time in this critically acclaimed strategy game. Explore, expand, exploit and exterminate across multiple eras.',
 5, 2016, 'Firaxis Games', '2K Games', 'civ6.jpg'),

('FIFA 24',
 'EA Sports FC brings the world\'s game to life with HyperMotion V technology, delivering a true-to-life football experience.',
 6, 2023, 'EA Vancouver', 'EA Sports', 'fifa24.jpg'),

('Resident Evil Village',
 'Set a few years after Resident Evil 7, Ethan Winters searches a mysterious village for his kidnapped daughter while uncovering dark secrets.',
 7, 2021, 'Capcom', 'Capcom', 're-village.jpg'),

('Hollow Knight',
 'A challenging 2D action-adventure through a vast ruined kingdom of insects and heroes. Explore twisting caverns and battle tainted creatures.',
 8, 2017, 'Team Cherry', 'Team Cherry', 'hollow-knight.jpg'),

('God of War Ragnarök',
 'Kratos and his son Atreus must journey to each of the Nine Realms in search of answers as Fimbulwinter approaches its end.',
 1, 2022, 'Santa Monica Studio', 'Sony Interactive Entertainment', 'gow-ragnarok.jpg'),

('Baldur\'s Gate 3',
 'Gather your party and return to the Forgotten Realms in this tale of fellowship and betrayal, sacrifice and survival, and the lure of absolute power.',
 2, 2023, 'Larian Studios', 'Larian Studios', 'bg3.jpg'),

('Doom Eternal',
 'Hell\'s armies have invaded Earth. Become the Slayer in an epic single-player campaign to conquer demons across dimensions.',
 3, 2020, 'id Software', 'Bethesda Softworks', 'doom-eternal.jpg');

-- ------------------------------------------------------------
-- Game–Platform links
-- ------------------------------------------------------------
INSERT INTO game_platforms (game_id, platform_id) VALUES
(1,1),(1,2),(1,4),   -- Elden Ring: PC, PS5, XSX
(2,1),(2,3),(2,6),   -- Witcher 3: PC, PS4, XOne
(3,1),(3,2),(3,4),   -- Cyberpunk: PC, PS5, XSX
(4,1),(4,4),          -- Halo Infinite: PC, XSX
(5,5),                -- BotW: Switch
(6,1),                -- Civ VI: PC
(7,1),(7,3),(7,6),   -- FIFA 24: PC, PS4, XOne
(8,1),(8,2),(8,5),   -- RE Village: PC, PS5, Switch
(9,1),(9,5),          -- Hollow Knight: PC, Switch
(10,2),               -- GoW Ragnarök: PS5
(11,1),(11,2),        -- BG3: PC, PS5
(12,1),(12,4);        -- Doom Eternal: PC, XSX

-- ------------------------------------------------------------
-- Ratings & Reviews
-- ------------------------------------------------------------
INSERT INTO ratings (game_id, reviewer_name, score, review_text) VALUES
(1,'Alex M', 10,'An absolute masterpiece. The world design and boss fights are unmatched.'),
(1,'Sara K',  9, 'Breathtaking world but steep learning curve for newcomers.'),
(1,'Tom R',  10,'Best game I have ever played. Period.'),

(2,'Emily W', 10,'The story, the characters, the world — all perfect. A gold standard RPG.'),
(2,'Jake S',   9,'Incredible narrative depth. Some quests genuinely moved me.'),
(2,'Nina P',  10,'Gwent alone is worth the price. Timeless classic.'),

(3,'Chris B',  7,'Rough at launch but now a fantastic cyberpunk experience.'),
(3,'Mia T',    8,'Night City is stunningly detailed. Story is gripping.'),
(3,'Liam O',   9,'The 2.0 update transformed this game completely.'),

(4,'Ryan H',   8,'The campaign is solid and multiplayer is addictive.'),
(4,'Zoe L',    7,'Good but feels like a step back from Halo 5 in some areas.'),

(5,'Daniel C',10,'Redefines what open-world games can be. Pure joy to explore.'),
(5,'Anna B',   9,'Still magical years later. A benchmark for game design.'),

(6,'Marcus F', 9,'The deepest strategy experience you can get. Hundreds of hours of content.'),
(6,'Luna V',   8,'Perfect for fans of the series. The civics system is genius.'),

(7,'Kevin P',  7,'Solid gameplay improvements but still feels incremental.'),
(7,'Jess A',   6,'Good football sim but little innovation year over year.'),

(8,'Frank D',  9,'Terrifying atmosphere and brilliant level design. Loved every second.'),
(8,'Grace E',  8,'Lady Dimitrescu sections are iconic. Great horror game.'),

(9,'Olivia N',10,'One of the greatest indie games ever made. Art, music, gameplay — all perfect.'),
(9,'Sam Q',    9,'Brutally difficult but extremely rewarding. A true gem.'),

(10,'Isabella R',10,'Emotional, epic, and beautiful. Sets a new bar for action games.'),
(10,'Henry S',   9, 'The father-son dynamic is handled with so much care.'),

(11,'Sophia T', 10,'A revolution in RPGs. The reactivity and depth are unparalleled.'),
(11,'Arthur U',  9,'Larian has outdone themselves. This is D&D brought to life perfectly.'),

(12,'Chloe V',   9,'Non-stop adrenaline. Best gunplay in any FPS, full stop.'),
(12,'Ethan W',   8,'Incredible movement system and satisfying combat loop.');

-- Update avg_rating manually for initial load (triggers handle future inserts)
UPDATE games g
SET avg_rating  = (SELECT ROUND(AVG(score),1) FROM ratings WHERE game_id = g.id),
    rating_count = (SELECT COUNT(*)            FROM ratings WHERE game_id = g.id);
