<?php
include("includes/db.php");
$page_title = "Home - MovieZone";
include("includes/header.php");

$limit = 8;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Total movie count
$result = $conn->query("SELECT COUNT(*) AS total FROM movies");
$row = $result->fetch_assoc();
$total_movies = $row['total'];
$total_pages = ceil($total_movies / $limit);

// Movies for current page
$query = "SELECT * FROM movies ORDER BY id DESC LIMIT $limit OFFSET $offset";
$movies = $conn->query($query);
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
.pagination .page-link {
  color: var(--primary-color);
  border-radius: 50px !important;
  margin: 0 3px;
  border: none;
}
.pagination .page-item.active .page-link {
  background: var(--primary-gradient);
  color: #fff;
  border: none;
}
</style>

<div class="container my-5">
  <h2 class="section-header mb-4">Latest Movies</h2>
  
  <div class="row g-4">
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
            </p>
            <a href="movie.php?id=<?= $movie['id'] ?>" class="btn btn-view w-100 mt-3">
              <i class="fas fa-eye me-2"></i>View & Download
            </a>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>

  <!-- Pagination -->
  <nav>
    <ul class="pagination justify-content-center">
      <?php if ($page > 1): ?>
        <li class="page-item">
          <a class="page-link" href="?page=<?= $page - 1 ?>" aria-label="Previous">
            <span aria-hidden="true">&laquo;</span>
          </a>
        </li>
      <?php endif; ?>

      <?php 
      // Show limited page numbers with ellipsis
      $start_page = max(1, min($page - 2, $total_pages - 4));
      $end_page = min($total_pages, $start_page + 4);
      
      if ($start_page > 1): ?>
        <li class="page-item"><a class="page-link" href="?page=1">1</a></li>
        <?php if ($start_page > 2): ?>
          <li class="page-item disabled"><span class="page-link">...</span></li>
        <?php endif; ?>
      <?php endif; ?>
      
      <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
          <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>
      
      <?php if ($end_page < $total_pages): ?>
        <?php if ($end_page < $total_pages - 1): ?>
          <li class="page-item disabled"><span class="page-link">...</span></li>
        <?php endif; ?>
        <li class="page-item"><a class="page-link" href="?page=<?= $total_pages ?>"><?= $total_pages ?></a></li>
      <?php endif; ?>

      <?php if ($page < $total_pages): ?>
        <li class="page-item">
          <a class="page-link" href="?page=<?= $page + 1 ?>" aria-label="Next">
            <span aria-hidden="true">&raquo;</span>
          </a>
        </li>
      <?php endif; ?>
    </ul>
  </nav>
</div>

<?php
include("includes/footer.php");
?>
