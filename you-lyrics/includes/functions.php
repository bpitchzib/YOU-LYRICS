cat > includes/functions.php << 'EOF'
<?php
// Helper functions for YOU LYRICS

/**
 * Format view counts (1000 -> 1K, 1000000 -> 1M)
 */
function formatViews($views) {
    if ($views >= 1000000) {
        return round($views / 1000000, 1) . 'M';
    }
    if ($views >= 1000) {
        return round($views / 1000, 1) . 'K';
    }
    return $views;
}

/**
 * Get time ago string (e.g., "2 hours ago")
 */
function getTimeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 2592000) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $time);
    }
}

/**
 * Get country flag emoji
 */
function getCountryFlag($country) {
    $flags = [
        'Nigeria' => '🇳🇬',
        'Ghana' => '🇬🇭',
        'South Africa' => '🇿🇦',
        'Kenya' => '🇰🇪',
        'Tanzania' => '🇹🇿',
        'DR Congo' => '🇨🇩',
        'Uganda' => '🇺🇬',
        'Morocco' => '🇲🇦',
        'Egypt' => '🇪🇬',
        'Algeria' => '🇩🇿',
        'Ethiopia' => '🇪🇹',
        'Angola' => '🇦🇴',
        'Mozambique' => '🇲🇿',
        'Cameroon' => '🇨🇲',
        'Ivory Coast' => '🇨🇮',
        'Senegal' => '🇸🇳',
        'Mali' => '🇲🇱',
        'Zimbabwe' => '🇿🇼',
        'Zambia' => '🇿🇲',
        'Rwanda' => '🇷🇼'
    ];
    
    return $flags[$country] ?? '🌍';
}

/**
 * Truncate text to specified length
 */
function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

/**
 * Get songs by genre
 */
function getSongsByGenre($pdo, $genre, $limit = 20) {
    $sql = "SELECT * FROM songs WHERE genre = ? ORDER BY views DESC, date_added DESC LIMIT ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$genre, $limit]);
    return $stmt->fetchAll();
}

/**
 * Get songs by country
 */
function getSongsByCountry($pdo, $country, $limit = 20) {
    $sql = "SELECT * FROM songs WHERE country = ? ORDER BY views DESC, date_added DESC LIMIT ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$country, $limit]);
    return $stmt->fetchAll();
}

/**
 * Get trending songs
 */
function getTrendingSongs($pdo, $limit = 20) {
    $sql = "SELECT * FROM songs ORDER BY views DESC, last_played DESC LIMIT ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

/**
 * Get recent songs
 */
function getRecentSongs($pdo, $limit = 20) {
    $sql = "SELECT * FROM songs ORDER BY date_added DESC LIMIT ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}
?>
EOF

echo "✅ Created includes/functions.php"