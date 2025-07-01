<?php
// movie.php
include("includes/db.php");

$id = intval($_GET['id']);
$movie = $conn->query("SELECT * FROM movies WHERE id = $id")->fetch_assoc();
if (!$movie) die("Movie not found!");

$page_title = htmlspecialchars($movie['title']) . " - MovieZone";
include("includes/header.php");

// Get the first mirror ID
$firstMirrorRes = $conn->query("SELECT id FROM movie_mirrors WHERE movie_id = $id ORDER BY id ASC LIMIT 1");
$firstMirror = $firstMirrorRes->fetch_assoc();

// Get all mirrors for this movie
$stmt = $conn->prepare("SELECT * FROM movie_mirrors WHERE movie_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$mirrors = $stmt->get_result();

function get_host_logo($url)
{
  if (strpos($url, 'drive.google.com') !== false) return 'assets/img/drive.png';
  if (strpos($url, 'dropbox.com') !== false) return 'assets/img/dropbox.png';
  if (strpos($url, 'mediafire.com') !== false) return 'assets/img/mediafire.png';
  if (strpos($url, 'terabox.com') !== false) return 'assets/img/terabox.png';
  return 'assets/img/default_host.png';
}

// Get related movies
$category = $conn->real_escape_string($movie['category']);
$current_id = $movie['id'];
$related_query = "SELECT * FROM movies WHERE category = '{$category}' AND id != {$current_id} ORDER BY RAND() LIMIT 4";
$related_movies = $conn->query($related_query);

?>


<style>
  .section-header {
    background: linear-gradient(90deg, #6C63FF 0%, #4ECDC4 100%);
    -webkit-background-clip: text;
    background-clip: text;
    background-clip: text;
    background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    font-weight: 700;
    font-size: 2rem;
    margin-bottom: 2rem;
    display: inline-block;
    position: relative;
  }

  .movie-title {
    font-size: 2.2rem;
    font-weight: 800;
    color: var(--primary-color);
    margin-bottom: 1rem;
    background: linear-gradient(90deg, #6C63FF 0%, #4ECDC4 100%);
    -webkit-background-clip: text;
    background-clip: text;
    background-clip: text;
    background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
  }

  .movie-meta {
    margin-bottom: 1.2rem;
  }

  .movie-meta-item {
    background: #f2f6ff;
    color: #232946;
    border-radius: 18px;
    padding: 6px 16px;
    margin-right: 10px;
    font-weight: 600;
    font-size: 1rem;
    display: inline-block;
  }

  .detail-heading {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
    margin-top: 1.2rem;
  }

  .movie-description,
  .movie-cast {
    background: var(--card-gradient);
    border-radius: 14px;
    padding: 1.2rem 1.5rem;
    margin-bottom: 1.2rem;
    box-shadow: 0 2px 8px rgba(76, 99, 255, 0.07);
  }

  .movie-poster-container {
    position: relative;
  }

  .movie-poster {
    border-radius: 18px;
    box-shadow: 0 6px 32px rgba(76, 99, 255, 0.13);
  }

  .movie-category-badge {
    position: absolute;
    top: 16px;
    left: 16px;
    background: var(--accent-color);
    color: #fff;
    font-weight: 600;
    border-radius: 20px;
    padding: 6px 18px;
    font-size: 0.85rem;
    box-shadow: 0 2px 8px rgba(76, 99, 255, 0.10);
  }

  .btn-download {
    background: var(--primary-gradient);
    color: #fff;
    border: none;
    border-radius: 50px;
    font-weight: 600;
    transition: background 0.2s;
    padding: 10px 32px;
    font-size: 1.1rem;
    box-shadow: 0 2px 8px rgba(76, 99, 255, 0.08);
  }

  .btn-download:hover {
    background: var(--secondary-color);
    color: #fff;
  }

  .download-section {
    background: var(--card-gradient);
    border-radius: 14px;
    padding: 1.2rem 1.5rem;
    box-shadow: 0 2px 8px rgba(76, 99, 255, 0.07);
  }

  .download-host img {
    border-radius: 8px;
    background: #fff;
    box-shadow: 0 2px 8px rgba(76, 99, 255, 0.07);
    margin-right: 10px;
  }

  .download-info-item {
    color: #232946;
    font-size: 1rem;
    margin-bottom: 6px;
  }

  .unavailable-notice {
    background: #ffeaea;
    color: #ff6b6b;
    border-radius: 12px;
    font-weight: 600;
  }

  .share-section {
    margin-top: 2rem;
  }

  .share-buttons {
    display: flex;
    gap: 12px;
    margin-top: 0.5rem;
  }

  .share-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    border-radius: 50%;
    font-size: 1.2rem;
    color: #fff;
    background: #6C63FF;
    transition: background 0.2s, color 0.2s;
    text-decoration: none;
  }

  .share-btn.facebook {
    background: #3b5998;
  }

  .share-btn.twitter {
    background: #1da1f2;
  }

  .share-btn.whatsapp {
    background: #25d366;
  }

  .share-btn.telegram {
    background: #0088cc;
  }

  .share-btn:hover {
    filter: brightness(1.15);
  }

  .related-movies .movie-card {
    background: var(--card-gradient);
  }

  .related-movies .card-title {
    font-size: 1rem;
  }

  @media (max-width: 767px) {
    .movie-title {
      font-size: 1.5rem;
    }

    .movie-meta-item {
      font-size: 0.95rem;
      padding: 5px 10px;
    }

    .movie-description,
    .movie-cast {
      padding: 1rem;
    }
  }
</style>


<div class="container my-5">
  <!-- ... breadcrumb and poster/details ... -->
  <nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
      <li class="breadcrumb-item"><a href="category.php?cat=<?= urlencode($movie['category']) ?>" class="text-decoration-none"><?= htmlspecialchars($movie['category']) ?></a></li>
      <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($movie['title']) ?></li>
    </ol>
  </nav>

  <div class="row g-4">
    <div class="col-md-4 mb-4">
      <div class="movie-poster-container">
        <img src="uploads/posters/<?= htmlspecialchars($movie['poster']) ?>" class="img-fluid rounded shadow-lg movie-poster" alt="<?= htmlspecialchars($movie['title']) ?>">
        <span class="movie-category-badge"><?= htmlspecialchars($movie['category']) ?></span>
      </div>
      <div class="mt-4 download-section">
        <div class="mb-3"><strong>Choose a Download Mirror:</strong></div>
        <?php if ($mirrors->num_rows > 0): ?>
          <?php while ($mirror = $mirrors->fetch_assoc()): ?>
            <div class="mb-2">
              <img src="<?= get_host_logo($mirror['download_link']) ?>" alt="host" height="28" style="vertical-align:middle;">
              <a href="download_page.php?mirror_id=<?= $mirror['id'] ?>" class="btn btn-download ms-2">
                <?= htmlspecialchars($mirror['mirror_label']) ?> (<?= htmlspecialchars($mirror['host']) ?>)
              </a>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="unavailable-notice p-3">
            <i class="fas fa-exclamation-circle me-2"></i>
            <span>No download mirrors available</span>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <!-- ... rest of your movie details column ... -->
    <div class="col-md-8">
      <h1 class="movie-title"><?= htmlspecialchars($movie['title']) ?></h1>

      <div class="movie-meta mb-3">
        <span class="movie-meta-item"><i class="fas fa-film me-2"></i><?= htmlspecialchars($movie['category']) ?></span>
        <span class="movie-meta-item"><i class="fas fa-hdd me-2"></i><?= htmlspecialchars($movie['size']) ?> GB</span>
        <?php if (!empty($movie['year'])): ?>
          <span class="movie-meta-item"><i class="fas fa-calendar me-2"></i><?= htmlspecialchars($movie['year']) ?></span>
        <?php endif; ?>
        <?php if (!empty($movie['language'])): ?>
          <span class="movie-meta-item"><i class="fas fa-language me-2"></i><?= htmlspecialchars($movie['language']) ?></span>
        <?php endif; ?>
      </div>

      <?php if (!empty($movie['cast'])): ?>
        <div class="movie-cast mt-4">
          <h4 class="detail-heading">Cast</h4>
          <p><?= htmlspecialchars($movie['cast']) ?></p>
        </div>
      <?php endif; ?>

      <div class="movie-description mt-4">
        <h4 class="detail-heading">Synopsis</h4>
        <div class="description-content">
          <?= nl2br(htmlspecialchars($movie['description'])) ?>
        </div>
      </div>

      <!-- Mobile Download Button (Visible on small screens) -->
      <div class="d-md-none mt-4">
        <?php if ($firstMirror): ?>
          <a href="download_page.php?mirror_id=<?= $firstMirror['id'] ?>"
            class="btn btn-download btn-lg w-100">
            <i class="fas fa-download me-2"></i>Download Now
          </a>
        <?php else: ?>
          <div class="unavailable-notice p-3 text-center">
            <i class="fas fa-exclamation-circle me-2"></i>
            <span>Download not available</span>
          </div>
        <?php endif; ?>
      </div>

      <!-- Social Share -->
      <div class="share-section mt-4">
        <h4 class="detail-heading">Share This Movie</h4>
        <div class="share-buttons">
          <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" target="_blank" class="share-btn facebook">
            <i class="fab fa-facebook-f"></i>
          </a>
          <a href="https://twitter.com/intent/tweet?url=<?= urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>&text=<?= urlencode('Check out ' . $movie['title'] . ' on MovieZone!') ?>" target="_blank" class="share-btn twitter">
            <i class="fab fa-twitter"></i>
          </a>
          <a href="https://wa.me/?text=<?= urlencode('Check out ' . $movie['title'] . ' on MovieZone: ' . 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" target="_blank" class="share-btn whatsapp">
            <i class="fab fa-whatsapp"></i>
          </a>
          <a href="https://telegram.me/share/url?url=<?= urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>&text=<?= urlencode('Check out ' . $movie['title'] . ' on MovieZone!') ?>" target="_blank" class="share-btn telegram">
            <i class="fab fa-telegram-plane"></i>
          </a>
        </div>
      </div>
    </div>
  </div>
  <!-- ... related movies ... -->
  <?php if ($related_movies->num_rows > 0): ?>
    <div class="related-movies mt-5">
      <h3 class="section-header">Related Movies</h3>
      <div class="row g-4">
        <?php while ($related = $related_movies->fetch_assoc()): ?>
          <div class="col-6 col-md-3">
            <div class="movie-card card h-100">
              <div class="card-img-container">
                <img src="uploads/posters/<?= htmlspecialchars($related['poster']) ?>" class="card-img-top" alt="<?= htmlspecialchars($related['title']) ?>">
                <span class="card-category"><?= htmlspecialchars($related['category']) ?></span>
              </div>
              <div class="card-body d-flex flex-column">
                <h5 class="card-title"><?= htmlspecialchars($related['title']) ?></h5>
                <p class="card-text mt-auto">
                  <i class="fas fa-film me-1"></i> <?= htmlspecialchars($related['category']) ?>
                  <span class="ms-2"><i class="fas fa-hdd me-1"></i> <?= htmlspecialchars($related['size']) ?> GB</span>
                </p>
                <a href="movie.php?id=<?= $related['id'] ?>" class="btn btn-view w-100 mt-3">
                  <i class="fas fa-eye me-2"></i>View & Download
                </a>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
  <?php endif; ?>

</div>
<?php include("includes/footer.php"); ?>