<?php if (!isset($hide_navbar)):
    /**
     * ARMAS Shared Footer
     * Include at the bottom of every page
     */
    ?>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-brand">
                <img src="/armas/assets/img/armas.png" alt="ARMAS Shield" class="footer-logo">
                <div>
                    <span class="logo-text">ARMAS</span>
                    <p class="footer-tagline">Protecting Every Filipino, Every Mile Away</p>
                </div>
            </div>

            <div class="footer-links">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="/armas/pages/landing.php">Home</a></li>
                    <li><a href="/armas/pages/landing.php#about">About</a></li>
                    <li><a href="/armas/pages/landing.php#services">Services</a></li>
                    <li><a href="/armas/pages/landing.php#contact">Contact</a></li>
                </ul>
            </div>

            <div class="footer-links">
                <h4>Legal</h4>
                <ul>
                    <li><a href="/armas/pages/landing.php#terms">Terms and Conditions</a></li>
                    <li><a href="/armas/pages/landing.php#privacy">Privacy Policy</a></li>
                </ul>
            </div>

            <div class="footer-contact">
                <h4>Contact Us</h4>
                <p>ARMAS Help Desk</p>
                <p>support@armas.gov.ph</p>
                <p>+63 2 8888 8888</p>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> ARMAS — Assistance and Repatriation Management and Action System. All rights
                reserved.</p>
        </div>
    </footer>
<?php endif; ?>

<!-- Common Modals -->
<div id="confirmModal" class="modal">
    <div class="modal-content">
        <h3>Confirm Action</h3>
        <p id="confirmMessage">Are you sure you want to proceed?</p>
        <div class="modal-actions">
            <button class="btn btn-outline" onclick="closeModal('confirmModal')">Cancel</button>
            <button class="btn btn-danger" id="confirmBtn">Confirm</button>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script src="/armas/assets/js/main.js"></script>
<?php if (isset($use_charts) && $use_charts): ?>
    <script src="/armas/assets/js/charts.js"></script>
<?php endif; ?>
<script src="/armas/assets/js/validation.js"></script>

</body>

</html>