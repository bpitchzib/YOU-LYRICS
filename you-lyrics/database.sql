cat > database.sql << 'EOF'
-- Create database
CREATE DATABASE IF NOT EXISTS you_lyrics 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE you_lyrics;

-- Artists table
CREATE TABLE IF NOT EXISTS artists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    country VARCHAR(100),
    region VARCHAR(50),
    biography TEXT,
    image_file VARCHAR(255),
    active BOOLEAN DEFAULT TRUE,
    created_at DATETIME NOT NULL,
    INDEX idx_country (country),
    FULLTEXT INDEX idx_search (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Songs table
CREATE TABLE IF NOT EXISTS songs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    artist_name VARCHAR(255) NOT NULL,
    artist_id INT,
    genre VARCHAR(100) NOT NULL,
    country VARCHAR(100) NOT NULL,
    region VARCHAR(50),
    original_language VARCHAR(50) DEFAULT 'en',
    lyrics_file VARCHAR(255) NOT NULL,
    video_file VARCHAR(255),
    thumbnail_file VARCHAR(255),
    views INT DEFAULT 0,
    source VARCHAR(50),
    source_id VARCHAR(255),
    auto_updated BOOLEAN DEFAULT FALSE,
    date_added DATETIME NOT NULL,
    last_played DATETIME,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (artist_id) REFERENCES artists(id) ON DELETE SET NULL,
    INDEX idx_genre (genre),
    INDEX idx_country (country),
    INDEX idx_views (views DESC),
    INDEX idx_date (date_added DESC),
    FULLTEXT INDEX idx_search (title, artist_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Lyrics translations table
CREATE TABLE IF NOT EXISTS lyrics_translations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    song_id INT NOT NULL,
    language_code VARCHAR(10) NOT NULL,
    language_name VARCHAR(50) NOT NULL,
    lyrics_content LONGTEXT NOT NULL,
    verified BOOLEAN DEFAULT FALSE,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE,
    INDEX idx_song_lang (song_id, language_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity log table
CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action VARCHAR(50) NOT NULL,
    details TEXT,
    created_at DATETIME NOT NULL,
    INDEX idx_action (action),
    INDEX idx_date (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample artists
INSERT INTO artists (name, country, region, active, created_at) VALUES
('Burna Boy', 'Nigeria', 'West', TRUE, NOW()),
('Diamond Platnumz', 'Tanzania', 'East', TRUE, NOW()),
('Master KG', 'South Africa', 'South', TRUE, NOW()),
('Fally Ipupa', 'DR Congo', 'Central', TRUE, NOW()),
('Sauti Sol', 'Kenya', 'East', TRUE, NOW()),
('Youssou N\'Dour', 'Senegal', 'West', TRUE, NOW()),
('Sarkodie', 'Ghana', 'West', TRUE, NOW()),
('Alicia Keys', 'South Africa', 'South', TRUE, NOW());

-- Insert sample songs
INSERT INTO songs (title, artist_name, genre, country, lyrics_file, views, date_added, created_at) VALUES
('Last Last', 'Burna Boy', 'Afrobeats', 'Nigeria', 'sample1.txt', 15000, NOW(), NOW()),
('Inama', 'Diamond Platnumz', 'Bongo Flava', 'Tanzania', 'sample2.txt', 12000, NOW(), NOW()),
('Jerusalema', 'Master KG', 'Amapiano', 'South Africa', 'sample3.txt', 30000, NOW(), NOW()),
('Science', 'Fally Ipupa', 'Rhumba', 'DR Congo', 'sample4.txt', 8000, NOW(), NOW()),
('Suzanna', 'Sauti Sol', 'Afro-soul', 'Kenya', 'sample5.txt', 10000, NOW(), NOW());
EOF

echo "✅ Created database.sql"