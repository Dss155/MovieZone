<?php
// search.php
include("includes/db.php");

$keyword = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : '';
if (!$keyword) {
    die("Search query is missing.");
}

$page_title = "Search Results - MovieZone";
include("includes/header.php");

$query = "SELECT * FROM movies WHERE title LIKE '%$keyword%' OR cast LIKE '%$keyword%' OR description LIKE '%$keyword%' ORDER BY id DESC";
$results = $conn->query($query);
?>

<style>
.section-header {
  background: linear-gradient(90deg, #6C63FF 0%, #4ECDC4 100%);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
  font-weight: 700;
  font-size: 2rem;
  margin-bottom: 2rem;
  display: inline-block;
  position: relative;
}
.movie-card {
  background: linear-gradient(135deg, #f2f6ff 0%, #e0eafc 100%);
  border-radius: 18px;
  box-shadow: 0 4px 24px rgba(76, 99, 255, 0.07);
  border: none;
  transition: transform 0.2s, box-shadow 0.2s;
  overflow: hidden;
}
.movie-card:hover {
  transform: translateY(-6px) scale(1.02);
  box-shadow: 0 12px 32px rgba(76, 99, 255, 0.13);
}
.card-img-container {
  position: relative;
}
.card-category {
  position: absolute;
  top: 10px;
  left: 10px;
  background: var(--accent-color);
  color: #fff;
  font-weight: 600;
  border-radius: 20px;
  padding: 4px 14px;
  font-size: 0.75rem;
  box-shadow: 0 2px 8px rgba(76, 99, 255, 0.10);
}
.card-title {
  font-weight: 700;
  color: var(--dark-color);
}
.btn-view {
  background: var(--primary-gradient);
  color: #fff;
  border: none;
  border-radius: 50px;
  font-weight: 600;
  transition: background 0.2s;
}
.btn-view:hover {
  background: var(--secondary-color);
  color: #fff;
}
</style>

<div class="container my-5">
  <h2 class="section-header">Search Results for "<?= htmlspecialchars($keyword) ?>"</h2>
  
  <div class="row g-4">
    <?php if ($results->num_rows > 0): ?>
      <?php while ($movie = $results->fetch_assoc()): ?>
        <div class="col-6 col-md-3">
          <div class="movie-card card h-100">
            <div class="card-img-container">
              <img src="uploads/posters/<?= htmlspecialchars($movie['poster']) ?>" class="card-img-top" alt="<?= htmlspecialchars($movie['title']) ?>">
              <span class="card-category"><?= htmlspecialchars($movie['category']) ?></span>
            </div>
            <div class="card-body d-flex flex-column">
              <h5 class="card-title"><?= htmlspecialchars($movie['title']) ?></h5>
              <p class="card-text mt-auto">
                <i class="fas fa-film me-1"></i> <?= htmlspecialchars($movie['category']) ?>
                <span class="ms-2"><i class="fas fa-hdd me-1"></i> <?= htmlspecialchars($movie['size']) ?> GB</span>
              </p>
              <a href="movie.php?id=<?= $movie['id'] ?>" class="btn btn-view w-100 mt-3">
                <i class="fas fa-eye me-2"></i>View & Download
              </a>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="col-12">
        <div class="alert alert-warning border-0 shadow-sm">
          <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-circle fa-2x me-3 text-warning"></i>
            <div>
              <h5 class="mb-1">No results found</h5>
              <p class="mb-0">We couldn't find any movies matching "<strong><?= htmlspecialchars($keyword) ?></strong>".</p>
            </div>
          </div>
        </div>
        <div class="text-center mt-4">
          <a href="index.php" class="btn btn-view px-4">
            <i class="fas fa-home me-2"></i>Back to Home
          </a>
        </div>
      </div>
    <?php endif; ?>
  </div>
  
  <?php if ($results->num_rows > 0): ?>
    <div class="text-center mt-5">
      <a href="index.php" class="btn btn-outline-secondary px-4">
        <i class="fas fa-arrow-left me-2"></i>Back to Browse
      </a>
    </div>
  <?php endif; ?>
</div>

<?php include("includes/footer.php"); ?>
