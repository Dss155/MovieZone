<?php
include("includes/db.php");

if (!isset($_GET['mirror_id']) || !is_numeric($_GET['mirror_id'])) {
    die("Invalid download request.");
}

$mirror_id = (int)$_GET['mirror_id'];
$stmt = $conn->prepare("SELECT m.title, m.id as movie_id, mm.host, mm.mirror_label FROM movie_mirrors mm JOIN movies m ON mm.movie_id = m.id WHERE mm.id = ?");
$stmt->bind_param("i", $mirror_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    die("Mirror not found.");
}

$row = $res->fetch_assoc();
$title = $row['title'];
$host = $row['host'];
$mirror_label = $row['mirror_label'];
$movie_id = $row['movie_id'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preparing Download - <?= htmlspecialchars($title) ?> | MovieZone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #232946;
            font-family: 'Segoe UI', Arial, sans-serif;
            min-height: 100vh;
            padding: 20px 0;
        }

        .download-container {
            max-width: 600px;
            margin: 0 auto;
        }

        .download-box {
            padding: 40px 35px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .movie-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .mirror-info {
            background: #e8f4fd;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            border-left: 4px solid #3498db;
        }

        .countdown-section {
            margin: 30px 0;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
        }

        .countdown-text {
            font-size: 1.1rem;
            margin-top: 15px;
            color: #555;
        }

        .counter-number {
            font-weight: bold;
            color: #e74c3c;
            font-size: 1.3rem;
        }

        #downloadBtn {
            display: none;
            padding: 12px 30px;
            font-size: 1.1rem;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        #downloadBtn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        }

        .navigation-buttons {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .nav-btn {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .nav-btn:hover {
            transform: translateY(-1px);
            text-decoration: none;
        }

        .btn-back {
            background-color: #6c757d;
            color: white;
            border: 2px solid #6c757d;
        }

        .btn-back:hover {
            background-color: #5a6268;
            border-color: #5a6268;
            color: white;
        }

        .btn-home {
            background-color: #007bff;
            color: white;
            border: 2px solid #007bff;
        }

        .btn-home:hover {
            background-color: #0056b3;
            border-color: #0056b3;
            color: white;
        }

        .progress-container {
            margin: 20px 0;
        }

        .progress {
            height: 8px;
            border-radius: 10px;
            background-color: #e9ecef;
        }

        .progress-bar {
            border-radius: 10px;
            transition: width 1s ease;
        }

        @media (max-width: 576px) {
            .download-box {
                margin: 20px 15px;
                padding: 25px 20px;
            }

            .movie-title {
                font-size: 1.5rem;
            }

            .navigation-buttons {
                flex-direction: column;
                align-items: center;
            }

            .nav-btn {
                width: 100%;
                max-width: 200px;
                justify-content: center;
            }
        }

        #downloadBtn {
            z-index: 9999;
            position: relative;
        }
    </style>
</head>

<body>
    <div class="download-container">
        <div class="download-box text-center">
            <div class="mb-4">
                <i class="fas fa-film fa-3x text-primary mb-3"></i>
                <h2 class="movie-title"><?= htmlspecialchars($title) ?></h2>
            </div>

            <div class="mirror-info">
                <h6 class="mb-2"><i class="fas fa-server me-2"></i>Download Mirror</h6>
                <p class="mb-0">
                    <strong><?= htmlspecialchars($mirror_label) ?></strong>
                    <span class="text-muted">(<?= htmlspecialchars($host) ?>)</span>
                </p>
            </div>

            <div id="waitText" class="countdown-section">
                <div class="spinner-border text-warning mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="countdown-text">
                    This Movie is uploaded by <strong>Divyesh</strong>.<br>
                    Preparing your download...<br>
                    Please wait <span id="counter" class="counter-number">10</span> seconds
                </p>
                <div class="progress-container">
                    <div class="progress">
                        <div id="progressBar" class="progress-bar bg-success" role="progressbar" style="width: 0%"></div>
                    </div>
                </div>
            </div>

            <a id="downloadBtn" href="download.php?mirror_id=<?= $mirror_id ?>" class="btn btn-success btn-lg">
                <i class="fas fa-download me-2"></i>Click here to Download
            </a>

            <div class="navigation-buttons">
                <a href="javascript:history.back()" class="nav-btn btn-back">
                    <i class="fas fa-arrow-left"></i>
                    Go Back
                </a>
                <a href="index.php" class="nav-btn btn-home">
                    <i class="fas fa-home"></i>
                    Home
                </a>
            </div>
            <div class="text-muted mt-3">
                <small>Special Thanks to <strong>Divyesh</strong> for giving this movie.</small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let count = 10;

        function startCountdown() {
            const counter = document.getElementById("counter");
            const button = document.getElementById("downloadBtn");
            const waitText = document.getElementById("waitText");
            const progressBar = document.getElementById("progressBar");

            const interval = setInterval(() => {
                count--;
                counter.innerText = count;

                // Update progress bar
                const progress = ((10 - count) / 10) * 100;
                progressBar.style.width = progress + "%";

                if (count <= 0) {
                    clearInterval(interval);
                    button.style.display = "inline-block";
                    waitText.style.display = "none";
                }
            }, 1000);
        }

        document.addEventListener("DOMContentLoaded", function() {
            startCountdown();

            // Fix: force enable click on mobile
            const downloadBtn = document.getElementById("downloadBtn");
            downloadBtn.addEventListener("touchstart", function() {
                window.location.href = downloadBtn.href;
            });

            // Also support click
            downloadBtn.addEventListener("click", function(e) {
                // Optional: track
                console.log("Clicked download button");
            });
        });
    </script>

</body>

</html>