<?php
session_start();
include("../includes/db.php");

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

// --- CSRF Protection ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$success = '';
$error = '';

// Fetch categories for dropdown
$categories = [];
$cat_result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
if ($cat_result) {
    while ($row = $cat_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// --- Handle form submission ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // CSRF check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid CSRF token. Please refresh and try again.";
    } else {
        $title = trim($_POST['title']);
        $desc = trim($_POST['description']);
        $cat = trim($_POST['category']);
        $size = floatval($_POST['size']);
        $cast = trim($_POST['cast']);

        // Mirror arrays
        $mirror_labels = $_POST['mirror_label'];
        $mirror_hosts = $_POST['mirror_host'];
        $mirror_links = $_POST['mirror_link'];

        // --- Validate required fields ---
        if (
            empty($title) || empty($cat) || $size <= 0 ||
            empty($mirror_labels[0]) || empty($mirror_hosts[0]) || empty($mirror_links[0]) ||
            empty($_FILES['poster']['name'])
        ) {
            $error = "Please fill all required fields, add at least one mirror, and ensure size is positive.";
        } else {
            // --- File upload validation ---
            $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
            $max_file_size = 2 * 1024 * 1024; // 2MB

            $poster_tmp = $_FILES['poster']['tmp_name'];
            $poster_name = basename($_FILES['poster']['name']);
            $poster_type = mime_content_type($poster_tmp);
            $poster_size = $_FILES['poster']['size'];

            if (!in_array($poster_type, $allowed_types)) {
                $error = "Poster must be a JPG, PNG, or WEBP image.";
            } elseif ($poster_size > $max_file_size) {
                $error = "Poster image must be less than 2MB.";
            } else {
                // --- Unique filename ---
                $ext = pathinfo($poster_name, PATHINFO_EXTENSION);
                $unique_poster = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $poster_target = "../uploads/posters/" . $unique_poster;

                if (move_uploaded_file($poster_tmp, $poster_target)) {
                    // --- Insert into movies table ---
                    $stmt = $conn->prepare("INSERT INTO movies (title, description, category, size, cast, poster) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssssss", $title, $desc, $cat, $size, $cast, $unique_poster);
                    if ($stmt->execute()) {
                        $movie_id = $conn->insert_id;

                        // --- Insert mirrors ---
                        $mirror_stmt = $conn->prepare("INSERT INTO movie_mirrors (movie_id, host, mirror_label, download_link) VALUES (?, ?, ?, ?)");
                        for ($i = 0; $i < count($mirror_labels); $i++) {
                            $mlabel = trim($mirror_labels[$i]);
                            $mhost = trim($mirror_hosts[$i]);
                            $mlink = trim($mirror_links[$i]);
                            if ($mlabel && $mhost && $mlink) {
                                $mirror_stmt->bind_param("isss", $movie_id, $mhost, $mlabel, $mlink);
                                $mirror_stmt->execute();
                            }
                        }

                        $success = "Movie and mirrors added successfully!";
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    } else {
                        $error = "Database error: " . $conn->error;
                        @unlink($poster_target);
                    }
                } else {
                    $error = "Failed to upload poster image.";
                }
            }
        }
    }
}

include("includes/header.php");
?>

<style>
.add-movie-card {
    background: rgba(255,255,255,0.9);
    box-shadow: 0 8px 32px 0 rgba(127,127,213,0.11);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    border-radius: 18px;
    border: 1px solid rgba(127,127,213,0.08);
    padding: 32px 28px;
    margin-top: 24px;
}
.add-movie-title {
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
        <h3 class="add-movie-title">Add New Movie</h3>
        <a href="dashboard.php" class="btn btn-outline-primary">Back to Dashboard</a>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="add-movie-card">
                <form method="post" enctype="multipart/form-data" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <div class="mb-3">
                        <label class="form-label"><strong>Title</strong></label>
                        <input type="text" name="title" class="form-control" required value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><strong>Description</strong></label>
                        <textarea name="description" class="form-control" rows="3"><?= isset($_POST['description']) ? htmlspecialchars($_POST['description']) : '' ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><strong>Category</strong></label>
                        <select name="category" class="form-select" required>
                            <option value="">-- Select Category --</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= htmlspecialchars($category['name']) ?>" 
                                    <?= (isset($_POST['category']) && $_POST['category'] === $category['name']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><strong>Size (GB)</strong></label>
                        <input type="number" step="0.01" min="0.01" name="size" class="form-control" required value="<?= isset($_POST['size']) ? htmlspecialchars($_POST['size']) : '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><strong>Cast</strong></label>
                        <input type="text" name="cast" class="form-control" value="<?= isset($_POST['cast']) ? htmlspecialchars($_POST['cast']) : '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><strong>Poster Image</strong> <small class="text-muted">(JPG, PNG, WEBP, max 2MB)</small></label>
                        <input type="file" name="poster" class="form-control" required accept="image/jpeg,image/png,image/webp">
                    </div>

                    <!-- Multi-mirror input fields -->
                    <div class="mb-3">
                        <label class="form-label"><strong>Download Mirrors</strong> <small class="text-muted">(Add at least one)</small></label>
                        <div id="mirrors">
                            <div class="row g-2 mb-2 mirror-row">
                                <div class="col-md-3">
                                    <input type="text" class="form-control" name="mirror_label[]" placeholder="Label (e.g. Mirror 1, Google Drive)" required>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" name="mirror_host[]" required>
                                        <option value="">Host</option>
                                        <option value="Google Drive">Google Drive</option>
                                        <option value="Dropbox">Dropbox</option>
                                        <option value="Mediafire">Mediafire</option>
                                        <option value="TeraBox">TeraBox</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <input type="url" class="form-control" name="mirror_link[]" placeholder="Paste download link here" required>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="addMirrorRow()">+ Add Mirror</button>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button class="btn btn-gradient">Add Movie</button>
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
        <div class="col-md-3">
            <input type="text" class="form-control" name="mirror_label[]" placeholder="Label (e.g. Mirror 2, Dropbox)" required>
        </div>
        <div class="col-md-3">
            <select class="form-select" name="mirror_host[]" required>
                <option value="">Host</option>
                <option value="Google Drive">Google Drive</option>
                <option value="Dropbox">Dropbox</option>
                <option value="Mediafire">Mediafire</option>
                <option value="TeraBox">TeraBox</option>
                <option value="Other">Other</option>
            </select>
        </div>
        <div class="col-md-6">
            <input type="url" class="form-control" name="mirror_link[]" placeholder="Paste download link here" required>
        </div>
    `;
    document.getElementById('mirrors').appendChild(row);
}
</script>

<?php include("includes/footer.php"); ?>
