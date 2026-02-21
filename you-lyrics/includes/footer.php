cat > includes/footer.php << 'EOF'
<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h4>YOU LYRICS</h4>
                <p>Discover African music with bilingual lyrics. Updated automatically with the latest songs from across Africa.</p>
            </div>
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="<?php echo SITE_URL; ?>/index.php">Home</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/upload.php">Upload Song</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/index.php?genre=Afrobeats">Afrobeats</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/index.php?genre=Gospel">Gospel</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Countries</h4>
                <ul>
                    <li><a href="<?php echo SITE_URL; ?>/index.php?country=Nigeria">Nigeria</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/index.php?country=South Africa">South Africa</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/index.php?country=Ghana">Ghana</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/index.php?country=Kenya">Kenya</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/index.php?country=Tanzania">Tanzania</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Follow Us</h4>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> YOU LYRICS. All rights reserved. Auto-updated every hour.</p>
        </div>
    </div>
</footer>
EOF

echo "✅ Created includes/footer.php"