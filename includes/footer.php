    </main>
    <footer class="bg-dark text-light py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>Tree Smoker</h5>
                    <p>Your ultimate destination for premium coffee beans and accessories. Relax, brew, and enjoy.</p>
                    <div class="social-icons">
                        <a href="#" class="text-light me-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-light me-2"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-light me-2"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
                <div class="col-md-3">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo SITE_URL; ?>" class="text-light">Home</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/shop.php" class="text-light">Shop</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/about.php" class="text-light">About Us</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/contact.php" class="text-light">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-5">
                    <h5>Newsletter</h5>
                    <p>Subscribe to receive updates on new products and special offers</p>
                    <form class="d-flex">
                        <input type="email" class="form-control me-2" placeholder="Your email">
                        <button type="submit" class="btn btn-success">Subscribe</button>
                    </form>
                </div>
            </div>
            <hr class="my-4">
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; <?php echo date('Y'); ?> Tree Smoker. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>
                        <a href="<?php echo SITE_URL; ?>/privacy.php" class="text-light me-3">Privacy Policy</a>
                        <a href="<?php echo SITE_URL; ?>/terms.php" class="text-light">Terms of Service</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Custom JS -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body>
</html> 