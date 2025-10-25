<!-- Pakistan Heatmap Section -->
<section class="py-5">
    <div class="container">
        <h3 class="mb-4">
            <i class="bi bi-map me-2"></i>
            <span data-en="Pakistan Speed Map (Today)" data-ur="پاکستان رفتار نقشہ (آج)">Pakistan Speed Map (Today)</span>
        </h3>
        
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <!-- Simple canvas-based bubble map -->
                <div id="map-container" style="position: relative; width: 100%; height: 400px; background: #f8f9fa;">
                    <canvas id="pakistan-map" style="width: 100%; height: 100%;"></canvas>
                </div>
                
                <!-- Legend -->
                <div class="p-3 bg-light border-top">
                    <div class="d-flex flex-wrap gap-3 justify-content-center align-items-center">
                        <small class="fw-bold text-muted">
                            <span data-en="Download Speed" data-ur="ڈاؤن لوڈ رفتار">Download Speed</span>:
                        </small>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge" style="background-color: #dc3545; width: 20px; height: 20px;"></span>
                            <small data-en="< 5 Mbps" data-ur="< 5 Mbps">< 5 Mbps</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge" style="background-color: #ffc107; width: 20px; height: 20px;"></span>
                            <small>5-20 Mbps</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge" style="background-color: #198754; width: 20px; height: 20px;"></span>
                            <small data-en="> 20 Mbps" data-ur="> 20 Mbps">> 20 Mbps</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <small class="text-muted">
                                <span data-en="Bubble size = test count" data-ur="بلبلے کا سائز = ٹیسٹوں کی تعداد">Bubble size = test count</span>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
