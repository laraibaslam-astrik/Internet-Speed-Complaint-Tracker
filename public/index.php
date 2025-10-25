<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Test your internet speed in Pakistan. Track ISP performance, view city-level heatmaps, and compare speeds across providers.">
    <meta name="keywords" content="Pakistan, Internet Speed, Speed Test, ISP, PTCL, Nayatel, StormFiber">
    
    <!-- Open Graph -->
    <meta property="og:title" content="Pakistan Internet Speed Tracker">
    <meta property="og:description" content="Test and compare internet speeds across Pakistan">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://your-domain.com">
    <meta property="og:image" content="https://your-domain.com/og-image.png">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Pakistan Internet Speed Tracker">
    <meta name="twitter:description" content="Test and compare internet speeds across Pakistan">
    <meta name="twitter:image" content="https://your-domain.com/og-image.png">
    
    <title>Pakistan Internet Speed Tracker</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary-color: #0d6efd;
            --success-color: #198754;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #0dcaf0;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        
        @media (prefers-color-scheme: dark) {
            :root {
                color-scheme: dark;
            }
        }
        
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
        
        .display-4 {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1.2;
        }
        
        @media (max-width: 768px) {
            .display-4 {
                font-size: 2rem;
            }
        }
        
        .card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
        }
        
        canvas {
            image-rendering: auto;
            image-rendering: crisp-edges;
            image-rendering: pixelated;
        }
        
        .btn {
            transition: all 0.2s ease;
        }
        
        .btn:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-size: 1.25rem;
        }
        
        @media (max-width: 576px) {
            .navbar-brand {
                font-size: 1rem;
            }
        }
        
        /* Urdu font support */
        [lang="ur"], .urdu-text {
            font-family: 'Noto Nastaliq Urdu', 'Jameel Noori Nastaleeq', 'Urdu Typesetting', Arial, sans-serif;
            direction: rtl;
        }
        
        .toast {
            min-width: 250px;
        }
    </style>
