<?php
// includes/footer.php
?>
    </main>

    <!-- Footer -->
    <footer class="footer mt-auto py-3 bg-light">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <span class="text-muted">
                        &copy; <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved.
                    </span>
                </div>
                <div class="col-md-6 text-end">
                    <span class="text-muted">
                        Version <?= APP_VERSION ?>
                    </span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <script src="<?= BASE_URL ?>/assets/js/script.js"></script>

    <!-- Page Specific Scripts -->
    <?php if (isset($extra_js)): ?>
        <?= $extra_js ?>
    <?php endif; ?>

    <script>
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })

    // Initialize Select2
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap-5'
        });
    });
    </script>
</body>
</html>
