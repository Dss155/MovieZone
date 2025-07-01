<?php
session_start();
include("../includes/db.php");

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

// --- Fix: Whitelist allowed sort options ---
$allowed_sorts = [
    'uploaded_on DESC', 'uploaded_on ASC',
    'category ASC', 'size DESC', 'size ASC'
];
$sort = $_GET['sort'] ?? 'uploaded_on DESC';
if (!in_array($sort, $allowed_sorts)) {
    $sort = 'uploaded_on DESC';
}

// --- Fix: Sanitize page number ---
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$limit = 6;
$offset = ($page - 1) * $limit;

// --- Fix: Use prepared statements for search ---
$search = trim($_GET['search'] ?? '');
$search_sql = '';
$search_param = '';
if ($search) {
    $search_sql = "WHERE title LIKE ? OR category LIKE ?";
    $search_param = "%$search%";
}

// Analytics
$total_movies = $conn->query("SELECT COUNT(*) AS total FROM movies")->fetch_assoc()['total'];
$total_categories = $conn->query("SELECT COUNT(DISTINCT category) AS total FROM movies")->fetch_assoc()['total'];

// Count total for pagination (with search)
if ($search) {
    $count_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM movies $search_sql");
    $count_stmt->bind_param("ss", $search_param, $search_param);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_rows = $count_result->fetch_assoc()['total'];
    $count_stmt->close();
} else {
    $count_result = $conn->query("SELECT COUNT(*) AS total FROM movies");
    $total_rows = $count_result->fetch_assoc()['total'];
}
$total_pages = ceil($total_rows / $limit);

// --- Fix: Pagination edge case, redirect if page too high ---
if ($page > $total_pages && $total_pages > 0) {
    header("Location: ?search=" . urlencode($search) . "&sort=" . urlencode($sort) . "&page=$total_pages");
    exit;
}

// Fetch movies (with search and sort)
if ($search) {
    $stmt = $conn->prepare("SELECT * FROM movies $search_sql ORDER BY $sort LIMIT ? OFFSET ?");
    $stmt->bind_param("ssii", $search_param, $search_param, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    $stmt = $conn->prepare("SELECT * FROM movies ORDER BY $sort LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
}

include("includes/header.php");
?>

<style>
.glass-card {
    background: rgba(255,255,255,0.82);
    box-shadow: 0 8px 32px 0 rgba(127,127,213,0.11);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    border-radius: 18px;
    border: 1px solid rgba(127,127,213,0.10);
    color: #3b486b;
}
.analytics-card {
    background: linear-gradient(135deg, #a5d8dd 0%, #7f7fd5 100%);
    color: #3b486b;
    border-radius: 18px;
    box-shadow: 0 4px 24px rgba(127,127,213,0.13);
}
.analytics-card .icon {
    font-size: 2.5rem;
    opacity: 0.8;
    color: #7f7fd5;
}
.movie-card {
    border-radius: 18px;
    box-shadow: 0 4px 24px rgba(127,127,213,0.09);
    border: none;
    background: rgba(255,255,255,0.95);
    transition: transform 0.2s, box-shadow 0.2s;
}
.movie-card:hover {
    transform: scale(1.03);
    box-shadow: 0 10px 32px rgba(127,127,213,0.15);
}
.movie-card img {
    height: 200px;
    object-fit: cover;
    border-radius: 14px 14px 0 0;
}
.btn-view, .btn-outline-warning, .btn-outline-danger {
    border-radius: 50px;
    font-weight: 600;
}
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold" style="color:#7f7fd5;">Dashboard</h2>
    </div>

    <!-- Analytics -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="analytics-card p-4 text-center shadow-sm glass-card">
                <div class="icon mb-2"><i class="bi bi-film"></i></div>
                <h4 class="fw-bold mb-0"><?= $total_movies ?></h4>
                <div>Total Movies</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="analytics-card p-4 text-center shadow-sm glass-card" style="background: linear-gradient(135deg, #e0eafe 0%, #a5d8dd 100%);">
                <div class="icon mb-2"><i class="bi bi-tags"></i></div>
                <h4 class="fw-bold mb-0"><?= $total_categories ?></h4>
                <div>Total Categories</div>
            </div>
        </div>
        <div class="col-md-3">
    <div class="analytics-card p-4 text-center shadow-sm glass-card">
        <div class="icon mb-2"><i class="bi bi-envelope"></i></div>
        <h4 class="fw-bold mb-0">
            <a href="admin_messages.php" class="stretched-link text-decoration-none" style="color:inherit;">
                User Messages
            </a>
        </h4>
        <div>View &amp; Manage</div>
    </div>
</div>
    </div>
        <!-- Add more analytics cards here if needed -->
    </div>

    <!-- Controls -->
    <form method="get" class="row g-2 mb-4">
        <div class="col-md-4">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control glass-card" placeholder="Search title or category">
        </div>
        <div class="col-md-4">
            <select name="sort" class="form-select glass-card">
                <option value="uploaded_on DESC" <?= $sort == 'uploaded_on DESC' ? 'selected' : '' ?>>Newest</option>
                <option value="uploaded_on ASC" <?= $sort == 'uploaded_on ASC' ? 'selected' : '' ?>>Oldest</option>
                <option value="category ASC" <?= $sort == 'category ASC' ? 'selected' : '' ?>>Category A-Z</option>
                <option value="size DESC" <?= $sort == 'size DESC' ? 'selected' : '' ?>>Size (Big to Small)</option>
                <option value="size ASC" <?= $sort == 'size ASC' ? 'selected' : '' ?>>Size (Small to Big)</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-view w-100" style="background: linear-gradient(90deg, #a5d8dd 0%, #7f7fd5 100%); color:#fff;">Filter</button>
        </div>
        <div class="col-md-2">
            <a href="add_movie.php" class="btn btn-success w-100">+ Add Movie</a>
        </div>
    </form>

    <!-- Movie Cards -->
    <div class="row">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($movie = $result->fetch_assoc()): ?>
            <div class="col-md-4 mb-4">
                <div class="card movie-card glass-card h-100">
                    <img src="../uploads/posters/<?= htmlspecialchars($movie['poster']) ?>" class="card-img-top" alt="Poster for <?= htmlspecialchars($movie['title']) ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($movie['title']) ?></h5>
                        <p class="card-text mb-2">
                            <span class="badge bg-primary"><?= htmlspecialchars($movie['category']) ?></span>
                            <span class="badge bg-info ms-2"><?= htmlspecialchars($movie['size']) ?> GB</span>
                        </p>
                        <div class="d-flex justify-content-between">
                            <a href="edit_movie.php?id=<?= $movie['id'] ?>" class="btn btn-outline-warning "><i class="bi bi-pencil"></i> Edit</a>
                            <a href="delete_movie.php?id=<?= $movie['id'] ?>" class="btn btn-outline-danger"><i class="bi bi-trash"></i> Delete</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center text-muted mb-4">
                <div class="alert alert-info">No movies found.</div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <nav>
        <ul class="pagination justify-content-center mt-4">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?search=<?= urlencode($search) ?>&sort=<?= urlencode($sort) ?>&page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<?php include("includes/footer.php"); ?>