</head>
<body>
    <?php include 'components/header.php'; ?>
    <?php include 'components/metrics.php'; ?>
    <?php include 'components/map.php'; ?>
    <?php include 'components/leaderboard.php'; ?>
    <?php include 'components/share.php'; ?>
    <?php include 'components/footer.php'; ?>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // ============ GLOBAL STATE ============
        let currentLang = 'en';
        let testInProgress = false;
        let testAborted = false;
        let rateLimitUntil = null;
        let detectedInfo = { isp_name: 'Unknown', city: 'Unknown', asn: null };
        let lastTestResults = null;
        let shareUrl = null;
        
        // Sparkline data
        const sparklineData = {
            download: [],
            upload: [],
            ping: [],
            jitter: []
        };
        
        // ============ LANGUAGE TOGGLE ============
        function setLanguage(lang) {
            currentLang = lang;
            document.querySelectorAll('[data-en]').forEach(el => {
                if (lang === 'en') {
                    el.textContent = el.dataset.en;
                } else {
                    el.textContent = el.dataset.ur || el.dataset.en;
                    el.classList.add('urdu-text');
                }
            });
            
            document.getElementById('btn-lang-en').classList.toggle('active', lang === 'en');
            document.getElementById('btn-lang-ur').classList.toggle('active', lang === 'ur');
            
            localStorage.setItem('lang', lang);
        }
        
        // ============ UTILITY FUNCTIONS ============
        function formatMbps(num) {
            return num > 0 ? num.toFixed(2) : '--';
        }
        
        function formatMs(num) {
            return num > 0 ? num.toFixed(1) : '--';
        }
        
        function hashToColor(str, speed = 0) {
            if (speed < 5) return '#dc3545'; // red
            if (speed < 20) return '#ffc107'; // yellow
            return '#198754'; // green
        }
        
        function showToast(message, type = 'info') {
            const toast = document.getElementById('toast-notification');
            const toastBody = document.getElementById('toast-message');
            
            toast.className = `toast align-items-center border-0 text-bg-${type}`;
            toastBody.textContent = message;
            
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
        }
        
        function updateMetric(metric, value) {
            const el = document.getElementById(`metric-${metric}`);
            if (el) {
                el.textContent = typeof value === 'number' ? 
                    (metric === 'download' || metric === 'upload' ? formatMbps(value) : formatMs(value)) : 
                    value;
            }
        }
        
        // ============ SPARKLINE RENDERING ============
        function drawSparkline(canvasId, data, color) {
            const canvas = document.getElementById(canvasId);
            if (!canvas) return;
            
            const ctx = canvas.getContext('2d');
            const width = canvas.width;
            const height = canvas.height;
            
            ctx.clearRect(0, 0, width, height);
            
            if (data.length < 2) return;
            
            const max = Math.max(...data, 1);
            const min = Math.min(...data);
            const range = max - min || 1;
            
            ctx.strokeStyle = color;
            ctx.lineWidth = 2;
            ctx.beginPath();
            
            data.forEach((value, index) => {
                const x = (index / (data.length - 1)) * width;
                const y = height - ((value - min) / range) * height;
                
                if (index === 0) {
                    ctx.moveTo(x, y);
                } else {
                    ctx.lineTo(x, y);
                }
            });
            
            ctx.stroke();
        }
        
        function updateSparkline(metric, value) {
            sparklineData[metric].push(value);
            if (sparklineData[metric].length > 20) {
                sparklineData[metric].shift();
            }
            
            const colors = {
                download: '#0d6efd',
                upload: '#198754',
                ping: '#0dcaf0',
                jitter: '#ffc107'
            };
            
            drawSparkline(`spark-${metric}`, sparklineData[metric], colors[metric]);
        }
        
        // ============ PAKISTAN MAP ============
        function initMap() {
            const canvas = document.getElementById('pakistan-map');
            const ctx = canvas.getContext('2d');
            
            // Set canvas size
            canvas.width = canvas.offsetWidth * 2; // Retina support
            canvas.height = canvas.offsetHeight * 2;
            ctx.scale(2, 2);
            
            // Draw basic Pakistan outline (simplified)
            ctx.fillStyle = '#e9ecef';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            
            // Major city coordinates (approximate, normalized to canvas)
            const cities = {
                'Karachi': { x: 0.35, y: 0.85 },
                'Lahore': { x: 0.55, y: 0.35 },
                'Islamabad': { x: 0.58, y: 0.25 },
                'Rawalpindi': { x: 0.57, y: 0.26 },
                'Faisalabad': { x: 0.52, y: 0.42 },
                'Multan': { x: 0.48, y: 0.52 },
                'Hyderabad': { x: 0.38, y: 0.78 },
                'Quetta': { x: 0.22, y: 0.52 },
                'Peshawar': { x: 0.52, y: 0.18 },
                'Pakistan': { x: 0.45, y: 0.50 }
            };
            
            return { canvas, ctx, cities };
        }
        
        function updateMap(heatmapData) {
            const { ctx, cities } = initMap();
            const canvasWidth = document.getElementById('pakistan-map').offsetWidth;
            const canvasHeight = document.getElementById('pakistan-map').offsetHeight;
            
            heatmapData.forEach(item => {
                const coords = cities[item.city];
                if (!coords) return;
                
                const x = coords.x * canvasWidth;
                const y = coords.y * canvasHeight;
                const radius = Math.sqrt(item.tests) * 3 + 5;
                const color = hashToColor(item.city, item.avg_dl);
                
                // Draw bubble
                ctx.fillStyle = color + 'aa'; // with alpha
                ctx.beginPath();
                ctx.arc(x, y, radius, 0, Math.PI * 2);
                ctx.fill();
                
                ctx.strokeStyle = '#fff';
                ctx.lineWidth = 2;
                ctx.stroke();
                
                // Draw city label
                ctx.fillStyle = '#000';
                ctx.font = '10px sans-serif';
                ctx.textAlign = 'center';
                ctx.fillText(item.city, x, y - radius - 5);
                ctx.fillText(item.avg_dl.toFixed(1) + ' Mbps', x, y - radius - 15);
            });
        }
        
        // ============ RATE LIMIT ============
        function checkRateLimit() {
            const stored = localStorage.getItem('rateLimitUntil');
            if (stored) {
                rateLimitUntil = parseInt(stored);
                if (rateLimitUntil > Date.now()) {
                    startCountdown();
                    return false;
                } else {
                    localStorage.removeItem('rateLimitUntil');
                    rateLimitUntil = null;
                }
            }
            return true;
        }
        
        function setRateLimit(seconds) {
            rateLimitUntil = Date.now() + (seconds * 1000);
            localStorage.setItem('rateLimitUntil', rateLimitUntil);
            startCountdown();
        }
        
        function startCountdown() {
            const btn = document.getElementById('btn-start-test');
            const notice = document.getElementById('rate-limit-notice');
            const timer = document.getElementById('countdown-timer');
            
            btn.disabled = true;
            notice.classList.remove('d-none');
            
            const interval = setInterval(() => {
                const remaining = Math.max(0, Math.floor((rateLimitUntil - Date.now()) / 1000));
                const minutes = Math.floor(remaining / 60);
                const seconds = remaining % 60;
                
                timer.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                
                if (remaining <= 0) {
                    clearInterval(interval);
                    btn.disabled = false;
                    notice.classList.add('d-none');
                    rateLimitUntil = null;
                    localStorage.removeItem('rateLimitUntil');
                }
            }, 1000);
        }
        
        // ============ DETECT WHO AM I ============
        async function detectWhoAmI() {
            try {
                const response = await fetch('/api/whoami.php');
                const data = await response.json();
                
                detectedInfo = data;
                
                document.getElementById('info-isp').textContent = data.isp_name;
                document.getElementById('info-city').textContent = data.city;
                
                // Detect tech from navigator.connection
                let tech = 'Unknown';
                if (navigator.connection) {
                    const effectiveType = navigator.connection.effectiveType;
                    tech = effectiveType === '4g' ? '4G/LTE' : 
                           effectiveType === '3g' ? '3G' : 
                           effectiveType === 'slow-2g' || effectiveType === '2g' ? '2G' : 
                           'Wi-Fi/Broadband';
                }
                
                document.getElementById('info-tech').textContent = tech;
                
                // Check for outages
                checkForOutages(data.city);
                
                return data;
            } catch (error) {
                console.error('Failed to detect ISP info:', error);
                return { isp_name: 'Unknown', city: 'Unknown', asn: null };
            }
        }
        
        async function checkForOutages(city) {
            try {
                const response = await fetch(`/api/leaderboard.php?date=today&city=${encodeURIComponent(city)}`);
                const data = await response.json();
                
                if (data.spike) {
                    const banner = document.getElementById('outage-banner');
                    const message = document.getElementById('outage-message');
                    message.textContent = currentLang === 'en' ? 
                        `Network issues detected in ${city}. Test results may be affected.` :
                        `${city} میں نیٹ ورک کے مسائل کا پتہ چلا۔`;
                    banner.classList.remove('d-none');
                }
            } catch (error) {
                console.error('Failed to check outages:', error);
            }
        }
        
        // ============ SPEED TEST ============
        async function startSpeedTest() {
            if (testInProgress) return;
            
            if (!checkRateLimit()) {
                showToast(currentLang === 'en' ? 
                    'Please wait before testing again' : 
                    'برائے مہربانی دوبارہ ٹیسٹ سے پہلے انتظار کریں', 'warning');
                return;
            }
            
            testInProgress = true;
            testAborted = false;
            
            // Reset metrics
            ['download', 'upload', 'ping', 'jitter'].forEach(m => {
                updateMetric(m, '--');
                sparklineData[m] = [];
            });
            
            // Show progress
            document.getElementById('test-progress').classList.remove('d-none');
            document.getElementById('btn-start-test').disabled = true;
            
            // Detect ISP first
            await detectWhoAmI();
            
            try {
                // Step 1: Ping Test
                await runPingTest();
                if (testAborted) return;
                
                // Step 2: Download Test
                await runDownloadTest();
                if (testAborted) return;
                
                // Step 3: Upload Test
                await runUploadTest();
                if (testAborted) return;
                
                // Submit results
                await submitResults();
                
                showToast(currentLang === 'en' ? 
                    'Test completed successfully!' : 
                    'ٹیسٹ کامیابی سے مکمل ہوا!', 'success');
                
                // Set rate limit
                setRateLimit(600); // 10 minutes
                
            } catch (error) {
                console.error('Test failed:', error);
                showToast(currentLang === 'en' ? 
                    'Test failed. Please try again.' : 
                    'ٹیسٹ ناکام ہو گیا۔ دوبارہ کوشش کریں۔', 'danger');
            } finally {
                testInProgress = false;
                document.getElementById('test-progress').classList.add('d-none');
                if (!testAborted) {
                    document.getElementById('btn-start-test').disabled = false;
                }
            }
        }
        
        function cancelTest() {
            testAborted = true;
            testInProgress = false;
            document.getElementById('test-progress').classList.add('d-none');
            document.getElementById('btn-start-test').disabled = false;
            showToast(currentLang === 'en' ? 'Test cancelled' : 'ٹیسٹ منسوخ کر دیا گیا', 'info');
        }
        
        async function runPingTest() {
            updateProgress(10, currentLang === 'en' ? 'Testing ping...' : 'پنگ کی جانچ...');
            
            const pings = [];
            const iterations = 10;
            
            for (let i = 0; i < iterations; i++) {
                if (testAborted) return;
                
                const start = performance.now();
                await fetch('/api/ping.php?t=' + Date.now());
                const end = performance.now();
                
                const pingMs = end - start;
                pings.push(pingMs);
                
                const avgPing = pings.reduce((a, b) => a + b, 0) / pings.length;
                updateMetric('ping', avgPing);
                updateSparkline('ping', avgPing);
                
                await new Promise(r => setTimeout(r, 150));
            }
            
            // Calculate jitter (standard deviation)
            const avg = pings.reduce((a, b) => a + b, 0) / pings.length;
            const variance = pings.reduce((a, b) => a + Math.pow(b - avg, 2), 0) / pings.length;
            const jitter = Math.sqrt(variance);
            
            updateMetric('jitter', jitter);
            updateSparkline('jitter', jitter);
            
            lastTestResults = { ...lastTestResults, ping_ms: avg, jitter_ms: jitter };
        }
        
        async function runDownloadTest() {
            updateProgress(30, currentLang === 'en' ? 'Testing download speed...' : 'ڈاؤن لوڈ کی رفتار کی جانچ...');
            
            const startTime = performance.now();
            const duration = 12000; // 12 seconds
            const connections = 4;
            let totalBytes = 0;
            
            const downloadPromises = [];
            
            for (let i = 0; i < connections; i++) {
                downloadPromises.push((async () => {
                    while (performance.now() - startTime < duration && !testAborted) {
                        const response = await fetch('/speed/down.php?b=2&r=' + Math.random());
                        const reader = response.body.getReader();
                        
                        while (true) {
                            const { done, value } = await reader.read();
                            if (done || testAborted) break;
                            
                            totalBytes += value.length;
                            
                            const elapsed = (performance.now() - startTime) / 1000;
                            const mbps = (totalBytes * 8) / (elapsed * 1000000);
                            
                            updateMetric('download', mbps);
                            updateSparkline('download', mbps);
                            updateProgress(30 + (elapsed / duration) * 30);
                        }
                    }
                })());
            }
            
            await Promise.all(downloadPromises);
            
            const totalTime = (performance.now() - startTime) / 1000;
            const finalMbps = (totalBytes * 8) / (totalTime * 1000000);
            
            lastTestResults = { ...lastTestResults, dl_mbps: finalMbps };
        }
        
        async function runUploadTest() {
            updateProgress(60, currentLang === 'en' ? 'Testing upload speed...' : 'اپ لوڈ کی رفتار کی جانچ...');
            
            const startTime = performance.now();
            const duration = 10000; // 10 seconds
            const chunkSize = 512 * 1024; // 512 KB
            let totalBytes = 0;
            
            while (performance.now() - startTime < duration && !testAborted) {
                const chunk = new ArrayBuffer(chunkSize);
                const view = new Uint8Array(chunk);
                for (let i = 0; i < chunkSize; i++) {
                    view[i] = Math.floor(Math.random() * 256);
                }
                
                const uploadStart = performance.now();
                await fetch('/speed/up.php', {
                    method: 'POST',
                    body: chunk
                });
                
                totalBytes += chunkSize;
                
                const elapsed = (performance.now() - startTime) / 1000;
                const mbps = (totalBytes * 8) / (elapsed * 1000000);
                
                updateMetric('upload', mbps);
                updateSparkline('upload', mbps);
                updateProgress(60 + (elapsed / duration) * 30);
            }
            
            const totalTime = (performance.now() - startTime) / 1000;
            const finalMbps = (totalBytes * 8) / (totalTime * 1000000);
            
            lastTestResults = { ...lastTestResults, ul_mbps: finalMbps };
        }
        
        function updateProgress(percent, stage) {
            const bar = document.getElementById('progress-bar');
            const stageEl = document.getElementById('test-stage');
            
            bar.style.width = percent + '%';
            bar.setAttribute('aria-valuenow', percent);
            stageEl.textContent = stage;
        }
        
        async function submitResults() {
            updateProgress(95, currentLang === 'en' ? 'Submitting results...' : 'نتائج جمع کروا رہے ہیں...');
            
            const tech = document.getElementById('info-tech').textContent;
            const deviceType = /Mobile|Android|iPhone/i.test(navigator.userAgent) ? 'mobile' : 'desktop';
            
            const payload = {
                dl_mbps: lastTestResults.dl_mbps,
                ul_mbps: lastTestResults.ul_mbps,
                ping_ms: lastTestResults.ping_ms,
                jitter_ms: lastTestResults.jitter_ms,
                sample_ms: 30000,
                isp_name: detectedInfo.isp_name,
                asn: detectedInfo.asn,
                city: detectedInfo.city,
                tech: tech,
                device_type: deviceType
            };
            
            const response = await fetch('/api/submit.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            
            const data = await response.json();
            
            if (response.status === 429) {
                setRateLimit(data.retry_after || 600);
                throw new Error('Rate limited');
            }
            
            if (!response.ok) {
                throw new Error(data.error || 'Submission failed');
            }
            
            shareUrl = window.location.origin + data.share_url;
            enableShareButtons();
            
            updateProgress(100, currentLang === 'en' ? 'Complete!' : 'مکمل!');
            
            // Refresh leaderboard and map
            loadLeaderboard();
            loadHeatmap();
        }
        
        // ============ SHARE FUNCTIONS ============
        function enableShareButtons() {
            ['btn-copy-link', 'btn-share-twitter', 'btn-share-whatsapp', 'btn-share-facebook'].forEach(id => {
                document.getElementById(id).disabled = false;
            });
        }
        
        function getShareText() {
            const { dl_mbps, ul_mbps, ping_ms } = lastTestResults;
            return currentLang === 'en' ?
                `My internet speed today in ${detectedInfo.city} on ${detectedInfo.isp_name}: ${dl_mbps.toFixed(1)} Mbps / ${ul_mbps.toFixed(1)} Mbps, ping ${ping_ms.toFixed(0)} ms — via Pakistan Internet Speed Tracker` :
                `${detectedInfo.city} میں ${detectedInfo.isp_name} پر آج میری انٹرنیٹ کی رفتار: ${dl_mbps.toFixed(1)} Mbps / ${ul_mbps.toFixed(1)} Mbps, پنگ ${ping_ms.toFixed(0)} ms`;
        }
        
        function copyResultLink() {
            if (!shareUrl) return;
            navigator.clipboard.writeText(shareUrl);
            showToast(currentLang === 'en' ? 'Link copied!' : 'لنک کاپی ہو گیا!', 'success');
        }
        
        function shareToTwitter() {
            if (!shareUrl) return;
            const text = encodeURIComponent(getShareText());
            const url = encodeURIComponent(shareUrl);
            window.open(`https://twitter.com/intent/tweet?text=${text}&url=${url}`, '_blank');
        }
        
        function shareToWhatsApp() {
            if (!shareUrl) return;
            const text = encodeURIComponent(getShareText() + ' ' + shareUrl);
            window.open(`https://wa.me/?text=${text}`, '_blank');
        }
        
        function shareToFacebook() {
            if (!shareUrl) return;
            const url = encodeURIComponent(shareUrl);
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank');
        }
        
        // ============ DATA LOADING ============
        async function loadLeaderboard() {
            try {
                const response = await fetch('/api/leaderboard.php?date=today');
                const data = await response.json();
                
                const tbody = document.getElementById('leaderboard-body');
                tbody.innerHTML = '';
                
                if (data.leaderboard.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted py-4">
                        ${currentLang === 'en' ? 'No data available for today' : 'آج کے لیے کوئی ڈیٹا دستیاب نہیں'}
                    </td></tr>`;
                    return;
                }
                
                data.leaderboard.forEach((row, index) => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="ps-3">${index + 1}</td>
                        <td><strong>${row.isp_name}</strong><br><small class="text-muted">${row.city}</small></td>
                        <td class="text-end">${row.avg_dl} Mbps</td>
                        <td class="text-end">${row.avg_ul} Mbps</td>
                        <td class="text-end">${row.avg_ping} ms</td>
                        <td class="text-end pe-3">${row.tests_count}</td>
                    `;
                    tbody.appendChild(tr);
                });
            } catch (error) {
                console.error('Failed to load leaderboard:', error);
            }
        }
        
        async function loadHeatmap() {
            try {
                const response = await fetch('/api/heatmap.php?date=today');
                const data = await response.json();
                
                if (data.cities.length > 0) {
                    updateMap(data.cities);
                }
            } catch (error) {
                console.error('Failed to load heatmap:', error);
            }
        }
        
        // ============ INITIALIZATION ============
        document.addEventListener('DOMContentLoaded', () => {
            // Restore language preference
            const savedLang = localStorage.getItem('lang') || 'en';
            setLanguage(savedLang);
            
            // Check rate limit
            checkRateLimit();
            
            // Detect ISP info
            detectWhoAmI();
            
            // Load initial data
            loadLeaderboard();
            loadHeatmap();
            
            // Initialize map
            initMap();
            
            // Handle window resize for map
            window.addEventListener('resize', () => {
                initMap();
                loadHeatmap();
            });
        });
    </script>
</body>
</html>
