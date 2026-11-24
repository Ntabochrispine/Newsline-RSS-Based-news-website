</main>

<footer class="site-footer">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <h4 class="footer-title">RSS NEWS</h4>
                <p>Your source for the latest news from Kenya, Africa and around the world.</p>
                <div class="social-links">
                    <a href="<?= e(url('rss.php')) ?>" aria-label="RSS Feed" title="RSS Feed"><i class="bi bi-rss"></i></a>
                    <a href="#" aria-label="Facebook" title="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="#" aria-label="X" title="X"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" aria-label="Instagram" title="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="#" aria-label="YouTube" title="YouTube"><i class="bi bi-youtube"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-md-6 mb-4">
                <h5>Quick Links</h5>
                <ul class="footer-links">
                    <li><a href="<?= e(url('index.php')) ?>">Home</a></li>
                    <li><a href="<?= e(Url('latest.php?region=world')) ?>">World</a></li>
                    <li><a href="<?= e(Url('latest.php?region=africa')) ?>">Africa</a></li>
                    <li><a href="<?= e(Url('latest.php?region=kenya')) ?>">Kenya</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <h5>Categories</h5>
                <ul class="footer-links">
                    <li><a href="<?= e(categoryUrl('business')) ?>">Business</a></li>
                    <li><a href="<?= e(categoryUrl('technology')) ?>">Technology</a></li>
                    <li><a href="<?= e(categoryUrl('sports')) ?>">Sports</a></li>
                    <li><a href="<?= e(categoryUrl('health')) ?>">Health</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <h5>Account</h5>
                <ul class="footer-links">
                    <?php if (isLoggedIn()): ?>
                        <li><a href="<?= e(url(currentUserRole() === 'admin' ? 'admin/index.php' : (currentUserRole() === 'author' ? 'admin/articles.php' : 'user/dashboard.php'))) ?>">My Dashboard</a></li>
                        <li><a href="<?= e(url('logout.php')) ?>">Logout</a></li>
                    <?php else: ?>
                        <li><a href="<?= e(url('login.php')) ?>">Login</a></li>
                        <li><a href="<?= e(url('register.php')) ?>">Create Account</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        <hr>
        <div class="footer-bottom">
            <p class="mb-0">&copy; <?= date('Y') ?> RSS News Portal. All rights reserved.</p>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(url('assets/js/main.js')) ?>"></script>
</body>
</html>
