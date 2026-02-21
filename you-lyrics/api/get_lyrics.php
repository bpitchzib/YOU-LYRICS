cat > api/get_lyrics.php << 'EOF'
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config.php';

if (!isset($_GET['song_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Song ID required']);
    exit;
}

try {
    $songId = (int)$_GET['song_id'];
    
    // Get song details
    $sql = "SELECT s.*, a.image_file as artist_image 
            FROM songs s 
            LEFT JOIN artists a ON s.artist_id = a.id 
            WHERE s.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$songId]);
    $song = $stmt->fetch();
    
    if (!$song) {
        http_response_code(404);
        echo json_encode(['error' => 'Song not found']);
        exit;
    }
    
    // Get original lyrics
    $lyricsPath = LYRICS_DIR . $song['lyrics_file'];
    $originalLyrics = file_exists($lyricsPath) ? file_get_contents($lyricsPath) : 'No lyrics available';
    
    // Get translations
    $sql = "SELECT * FROM lyrics_translations WHERE song_id = ? ORDER BY language_code";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$songId]);
    $translations = $stmt->fetchAll();
    
    // Update view count
    $sql = "UPDATE songs SET views = views + 1, last_played = NOW() WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$songId]);
    
    echo json_encode([
        'success' => true,
        'song' => [
            'id' => $song['id'],
            'title' => $song['title'],
            'artist_name' => $song['artist_name'],
            'genre' => $song['genre'],
            'country' => $song['country'],
            'original_lyrics' => $originalLyrics,
            'video_file' => $song['video_file'],
            'thumbnail_file' => $song['thumbnail_file'],
            'views' => $song['views'] + 1
        ],
        'translations' => $translations
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_lyrics: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error occurred']);
}
?>
EOF

echo "✅ Created api/get_lyrics.php"