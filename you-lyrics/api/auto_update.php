cat > api/auto_update.php << 'EOF'
<?php
// ============================================
// AUTO-UPDATE SCRIPT - RUN VIA CRON JOB
// ============================================
// This script automatically finds new African songs
// Set up cron job: 0 * * * * php /path/to/api/auto_update.php
// ============================================

require_once '../config.php';

class AutoUpdater {
    private $pdo;
    private $youtubeApiKey;
    private $logFile;
    private $newSongsCount = 0;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->youtubeApiKey = YOUTUBE_API_KEY;
        $this->logFile = LOG_DIR . 'updates.log';
    }
    
    public function run() {
        $this->log("=== Starting auto-update ===");
        
        if (!AUTO_UPDATE_ENABLED) {
            $this->log("Auto-update is disabled in config");
            return;
        }
        
        // Check if YouTube API key is configured
        if ($this->youtubeApiKey === 'YOUR_YOUTUBE_API_KEY_HERE' || empty($this->youtubeApiKey)) {
            $this->log("ERROR: YouTube API key not configured!");
            $this->log("Please add your YouTube API key in config.php");
            return;
        }
        
        // Search for new African music
        $this->checkYouTube();
        
        $this->log("=== Auto-update completed. Found {$this->newSongsCount} new songs ===");
        
        // Log to database
        logActivity('auto_update', ['new_songs' => $this->newSongsCount]);
    }
    
    private function checkYouTube() {
        $this->log("Checking YouTube for new African music...");
        
        // African music search queries
        $queries = [
            'new afrobeats songs 2024',
            'latest bongo flava 2024',
            'new amapiano 2024',
            'african gospel music 2024',
            'nouveau coupe decale 2024',
            'new rhumba 2024',
            'african hip hop 2024',
            'soukous nouveau 2024',
            'mbalax nouveau 2024',
            'afro pop 2024',
            'nouveauté africaine 2024',
            'african traditional music'
        ];
        
        foreach ($queries as $query) {
            try {
                $url = "https://www.googleapis.com/youtube/v3/search?part=snippet&q=" . 
                       urlencode($query) . "&maxResults=10&type=video&order=date&key=" . 
                       $this->youtubeApiKey;
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode == 200) {
                    $data = json_decode($response, true);
                    
                    if (isset($data['items'])) {
                        foreach ($data['items'] as $item) {
                            $this->processYouTubeVideo($item);
                        }
                    }
                } else {
                    $this->log("YouTube API error: HTTP $httpCode");
                }
                
                // Avoid rate limiting
                sleep(1);
                
            } catch (Exception $e) {
                $this->log("YouTube API error: " . $e->getMessage());
            }
        }
    }
    
    private function processYouTubeVideo($item) {
        $title = $item['snippet']['title'];
        $channel = $item['snippet']['channelTitle'];
        $videoId = $item['id']['videoId'];
        $published = $item['snippet']['publishedAt'];
        $thumbnail = $item['snippet']['thumbnails']['high']['url'];
        
        // Skip if older than 7 days
        if (strtotime($published) < strtotime('-7 days')) {
            return;
        }
        
        // Check if song already exists
        if ($this->songExists($title, $channel)) {
            return;
        }
        
        // Determine country and genre
        $country = $this->guessCountry($channel);
        $genre = $this->guessGenre($title, $channel);
        
        // Skip if not African
        if ($country === 'Unknown' && !$this->isAfricanContent($title, $channel)) {
            return;
        }
        
        if ($country === 'Unknown') {
            $country = 'Africa';
        }
        
        // Add to database
        $this->addSong([
            'title' => $title,
            'artist' => $channel,
            'genre' => $genre,
            'country' => $country,
            'source' => 'youtube',
            'source_id' => $videoId,
            'thumbnail' => $thumbnail
        ]);
    }
    
    private function addSong($data) {
        try {
            // Create placeholder lyrics file
            $lyricsFilename = uniqid() . '_lyrics.txt';
            $lyricsPath = LYRICS_DIR . $lyricsFilename;
            
            $placeholderLyrics = "Title: " . $data['title'] . "\n";
            $placeholderLyrics .= "Artist: " . $data['artist'] . "\n";
            $placeholderLyrics .= "Genre: " . $data['genre'] . "\n";
            $placeholderLyrics .= "Country: " . $data['country'] . "\n\n";
            $placeholderLyrics .= "Lyrics will be added soon. Auto-imported from YouTube.\n";
            $placeholderLyrics .= "Help us by contributing translations!";
            
            file_put_contents($lyricsPath, $placeholderLyrics);
            
            // Download thumbnail
            $thumbnailFilename = null;
            if (!empty($data['thumbnail'])) {
                $thumbnailContent = file_get_contents($data['thumbnail']);
                if ($thumbnailContent) {
                    $thumbnailFilename = uniqid() . '_thumb.jpg';
                    file_put_contents(THUMBNAILS_DIR . $thumbnailFilename, $thumbnailContent);
                }
            }
            
            // Insert song
            $sql = "INSERT INTO songs (title, artist_name, genre, country, lyrics_file, 
                    thumbnail_file, source, source_id, auto_updated, date_added, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['title'],
                $data['artist'],
                $data['genre'],
                $data['country'],
                $lyricsFilename,
                $thumbnailFilename,
                $data['source'],
                $data['source_id']
            ]);
            
            $this->newSongsCount++;
            $this->log("Added new song: {$data['title']} by {$data['artist']} ({$data['country']} - {$data['genre']})");
            
        } catch (Exception $e) {
            $this->log("Error adding song: " . $e->getMessage());
        }
    }
    
    private function songExists($title, $artist) {
        $sql = "SELECT id FROM songs WHERE title LIKE ? AND artist_name LIKE ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['%' . $title . '%', '%' . $artist . '%']);
        return $stmt->fetch() !== false;
    }
    
    private function guessCountry($artist) {
        $artist = strtolower($artist);
        
        $countryMap = [
            'Nigeria' => ['burna boy', 'wizkid', 'davido', 'tiwa savage', 'rema', 'asake', 'omah lay', 'fireboy', 'ckay', 'yemi alade'],
            'Ghana' => ['sarkodie', 'stonebwoy', 'shatta wale', 'kuami eugene', 'king promise', 'kiDi'],
            'South Africa' => ['master kg', 'nomcebo', 'black coffee', 'kabza de small', 'dj mapa', 'sha sha'],
            'Tanzania' => ['diamond platnumz', 'harmonize', 'ali kiba', 'rayvanny', 'zuchu'],
            'Kenya' => ['sauti sol', 'nyashinski', 'otile brown', 'bien', 'nameless'],
            'DR Congo' => ['fally ipupa', 'ferre gola', 'koffi olomide', 'fabregas'],
            'Senegal' => ['youssou ndour', 'akeem', 'diego', 'viviane'],
            'Mali' => ['salif keita', 'oumou sangare', 'amadou et mariam'],
            'Cameroon' => ['locko', 'blanche bailly', 'magasco', 'tenor'],
            'Uganda' => ['edy kenzo', 'bebe cool', 'sheebah', 'lil pazo']
        ];
        
        foreach ($countryMap as $country => $artists) {
            foreach ($artists as $knownArtist) {
                if (strpos($artist, $knownArtist) !== false) {
                    return $country;
                }
            }
        }
        
        return 'Unknown';
    }
    
    private function guessGenre($title, $artist) {
        $text = strtolower($title . ' ' . $artist);
        
        $genreMap = [
            'Afrobeats' => ['afrobeats', 'afrobeat', 'afro beats', 'wizkid', 'burna', 'davido'],
            'Bongo Flava' => ['bongo', 'flava', 'diamond', 'harmonize', 'ali kiba'],
            'Amapiano' => ['amapiano', 'piano', 'kabza', 'dj mapa', 'master kg'],
            'Gospel' => ['gospel', 'worship', 'praise', 'hallelujah', 'christ'],
            'Rhumba' => ['rhumba', 'rumba', 'fally', 'fera', 'koffi', 'lingala'],
            'Soukous' => ['soukous', 'ndombolo'],
            'Mbalax' => ['mbalax', 'youssou'],
            'Highlife' => ['highlife'],
            'Hip Hop' => ['hip hop', 'rap', 'rapper'],
            'Traditional' => ['traditional', 'folk', 'cultural']
        ];
        
        foreach ($genreMap as $genre => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($text, $keyword) !== false) {
                    return $genre;
                }
            }
        }
        
        return 'Afrobeats';
    }
    
    private function isAfricanContent($title, $artist) {
        $text = strtolower($title . ' ' . $artist);
        
        $africanKeywords = [
            'africa', 'african', 'afrobeats', 'amapiano', 'bongo', 'ndombolo',
            'soukous', 'mbalax', 'highlife', 'kwaito', 'afrobeat', 'naija',
            'ghana', 'nigeria', 'kenya', 'tanzania', 'south africa', 'senegal',
            'drc', 'congo', 'uganda', 'rwanda', 'cameroon'
        ];
        
        foreach ($africanKeywords as $keyword) {
            if (strpos($text, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message\n";
        
        // Write to log file
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
        
        // Output to console
        echo $logMessage;
    }
}

// Run the updater
$updater = new AutoUpdater($pdo);
$updater->run();
?>
EOF

echo "✅ Created api/auto_update.php"