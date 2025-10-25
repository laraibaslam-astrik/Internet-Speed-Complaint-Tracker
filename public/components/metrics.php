<!-- Live Metrics Cards -->
<section class="py-5" style="background: transparent;">
    <div class="container">
        <!-- Detected Info Line -->
        <div class="text-center mb-4" id="detected-info">
            <div class="d-inline-flex align-items-center gap-3 p-3 rounded-pill" style="background: #ffffff; border: 2px solid #e5e7eb; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                <div class="d-flex align-items-center">
                    <i class="bi bi-router-fill me-2" style="color: #4f46e5; font-size: 1.2rem;"></i>
                    <span class="small me-1" style="color: #6b7280;" data-en="ISP" data-ur="ISP">ISP</span>
                    <strong id="info-isp" style="color: #111827; font-weight: 600;">--</strong>
                </div>
                <div style="width: 1px; height: 20px; background: #d1d5db;"></div>
                <div class="d-flex align-items-center">
                    <i class="bi bi-geo-alt-fill me-2" style="color: #059669; font-size: 1.2rem;"></i>
                    <span class="small me-1" style="color: #6b7280;" data-en="City" data-ur="شہر">City</span>
                    <strong id="info-city" style="color: #111827; font-weight: 600;">--</strong>
                </div>
                <div style="width: 1px; height: 20px; background: #d1d5db;"></div>
                <div class="d-flex align-items-center">
                    <i class="bi bi-broadcast-pin me-2" style="color: #d97706; font-size: 1.2rem;"></i>
                    <span class="small me-1" style="color: #6b7280;" data-en="Tech" data-ur="ٹیکنالوجی">Tech</span>
                    <strong id="info-tech" style="color: #111827; font-weight: 600;">--</strong>
                </div>
            </div>
        </div>

        <!-- Metrics Grid -->
        <div class="row g-3">
            <!-- Download Speed -->
            <div class="col-6 col-md-3">
                <div class="card h-100 position-relative overflow-hidden">
                    <div class="position-absolute top-0 end-0 p-3" style="opacity: 0.06; font-size: 3rem; color: #4f46e5;">
                        <i class="bi bi-arrow-down-circle-fill"></i>
                    </div>
                    <div class="card-body text-center position-relative">
                        <div class="mb-3 d-flex align-items-center justify-content-center gap-2 metric-label" style="color: #0f172a; font-weight: 800; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.08em;">
                            <i class="bi bi-download" style="font-size: 1.3rem; color: #4f46e5;"></i>
                            <span data-en="Download" data-ur="ڈاؤن لوڈ">Download</span>
                        </div>
                        <div class="display-4 fw-bold mb-2 metric-number" id="metric-download" style="color: #0f172a !important; font-weight: 900 !important; font-size: 3.5rem !important; letter-spacing: -0.04em; text-shadow: 0 2px 8px rgba(79, 70, 229, 0.15);">--</div>
                        <div class="unit-label mb-3" style="color: #0f172a; font-weight: 800; font-size: 1.15rem; letter-spacing: 0.05em;">Mbps</div>
                        <canvas id="spark-download" width="100" height="30" class="mt-2" aria-hidden="true"></canvas>
                    </div>
                </div>
            </div>

            <!-- Upload Speed -->
            <div class="col-6 col-md-3 animate-fade-in delay-200">
                <div class="card h-100 position-relative overflow-hidden">
                    <div class="position-absolute top-0 end-0 p-3" style="opacity: 0.06; font-size: 3rem; color: #059669;">
                        <i class="bi bi-arrow-up-circle-fill"></i>
                    </div>
                    <div class="card-body text-center position-relative">
                        <div class="mb-3 d-flex align-items-center justify-content-center gap-2 metric-label" style="color: #0f172a; font-weight: 800; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.08em;">
                            <i class="bi bi-upload" style="font-size: 1.3rem; color: #059669;"></i>
                            <span data-en="Upload" data-ur="اپ لوڈ">Upload</span>
                        </div>
                        <div class="display-4 fw-bold mb-2 metric-number" id="metric-upload" style="color: #0f172a !important; font-weight: 900 !important; font-size: 3.5rem !important; letter-spacing: -0.04em; text-shadow: 0 2px 8px rgba(5, 150, 105, 0.15);">--</div>
                        <div class="unit-label mb-3" style="color: #0f172a; font-weight: 800; font-size: 1.15rem; letter-spacing: 0.05em;">Mbps</div>
                        <canvas id="spark-upload" width="100" height="30" class="mt-2" aria-hidden="true"></canvas>
                    </div>
                </div>
            </div>

            <!-- Ping -->
            <div class="col-6 col-md-3">
                <div class="card h-100 position-relative overflow-hidden">
                    <div class="position-absolute top-0 end-0 p-3" style="opacity: 0.06; font-size: 3rem; color: #0891b2;">
                        <i class="bi bi-clock-fill"></i>
                    </div>
                    <div class="card-body text-center position-relative">
                        <div class="mb-3 d-flex align-items-center justify-content-center gap-2 metric-label" style="color: #0f172a; font-weight: 800; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.08em;">
                            <i class="bi bi-clock" style="font-size: 1.3rem; color: #0891b2;"></i>
                            <span data-en="Ping" data-ur="پنگ">Ping</span>
                        </div>
                        <div class="display-4 fw-bold mb-2 metric-number" id="metric-ping" style="color: #0f172a !important; font-weight: 900 !important; font-size: 3.5rem !important; letter-spacing: -0.04em; text-shadow: 0 2px 8px rgba(8, 145, 178, 0.15);">--</div>
                        <div class="unit-label mb-3" style="color: #0f172a; font-weight: 800; font-size: 1.15rem; letter-spacing: 0.05em;">ms</div>
                        <canvas id="spark-ping" width="100" height="30" class="mt-2" aria-hidden="true"></canvas>
                    </div>
                </div>
            </div>

            <!-- Jitter -->
            <div class="col-6 col-md-3 animate-fade-in delay-400">
                <div class="card h-100 position-relative overflow-hidden">
                    <div class="position-absolute top-0 end-0 p-3" style="opacity: 0.06; font-size: 3rem; color: #d97706;">
                        <i class="bi bi-activity"></i>
                    </div>
                    <div class="card-body text-center position-relative">
                        <div class="mb-3 d-flex align-items-center justify-content-center gap-2 metric-label" style="color: #0f172a; font-weight: 800; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.08em;">
                            <i class="bi bi-activity" style="font-size: 1.3rem; color: #d97706;"></i>
                            <span data-en="Jitter" data-ur="جٹر">Jitter</span>
                        </div>
                        <div class="display-4 fw-bold mb-2 metric-number" id="metric-jitter" style="color: #0f172a !important; font-weight: 900 !important; font-size: 3.5rem !important; letter-spacing: -0.04em; text-shadow: 0 2px 8px rgba(217, 119, 6, 0.15);">--</div>
                        <div class="unit-label mb-3" style="color: #0f172a; font-weight: 800; font-size: 1.15rem; letter-spacing: 0.05em;">ms</div>
                        <canvas id="spark-jitter" width="100" height="30" class="mt-2" aria-hidden="true"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Test Progress -->
        <div id="test-progress" class="mt-4 d-none">
            <div class="card border-0 shadow-lg" style="border-radius: 20px; overflow: hidden; background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="spinner-border spinner-border-sm" role="status" style="color: #667eea;">
                                <span class="visually-hidden">Testing...</span>
                            </div>
                            <span class="fw-bold" style="color: #667eea;" id="test-stage">Initializing...</span>
                        </div>
                        <button class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="cancelTest()">
                            <i class="bi bi-x-circle me-1"></i>
                            <span data-en="Cancel" data-ur="منسوخ کریں">Cancel</span>
                        </button>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rate Limit Countdown -->
        <div id="rate-limit-notice" class="mt-4 d-none">
            <div class="alert alert-warning text-center mb-0 border-0 shadow-lg" style="border-radius: 20px; background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(251, 146, 60, 0.1) 100%);">
                <div class="d-flex align-items-center justify-content-center gap-3">
                    <i class="bi bi-hourglass-split" style="font-size: 1.5rem; color: #f59e0b;"></i>
                    <div>
                        <div class="fw-bold" style="color: #92400e;">
                            <span data-en="Please wait" data-ur="برائے مہربانی انتظار کریں">Please wait</span>
                            <span class="mx-2" style="font-size: 1.25rem;" id="countdown-timer">10:00</span>
                        </div>
                        <small class="text-muted">
                            <span data-en="before retesting" data-ur="دوبارہ ٹیسٹ سے پہلے">before retesting</span>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
