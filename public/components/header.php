<!-- Header Component -->
<header role="banner">
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm sticky-top" role="navigation" aria-label="Main navigation">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="/">
            <div class="me-2 bounce-slow" style="font-size: 1.75rem;">
                <i class="bi bi-speedometer2"></i>
            </div>
            <div>
                <div style="font-size: 1.1rem; line-height: 1.2;">
                    <span data-en="Pakistan Speed Tracker" data-ur="پاکستان رفتار ٹریکر">Pakistan Speed Tracker</span>
                </div>
                <div style="font-size: 0.65rem; opacity: 0.8; font-weight: 400; letter-spacing: 2px;">
                    <span data-en="REAL-TIME MONITORING" data-ur="حقیقی وقت کی نگرانی">REAL-TIME MONITORING</span>
                </div>
            </div>
        </a>
        
        <div class="d-flex align-items-center gap-2">
            <!-- Language Toggle -->
            <div class="btn-group btn-group-sm" role="group" aria-label="Language toggle" style="border-radius: 50px; overflow: hidden;">
                <button type="button" class="btn btn-outline-light active" id="btn-lang-en" onclick="setLanguage('en')" aria-label="English" style="border-radius: 50px 0 0 50px; min-width: 45px;">
                    🇬🇧
                </button>
                <button type="button" class="btn btn-outline-light" id="btn-lang-ur" onclick="setLanguage('ur')" aria-label="اردو" style="border-radius: 0 50px 50px 0; min-width: 45px;">
                    🇵🇰
                </button>
            </div>
            
            <!-- Start Test Button -->
            <button class="btn btn-warning fw-bold shadow-lg" id="btn-start-test" onclick="startSpeedTest()" aria-label="Start speed test" style="position: relative; z-index: 1;">
                <i class="bi bi-lightning-charge-fill me-1"></i>
                <span data-en="Start Test" data-ur="ٹیسٹ شروع کریں">Start Test</span>
            </button>
        </div>
    </div>
</nav>
</header>

<!-- Outage/Spike Banner -->
<div id="outage-banner" class="alert alert-danger alert-dismissible fade show m-0 rounded-0 d-none" role="alert">
    <div class="container">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <div>
                <strong data-en="Network Issue Detected!" data-ur="نیٹ ورک کا مسئلہ دریافت ہوا!">Network Issue Detected!</strong>
                <span id="outage-message"></span>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
</div>
