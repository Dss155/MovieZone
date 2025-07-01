<?php
session_start();
include("../includes/db.php");

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$movie = $stmt->get_result()->fetch_assoc();

if (!$movie) die("Movie not found.");

// Fetch categories for dropdown
$categories = [];
$cat_result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
if ($cat_result) while ($row = $cat_result->fetch_assoc()) $categories[] = $row;

// Fetch all mirrors for this movie
$mirrors = [];
$mstmt = $conn->prepare("SELECT * FROM movie_mirrors WHERE movie_id = ?");
$mstmt->bind_param("i", $id);
$mstmt->execute();
$mres = $mstmt->get_result();
while ($row = $mres->fetch_assoc()) $mirrors[] = $row;

$success = '';
$error = '';

// Handle form
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $cat = trim($_POST['category']);
    $size = floatval($_POST['size']);
    $cast = trim($_POST['cast']);
    $poster = $movie['poster'];

    // Handle poster upload
    if (!empty($_FILES['poster']['name'])) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
        $max_file_size = 2 * 1024 * 1024;
        $poster_tmp = $_FILES['poster']['tmp_name'];
        $poster_name = basename($_FILES['poster']['name']);
        $poster_type = mime_content_type($poster_tmp);
        $poster_size = $_FILES['poster']['size'];
        if (!in_array($poster_type, $allowed_types)) {
            $error = "Poster must be JPG, PNG, or WEBP.";
        } elseif ($poster_size > $max_file_size) {
            $error = "Poster image must be less than 2MB.";
        } else {
            $ext = pathinfo($poster_name, PATHINFO_EXTENSION);
            $unique_poster = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $poster_target = "../uploads/posters/" . $unique_poster;
            if (move_uploaded_file($poster_tmp, $poster_target)) {
                $poster = $unique_poster;
            } else {
                $error = "Failed to upload new poster image.";
            }
        }
    }

    // Mirror fields
    $mirror_ids    = $_POST['mirror_id'] ?? [];
    $mirror_labels = $_POST['mirror_label'] ?? [];
    $mirror_hosts  = $_POST['mirror_host'] ?? [];
    $mirror_links  = $_POST['mirror_link'] ?? [];
    $mirror_delete = $_POST['delete_mirror'] ?? [];

    if (!$error) {
        // Update movie
        $stmt = $conn->prepare("UPDATE movies SET title=?, description=?, category=?, size=?, cast=?, poster=? WHERE id=?");
        $stmt->bind_param("ssssssi", $title, $desc, $cat, $size, $cast, $poster, $id);
        if ($stmt->execute()) {
            // Update or add mirrors
            for ($i = 0; $i < count($mirror_labels); $i++) {
                $mlabel = trim($mirror_labels[$i]);
                $mhost  = trim($mirror_hosts[$i]);
                $mlink  = trim($mirror_links[$i]);
                $mid    = intval($mirror_ids[$i]);

                if (isset($mirror_delete[$i]) && $mirror_delete[$i] == 'on' && $mid > 0) {
                    // Delete this mirror
                    $delstmt = $conn->prepare("DELETE FROM movie_mirrors WHERE id=? AND movie_id=?");
                    $delstmt->bind_param("ii", $mid, $id);
                    $delstmt->execute();
                    continue;
                }
                if ($mlabel && $mhost && $mlink) {
                    if ($mid > 0) {
                        // Update existing mirror
                        $ustmt = $conn->prepare("UPDATE movie_mirrors SET mirror_label=?, host=?, download_link=? WHERE id=? AND movie_id=?");
                        $ustmt->bind_param("sssii", $mlabel, $mhost, $mlink, $mid, $id);
                        $ustmt->execute();
                    } else {
                        // New mirror
                        $istmt = $conn->prepare("INSERT INTO movie_mirrors (movie_id, host, mirror_label, download_link) VALUES (?, ?, ?, ?)");
                        $istmt->bind_param("isss", $id, $mhost, $mlabel, $mlink);
                        $istmt->execute();
                    }
                }
            }
            $success = "Movie and mirrors updated successfully!";
            // Refresh data
            $stmt = $conn->prepare("SELECT * FROM movies WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $movie = $stmt->get_result()->fetch_assoc();
            // Reload mirrors
            $mirrors = [];
            $mstmt = $conn->prepare("SELECT * FROM movie_mirrors WHERE movie_id = ?");
            $mstmt->bind_param("i", $id);
            $mstmt->execute();
            $mres = $mstmt->get_result();
            while ($row = $mres->fetch_assoc()) $mirrors[] = $row;
        } else {
            $error = "Update failed: " . $conn->error;
        }
    }
}

include("includes/header.php");
?>

