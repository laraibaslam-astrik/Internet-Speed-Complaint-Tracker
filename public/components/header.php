<!-- Header Component -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/">
            <i class="bi bi-speedometer2 me-2"></i>
            <span data-en="Pakistan Internet Speed Tracker" data-ur="پاکستان انٹرنیٹ رفتار ٹریکر">Pakistan Internet Speed Tracker</span>
        </a>
        
        <div class="d-flex align-items-center gap-3">
            <!-- Language Toggle -->
            <div class="btn-group btn-group-sm" role="group" aria-label="Language toggle">
                <button type="button" class="btn btn-outline-light active" id="btn-lang-en" onclick="setLanguage('en')" aria-label="English">
                    EN
                </button>
                <button type="button" class="btn btn-outline-light" id="btn-lang-ur" onclick="setLanguage('ur')" aria-label="اردو">
                    UR
                </button>
            </div>
            
            <!-- Start Test Button -->
            <button class="btn btn-warning fw-bold" id="btn-start-test" onclick="startSpeedTest()" aria-label="Start speed test">
                <i class="bi bi-play-fill me-1"></i>
                <span data-en="Start Test" data-ur="ٹیسٹ شروع کریں">Start Test</span>
            </button>
        </div>
    </div>
</nav>

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
