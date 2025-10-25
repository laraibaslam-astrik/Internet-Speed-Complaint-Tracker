<!-- ISP Leaderboard Section -->
<section class="py-5" style="background: rgba(255,255,255,0.6); backdrop-filter: blur(10px);">
    <div class="container">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center gap-3 mb-2">
                <div class="d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: #d97706; border-radius: 12px;">
                    <i class="bi bi-trophy-fill text-white" style="font-size: 1.5rem;"></i>
                </div>
                <h3 class="mb-0 fw-bold" style="color: #111827;">
                    <span data-en="ISP Leaderboard" data-ur="ISP لیڈر بورڈ">ISP Leaderboard</span>
                </h3>
            </div>
            <p style="color: #6b7280; margin: 0;">
                <span data-en="Compare ISP performance today" data-ur="آج کے ISP کارکردگی کا موازنہ">Compare ISP performance today</span>
            </p>
        </div>
        
        <div class="card shadow-lg" style="border-radius: 16px; overflow: hidden; border: 1px solid #e5e7eb;">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="leaderboard-table">
                        <thead style="background: #4f46e5;">
                            <tr>
                                <th scope="col" class="ps-4 py-3 text-white fw-semibold">#</th>
                                <th scope="col" class="py-3 text-white fw-semibold">
                                    <i class="bi bi-router me-1"></i>
                                    <span data-en="ISP" data-ur="ISP">ISP</span>
                                </th>
                                <th scope="col" class="text-end py-3 text-white fw-semibold">
                                    <i class="bi bi-download me-1"></i>
                                    <span data-en="Download" data-ur="ڈاؤن لوڈ">Download</span>
                                </th>
                                <th scope="col" class="text-end py-3 text-white fw-semibold">
                                    <i class="bi bi-upload me-1"></i>
                                    <span data-en="Upload" data-ur="اپ لوڈ">Upload</span>
                                </th>
                                <th scope="col" class="text-end py-3 text-white fw-semibold">
                                    <i class="bi bi-clock me-1"></i>
                                    <span data-en="Ping" data-ur="پنگ">Ping</span>
                                </th>
                                <th scope="col" class="text-end pe-4 py-3 text-white fw-semibold">
                                    <i class="bi bi-bar-chart me-1"></i>
                                    <span data-en="Tests" data-ur="ٹیسٹس">Tests</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="leaderboard-body" style="background: white;">
                            <tr>
                                <td colspan="6" class="text-center py-5" style="color: #6b7280;">
                                    <div class="spinner-border spinner-border-sm me-2" role="status" style="color: #4f46e5;">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <span data-en="Loading data..." data-ur="ڈیٹا لوڈ ہو رہا ہے...">Loading data...</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