<style>
.edit-movie-card {
    background: rgba(255,255,255,0.92);
    box-shadow: 0 8px 32px 0 rgba(127,127,213,0.11);
    border-radius: 18px;
    border: 1px solid rgba(127,127,213,0.08);
    padding: 32px 28px;
    margin-top: 24px;
}
.edit-movie-title {
    color: #7f7fd5;
    font-weight: 800;
    letter-spacing: 1px;
    margin-bottom: 1.5rem;
}
.form-label {
    color: #3b486b;
    font-weight: 500;
}
.btn-gradient {
    background: linear-gradient(90deg, #a5d8dd 0%, #7f7fd5 100%);
    color: #fff;
    font-weight: 700;
    border-radius: 50px;
    transition: background 0.2s;
}
.btn-gradient:hover {
    background: linear-gradient(90deg, #7f7fd5 0%, #a5d8dd 100%);
    color: #fff;
}
</style>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="edit-movie-title">Edit Movie</h3>
        <a href="dashboard.php" class="btn btn-outline-primary">Back to Dashboard</a>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="edit-movie-card">
                <form method="post" enctype="multipart/form-data" autocomplete="off">
                    <div class="mb-3">
                        <label class="form-label"><strong>Title</strong></label>
                        <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($movie['title']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><strong>Description</strong></label>
                        <textarea name="description" class="form-control"><?= htmlspecialchars($movie['description']) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><strong>Category</strong></label>
                        <select name="category" class="form-select" required>
                            <option value="">-- Select Category --</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= htmlspecialchars($category['name']) ?>" <?= ($movie['category'] === $category['name']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><strong>Size (GB)</strong></label>
                        <input type="number" step="0.01" name="size" class="form-control" required value="<?= htmlspecialchars($movie['size']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><strong>Cast</strong></label>
                        <input type="text" name="cast" class="form-control" value="<?= htmlspecialchars($movie['cast']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><strong>Poster Image (Optional)</strong></label>
                        <input type="file" name="poster" class="form-control">
                        <p class="mt-2">Current: <span class="badge bg-secondary"><?= htmlspecialchars($movie['poster']) ?></span></p>
                    </div>

                    <!-- Multi-mirror edit fields -->
                    <div class="mb-3">
                        <label class="form-label"><strong>Edit Download Mirrors</strong></label>
                        <div id="mirrors">
                            <?php foreach ($mirrors as $i => $mirror): ?>
                                <div class="row g-2 mb-2 mirror-row align-items-center">
                                    <input type="hidden" name="mirror_id[]" value="<?= $mirror['id'] ?>">
                                    <div class="col-md-3">
                                        <input type="text" class="form-control" name="mirror_label[]" required value="<?= htmlspecialchars($mirror['mirror_label']) ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-select" name="mirror_host[]" required>
                                            <option value="">Host</option>
                                            <option value="Google Drive" <?= $mirror['host']=='Google Drive'?'selected':'' ?>>Google Drive</option>
                                            <option value="Dropbox" <?= $mirror['host']=='Dropbox'?'selected':'' ?>>Dropbox</option>
                                            <option value="Mediafire" <?= $mirror['host']=='Mediafire'?'selected':'' ?>>Mediafire</option>
                                            <option value="TeraBox" <?= $mirror['host']=='TeraBox'?'selected':'' ?>>TeraBox</option>
                                            <option value="Other" <?= $mirror['host']=='Other'?'selected':'' ?>>Other</option>
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <input type="url" class="form-control" name="mirror_link[]" required value="<?= htmlspecialchars($mirror['download_link']) ?>">
                                    </div>
                                    <div class="col-md-1">
                                        <input type="checkbox" name="delete_mirror[<?= $i ?>]" title="Delete this mirror">
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <!-- New mirror row template -->
                            <div class="row g-2 mb-2 mirror-row">
                                <input type="hidden" name="mirror_id[]" value="0">
                                <div class="col-md-3">
                                    <input type="text" class="form-control" name="mirror_label[]" placeholder="Label (e.g. Mirror 2, Dropbox)">
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" name="mirror_host[]">
                                        <option value="">Host</option>
                                        <option value="Google Drive">Google Drive</option>
                                        <option value="Dropbox">Dropbox</option>
                                        <option value="Mediafire">Mediafire</option>
                                        <option value="TeraBox">TeraBox</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <input type="url" class="form-control" name="mirror_link[]" placeholder="Paste download link here">
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="addMirrorRow()">+ Add Mirror</button>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button class="btn btn-gradient">Update Movie</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function addMirrorRow() {
    const row = document.createElement('div');
    row.className = 'row g-2 mb-2 mirror-row';
    row.innerHTML = `
        <input type="hidden" name="mirror_id[]" value="0">
        <div class="col-md-3">
            <input type="text" class="form-control" name="mirror_label[]" placeholder="Label (e.g. Mirror 2, Dropbox)">
        </div>
        <div class="col-md-3">
            <select class="form-select" name="mirror_host[]">
                <option value="">Host</option>
                <option value="Google Drive">Google Drive</option>
                <option value="Dropbox">Dropbox</option>
                <option value="Mediafire">Mediafire</option>
                <option value="TeraBox">TeraBox</option>
                <option value="Other">Other</option>
            </select>
        </div>
        <div class="col-md-6">
            <input type="url" class="form-control" name="mirror_link[]" placeholder="Paste download link here">
        </div>
    `;
    document.getElementById('mirrors').appendChild(row);
}
</script>

<?php include("includes/footer.php"); ?>
