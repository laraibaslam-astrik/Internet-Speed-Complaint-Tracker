<!-- Share Bar -->
<section class="py-4 bg-white border-top border-bottom" id="share-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-4 mb-3 mb-md-0">
                <h5 class="mb-0">
                    <i class="bi bi-share me-2"></i>
                    <span data-en="Share Your Results" data-ur="اپنے نتائج شیئر کریں">Share Your Results</span>
                </h5>
            </div>
            <div class="col-md-8">
                <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                    <button class="btn btn-outline-primary btn-sm" onclick="copyResultLink()" id="btn-copy-link" disabled>
                        <i class="bi bi-link-45deg me-1"></i>
                        <span data-en="Copy Link" data-ur="لنک کاپی کریں">Copy Link</span>
                    </button>
                    <button class="btn btn-outline-info btn-sm" onclick="shareToTwitter()" id="btn-share-twitter" disabled>
                        <i class="bi bi-twitter-x me-1"></i>
                        <span data-en="Share on X" data-ur="X پر شیئر کریں">Share on X</span>
                    </button>
                    <button class="btn btn-outline-success btn-sm" onclick="shareToWhatsApp()" id="btn-share-whatsapp" disabled>
                        <i class="bi bi-whatsapp me-1"></i>
                        WhatsApp
                    </button>
                    <button class="btn btn-outline-primary btn-sm" onclick="shareToFacebook()" id="btn-share-facebook" disabled>
                        <i class="bi bi-facebook me-1"></i>
                        Facebook
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
