<?php
session_start();
include("../includes/db.php");

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

// CSRF protection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }
    $id = intval($_POST['id']);

    // Fetch poster filename to delete the image
    $stmt = $conn->prepare("SELECT poster FROM movies WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $poster = '';
    if ($row = $result->fetch_assoc()) {
        $poster = $row['poster'];
    }

    // Delete the movie
    $del = $conn->prepare("DELETE FROM movies WHERE id = ?");
    $del->bind_param("i", $id);
    if ($del->execute()) {
        // Delete poster file if exists
        if ($poster && file_exists("../uploads/posters/" . $poster)) {
            @unlink("../uploads/posters/" . $poster);
        }
        $success = "Movie deleted successfully!";
    } else {
        $error = "Failed to delete movie.";
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    // Show confirmation form
    $id = intval($_GET['id']);
    // Fetch movie info for confirmation
    $stmt = $conn->prepare("SELECT title FROM movies WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $movie = $stmt->get_result()->fetch_assoc();

    // Generate CSRF token
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
} else {
    header("Location: dashboard.php");
    exit;
}

include("includes/header.php");
?>

<div class="container" style="max-width:500px;">
    <?php if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($movie['title'])): ?>
        <div class="card mt-5 p-4 shadow">
            <h4 class="mb-3 text-danger">Confirm Delete</h4>
            <p>Are you sure you want to delete <strong><?= htmlspecialchars($movie['title']) ?></strong>?</p>
            <form method="post">
                <input type="hidden" name="id" value="<?= $id ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <div class="d-flex justify-content-between">
                    <a href="dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-danger">Yes, Delete</button>
                </div>
            </form>
        </div>
    <?php elseif (isset($success)): ?>
        <div class="alert alert-success mt-5"><?= htmlspecialchars($success) ?></div>
        <a href="dashboard.php" class="btn btn-primary mt-2">Back to Dashboard</a>
        <script>
            setTimeout(function(){ window.location = 'dashboard.php'; }, 1500);
        </script>
    <?php elseif (isset($error)): ?>
        <div class="alert alert-danger mt-5"><?= htmlspecialchars($error) ?></div>
        <a href="dashboard.php" class="btn btn-primary mt-2">Back to Dashboard</a>
    <?php endif; ?>
</div>

<?php include("includes/footer.php"); ?>
