cat > upload.php << 'EOF'
<?php
require_once 'config.php';
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Song - YOU LYRICS</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .upload-container {
            max-width: 600px;
            margin: 100px auto 50px;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #8B4513;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #FF6B00;
            outline: none;
        }
        .submit-btn {
            background: linear-gradient(135deg, #FF6B00, #8B4513);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 30px;
            font-size: 18px;
            cursor: pointer;
            width: 100%;
            transition: transform 0.3s;
        }
        .submit-btn:hover {
            transform: translateY(-2px);
        }
        .upload-status {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            display: none;
        }
        .upload-status.success {
            background: #d4edda;
            color: #155724;
            display: block;
        }
        .upload-status.error {
            background: #f8d7da;
            color: #721c24;
            display: block;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="upload-container">
        <h2 style="text-align: center; margin-bottom: 30px; color: #8B4513;">Upload Your Song Lyrics</h2>
        
        <form id="uploadForm" enctype="multipart/form-data">
            <div class="form-group">
                <label>Song Title *</label>
                <input type="text" id="title" name="title" required>
            </div>
            
            <div class="form-group">
                <label>Artist Name *</label>
                <input type="text" id="artist" name="artist" required>
            </div>
            
            <div class="form-group">
                <label>Genre *</label>
                <select id="genre" name="genre" required>
                    <option value="">Select Genre</option>
                    <?php
                    $genres = unserialize(MUSIC_GENRES);
                    foreach ($genres as $genre) {
                        echo "<option value=\"$genre\">$genre</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Country *</label>
                <select id="country" name="country" required>
                    <option value="">Select Country</option>
                    <?php
                    $countries = unserialize(AFRICAN_COUNTRIES);
                    foreach ($countries as $country) {
                        echo "<option value=\"$country\">$country</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Original Language</label>
                <select id="language" name="language">
                    <?php
                    $languages = unserialize(SUPPORTED_LANGUAGES);
                    foreach ($languages as $code => $name) {
                        echo "<option value=\"$code\">$name</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Original Lyrics *</label>
                <textarea id="originalLyrics" name="originalLyrics" rows="8" required placeholder="Enter the original lyrics here..."></textarea>
            </div>
            
            <div class="form-group">
                <label>Translated Lyrics (Optional)</label>
                <textarea id="translatedLyrics" name="translatedLyrics" rows="8" placeholder="Enter English translation here..."></textarea>
            </div>
            
            <div class="form-group">
                <label>Video File (Optional)</label>
                <input type="file" id="video" name="video" accept="video/*">
                <small>MP4, WebM, Ogg (Max 100MB)</small>
            </div>
            
            <div class="form-group">
                <label>Thumbnail Image (Optional)</label>
                <input type="file" id="thumbnail" name="thumbnail" accept="image/*">
                <small>JPG, PNG, GIF (Max 5MB)</small>
            </div>
            
            <button type="submit" class="submit-btn" id="submitBtn">
                <i class="fas fa-upload"></i> Upload Song
            </button>
        </form>
        
        <div id="uploadStatus" class="upload-status"></div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
    document.getElementById('uploadForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData();
        formData.append('title', document.getElementById('title').value);
        formData.append('artist', document.getElementById('artist').value);
        formData.append('genre', document.getElementById('genre').value);
        formData.append('country', document.getElementById('country').value);
        formData.append('language', document.getElementById('language').value);
        formData.append('originalLyrics', document.getElementById('originalLyrics').value);
        
        const translatedLyrics = document.getElementById('translatedLyrics').value;
        if (translatedLyrics) {
            formData.append('translatedLyrics', translatedLyrics);
        }
        
        const videoFile = document.getElementById('video').files[0];
        if (videoFile) {
            formData.append('video', videoFile);
        }
        
        const thumbFile = document.getElementById('thumbnail').files[0];
        if (thumbFile) {
            formData.append('thumbnail', thumbFile);
        }
        
        const submitBtn = document.getElementById('submitBtn');
        const statusDiv = document.getElementById('uploadStatus');
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
        statusDiv.className = 'upload-status';
        statusDiv.textContent = 'Uploading...';
        
        try {
            const response = await fetch('api/upload.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                statusDiv.className = 'upload-status success';
                statusDiv.textContent = '✅ Song uploaded successfully! Redirecting...';
                document.getElementById('uploadForm').reset();
                
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 2000);
            } else {
                throw new Error(result.error || 'Upload failed');
            }
        } catch (error) {
            statusDiv.className = 'upload-status error';
            statusDiv.textContent = '❌ Error: ' + error.message;
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-upload"></i> Upload Song';
        }
    });
    </script>
</body>
</html>
EOF

echo "✅ Created upload.php"