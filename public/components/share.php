<!-- Share Bar -->
<section class="py-5" id="share-section" style="background: #f9fafb;">
    <div class="container">
        <div class="card shadow-lg" style="border-radius: 16px; border: 1px solid #e5e7eb; background: #ffffff;">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="d-flex align-items-center gap-2">
                            <div class="d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: #4f46e5; border-radius: 12px;">
                                <i class="bi bi-share-fill text-white" style="font-size: 1.25rem;"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold" style="color: #111827;">
                                    <span data-en="Share Results" data-ur="نتائج شیئر کریں">Share Results</span>
                                </h5>
                                <small style="color: #6b7280;">
                                    <span data-en="Spread the word" data-ur="پیغام پھیلائیں">Spread the word</span>
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                            <button class="btn btn-outline-primary rounded-pill px-4" onclick="copyResultLink()" id="btn-copy-link" disabled>
                                <i class="bi bi-link-45deg me-1"></i>
                                <span data-en="Copy Link" data-ur="لنک کاپی کریں">Copy Link</span>
                            </button>
                            <button class="btn btn-outline-info rounded-pill px-4" onclick="shareToTwitter()" id="btn-share-twitter" disabled>
                                <i class="bi bi-twitter-x me-1"></i>
                                <span data-en="Twitter" data-ur="ٹویٹر">Twitter</span>
                            </button>
                            <button class="btn btn-outline-success rounded-pill px-4" onclick="shareToWhatsApp()" id="btn-share-whatsapp" disabled>
                                <i class="bi bi-whatsapp me-1"></i>
                                WhatsApp
                            </button>
                            <button class="btn btn-outline-primary rounded-pill px-4" onclick="shareToFacebook()" id="btn-share-facebook" disabled>
                                <i class="bi bi-facebook me-1"></i>
                                Facebook
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
