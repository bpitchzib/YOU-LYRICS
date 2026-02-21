cat > api/upload.php << 'EOF'
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Validate required fields
    $required = ['title', 'artist', 'genre', 'country', 'originalLyrics'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Field $field is required");
        }
    }
    
    // Handle video upload
    $videoFilename = null;
    if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
        $videoFile = $_FILES['video'];
        $allowedTypes = ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime'];
        
        if (!in_array($videoFile['type'], $allowedTypes)) {
            throw new Exception('Invalid video format. Allowed: MP4, WebM, OGG');
        }
        
        if ($videoFile['size'] > MAX_VIDEO_SIZE) {
            throw new Exception('Video too large. Max size: ' . (MAX_VIDEO_SIZE/1024/1024) . 'MB');
        }
        
        $extension = pathinfo($videoFile['name'], PATHINFO_EXTENSION);
        $videoFilename = uniqid() . '_' . time() . '.' . $extension;
        
        if (!move_uploaded_file($videoFile['tmp_name'], VIDEOS_DIR . $videoFilename)) {
            throw new Exception('Failed to upload video');
        }
    }
    
    // Handle thumbnail upload
    $thumbnailFilename = null;
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $thumbnailFile = $_FILES['thumbnail'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (in_array($thumbnailFile['type'], $allowedTypes) && $thumbnailFile['size'] <= MAX_IMAGE_SIZE) {
            $extension = pathinfo($thumbnailFile['name'], PATHINFO_EXTENSION);
            $thumbnailFilename = uniqid() . '_thumb.' . $extension;
            move_uploaded_file($thumbnailFile['tmp_name'], THUMBNAILS_DIR . $thumbnailFilename);
        }
    }
    
    // Save lyrics
    $lyricsFilename = uniqid() . '_lyrics.txt';
    $lyricsPath = LYRICS_DIR . $lyricsFilename;
    file_put_contents($lyricsPath, $_POST['originalLyrics']);
    
    // Insert song into database
    $sql = "INSERT INTO songs (title, artist_name, genre, country, original_language, 
            lyrics_file, video_file, thumbnail_file, date_added, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        sanitize($_POST['title']),
        sanitize($_POST['artist']),
        sanitize($_POST['genre']),
        sanitize($_POST['country']),
        $_POST['language'] ?? 'en',
        $lyricsFilename,
        $videoFilename,
        $thumbnailFilename
    ]);
    
    $songId = $pdo->lastInsertId();
    
    // Add translation if provided
    if (!empty($_POST['translatedLyrics'])) {
        $sql = "INSERT INTO lyrics_translations (song_id, language_code, language_name, lyrics_content, created_at) 
                VALUES (?, 'en', 'English', ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$songId, $_POST['translatedLyrics']]);
    }
    
    // Log activity
    logActivity('song_uploaded', [
        'song_id' => $songId,
        'title' => $_POST['title'],
        'artist' => $_POST['artist']
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Song uploaded successfully',
        'song_id' => $songId
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
EOF

echo "✅ Created api/upload.php"