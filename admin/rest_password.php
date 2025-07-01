<?php
session_start();
include("../includes/db.php");

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $newpass = trim($_POST['new_password']);

    if (strlen($newpass) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM admin WHERE reset_token=? AND reset_expires >= ?");
        $now = time();
        $stmt->bind_param("si", $token, $now);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($admin = $res->fetch_assoc()) {
            // For md5 (legacy):
            $hash = md5($newpass);
            // For future: $hash = password_hash($newpass, PASSWORD_BCRYPT);
            $conn->query("UPDATE admin SET password='$hash', reset_token=NULL, reset_expires=NULL WHERE id={$admin['id']}");
            $success = "Password reset successfully! <a href='login.php'>Login</a>";
        } else {
            $error = "Invalid or expired reset link.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password - MovieZone Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background:linear-gradient(120deg, #e9f5ff 0%, #a5d8dd 100%);">
<div class="container" style="max-width:420px; margin-top:80px;">
    <div class="card p-4 shadow">
        <h3 class="mb-3 text-center" style="color:#7f7fd5;">Reset Password</h3>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php else: ?>
            <form method="post">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" name="new_password" class="form-control" required minlength="6">
                </div>
                <button class="btn btn-primary w-100">Reset Password</button>
            </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
