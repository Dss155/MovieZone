<?php
session_start();
include("../includes/db.php");


if (isset($_SESSION['admin_logged_in'])) {
    header("Location: dashboard.php");
    exit;
}


$error = "";
$success = "";

// --- CSRF Protection ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// --- Handle login ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid CSRF token. Please refresh and try again.";
    } else {
        $username = trim($_POST['username']);
        $password = md5(trim($_POST['password'])); // Legacy: use bcrypt in future!

        $stmt = $conn->prepare("SELECT * FROM admin WHERE username=? AND password=?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 1) {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    }
}

// --- Handle forgot password ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['forgot'])) {
    $admin_email = trim($_POST['admin_email'] ?? '');
    if (filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $conn->prepare("SELECT * FROM admin WHERE email=?");
        $stmt->bind_param("s", $admin_email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($admin = $res->fetch_assoc()) {
            // Generate a reset token (valid for 15 min)
            $token = bin2hex(random_bytes(32));
            $expires = time() + 900; // 15 minutes
            $conn->query("UPDATE admin SET reset_token='$token', reset_expires=$expires WHERE email='$admin_email'");
            $reset_link = "https://yourdomain.com/admin/reset_password.php?token=$token";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - MovieZone</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(120deg, #e9f5ff 0%, #a5d8dd 100%);
            font-family: 'Segoe UI', Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-box {
            max-width: 420px;
            width: 100%;
            padding: 36px 32px 28px 32px;
            background: rgba(255,255,255,0.85);
            border-radius: 18px;
            box-shadow: 0 8px 32px 0 rgba(127,127,213,0.13);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(127,127,213,0.08);
        }
        .login-title {
            color: #7f7fd5;
            font-weight: 800;
            letter-spacing: 1px;
        }
        .form-label {
            color: #3b486b;
            font-weight: 500;
        }
        .btn-login {
            background: linear-gradient(90deg, #a5d8dd 0%, #7f7fd5 100%);
            color: #fff;
            font-weight: 700;
            border-radius: 50px;
            transition: background 0.2s;
        }
        .btn-login:hover {
            background: linear-gradient(90deg, #7f7fd5 0%, #a5d8dd 100%);
            color: #fff;
        }
        .brand-admin {
            font-size: 2rem;
            font-weight: 900;
            letter-spacing: 2px;
            color: #7f7fd5;
            text-align: center;
            margin-bottom: 1.2rem;
        }
        .forgot-link {
            display: block;
            text-align: right;
            margin-top: 8px;
            color: #7f7fd5;
            cursor: pointer;
            font-size: 0.98rem;
        }
        .modal-content {
            border-radius: 18px;
        }
    </style>
</head>
<body>
    <div class="login-box shadow">
        <div class="brand-admin mb-2">🎬 MovieZone</div>
        <h3 class="text-center login-title mb-4">Admin Login</h3>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" name="username" id="username" class="form-control" required autocomplete="off">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" required autocomplete="off">
            </div>
            <button type="submit" name="login" class="btn btn-login w-100 mt-2">Login</button>
        </form>
        <a class="forgot-link" data-bs-toggle="modal" data-bs-target="#forgotModal">Forgot password?</a>
    </div>

    <!-- Forgot Password Modal -->
    <div class="modal fade" id="forgotModal" tabindex="-1" aria-labelledby="forgotModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content p-3">
          <form method="post">
            <div class="modal-header border-0">
              <h5 class="modal-title" id="forgotModalLabel">Forgot Password</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <div class="mb-3">
                    <label for="admin_email" class="form-label">Admin Email</label>
                    <input type="email" name="admin_email" id="admin_email" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer border-0">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" name="forgot" class="btn btn-login">Send Reset Link</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
        