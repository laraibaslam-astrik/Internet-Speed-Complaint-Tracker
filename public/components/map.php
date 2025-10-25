<!-- Pakistan Heatmap Section -->
<section class="py-5" style="background: transparent;">
    <div class="container">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center gap-3 mb-2">
                <div class="d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: #4f46e5; border-radius: 12px;">
                    <i class="bi bi-map-fill text-white" style="font-size: 1.5rem;"></i>
                </div>
                <h3 class="mb-0 fw-bold" style="color: #111827;">
                    <span data-en="Pakistan Speed Map" data-ur="پاکستان رفتار نقشہ">Pakistan Speed Map</span>
                </h3>
            </div>
            <p style="color: #6b7280; margin: 0;">
                <span data-en="Real-time speed data across major cities" data-ur="بڑے شہروں میں حقیقی وقت کی رفتار کا ڈیٹا">Real-time speed data across major cities</span>
            </p>
        </div>
        
        <div class="card shadow-lg" style="border-radius: 16px; overflow: hidden; border: 1px solid #e5e7eb;">
            <div class="card-body p-0">
                <!-- Simple canvas-based bubble map -->
                <div id="map-container" style="position: relative; width: 100%; height: 450px; background: #e0e7ff; min-height: 300px;">
                    <canvas id="pakistan-map" width="800" height="450" style="width: 100%; height: 100%; display: block;"></canvas>
                </div>
                
                <!-- Legend -->
                <div class="p-4" style="background: #ffffff; border-top: 1px solid #e5e7eb;">
                    <div class="d-flex flex-wrap gap-4 justify-content-center align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-circle-fill" style="color: #ef4444; font-size: 0.75rem;"></i>
                            <small class="fw-semibold" style="color: #374151;" data-en="< 5 Mbps" data-ur="< 5 Mbps">< 5 Mbps</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-circle-fill" style="color: #f59e0b; font-size: 0.75rem;"></i>
                            <small class="fw-semibold" style="color: #374151;">5-20 Mbps</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-circle-fill" style="color: #10b981; font-size: 0.75rem;"></i>
                            <small class="fw-semibold" style="color: #374151;" data-en="> 20 Mbps" data-ur="> 20 Mbps">> 20 Mbps</small>
                        </div>
                        <div style="width: 1px; height: 20px; background: #d1d5db;"></div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-info-circle-fill" style="color: #4f46e5;"></i>
                            <small style="color: #6b7280;">
                                <span data-en="Bubble size indicates test volume" data-ur="بلبلے کا سائز ٹیسٹوں کی تعداد">Bubble size indicates test volume</span>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
