<?php
session_start();
$page_title = "Contact - MovieZone";
include("includes/db.php");
include("includes/header.php");

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = "Invalid CSRF token. Please refresh the page and try again.";
    } else {
        $name = trim(filter_var($_POST['name'], FILTER_SANITIZE_STRING));
        $email = trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL));
        $message = trim(filter_var($_POST['message'], FILTER_SANITIZE_STRING));

        if (empty($name)) {
            $errors[] = "Please enter your name.";
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address.";
        }
        if (empty($message)) {
            $errors[] = "Please enter your message.";
        }

        if (empty($errors)) {
            $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $message);
            if ($stmt->execute()) {
                $success = "Thank you for contacting us! We will get back to you shortly.";
                // Clear POST data to reset form
                $_POST = [];
            } else {
                $errors[] = "Failed to send your message. Please try again later.";
            }
        }
    }
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<style>
  .btn-gradient {
    background: linear-gradient(90deg, #6C63FF 0%, #4ECDC4 100%);
    color: #fff !important;
    font-weight: 700;
    border-radius: 50px;
    padding: 12px 30px;
    font-size: 1.1rem;
    border: none;
    transition: background 0.3s ease;
  }
  .btn-gradient:hover,
  .btn-gradient:focus {
    background: linear-gradient(90deg, #4ECDC4 0%, #6C63FF 100%);
    color: #fff !important;
    box-shadow: 0 0 10px rgba(76, 99, 255, 0.5);
  }
  .form-label {
    color: var(--dark-color);
    font-weight: 600;
  }
  .card {
    background: var(--card-gradient);
    border-radius: 18px;
    box-shadow: 0 4px 24px rgba(76, 99, 255, 0.1);
  }
</style>

<div class="container my-5">
  <h2 class="section-header mb-4 text-center" style="font-weight: 800; color: var(--primary-color);">
    Contact Us
  </h2>
  <div class="card p-4 shadow-sm" style="max-width: 600px; margin: auto;">
    <?php if ($success): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php elseif (!empty($errors)): ?>
      <div class="alert alert-danger">
        <ul class="mb-0">
          <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form id="contactForm" method="post" novalidate>
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
      <div class="mb-3">
        <label for="name" class="form-label">Your Name</label>
        <input type="text" class="form-control" id="name" name="name" placeholder="Enter your name" required value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
        <div class="invalid-feedback">Please enter your name.</div>
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Your Email</label>
        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
        <div class="invalid-feedback">Please enter a valid email address.</div>
      </div>
      <div class="mb-3">
        <label for="message" class="form-label">Message</label>
        <textarea class="form-control" id="message" name="message" rows="5" placeholder="Type your message..." required><?= isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '' ?></textarea>
        <div class="invalid-feedback">Please enter your message.</div>
      </div>
      <button type="submit" class="btn btn-gradient w-100">Send Message</button>
    </form>
  </div>
</div>

<script>
// Bootstrap 5 client-side validation
(() => {
  'use strict'
  const form = document.getElementById('contactForm');
  form.addEventListener('submit', event => {
    if (!form.checkValidity()) {
      event.preventDefault()
      event.stopPropagation()
    }
    form.classList.add('was-validated')
  }, false)
})();
</script>

<?php include("includes/footer.php"); ?>
