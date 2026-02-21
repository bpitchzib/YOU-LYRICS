cat > admin/index.php << 'EOF'
<?php
require_once '../config.php';

// Simple admin check (you can expand this)
session_start();
$isAdmin = false; // Set to true after login

if (!$isAdmin) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - YOU LYRICS</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div style="padding: 50px; text-align: center;">
        <h1>Admin Dashboard</h1>
        <p>Coming Soon - Admin features will be available in the next update.</p>
        <p><a href="../index.php">← Back to Site</a></p>
    </div>
</body>
</html>
EOF

echo "✅ Created admin/index.php"