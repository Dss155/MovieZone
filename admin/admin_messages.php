<?php
session_start();
include("../includes/db.php");

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$page_title = "User Messages - Admin | MovieZone";
include("includes/header.php");

// Pagination and search
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = trim($_GET['search'] ?? '');
$where = '';
$params = [];
$param_types = '';

if ($search) {
    $where = "WHERE name LIKE ? OR email LIKE ? OR message LIKE ?";
    $param_types = 'sss';
    $search_param = "%$search%";
    $params = [$search_param, $search_param, $search_param];
}

// Count total
if ($where) {
    $count_stmt = $conn->prepare("SELECT COUNT(*) FROM contact_messages $where");
    $count_stmt->bind_param($param_types, ...$params);
} else {
    $count_stmt = $conn->prepare("SELECT COUNT(*) FROM contact_messages");
}
$count_stmt->execute();
$count_stmt->bind_result($total_messages);
$count_stmt->fetch();
$count_stmt->close();
$total_pages = max(1, ceil($total_messages / $limit));

// Fetch messages
if ($where) {
    $stmt = $conn->prepare("SELECT id, name, email, message, created_at FROM contact_messages $where ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $param_types .= 'ii';
    $params[] = $limit;
    $params[] = $offset;
    $stmt->bind_param($param_types, ...$params);
} else {
    $stmt = $conn->prepare("SELECT id, name, email, message, created_at FROM contact_messages ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->bind_param('ii', $limit, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<style>
  body {
    background: #f4f7fa;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #2c3e50;
  }
  .container {
    max-width: 1100px;
  }
  h2 {
    font-weight: 700;
    color: #34495e;
    margin-bottom: 1.5rem;
  }
  .search-bar {
    max-width: 400px;
  }
  .search-bar input {
    border-radius: 30px 0 0 30px;
    border: 1px solid #ced4da;
    padding-left: 20px;
    height: 42px;
  }
  .search-bar button {
    border-radius: 0 30px 30px 0;
    height: 42px;
    border: none;
    background: #2980b9;
    color: white;
    font-weight: 600;
    padding: 0 25px;
    transition: background 0.3s ease;
  }
  .search-bar button:hover {
    background: #3498db;
  }
  table {
    border-collapse: separate;
    border-spacing: 0 15px;
    width: 100%;
  }
  thead tr {
    background: transparent;
  }
  thead th {
    text-align: left;
    font-weight: 600;
    font-size: 0.9rem;
    color: #7f8c8d;
    padding-left: 15px;
  }
  tbody tr {
    background: white;
    box-shadow: 0 2px 6px rgb(0 0 0 / 0.1);
    border-radius: 12px;
    transition: box-shadow 0.3s ease;
  }
  tbody tr:hover {
    box-shadow: 0 6px 12px rgb(0 0 0 / 0.15);
  }
  tbody td {
    padding: 18px 15px;
    vertical-align: middle;
    font-size: 0.95rem;
    color: #34495e;
  }
  tbody td a {
    color: #2980b9;
    text-decoration: none;
  }
  tbody td a:hover {
    text-decoration: underline;
  }
  .btn-view, .btn-delete {
    border-radius: 30px;
    padding: 6px 18px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: background 0.3s ease;
  }
  .btn-view {
    background: #27ae60;
    color: white;
  }
  .btn-view:hover {
    background: #2ecc71;
  }
  .btn-delete {
    background: #e74c3c;
    color: white;
    margin-left: 8px;
  }
  .btn-delete:hover {
    background: #c0392b;
  }
  /* Modal styling */
  .modal-content {
    border-radius: 12px;
    padding: 20px;
    font-size: 1rem;
    color: #2c3e50;
  }
  .modal-header {
    border-bottom: none;
  }
  .modal-title {
    font-weight: 700;
    font-size: 1.3rem;
  }
  .modal-body p {
    white-space: pre-line;
    line-height: 1.5;
  }
  /* Pagination */
  .pagination {
    justify-content: center;
    margin-top: 25px;
  }
  .page-link {
    border-radius: 50% !important;
    width: 38px;
    height: 38px;
    line-height: 38px;
    text-align: center;
    padding: 0;
    margin: 0 5px;
    font-weight: 600;
    color: #2980b9;
    border: 1px solid #2980b9;
    transition: all 0.3s ease;
  }
  .page-link:hover {
    background: #2980b9;
    color: white;
  }
  .page-item.active .page-link {
    background: #2980b9;
    color: white;
    border-color: #2980b9;
  }
</style>

<div class="container my-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>User Contact Messages</h2>
    <a href="dashboard.php" class="btn btn-outline-primary">Back to Dashboard</a>
  </div>

  <!-- Search bar -->
  <form method="get" class="d-flex mb-4">
    <div class="search-bar d-flex w-auto">
      <input type="text" name="search" placeholder="Search messages..." value="<?= htmlspecialchars($search) ?>" aria-label="Search messages">
      <button type="submit">Search</button>
    </div>
    <a href="admin_messages.php" class="btn btn-secondary ms-3">Reset</a>
  </form>

  <?php if ($total_messages == 0): ?>
    <div class="alert alert-info">No messages found.</div>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Email</th>
          <th>Message Preview</th>
          <th>Date Sent</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php $count = $offset + 1; ?>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $count++ ?></td>
          <td><?= htmlspecialchars($row['name']) ?></td>
          <td><a href="mailto:<?= htmlspecialchars($row['email']) ?>"><?= htmlspecialchars($row['email']) ?></a></td>
          <td><?= htmlspecialchars(mb_strimwidth($row['message'], 0, 60, '...')) ?></td>
          <td><?= date("M d, Y H:i", strtotime($row['created_at'])) ?></td>
          <td>
            <button class="btn btn-view" data-bs-toggle="modal" data-bs-target="#messageModal<?= $row['id'] ?>">View</button>
            <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this message?');">
              <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
              <button type="submit" class="btn btn-delete">Delete</button>
            </form>
          </td>
        </tr>

        <!-- Modal -->
        <div class="modal fade" id="messageModal<?= $row['id'] ?>" tabindex="-1" aria-labelledby="modalLabel<?= $row['id'] ?>" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="modalLabel<?= $row['id'] ?>">Message from <?= htmlspecialchars($row['name']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <p><strong>Email:</strong> <a href="mailto:<?= htmlspecialchars($row['email']) ?>"><?= htmlspecialchars($row['email']) ?></a></p>
                <p><strong>Sent on:</strong> <?= date("M d, Y H:i", strtotime($row['created_at'])) ?></p>
                <hr>
                <p><?= nl2br(htmlspecialchars($row['message'])) ?></p>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>
        <?php endwhile; ?>
      </tbody>
    </table>

    <!-- Pagination -->
    <nav aria-label="Page navigation">
      <ul class="pagination">
        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
          <a class="page-link" href="?page=<?= max(1, $page - 1) ?>&search=<?= urlencode($search) ?>" aria-label="Previous">&laquo;</a>
        </li>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
          <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
          <a class="page-link" href="?page=<?= min($total_pages, $page + 1) ?>&search=<?= urlencode($search) ?>" aria-label="Next">&raquo;</a>
        </li>
      </ul>
    </nav>
  <?php endif; ?>
</div>

<?php
$stmt->close();
include("includes/footer.php");
?>
