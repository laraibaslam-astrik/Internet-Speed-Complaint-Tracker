<!-- Live Metrics Cards -->
<section class="py-4 bg-light">
    <div class="container">
        <!-- Detected Info Line -->
        <div class="text-center mb-4" id="detected-info">
            <small class="text-muted">
                <i class="bi bi-router me-1"></i>
                <span data-en="ISP" data-ur="ISP">ISP</span>: <strong id="info-isp">--</strong>
                <span class="mx-2">•</span>
                <i class="bi bi-geo-alt me-1"></i>
                <span data-en="City" data-ur="شہر">City</span>: <strong id="info-city">--</strong>
                <span class="mx-2">•</span>
                <i class="bi bi-broadcast me-1"></i>
                <span data-en="Tech" data-ur="ٹیکنالوجی">Tech</span>: <strong id="info-tech">--</strong>
            </small>
        </div>

        <!-- Metrics Grid -->
        <div class="row g-3">
            <!-- Download Speed -->
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="text-muted small mb-2">
                            <i class="bi bi-download me-1"></i>
                            <span data-en="Download" data-ur="ڈاؤن لوڈ">Download</span>
                        </div>
                        <div class="display-4 fw-bold text-primary" id="metric-download">--</div>
                        <div class="text-muted small">Mbps</div>
                        <canvas id="spark-download" width="100" height="30" class="mt-2" aria-hidden="true"></canvas>
                    </div>
                </div>
            </div>

            <!-- Upload Speed -->
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="text-muted small mb-2">
                            <i class="bi bi-upload me-1"></i>
                            <span data-en="Upload" data-ur="اپ لوڈ">Upload</span>
                        </div>
                        <div class="display-4 fw-bold text-success" id="metric-upload">--</div>
                        <div class="text-muted small">Mbps</div>
                        <canvas id="spark-upload" width="100" height="30" class="mt-2" aria-hidden="true"></canvas>
                    </div>
                </div>
            </div>

            <!-- Ping -->
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="text-muted small mb-2">
                            <i class="bi bi-clock me-1"></i>
                            <span data-en="Ping" data-ur="پنگ">Ping</span>
                        </div>
                        <div class="display-4 fw-bold text-info" id="metric-ping">--</div>
                        <div class="text-muted small">ms</div>
                        <canvas id="spark-ping" width="100" height="30" class="mt-2" aria-hidden="true"></canvas>
                    </div>
                </div>
            </div>

            <!-- Jitter -->
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="text-muted small mb-2">
                            <i class="bi bi-activity me-1"></i>
                            <span data-en="Jitter" data-ur="جٹر">Jitter</span>
                        </div>
                        <div class="display-4 fw-bold text-warning" id="metric-jitter">--</div>
                        <div class="text-muted small">ms</div>
                        <canvas id="spark-jitter" width="100" height="30" class="mt-2" aria-hidden="true"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Test Progress -->
        <div id="test-progress" class="mt-3 d-none">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small" id="test-stage">Initializing...</span>
                        <button class="btn btn-sm btn-outline-danger" onclick="cancelTest()" data-en="Cancel" data-ur="منسوخ کریں">
                            Cancel
                        </button>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rate Limit Countdown -->
        <div id="rate-limit-notice" class="mt-3 d-none">
            <div class="alert alert-warning text-center mb-0">
                <i class="bi bi-hourglass-split me-2"></i>
                <span data-en="Please wait" data-ur="برائے مہربانی انتظار کریں">Please wait</span>
                <strong id="countdown-timer">10:00</strong>
                <span data-en="before retesting" data-ur="دوبارہ ٹیسٹ سے پہلے">before retesting</span>
            </div>
        </div>
    </div>
</section>
