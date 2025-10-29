<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $username = trim($_POST["username"]);
  $password = trim($_POST["password"]);

  $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
  $stmt->execute([$username]);
  $user = $stmt->fetch();

  if ($user && password_verify($password, $user["password"])) {
    $_SESSION["user_id"] = $user["id"];
    $_SESSION["username"] = $user["username"];
    $_SESSION["role"] = $user["role"];
    header("Location: admin/index.php");
    exit;
  } else {
    $error = "Invalid username or password.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Greenhouse Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- AdminLTE + Bootstrap CDN -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">

  <style>
    /* 🌿 Background Image */
    body {
      background: url('dashboard/background.jpg') no-repeat center center fixed;
      background-size: cover;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: "Poppins", sans-serif;
    }

    /* Dark overlay for readability */
    body::before {
      content: "";
      position: absolute;
      inset: 0;
      background: rgba(0, 0, 0, 0.5);
      z-index: 0;
    }

    /* Login container */
    .login-box {
      position: relative;
      z-index: 1;
      width: 400px;
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(10px);
      border-radius: 15px;
      box-shadow: 0 0 25px rgba(0, 0, 0, 0.3);
      padding: 30px;
      color: #fff;
    }

    /* Center logos */
    .login-logo img {
      width: 90px;
      height: 90px;
      object-fit: contain;
      border-radius: 50%;
      box-shadow: 0 0 10px rgba(255,255,255,0.3);
    }

    .secondary-logo img {
      width: 130px;
      height: auto;
      margin-top: 10px;
      border-radius: 10px;
      opacity: 0.95;
    }

    /* Form styling */
    .card-body input {
      background-color: rgba(255, 255, 255, 0.2);
      border: none;
      color: #fff;
    }

    .card-body input::placeholder {
      color: #ccc;
    }

    .btn-success {
      background-color: #28a745;
      border: none;
      transition: 0.3s;
    }

    .btn-success:hover {
      background-color: #218838;
    }

    .error-message {
      color: #ff7b7b;
      text-align: center;
      margin-bottom: 10px;
    }
  </style>
</head>

<body>
  <div class="login-box text-center">

    <!-- 🌿 Primary Logo -->
    <div class="login-logo">
      <img src="dashboard/slsulogo.png" alt="Main Logo">
    </div>

    <!-- 🌾 Secondary Logo -->
    <div class="secondary-logo">
      <img src="dashboard/orglogo.jpg" alt="Secondary Logo">
    </div>

    <h3 class="mt-3 mb-3 font-weight-bold">Greenhouse IoT System</h3>

    <form method="POST" class="card-body text-left">
      <?php if (!empty($error)): ?>
        <p class="error-message"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>

      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" name="username" class="form-control" placeholder="Enter username" required>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" name="password" class="form-control" placeholder="Enter password" required>
      </div>

      <button type="submit" class="btn btn-success btn-block">Login</button>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
