cat > test.php << 'EOF'
<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YOU LYRICS - Installation Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
        h1 { color: #8B4513; }
        .success { color: green; padding: 10px; background: #e8f5e8; border-radius: 5px; margin: 5px 0; }
        .warning { color: orange; padding: 10px; background: #fff3e0; border-radius: 5px; margin: 5px 0; }
        .error { color: red; padding: 10px; background: #ffebee; border-radius: 5px; margin: 5px 0; }
        .info { color: blue; padding: 10px; background: #e3f2fd; border-radius: 5px; margin: 5px 0; }
        .section { margin: 30px 0; padding: 20px; border: 1px solid #ddd; border-radius: 10px; }
    </style>
</head>
<body>
    <h1>🔧 YOU LYRICS - Installation Test</h1>
    
    <div class="section">
        <h2>📁 Directory Structure</h2>
        <?php
        $directories = [
            'uploads/' => UPLOAD_DIR,
            'uploads/videos/' => VIDEOS_DIR,
            'uploads/thumbnails/' => THUMBNAILS_DIR,
            'uploads/lyrics/' => LYRICS_DIR,
            'uploads/artists/' => ARTISTS_DIR,
            'logs/' => LOG_DIR
        ];
        
        foreach ($directories as $name => $dir) {
            if (file_exists($dir)) {
                if (is_writable($dir)) {
                    echo "<div class='success'>✅ $name exists and is writable</div>";
                } else {
                    echo "<div class='error'>❌ $name exists but is NOT writable (chmod 755)</div>";
                }
            } else {
                echo "<div class='error'>❌ $name does not exist</div>";
            }
        }
        ?>
    </div>
    
    <div class="section">
        <h2>🗄️ Database Connection</h2>
        <?php
        try {
            $pdo->query("SELECT 1");
            echo "<div class='success'>✅ Connected to database successfully</div>";
            
            // Check tables
            $tables = ['artists', 'songs', 'lyrics_translations', 'activity_log'];
            foreach ($tables as $table) {
                $result = $pdo->query("SHOW TABLES LIKE '$table'");
                if ($result->rowCount() > 0) {
                    echo "<div class='success'>✅ Table '$table' exists</div>";
                } else {
                    echo "<div class='error'>❌ Table '$table' missing - run database.sql</div>";
                }
            }
        } catch (Exception $e) {
            echo "<div class='error'>❌ Database connection failed: " . $e->getMessage() . "</div>";
        }
        ?>
    </div>
    
    <div class="section">
        <h2>🔑 API Keys</h2>
        <?php
        if (YOUTUBE_API_KEY !== 'YOUR_YOUTUBE_API_KEY_HERE') {
            echo "<div class='success'>✅ YouTube API Key is configured</div>";
            
            // Test YouTube API
            $testUrl = "https://www.googleapis.com/youtube/v3/search?part=snippet&q=afrobeats&maxResults=1&key=" . YOUTUBE_API_KEY;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $testUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode == 200) {
                echo "<div class='success'>✅ YouTube API is working</div>";
            } else {
                echo "<div class='warning'>⚠️ YouTube API returned code $httpCode - check your key</div>";
            }
        } else {
            echo "<div class='warning'>⚠️ YouTube API Key not configured - auto-update will not work</div>";
            echo "<div class='info'>Get your API key from: https://console.cloud.google.com/</div>";
        }
        ?>
    </div>
    
    <div class="section">
        <h2>⚙️ Configuration</h2>
        <?php
        echo "<div class='info'>Site URL: " . SITE_URL . "</div>";
        echo "<div class='info'>Auto-Update: " . (AUTO_UPDATE_ENABLED ? 'Enabled' : 'Disabled') . "</div>";
        echo "<div class='info'>Max Video Size: " . (MAX_VIDEO_SIZE / 1024 / 1024) . "MB</div>";
        echo "<div class='info'>Supported Languages: " . count(unserialize(SUPPORTED_LANGUAGES)) . "</div>";
        echo "<div class='info'>African Countries: " . count(unserialize(AFRICAN_COUNTRIES)) . "</div>";
        echo "<div class='info'>Music Genres: " . count(unserialize(MUSIC_GENRES)) . "</div>";
        ?>
    </div>
    
    <div class="section">
        <h2>📄 File Checks</h2>
        <?php
        $required_files = [
            'index.php',
            'config.php',
            '.htaccess',
            'upload.php',
            'css/style.css',
            'js/main.js',
            'js/player.js',
            'api/upload.php',
            'api/get_lyrics.php',
            'api/get_songs.php',
            'api/search.php',
            'api/auto_update.php',
            'includes/header.php',
            'includes/footer.php',
            'includes/functions.php'
        ];
        
        foreach ($required_files as $file) {
            if (file_exists($file)) {
                echo "<div class='success'>✅ $file exists</div>";
            } else {
                echo "<div class='error'>❌ $file missing</div>";
            }
        }
        ?>
    </div>
    
    <div class="section">
        <h2>🚀 Next Steps</h2>
        <ol>
            <li>Import database.sql in phpMyAdmin</li>
            <li>Add your YouTube API key to config.php</li>
            <li>Set up cron job for auto-update: <code>0 * * * * php <?php echo ROOT_PATH; ?>/api/auto_update.php</code></li>
            <li>Visit <a href="index.php">index.php</a> to see your site</li>
            <li>Test upload at <a href="upload.php">upload.php</a></li>
        </ol>
    </div>
</body>
</html>
EOF

echo "✅ Created test.php"