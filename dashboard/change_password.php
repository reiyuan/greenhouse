<?php
require_once '../auth.php';
require_once '../config.php';

$message = '';
$type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST['old_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    $user_id = $_SESSION['user_id'];

    // Fetch current password hash
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($old, $user['password'])) {
        $message = 'Current password is incorrect.';
        $type = 'danger';
    } elseif ($new !== $confirm) {
        $message = 'New passwords do not match.';
        $type = 'warning';
    } elseif (strlen($new) < 6) {
        $message = 'Password must be at least 6 characters long.';
        $type = 'warning';
    } else {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update->execute([$hash, $user_id]);

        $message = 'Password updated successfully!';
        $type = 'success';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Change Password | Greenhouse System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- AdminLTE + Bootstrap -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
        <a class="nav-link text-secondary" href="#">
          <i class="fas fa-user"></i> <?php echo $_SESSION['username']; ?>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-danger" href="logout.php">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </li>
    </ul>
  </nav>

  <!-- Content Wrapper -->
  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <h4>Change Password</h4>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">
        <div class="card card-primary card-outline col-md-6 mx-auto">
          <div class="card-body">
            <?php if ($message): ?>
              <div class="alert alert-<?php echo $type; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
            <?php endif; ?>

            <form method="POST" action="">
              <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="old_password" class="form-control" required>
              </div>
              <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" class="form-control" required>
              </div>
              <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
              </div>
              <button type="submit" class="btn btn-success">Update Password</button>
              <a href="index.php" class="btn btn-secondary float-right">Back to Dashboard</a>
            </form>
          </div>
        </div>
      </div>
    </section>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
