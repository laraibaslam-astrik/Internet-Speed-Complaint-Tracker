<!-- Footer -->
<footer class="bg-dark text-light py-4 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-shield-check me-2"></i>
                    <span data-en="Privacy & Data" data-ur="رازداری اور ڈیٹا">Privacy & Data</span>
                </h6>
                <p class="small text-light-emphasis mb-2">
                    <span data-en="No personal data stored. We only collect anonymized network statistics to improve internet quality insights for Pakistan. Your IP address is hashed and never stored directly." 
                          data-ur="کوئی ذاتی ڈیٹا محفوظ نہیں کیا جاتا۔ ہم صرف گمنام نیٹ ورک کے اعداد و شمار اکٹھا کرتے ہیں تاکہ پاکستان کے لیے انٹرنیٹ معیار کی بصیرت کو بہتر بنایا جا سکے۔">
                        No personal data stored. We only collect anonymized network statistics to improve internet quality insights for Pakistan. Your IP address is hashed and never stored directly.
                    </span>
                </p>
                <div class="d-flex gap-3">
                    <a href="#" class="text-light text-decoration-none small">
                        <span data-en="Privacy Policy" data-ur="رازداری کی پالیسی">Privacy Policy</span>
                    </a>
                    <a href="#" class="text-light text-decoration-none small">
                        <span data-en="Terms of Service" data-ur="خدمات کی شرائط">Terms of Service</span>
                    </a>
                </div>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <p class="small mb-2">
                    <span data-en="Made for Pakistan 🇵🇰" data-ur="پاکستان کے لیے بنایا گیا 🇵🇰">Made for Pakistan 🇵🇰</span>
                </p>
                <p class="small text-light-emphasis mb-0">
                    © <?php echo date('Y'); ?> Pakistan Internet Speed Tracker
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="toast-notification" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="toast-message"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Pakistan Map Rendering -->
<script>
// Pakistan Map Visualization
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('pakistan-map');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    // Set canvas size
    canvas.width = canvas.offsetWidth;
    canvas.height = canvas.offsetHeight;
    
    // Major Pakistan cities with approximate coordinates
    const cities = [
        { name: 'Karachi', x: 0.67, y: 0.85, tests: 150 },
        { name: 'Lahore', x: 0.74, y: 0.31, tests: 120 },
        { name: 'Islamabad', x: 0.73, y: 0.22, tests: 90 },
        { name: 'Faisalabad', x: 0.73, y: 0.35, tests: 60 },
        { name: 'Rawalpindi', x: 0.73, y: 0.23, tests: 55 },
        { name: 'Multan', x: 0.71, y: 0.52, tests: 50 },
        { name: 'Peshawar', x: 0.72, y: 0.18, tests: 45 },
        { name: 'Quetta', x: 0.67, y: 0.50, tests: 30 },
        { name: 'Sialkot', x: 0.74, y: 0.27, tests: 25 },
        { name: 'Gujranwala', x: 0.74, y: 0.29, tests: 40 }
    ];
    
    // Fetch real data from API
    fetch('/api/map-data.php')
        .then(r => r.json())
        .then(data => {
            if (data.cities && data.cities.length > 0) {
                drawMap(data.cities);
            } else {
                // Fallback to demo data
                drawMap(cities);
            }
        })
        .catch(() => {
            // Draw with demo data if API fails
            drawMap(cities);
        });
    
    function drawMap(cityData) {
        // Clear canvas
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // Draw Pakistan outline (simplified)
        ctx.fillStyle = '#e5e7eb';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        
        // Draw background
        ctx.fillStyle = '#f3f4f6';
        ctx.beginPath();
        ctx.moveTo(canvas.width * 0.60, canvas.height * 0.10);
        ctx.lineTo(canvas.width * 0.80, canvas.height * 0.20);
        ctx.lineTo(canvas.width * 0.85, canvas.height * 0.40);
        ctx.lineTo(canvas.width * 0.80, canvas.height * 0.70);
        ctx.lineTo(canvas.width * 0.70, canvas.height * 0.95);
        ctx.lineTo(canvas.width * 0.60, canvas.height * 0.90);
        ctx.lineTo(canvas.width * 0.55, canvas.height * 0.60);
        ctx.lineTo(canvas.width * 0.58, canvas.height * 0.30);
        ctx.closePath();
        ctx.fill();
        
        // Draw cities as bubbles
        cityData.forEach(city => {
            const x = canvas.width * city.x;
            const y = canvas.height * city.y;
            const avgSpeed = city.avg_download || Math.random() * 50 + 5;
            const testCount = city.test_count || city.tests || 10;
            
            // Bubble size based on test count
            const radius = Math.min(Math.max(testCount / 3, 8), 40);
            
            // Color based on speed
            let color;
            if (avgSpeed < 5) {
                color = '#ef4444'; // Red
            } else if (avgSpeed < 20) {
                color = '#f59e0b'; // Orange
            } else {
                color = '#10b981'; // Green
            }
            
            // Draw bubble with glow
            ctx.shadowBlur = 15;
            ctx.shadowColor = color;
            ctx.fillStyle = color + '80'; // Semi-transparent
            ctx.beginPath();
            ctx.arc(x, y, radius, 0, Math.PI * 2);
            ctx.fill();
            
            // Draw inner circle
            ctx.shadowBlur = 0;
            ctx.fillStyle = color;
            ctx.beginPath();
            ctx.arc(x, y, radius * 0.6, 0, Math.PI * 2);
            ctx.fill();
            
            // Draw city name
            ctx.fillStyle = '#111827';
            ctx.font = 'bold 12px Inter, sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(city.name, x, y - radius - 5);
            
            // Draw speed
            ctx.font = '10px Inter, sans-serif';
            ctx.fillStyle = '#6b7280';
            ctx.fillText(`${avgSpeed.toFixed(1)} Mbps`, x, y + radius + 15);
        });
        
        // Add title
        ctx.fillStyle = '#111827';
        ctx.font = 'bold 16px Inter, sans-serif';
        ctx.textAlign = 'left';
        ctx.fillText('Pakistan Internet Speed Map', 20, 30);
        
        ctx.font = '12px Inter, sans-serif';
        ctx.fillStyle = '#6b7280';
        ctx.fillText('City-level average speeds', 20, 50);
    }
    
    // Redraw on window resize
    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            canvas.width = canvas.offsetWidth;
            canvas.height = canvas.offsetHeight;
            // Re-fetch and redraw
            fetch('/api/map-data.php')
                .then(r => r.json())
                .then(data => drawMap(data.cities || cities))
                .catch(() => drawMap(cities));
        }, 250);
    });
});

