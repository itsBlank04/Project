  <footer class="mt-5">
      <div class="container">
          <div class="row g-4">
              <div class="col-lg-4">
                  <div class="d-flex align-items-center mb-3">
                      <img src="<?php echo function_exists('base_url') ? base_url('assets/logo.png') : '/public/assets/logo.png'; ?>"
                          alt="DHCC logo" class="footer-logo me-3">
                      <h5 class="mb-0 text-white fw-bold">Digital Hobby Community Club</h5>
                  </div>
                  <p class="text-white-50 mb-3">Connect with fellow hobbyists, share your passion, and discover new
                      interests in our vibrant community.</p>
                  <div class="social-links">
                      <a href="#" class="text-white-50 me-3 fs-5"><i class="fab fa-facebook-f"></i></a>
                      <a href="#" class="text-white-50 me-3 fs-5"><i class="fab fa-twitter"></i></a>
                      <a href="#" class="text-white-50 me-3 fs-5"><i class="fab fa-instagram"></i></a>
                      <a href="#" class="text-white-50 me-3 fs-5"><i class="fab fa-discord"></i></a>
                  </div>
              </div>
              <div class="col-lg-2 col-md-3">
                  <h6 class="text-white fw-bold mb-3">Quick Links</h6>
                  <ul class="list-unstyled">
                      <li class="mb-2"><a
                              href="<?php echo function_exists('base_url') ? base_url('clubs.php') : '/public/clubs.php'; ?>"
                              class="text-white-50 text-decoration-none">All Clubs</a></li>
                      <li class="mb-2"><a
                              href="<?php echo function_exists('base_url') ? base_url('feed.php') : '/public/feed.php'; ?>"
                              class="text-white-50 text-decoration-none">Activity Feed</a></li>
                      <li class="mb-2"><a
                              href="<?php echo function_exists('base_url') ? base_url('create_club.php') : '/public/create_club.php'; ?>"
                              class="text-white-50 text-decoration-none">Create Club</a></li>
                      <li class="mb-2"><a
                              href="<?php echo function_exists('base_url') ? base_url('create_post.php') : '/public/create_post.php'; ?>"
                              class="text-white-50 text-decoration-none">Create Post</a></li>
                      <li class="mb-2"><a
                              href="<?php echo function_exists('base_url') ? base_url('members.php') : '/public/members.php'; ?>"
                              class="text-white-50 text-decoration-none">Members</a></li>
                  </ul>
              </div>
              <div class="col-lg-2 col-md-3">
                  <h6 class="text-white fw-bold mb-3">Account</h6>
                  <ul class="list-unstyled">
                      <li class="mb-2"><a
                              href="<?php echo function_exists('base_url') ? base_url('dashboard.php') : '/public/dashboard.php'; ?>"
                              class="text-white-50 text-decoration-none">Dashboard</a></li>
                      <li class="mb-2"><a
                              href="<?php echo function_exists('base_url') ? base_url('my_club.php') : '/public/my_club.php'; ?>"
                              class="text-white-50 text-decoration-none">My Clubs</a></li>
                      <li class="mb-2"><a
                              href="<?php echo function_exists('base_url') ? base_url('actions/logout.php') : '/public/actions/logout.php'; ?>"
                              class="text-white-50 text-decoration-none">Logout</a></li>
                  </ul>
              </div>

          </div>
          <hr class="my-4 border-white-50">
          <div class="row align-items-center">
              <div class="col-md-6">
                  <p class="text-white-50 small mb-0">&copy; <?php echo date('Y'); ?> Digital Hobby Community Club. All
                      rights reserved.</p>
              </div>
              <div class="col-md-6 text-md-end">
                  <a href="#" class="text-white-50 text-decoration-none small me-3">Privacy Policy</a>
                  <a href="#" class="text-white-50 text-decoration-none small me-3">Terms of Service</a>
                  <a href="#" class="text-white-50 text-decoration-none small">Contact Us</a>
              </div>
          </div>
      </div>
  </footer>

  <!-- Back to Top Button -->
  <button id="backToTop" class="btn btn-primary position-fixed"
      style="bottom: 20px; right: 20px; display: none; border-radius: 50%; width: 50px; height: 50px; z-index: 1050;">
      <i class="fas fa-arrow-up"></i>
  </button>
  </div> <!-- container -->
  <!-- MDBootstrap JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.js"></script>

  <!-- Custom JavaScript for enhanced functionality -->
  <script>
document.addEventListener('DOMContentLoaded', function() {
    // Back to Top Button functionality
    const backToTopBtn = document.getElementById('backToTop');

    // Show/hide button based on scroll position
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTopBtn.classList.add('show');
        } else {
            backToTopBtn.classList.remove('show');
        }
    });

    // Smooth scroll to top
    backToTopBtn.addEventListener('click', function(e) {
        e.preventDefault();
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // Navbar scroll effect
    const navbar = document.querySelector('.navbar');
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // Add loading animation to cards
    const cards = document.querySelectorAll('.card');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
    });

    // Newsletter form submission (placeholder)
    const newsletterForm = document.querySelector('.newsletter-signup');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;
            if (email) {
                alert('Thank you for subscribing! We\'ll keep you updated with the latest news.');
                this.reset();
            }
        });
    }

    // Add ripple effect to buttons
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            ripple.classList.add('ripple-effect');
            this.appendChild(ripple);

            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = e.clientX - rect.left - size / 2 + 'px';
            ripple.style.top = e.clientY - rect.top - size / 2 + 'px';

            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
});
  </script>

  <style>
/* Ripple effect for buttons */
.ripple-effect {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.4);
    transform: scale(0);
    animation: ripple 0.6s linear;
    pointer-events: none;
}

@keyframes ripple {
    to {
        transform: scale(4);
        opacity: 0;
    }
}
  </style>
  </body>

  </html>