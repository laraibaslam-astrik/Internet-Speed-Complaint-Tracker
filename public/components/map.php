<!-- Pakistan Heatmap Section -->
<section class="py-5" style="background: transparent;">
    <div class="container">
        <div class="mb-4 text-center">
            <div class="d-inline-block px-4 py-2 rounded-3" style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);">
                <h3 class="mb-2" style="color: #000000; font-weight: 800;">
                    <i class="bi bi-map" style="color: #667eea;"></i>
                    <span data-en="Pakistan Internet Speed Map" data-ur="پاکستان انٹرنیٹ رفتار نقشہ">Pakistan Internet Speed Map</span>
                </h3>
            </div>
            <p style="color: #000000; margin: 0; font-weight: 700; font-size: 1rem;">
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
                            <i class="bi bi-circle-fill" style="color: #ef4444; font-size: 1rem;"></i>
                            <span class="fw-bold" style="color: #000000; font-size: 0.95rem;" data-en="< 5 Mbps" data-ur="< 5 Mbps">< 5 Mbps</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-circle-fill" style="color: #f59e0b; font-size: 1rem;"></i>
                            <span class="fw-bold" style="color: #000000; font-size: 0.95rem;" data-en="5-20 Mbps" data-ur="5-20 Mbps">5-20 Mbps</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-circle-fill" style="color: #10b981; font-size: 1rem;"></i>
                            <span class="fw-bold" style="color: #000000; font-size: 0.95rem;" data-en="> 20 Mbps" data-ur="> 20 Mbps">> 20 Mbps</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-info-circle-fill" style="color: #6366f1; font-size: 1rem;"></i>
                            <span class="fw-bold" style="color: #000000; font-size: 0.95rem;" data-en="Bubble size = test volume" data-ur="بلبلے کا سائز = ٹیسٹ والیوم">Bubble size = test volume</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