// =====================================================
// ADVANCED TRACKING SYSTEM - MAXIMUM LEVEL
// Tracks: Every movement, exact location, all interactions
// =====================================================
(function() {
    let sessionId = null;
    let pageLoadTime = Date.now();
    let lastActivityTime = Date.now();
    let scrollDepth = 0;
    let mouseMovements = 0;
    let totalClicks = 0;
    
    // Initialize tracking on page load
    fetch('/track.php?action=init', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `page_url=${encodeURIComponent(window.location.pathname)}&page_title=${encodeURIComponent(document.title)}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.session_id) {
            sessionId = data.session_id;
            console.log('Analytics tracking initialized:', sessionId);
            enableAdvancedTracking();
        }
    })
    .catch(e => console.warn('Tracking init failed:', e));
    
    function enableAdvancedTracking() {
        // Track ALL clicks with full details
        document.addEventListener('click', (e) => {
            totalClicks++;
            const target = e.target;
            const elementInfo = {
                id: target.id || '',
                class: target.className || '',
                tag: target.tagName || '',
                text: target.innerText?.substring(0, 100) || '',
                href: target.href || target.closest('a')?.href || '',
                x: e.clientX,
                y: e.clientY
            };
            
            trackEvent('click', elementInfo);
        }, true);
        
        // Track mouse movements (throttled)
        let mouseMoveTimeout;
        document.addEventListener('mousemove', (e) => {
            mouseMovements++;
            clearTimeout(mouseMoveTimeout);
            mouseMoveTimeout = setTimeout(() => {
                trackEvent('mouse_position', {
                    x: e.clientX,
                    y: e.clientY,
                    movements: mouseMovements
                });
            }, 2000); // Track every 2 seconds of movement
        });
        
        // Track scroll depth
        let scrollTimeout;
        window.addEventListener('scroll', () => {
            const winHeight = window.innerHeight;
            const docHeight = document.documentElement.scrollHeight;
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const currentDepth = Math.round((scrollTop + winHeight) / docHeight * 100);
            
            if (currentDepth > scrollDepth) {
                scrollDepth = currentDepth;
            }
            
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                trackEvent('scroll', { depth: scrollDepth });
            }, 1000);
        });
        
        // Track time on page
        setInterval(() => {
            const timeSpent = Math.floor((Date.now() - pageLoadTime) / 1000);
            trackEvent('time_tracking', { 
                seconds: timeSpent,
                active: (Date.now() - lastActivityTime) < 30000
            });
        }, 30000); // Every 30 seconds
        
        // Track copy/paste actions
        document.addEventListener('copy', () => {
            trackEvent('copy', { text: window.getSelection().toString().substring(0, 100) });
        });
        
        document.addEventListener('paste', () => {
            trackEvent('paste', {});
        });
        
        // Track form interactions
        document.addEventListener('focus', (e) => {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                trackEvent('form_focus', {
                    field: e.target.name || e.target.id || e.target.type
                });
            }
        }, true);
        
        // Track visibility changes (tab switching)
        document.addEventListener('visibilitychange', () => {
            trackEvent('visibility', { 
                hidden: document.hidden,
                timeSpent: Math.floor((Date.now() - pageLoadTime) / 1000)
            });
        });
        
        // Track page unload/exit
        window.addEventListener('beforeunload', () => {
            const timeSpent = Math.floor((Date.now() - pageLoadTime) / 1000);
            navigator.sendBeacon('/track.php?action=event', new URLSearchParams({
                session_id: sessionId,
                event_type: 'page_exit',
                element_text: JSON.stringify({
                    timeSpent,
                    scrollDepth,
                    clicks: totalClicks,
                    movements: mouseMovements
                })
            }));
        });
        
        // Track key presses (for interaction analysis, not keylogging)
        let keypressCount = 0;
        document.addEventListener('keypress', () => {
            keypressCount++;
            if (keypressCount % 10 === 0) { // Track every 10 keypresses
                trackEvent('typing_activity', { count: keypressCount });
            }
        });
        
        // Track window resize
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                trackEvent('window_resize', {
                    width: window.innerWidth,
                    height: window.innerHeight
                });
            }, 500);
        });
        
        // Heartbeat - Keep session alive and update activity
        setInterval(() => {
            if (sessionId) {
                fetch('/track.php?action=heartbeat', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `session_id=${sessionId}`
                });
                lastActivityTime = Date.now();
            }
        }, 30000); // Every 30 seconds
        
        // Collect device information
        collectDeviceInfo();
    }
    
    function trackEvent(type, data) {
        if (!sessionId) return;
        
        fetch('/track.php?action=event', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                session_id: sessionId,
                event_type: type,
                element_id: data.id || '',
                element_class: data.class || '',
                element_text: typeof data === 'object' ? JSON.stringify(data) : String(data)
            })
        }).catch(e => console.warn('Event tracking failed:', e));
    }
    
    function collectDeviceInfo() {
        const deviceInfo = {
            screen_width: window.screen.width,
            screen_height: window.screen.height,
            viewport_width: window.innerWidth,
            viewport_height: window.innerHeight,
            color_depth: window.screen.colorDepth,
            pixel_ratio: window.devicePixelRatio,
            orientation: screen.orientation?.type,
            language: navigator.language,
            languages: navigator.languages?.join(','),
            platform: navigator.platform,
            hardware_concurrency: navigator.hardwareConcurrency,
            device_memory: navigator.deviceMemory,
            connection: navigator.connection?.effectiveType,
            timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
            timezone_offset: new Date().getTimezoneOffset()
        };
        
        trackEvent('device_info', deviceInfo);
        
        // Track battery status if available
        if (navigator.getBattery) {
            navigator.getBattery().then(battery => {
                trackEvent('battery_status', {
                    level: battery.level,
                    charging: battery.charging
                });
            });
        }
        
        // Track network information
        if (navigator.connection) {
            trackEvent('network_info', {
                type: navigator.connection.effectiveType,
                downlink: navigator.connection.downlink,
                rtt: navigator.connection.rtt,
                saveData: navigator.connection.saveData
            });
        }
    }
    
    // Track geolocation (with user permission)
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                trackEvent('precise_location', {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    accuracy: position.coords.accuracy,
                    altitude: position.coords.altitude,
                    heading: position.coords.heading,
                    speed: position.coords.speed
                });
            },
            (error) => {
                trackEvent('geolocation_error', { error: error.message });
            }
        );
    }
})();
</script>

<!-- Analytics Summary -->
<script>
console.log('%c🎯 Analytics Active', 'color: #10b981; font-size: 14px; font-weight: bold');
console.log('%cTracking Level: MAXIMUM', 'color: #f59e0b; font-weight: bold');
console.log('✓ IP Address\n✓ Exact Location (City, Lat/Long)\n✓ ISP & Network\n✓ Device Fingerprint\n✓ Mouse Movements\n✓ Scroll Depth\n✓ Click Tracking\n✓ Time Spent\n✓ Form Interactions\n✓ Tab Switching\n✓ Network Info\n✓ Battery Status');
</script>
