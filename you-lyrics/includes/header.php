cat > includes/header.php << 'EOF'
<header class="header">
    <nav class="nav-container">
        <div class="logo">
            <a href="<?php echo SITE_URL; ?>/index.php" style="text-decoration: none;">
                <h1>YOU LYRICS</h1>
                <span class="logo-tagline">African Songs, Global Voice</span>
            </a>
        </div>
        <div class="nav-links">
            <a href="<?php echo SITE_URL; ?>/index.php">Home</a>
            <a href="<?php echo SITE_URL; ?>/index.php?genre=Afrobeats">Afrobeats</a>
            <a href="<?php echo SITE_URL; ?>/index.php?genre=Bongo Flava">Bongo Flava</a>
            <a href="<?php echo SITE_URL; ?>/index.php?genre=Amapiano">Amapiano</a>
            <a href="<?php echo SITE_URL; ?>/upload.php" class="upload-btn">Upload</a>
        </div>
    </nav>
</header>
EOF

echo "✅ Created includes/header.php"