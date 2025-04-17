<?php
$pageTitle = "Contact Us";
require_once 'includes/header.php';

// Process contact form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);
    
    // Simple validation
    $errors = [];
    
    if (empty($name)) $errors[] = "Name is required.";
    if (empty($email)) $errors[] = "Email is required.";
    if (empty($subject)) $errors[] = "Subject is required.";
    if (empty($message)) $errors[] = "Message is required.";
    
    if (empty($errors)) {
        // In a real application, you would send an email here
        // For now, we'll just simulate a successful submission
        
        $_SESSION['success_message'] = "Your message has been sent. We'll get back to you soon!";
        redirect(SITE_URL . '/contact.php');
    } else {
        $_SESSION['error_message'] = implode("<br>", $errors);
    }
}
?>

<section class="py-5">
    <div class="container">
        <h1 class="mb-4">Contact Us</h1>
        
        <div class="row">
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Send Us a Message</h5>
                        
                        <form action="<?php echo SITE_URL; ?>/contact.php" method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="name" class="form-label">Your Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                                <div class="invalid-feedback">
                                    Please enter your name.
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <div class="invalid-feedback">
                                    Please enter a valid email address.
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject</label>
                                <input type="text" class="form-control" id="subject" name="subject" required>
                                <div class="invalid-feedback">
                                    Please enter a subject.
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="message" class="form-label">Message</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                                <div class="invalid-feedback">
                                    Please enter your message.
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Send Message</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Contact Information</h5>
                        
                        <div class="d-flex mb-3">
                            <div class="me-3">
                                <i class="fas fa-map-marker-alt text-primary fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Address</h6>
                                <p class="mb-0">123 Coffee Lane, Beanville, CA 90210</p>
                            </div>
                        </div>
                        
                        <div class="d-flex mb-3">
                            <div class="me-3">
                                <i class="fas fa-phone text-primary fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Phone</h6>
                                <p class="mb-0">(555) 123-4567</p>
                            </div>
                        </div>
                        
                        <div class="d-flex mb-3">
                            <div class="me-3">
                                <i class="fas fa-envelope text-primary fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Email</h6>
                                <p class="mb-0">info@treesmoker.com</p>
                            </div>
                        </div>
                        
                        <div class="d-flex mb-3">
                            <div class="me-3">
                                <i class="fas fa-clock text-primary fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Business Hours</h6>
                                <p class="mb-0">Monday - Friday: 9:00 AM - 5:00 PM</p>
                                <p class="mb-0">Saturday: 10:00 AM - 3:00 PM</p>
                                <p class="mb-0">Sunday: Closed</p>
                            </div>
                        </div>
                        
                        <h5 class="mt-4 mb-3">Follow Us</h5>
                        <div class="social-icons">
                            <a href="#" class="me-2"><i class="fab fa-facebook-f fa-2x"></i></a>
                            <a href="#" class="me-2"><i class="fab fa-twitter fa-2x"></i></a>
                            <a href="#" class="me-2"><i class="fab fa-instagram fa-2x"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <h5 class="mb-3">Location</h5>
            <div class="ratio ratio-16x9">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d423283.43554977553!2d-118.69192399037387!3d34.02073049448939!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x80c2c75ddc27da13%3A0xe22fdf6f254608f4!2sLos%20Angeles%2C%20CA!5e0!3m2!1sen!2sus!4v1651766593226!5m2!1sen!2sus" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?> 