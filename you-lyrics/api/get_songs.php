cat > api/get_songs.php << 'EOF'
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config.php';

try {
    $genre = isset($_GET['genre']) ? sanitize($_GET['genre']) : '';
    $country = isset($_GET['country']) ? sanitize($_GET['country']) : '';
    $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 100) : 50;
    
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
    
    $sql .= " ORDER BY s.views DESC, s.date_added DESC LIMIT ?";
    $params[] = $limit;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $songs = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'count' => count($songs),
        'songs' => $songs
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
?>
EOF

echo "✅ Created api/get_songs.php"