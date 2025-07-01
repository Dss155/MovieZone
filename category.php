<?php
// Category page - displays movies filtered by category
include("includes/db.php");
$page_title = isset($_GET['cat']) ? htmlspecialchars($_GET['cat']) . " Movies - MovieZone" : "Category - MovieZone";
include("includes/header.php");

// Get and sanitize category parameter
$category = isset($_GET['cat']) ? $conn->real_escape_string($_GET['cat']) : '';

// Validate category parameter
if (!$category) {
    header("Location: index.php");
    exit("Invalid category!");
}

// Pagination setup
$limit = 8;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Count total movies in this category
$total_result = $conn->query("SELECT COUNT(*) AS total FROM movies WHERE category = '$category'");
$total_row = $total_result->fetch_assoc();
$total_movies = $total_row['total'];
$total_pages = ceil($total_movies / $limit);

// Query to fetch movies by category, paginated
$movies = $conn->query("SELECT * FROM movies WHERE category = '$category' ORDER BY id DESC LIMIT $limit OFFSET $offset");
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
  background: var(--accent-color, #4ECDC4);
  color: #fff;
  font-weight: 600;
  border-radius: 20px;
  padding: 4px 14px;
  font-size: 0.75rem;
  box-shadow: 0 2px 8px rgba(76, 99, 255, 0.10);
}
.card-title {
  font-weight: 700;
  color: var(--dark-color, #232946);
}
.btn-view {
  background: var(--primary-gradient, linear-gradient(90deg, #6C63FF 0%, #4ECDC4 100%));
  color: #fff;
  border: none;
  border-radius: 50px;
  font-weight: 600;
  transition: background 0.2s;
}
.btn-view:hover {
  background: var(--secondary-color, #FF6B6B);
  color: #fff;
}
.pagination .page-link {
  color: var(--primary-color, #6C63FF);
  border-radius: 50px !important;
  margin: 0 3px;
  border: none;
}
.pagination .page-item.active .page-link {
  background: var(--primary-gradient, linear-gradient(90deg, #6C63FF 0%, #4ECDC4 100%));
  color: #fff;
  border: none;
}
</style>

<div class="container my-5">
  <h2 class="section-header mb-4">🎥 <?= htmlspecialchars($category) ?> Movies</h2>
  
  <!-- Movies grid -->
  <div class="row g-4">
    <?php if ($movies->num_rows > 0): ?>
      <?php while ($movie = $movies->fetch_assoc()): ?>
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
                <?php if (isset($movie['quality']) && $movie['quality']): ?>
                  <span class="badge bg-info float-end"><?= htmlspecialchars($movie['quality']) ?></span>
                <?php endif; ?>
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
              <h5 class="mb-1">No movies found</h5>
              <p class="mb-0">No movies found in this category. Please check back later or browse other categories.</p>
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
  
  <!-- Pagination -->
  <?php if ($total_pages > 1): ?>
    <nav class="mt-5">
      <ul class="pagination justify-content-center">
        <?php if ($page > 1): ?>
          <li class="page-item">
            <a class="page-link" href="?cat=<?= urlencode($category) ?>&page=<?= $page - 1 ?>" aria-label="Previous">
              <span aria-hidden="true">&laquo;</span>
            </a>
          </li>
        <?php endif; ?>
        <?php
        $start_page = max(1, min($page - 2, $total_pages - 4));
        $end_page = min($total_pages, $start_page + 4);
        if ($start_page > 1): ?>
          <li class="page-item"><a class="page-link" href="?cat=<?= urlencode($category) ?>&page=1">1</a></li>
          <?php if ($start_page > 2): ?>
            <li class="page-item disabled"><span class="page-link">...</span></li>
          <?php endif; ?>
        <?php endif; ?>
        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
          <li class="page-item <?= $i === $page ? 'active' : '' ?>">
            <a class="page-link" href="?cat=<?= urlencode($category) ?>&page=<?= $i ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
        <?php if ($end_page < $total_pages): ?>
          <?php if ($end_page < $total_pages - 1): ?>
            <li class="page-item disabled"><span class="page-link">...</span></li>
          <?php endif; ?>
          <li class="page-item"><a class="page-link" href="?cat=<?= urlencode($category) ?>&page=<?= $total_pages ?>"><?= $total_pages ?></a></li>
        <?php endif; ?>
        <?php if ($page < $total_pages): ?>
          <li class="page-item">
            <a class="page-link" href="?cat=<?= urlencode($category) ?>&page=<?= $page + 1 ?>" aria-label="Next">
              <span aria-hidden="true">&raquo;</span>
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </nav>
  <?php endif; ?>
</div>

<?php include("includes/footer.php"); ?>
