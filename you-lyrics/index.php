cat > index.php << 'EOF'
<?php
require_once 'config.php';
require_once 'includes/functions.php';

// Get filter parameters
$genre = isset($_GET['genre']) ? sanitize($_GET['genre']) : '';
$country = isset($_GET['country']) ? sanitize($_GET['country']) : '';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Build query
$sql = "SELECT s.*, a.image_file as artist_image 
        FROM songs s 
        LEFT JOIN artists a ON s.artist_id = a.id 
        WHERE 1=1";
$params = [];

if ($genre) {
    $sql .= " AND s.genre = ?";
    $params[] = $genre;
}
if ($country) {
    $sql .= " AND s.country = ?";
    $params[] = $country;
}
if ($search) {
    $sql .= " AND (s.title LIKE ? OR s.artist_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY s.views DESC, s.date_added DESC LIMIT 50";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$songs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YOU LYRICS - African Songs Bilingual Lyrics</title>
    <meta name="description" content="Discover African music with bilingual lyrics. Afrobeats, Bongo Flava, Amapiano, Rhumba, and more.">
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h2>Discover African Music<br>In Your Language</h2>
            <p>Bilingual lyrics for the best African songs - updated automatically</p>
            <div class="search-box">
                <form action="index.php" method="GET">
                    <input type="text" name="search" placeholder="Search for a song or artist..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit"><i class="fas fa-search"></i> Search</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Filters Section -->
    <section class="filters-section">
        <div class="container">
            <div class="genre-filters">
                <h3>Browse by Genre:</h3>
                <div class="filter-chips">
                    <a href="index.php" class="chip <?php echo !$genre ? 'active' : ''; ?>">All</a>
                    <?php
                    $genres = unserialize(MUSIC_GENRES);
                    foreach ($genres as $g) {
                        $active = ($genre == $g) ? 'active' : '';
                        echo "<a href='?genre=" . urlencode($g) . "' class='chip $active'>$g</a>";
                    }
                    ?>
                </div>
            </div>
            
            <div class="country-filters">
                <h3>Browse by Country:</h3>
                <div class="filter-chips">
                    <a href="index.php" class="chip <?php echo !$country ? 'active' : ''; ?>">All</a>
                    <?php
                    $countries = unserialize(AFRICAN_COUNTRIES);
                    foreach ($countries as $c) {
                        $active = ($country == $c) ? 'active' : '';
                        echo "<a href='?country=" . urlencode($c) . "' class='chip $active'>$c</a>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Songs Grid -->
    <section class="songs-section">
        <div class="container">
            <h2 class="section-title">
                <?php 
                if ($search) echo "Search Results for: $search";
                elseif ($genre) echo "$genre Songs";
                elseif ($country) echo "Songs from $country";
                else echo "Trending African Songs";
                ?>
            </h2>
            
            <div class="songs-grid" id="songsGrid">
                <?php if (empty($songs)): ?>
                    <p class="no-results">No songs found. Check back soon!</p>
                <?php else: ?>
                    <?php foreach ($songs as $song): ?>
                    <div class="song-card" onclick="openSong(<?php echo $song['id']; ?>)">
                        <img src="<?php echo $song['thumbnail_file'] ? 'uploads/thumbnails/' . $song['thumbnail_file'] : 'https://via.placeholder.com/300x200?text=' . urlencode($song['title']); ?>" 
                             alt="<?php echo htmlspecialchars($song['title']); ?>">
                        <div class="song-info">
                            <h3><?php echo htmlspecialchars($song['title']); ?></h3>
                            <p><?php echo htmlspecialchars($song['artist_name']); ?></p>
                            <div class="song-meta">
                                <span><i class="fas fa-music"></i> <?php echo $song['genre']; ?></span>
                                <span><i class="fas fa-map-marker-alt"></i> <?php echo $song['country']; ?></span>
                                <span><i class="fas fa-eye"></i> <?php echo number_format($song['views']); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Featured Artists -->
    <section class="artists-section">
        <div class="container">
            <h2 class="section-title">Featured African Artists</h2>
            <div class="artists-grid">
                <?php
                $stmt = $pdo->query("SELECT * FROM artists WHERE active = 1 ORDER BY RAND() LIMIT 8");
                $artists = $stmt->fetchAll();
                foreach ($artists as $artist):
                ?>
                <div class="artist-card" onclick="filterByArtist('<?php echo htmlspecialchars($artist['name']); ?>')">
                    <img src="<?php echo $artist['image_file'] ? 'uploads/artists/' . $artist['image_file'] : 'https://via.placeholder.com/150?text=' . urlencode($artist['name']); ?>" 
                         alt="<?php echo htmlspecialchars($artist['name']); ?>">
                    <h4><?php echo htmlspecialchars($artist['name']); ?></h4>
                    <p><?php echo $artist['country']; ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Song Modal -->
    <div id="songModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="modalContent"></div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="js/main.js"></script>
    <script src="js/player.js"></script>
    <?php if (AUTO_UPDATE_ENABLED): ?>
    <script>
    setTimeout(function() {
        location.reload();
    }, 300000);
    </script>
    <?php endif; ?>
</body>
</html>
EOF

echo "✅ Created index.php"