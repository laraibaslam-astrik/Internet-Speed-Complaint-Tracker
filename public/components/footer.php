<!-- Footer -->
<footer class="bg-dark text-light py-4 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-shield-check me-2"></i>
                    <span data-en="Privacy & Data" data-ur="رازداری اور ڈیٹا">Privacy & Data</span>
                </h6>
                <p class="small text-light-emphasis mb-2">
                    <span data-en="No personal data stored. We only collect anonymized network statistics to improve internet quality insights for Pakistan. Your IP address is hashed and never stored directly." 
                          data-ur="کوئی ذاتی ڈیٹا محفوظ نہیں کیا جاتا۔ ہم صرف گمنام نیٹ ورک کے اعداد و شمار اکٹھا کرتے ہیں تاکہ پاکستان کے لیے انٹرنیٹ معیار کی بصیرت کو بہتر بنایا جا سکے۔">
                        No personal data stored. We only collect anonymized network statistics to improve internet quality insights for Pakistan. Your IP address is hashed and never stored directly.
                    </span>
                </p>
                <div class="d-flex gap-3">
                    <a href="#" class="text-light text-decoration-none small">
                        <span data-en="Privacy Policy" data-ur="رازداری کی پالیسی">Privacy Policy</span>
                    </a>
                    <a href="#" class="text-light text-decoration-none small">
                        <span data-en="Terms of Service" data-ur="خدمات کی شرائط">Terms of Service</span>
                    </a>
                </div>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <p class="small mb-2">
                    <span data-en="Made for Pakistan 🇵🇰" data-ur="پاکستان کے لیے بنایا گیا 🇵🇰">Made for Pakistan 🇵🇰</span>
                </p>
                <p class="small text-light-emphasis mb-0">
                    © <?php echo date('Y'); ?> Pakistan Internet Speed Tracker
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="toast-notification" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="toast-message"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>
