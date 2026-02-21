cat > api/search.php << 'EOF'
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config.php';

if (!isset($_GET['q'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Search query required']);
    exit;
}

$query = '%' . $_GET['q'] . '%';

try {
    // Search songs
    $sql = "SELECT id, title, artist_name, genre, country, thumbnail_file, views 
            FROM songs 
            WHERE title LIKE ? OR artist_name LIKE ? 
            ORDER BY views DESC 
            LIMIT 20";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$query, $query]);
    $songs = $stmt->fetchAll();
    
    // Search artists
    $sql = "SELECT id, name, country, image_file 
            FROM artists 
            WHERE name LIKE ? 
            LIMIT 10";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$query]);
    $artists = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'songs' => $songs,
        'artists' => $artists
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Search failed']);
}
?>
EOF

echo "✅ Created api/search.php"