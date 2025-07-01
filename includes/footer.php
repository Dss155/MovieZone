<footer>
  <style>
    footer {
      background: linear-gradient(90deg, #232946 0%, #4ECDC4 100%);
      color: #fff;
      padding: 2.5rem 0 1rem 0;
      margin-top: 2rem;
    }
    .footer-content {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 1.2rem;
    }
    .footer-brand {
      font-size: 2.2rem;
      font-weight: 900;
      letter-spacing: 2px;
    }
    .brand-part-1 {
      color: #fff;
    }
    .brand-part-2 {
      color: var(--accent-color);
    }
    .footer-links {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      justify-content: center;
      margin-bottom: 0.5rem;
    }
    .footer-link {
      color: #fff;
      opacity: 0.85;
      font-weight: 500;
      text-decoration: none;
      transition: color 0.2s;
    }
    .footer-link:hover {
      color: var(--accent-color);
      opacity: 1;
    }
    .social-links {
      display: flex;
      gap: 12px;
      margin-bottom: 0.5rem;
    }
    .social-link {
      background: rgba(255,255,255,0.13);
      color: #fff;
      border-radius: 50%;
      width: 38px;
      height: 38px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
      transition: background 0.2s, color 0.2s;
      text-decoration: none;
    }
    .social-link:hover {
      background: var(--accent-color);
      color: #fff;
    }
    .copyright {
      font-size: 0.95rem;
      opacity: 0.8;
      margin-top: 0.7rem;
      text-align: center;
    }
    @media (max-width: 600px) {
      .footer-brand { font-size: 1.5rem; }
      .footer-links { gap: 7px; }
      .social-link { width: 32px; height: 32px; font-size: 1rem; }
    }
  </style>
  <div class="container">
    <div class="footer-content">
      <div class="footer-brand">
        <span class="brand-part-1">Movie</span><span class="brand-part-2">Zone</span>
      </div>
      <div class="footer-links">
        <a href="index.php" class="footer-link">Home</a>
        <a href="category.php?cat=Bollywood" class="footer-link">Bollywood</a>
        <a href="category.php?cat=Hollywood" class="footer-link">Hollywood</a>
        <a href="category.php?cat=South" class="footer-link">South</a>
        <a href="about.php" class="footer-link">About</a>
        <a href="contact.php" class="footer-link">Contact</a>
      </div>
      <div class="social-links">
        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
        <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
      </div>
      <div class="copyright">
        © <?= date('Y') ?> MovieZone. All rights reserved.
      </div>
    </div>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
