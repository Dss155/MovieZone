<?php
// download.php
include("includes/db.php");

if (!isset($_GET['mirror_id']) || !is_numeric($_GET['mirror_id'])) {
    die("Invalid download request.");
}

$mirror_id = (int)$_GET['mirror_id'];
$stmt = $conn->prepare("SELECT m.title, mm.host, mm.download_link, mm.movie_id FROM movie_mirrors mm JOIN movies m ON mm.movie_id = m.id WHERE mm.id = ?");
$stmt->bind_param("i", $mirror_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    die("Mirror not found.");
}

$row = $res->fetch_assoc();
$title = $row['title'];
$host = $row['host'];
$download_url = trim($row['download_link']);
$movie_id = $row['movie_id'];

// Validate URL
if (empty($download_url) || !preg_match('/^https?:\/\//i', $download_url)) {
    die("Download link not available.");
}

// Universal direct link converter and user warning
function get_direct_download_link($url, $host, &$note = '') {
    switch (strtolower($host)) {
        case 'google drive':
            if (preg_match('/drive\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
                $note = "Google Drive: For files over 100MB, you may see a virus scan warning. For popular files, you may see a quota error. Try another mirror if blocked.";
                return 'https://drive.google.com/uc?export=download&id=' . $matches[1];
            }
            break;
        case 'dropbox':
            $note = "Dropbox: If the link is disabled, the daily bandwidth limit has been reached. Try another mirror.";
            $url = preg_replace('/(\?dl=\d|\?raw=1)?$/', '', $url);
            return $url . '?dl=1';
        case 'mediafire':
            $note = "Mediafire: You may see ads and must click the download button on the Mediafire page.";
            return $url;
        case 'terabox':
            $note = "TeraBox: Users must log in to download and may see ads. Max file size 4GB (free).";
            return $url;
        default:
            $note = "External host: Download experience may vary.";
            return $url;
    }
    return $url;
}

$note = '';
$final_url = get_direct_download_link($download_url, $host, $note);

// Update download count
$update = $conn->prepare("UPDATE movies SET download_count = download_count + 1 WHERE id = ?");
$update->bind_param("i", $movie_id);
$update->execute();

// Redirect after 2 seconds, show spinner and fallback
header("Content-Type: text/html; charset=UTF-8");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Redirecting to Download - <?= htmlspecialchars($title) ?> | MovieZone</title>
    <meta http-equiv="refresh" content="2;url=<?= htmlspecialchars($final_url) ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { text-align:center; padding-top:100px; font-family:'Segoe UI',sans-serif; background: #f2f6ff; color:#232946; }
        .spinner-border { width: 3rem; height: 3rem; color: #6C63FF; margin-bottom: 1.5rem; }
        .brand { font-size: 2rem; font-weight: 900; color: #6C63FF; margin-bottom: 1rem; }
        .note { color: #FF6B6B; margin-top: 1rem; }
        .download-title { font-size: 1.2rem; font-weight: 700; margin-bottom: 0.5rem; }
    </style>
</head>
<body>
    <div class="brand">MovieZone</div>
    <div class="download-title">Downloading: <?= htmlspecialchars($title) ?> (<?= htmlspecialchars($host) ?>)</div>
    <div>
        <div class="spinner-border" role="status"></div>
        <h2>Redirecting to download...</h2>
        <p>If not redirected, <a href="<?= htmlspecialchars($final_url) ?>">click here</a>.</p>
        <?php if ($note): ?>    
            <div class="note"><?= htmlspecialchars($note) ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
exit;
?>
