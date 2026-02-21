cat > js/main.js << 'EOF'
// Main JavaScript for YOU LYRICS
const SITE_URL = window.location.origin + '/you-lyrics';

// Open song modal
window.openSong = async function(songId) {
    try {
        const response = await fetch(`${SITE_URL}/api/get_lyrics.php?song_id=${songId}`);
        const data = await response.json();
        
        if (data.success) {
            displaySongModal(data);
        } else {
            showNotification('Error loading song', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Failed to load song', 'error');
    }
};

// Display song in modal
function displaySongModal(data) {
    const modal = document.getElementById('songModal');
    const content = document.getElementById('modalContent');
    const song = data.song;
    const translations = data.translations || [];
    
    // Create language tabs
    let tabsHTML = '<div class="lyrics-tabs">';
    tabsHTML += '<button class="tab-btn active" data-lang="original">Original</button>';
    
    translations.forEach(trans => {
        tabsHTML += `<button class="tab-btn" data-lang="${trans.language_code}">${trans.language_name}</button>`;
    });
    tabsHTML += '</div>';
    
    // Create lyrics content
    let lyricsHTML = '<div class="lyrics-content active" data-lang="original">';
    lyricsHTML += `<pre>${song.original_lyrics || 'No lyrics available'}</pre>`;
    lyricsHTML += '</div>';
    
    translations.forEach(trans => {
        lyricsHTML += `<div class="lyrics-content" data-lang="${trans.language_code}">`;
        lyricsHTML += `<pre>${trans.lyrics_content}</pre>`;
        lyricsHTML += '</div>';
    });
    
    // Build modal content
    content.innerHTML = `
        <div class="player-container">
            <h2>${song.title} - ${song.artist_name}</h2>
            <p>${song.genre} • ${song.country} • 👁️ ${formatNumber(song.views)} views</p>
            ${song.video_file ? `
                <video controls style="width: 100%; max-height: 400px; border-radius: 10px; margin: 20px 0;">
                    <source src="${SITE_URL}/uploads/videos/${song.video_file}" type="video/mp4">
                </video>
            ` : ''}
        </div>
        ${tabsHTML}
        ${lyricsHTML}
    `;
    
    // Setup tab switching
    content.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            content.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            content.querySelectorAll('.lyrics-content').forEach(c => c.classList.remove('active'));
            
            this.classList.add('active');
            const lang = this.dataset.lang;
            const targetContent = content.querySelector(`.lyrics-content[data-lang="${lang}"]`);
            if (targetContent) {
                targetContent.classList.add('active');
            }
        });
    });
    
    modal.style.display = 'block';
}

// Filter by artist
window.filterByArtist = function(artistName) {
    window.location.href = `index.php?search=${encodeURIComponent(artistName)}`;
}

// Format numbers
function formatNumber(num) {
    if (num >= 1000000) return (num/1000000).toFixed(1) + 'M';
    if (num >= 1000) return (num/1000).toFixed(1) + 'K';
    return num;
}

// Show notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Close modal
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('songModal');
    const closeBtn = document.querySelector('.close');
    
    if (closeBtn) {
        closeBtn.onclick = function() {
            modal.style.display = 'none';
        };
    }
    
    window.onclick = function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    };
    
    // Handle URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const songId = urlParams.get('song');
    if (songId) {
        openSong(songId);
    }
});
EOF

echo "✅ Created js/main.js"