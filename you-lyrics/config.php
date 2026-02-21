cat > config.php << 'EOF'
<?php
// ============================================
// YOU LYRICS - MAIN CONFIGURATION FILE
// ============================================
// IMPORTANT: Update these values with your own!

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'you_lyrics');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site URL (Change this to your domain)
define('SITE_URL', 'http://localhost/you-lyrics');

// YouTube API Key - REQUIRED for auto-update
// Get from: https://console.cloud.google.com/
define('YOUTUBE_API_KEY', 'YOUR_YOUTUBE_API_KEY_HERE');

// File Paths
define('ROOT_PATH', __DIR__);
define('UPLOAD_DIR', ROOT_PATH . '/uploads/');
define('VIDEOS_DIR', UPLOAD_DIR . 'videos/');
define('THUMBNAILS_DIR', UPLOAD_DIR . 'thumbnails/');
define('LYRICS_DIR', UPLOAD_DIR . 'lyrics/');
define('ARTISTS_DIR', UPLOAD_DIR . 'artists/');
define('LOG_DIR', ROOT_PATH . '/logs/');

// Upload Limits
define('MAX_VIDEO_SIZE', 100 * 1024 * 1024); // 100MB
define('MAX_IMAGE_SIZE', 5 * 1024 * 1024);   // 5MB

// Auto-Update Configuration
define('AUTO_UPDATE_ENABLED', true);
define('UPDATE_INTERVAL', 3600); // Check every hour
define('MAX_SONGS_PER_UPDATE', 20);

// African Countries
define('AFRICAN_COUNTRIES', serialize([
    'Nigeria', 'Ghana', 'South Africa', 'Kenya', 'Tanzania',
    'DR Congo', 'Uganda', 'Algeria', 'Morocco', 'Egypt',
    'Ethiopia', 'Angola', 'Mozambique', 'Cameroon', 'Ivory Coast',
    'Senegal', 'Mali', 'Zimbabwe', 'Zambia', 'Rwanda'
]));

// Music Genres
define('MUSIC_GENRES', serialize([
    'Afrobeats', 'Bongo Flava', 'Amapiano', 'Rhumba', 'Gospel',
    'Highlife', 'Soukous', 'Mbalax', 'Zouglou', 'Coupe Decale',
    'Kwaito', 'Afro-soul', 'Traditional', 'Hip Hop', 'R&B'
]));

// Supported Languages
define('SUPPORTED_LANGUAGES', serialize([
    'en' => 'English', 'fr' => 'French', 'pt' => 'Portuguese',
    'ar' => 'Arabic', 'sw' => 'Swahili', 'ha' => 'Hausa',
    'yo' => 'Yoruba', 'ig' => 'Igbo', 'am' => 'Amharic',
    'zu' => 'Zulu', 'xh' => 'Xhosa', 'ln' => 'Lingala'
]));

// Database Connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Site temporarily unavailable. Please try again later.");
}

// Create directories if they don't exist
$directories = [UPLOAD_DIR, VIDEOS_DIR, THUMBNAILS_DIR, LYRICS_DIR, ARTISTS_DIR, LOG_DIR];
foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
        file_put_contents($dir . 'index.html', '');
    }
}

// Helper function
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function logActivity($action, $details) {
    global $pdo;
    try {
        $sql = "INSERT INTO activity_log (action, details, created_at) VALUES (?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$action, json_encode($details)]);
    } catch (Exception $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }
}
?>
EOF

echo "✅ Created config.php"