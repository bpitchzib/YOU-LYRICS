cat > js/player.js << 'EOF'
// Video player with synchronized lyrics
class LyricsPlayer {
    constructor() {
        this.video = null;
        this.lyrics = [];
        this.currentLine = -1;
        this.container = null;
    }
    
    init(videoElement, lyricsData, containerId = 'currentLyrics') {
        this.video = videoElement;
        this.lyrics = lyricsData;
        this.container = document.getElementById(containerId);
        
        if (this.video && this.lyrics.length > 0) {
            this.video.addEventListener('timeupdate', () => this.update());
            this.createLyricsOverlay();
        }
    }
    
    createLyricsOverlay() {
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.id = 'currentLyrics';
            this.container.className = 'lyrics-overlay';
            this.video.parentNode.appendChild(this.container);
        }
    }
    
    update() {
        if (!this.video || !this.lyrics.length) return;
        
        const currentTime = this.video.currentTime;
        
        // Find current lyrics line
        for (let i = 0; i < this.lyrics.length; i++) {
            const line = this.lyrics[i];
            if (currentTime >= line.start && currentTime <= line.end) {
                if (this.currentLine !== i) {
                    this.currentLine = i;
                    this.displayCurrentLine(line);
                }
                return;
            }
        }
        
        // No matching line
        if (this.currentLine !== -1) {
            this.currentLine = -1;
            this.clearDisplay();
        }
    }
    
    displayCurrentLine(line) {
        if (this.container) {
            this.container.innerHTML = `
                <div class="current-lyric" style="
                    position: absolute;
                    bottom: 50px;
                    left: 50%;
                    transform: translateX(-50%);
                    background: rgba(0,0,0,0.8);
                    color: white;
                    padding: 15px 30px;
                    border-radius: 50px;
                    text-align: center;
                    font-size: 18px;
                    max-width: 80%;
                    z-index: 1000;
                ">
                    <div class="original" style="color: #FF6B00; font-weight: bold;">${line.original}</div>
                    ${line.translation ? `<div class="translation" style="font-size: 14px; margin-top: 5px;">${line.translation}</div>` : ''}
                </div>
            `;
        }
    }
    
    clearDisplay() {
        if (this.container) {
            this.container.innerHTML = '';
        }
    }
    
    loadLyrics(lyricsData) {
        this.lyrics = lyricsData;
        this.currentLine = -1;
    }
}

// Initialize player
const player = new LyricsPlayer();

// Auto-initialize when video is present
document.addEventListener('DOMContentLoaded', function() {
    const video = document.querySelector('video');
    if (video) {
        // Load sample lyrics if available
        const sampleLyrics = [
            { start: 0, end: 5, original: "Sample lyrics line 1", translation: "Translation line 1" },
            { start: 5, end: 10, original: "Sample lyrics line 2", translation: "Translation line 2" }
        ];
        player.init(video, sampleLyrics);
    }
});
EOF

echo "✅ Created js/player.js"