<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= isset($page_title) ? $page_title : "MovieZone" ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/theme.css">
  <style>
    :root {
      --primary-gradient: linear-gradient(90deg, #6C63FF 0%, #4ECDC4 100%);
      --primary-color: #6C63FF;
      --secondary-color: #FF6B6B;
      --accent-color: #4ECDC4;
      --dark-color: #232946;
      --light-color: #F7F9FC;
      --card-gradient: linear-gradient(135deg, #f2f6ff 0%, #e0eafc 100%);
      --footer-gradient: linear-gradient(90deg, #232946 0%, #4ECDC4 100%);
    }

    body {
      background: var(--light-color);
      min-height: 100vh;
      font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
    }

    .custom-navbar {
      background: var(--primary-gradient) !important;
      box-shadow: 0 2px 10px rgba(76, 99, 255, 0.08);
      padding: 1rem 0;

    }

    .navbar-brand span.brand-part-1 {
      color: #fff;
      font-weight: 800;
      letter-spacing: 1px;
    }

    .navbar-brand span.brand-part-2 {
      color: var(--accent-color);
      font-weight: 800;
    }

    .nav-link {
      color: #fff !important;
      font-weight: 500;
      letter-spacing: 0.5px;
    }

    .nav-link.active,
    .nav-link:focus,
    .nav-link:hover {
      color: var(--secondary-color) !important;
      border-bottom: 2px solid var(--secondary-color);
      background: transparent !important;
    }

    .theme-toggle-btn {
      background: rgba(255, 255, 255, 0.15);
      border: none;
      color: #fff;
      border-radius: 50%;
      width: 38px;
      height: 38px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background 0.2s;
      margin-left: 10px;
    }

    .theme-toggle-btn:hover {
      background: var(--accent-color);
      color: #fff;
    }

    .search-form input {
      border-radius: 30px;
      border: none;
      padding-left: 16px;
      background: rgba(255, 255, 255, 0.25);
      color: #232946;
    }

    .search-form input:focus {
      border: 1px solid var(--accent-color);
      background: #fff;
    }

    @media (max-width: 991px) {
      .navbar-nav {
        background: var(--primary-gradient);
        padding: 1rem;
        border-radius: 18px;
        margin-top: 10px;
      }
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg custom-navbar sticky-top">
    <div class="container">
      <a class="navbar-brand" href="index.php">
        <span class="brand-part-1">Movie</span><span class="brand-part-2">Zone</span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>" href="index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'category.php' && isset($_GET['cat']) && $_GET['cat'] == 'Bollywood' ? 'active' : '' ?>" href="category.php?cat=Bollywood">Bollywood</a></li>
          <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'category.php' && isset($_GET['cat']) && $_GET['cat'] == 'Hollywood' ? 'active' : '' ?>" href="category.php?cat=Hollywood">Hollywood</a></li>
          <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'category.php' && isset($_GET['cat']) && $_GET['cat'] == 'South' ? 'active' : '' ?>" href="category.php?cat=South">South</a></li>
          <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : '' ?>" href="about.php">About</a></li>
          <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : '' ?>" href="contact.php">Contact</a></li>
        </ul>
        <form class="d-flex ms-lg-4 search-form" action="search.php" method="get" role="search">
          <input class="form-control me-2" type="search" name="q" placeholder="Search movies..." aria-label="Search">
        </form>
        <!-- Theme toggle (optional) -->
        <button class="theme-toggle-btn" id="themeToggleBtn" title="Toggle theme">
          <i id="themeIcon" class="fa fa-moon"></i>
        </button>
      </div>
    </div>
  </nav>
  <script>
    const btn = document.getElementById('themeToggleBtn');
    const themeIcon = document.getElementById('themeIcon');
    // On page load, set theme from localStorage
    if (localStorage.getItem('theme') === 'dark') {
      document.body.classList.add('dark-theme');
      themeIcon.classList.remove('fa-moon');
      themeIcon.classList.add('fa-sun');
    }
    btn.addEventListener('click', () => {
      document.body.classList.toggle('dark-theme');
      if (document.body.classList.contains('dark-theme')) {
        themeIcon.classList.remove('fa-moon');
        themeIcon.classList.add('fa-sun');
        localStorage.setItem('theme', 'dark');
      } else {
        themeIcon.classList.remove('fa-sun');
        themeIcon.classList.add('fa-moon');
        localStorage.setItem('theme', 'light');
      }
    });
  </script>