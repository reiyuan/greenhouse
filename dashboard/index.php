<?php require_once '../auth.php'; ?>

<?php
require_once __DIR__ . '/../config.php';
// =============== PAGINATION ===============
$limit = 10; // number of readings per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Count total readings
$totalStmt = $pdo->query("SELECT COUNT(*) FROM sensor_readings");
$totalRecords = $totalStmt->fetchColumn();
$totalPages = ceil($totalRecords / $limit);

// Fetch paginated readings
$stmt = $pdo->prepare("SELECT * FROM sensor_readings ORDER BY id DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$readings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch latest command
$lastCmd = $pdo->query("SELECT * FROM commands ORDER BY id DESC LIMIT 1")->fetch();

// Latest reading
$latest = $pdo->query("SELECT * FROM sensor_readings ORDER BY id DESC LIMIT 1")->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Greenhouse Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- AdminLTE & dependencies via CDN -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="index.php" class="nav-link">Dashboard</a>
      </li>
    </ul>
    <ul class="navbar-nav ml-auto">
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#">
          <i class="fas fa-user-circle"></i> <?= $_SESSION['username']; ?>
        </a>
        <div class="dropdown-menu dropdown-menu-right shadow">
          <span class="dropdown-header">Account</span>
          <div class="dropdown-divider"></div>
          <a href="change_password.php" class="dropdown-item">
            <i class="fas fa-key mr-2 text-primary"></i> Change Password
          </a>
          <div class="dropdown-divider"></div>
          <a href="logout.php" class="dropdown-item text-danger">
            <i class="fas fa-sign-out-alt mr-2"></i> Logout
          </a>
        </div>
      </li>
    </ul>
  </nav>

  <!-- Sidebar -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="index.php" class="brand-link">
      <i class="fas fa-seedling brand-image img-circle elevation-3"></i>
      <span class="brand-text font-weight-light">Greenhouse</span>
    </a>
    <div class="sidebar">
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column">
          <li class="nav-item">
            <a href="index.php" class="nav-link active">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>
        </ul>
      </nav>
    </div>
  </aside>

  <!-- Content -->
  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <h1 class="m-0">Greenhouse Monitoring</h1>
      </div>
    </div>

    <div class="content">
      <div class="container-fluid">

        <!-- Summary Cards -->
        <div class="row">
          <?php
          $cards = [
            ["Temperature", "°C", "bg-danger", "fas fa-thermometer-half", $latest['temp'] ?? "N/A"],
            ["Humidity", "%", "bg-primary", "fas fa-tint", $latest['humidity'] ?? "N/A"],
            ["Soil Moisture", "%", "bg-success", "fas fa-seedling", $latest['soil_moisture'] ?? "N/A"],
            ["Light", "", "bg-warning", "fas fa-sun", $latest['light_intensity'] ?? "N/A"]
          ];
          foreach ($cards as $c): ?>
            <div class="col-md-3 col-sm-6 col-12">
              <div class="info-box <?= $c[2] ?>">
                <span class="info-box-icon"><i class="<?= $c[3] ?>"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text"><?= $c[0] ?></span>
                  <span class="info-box-number"><?= $c[4] . " " . $c[1] ?></span>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Last Command -->
        <div class="card card-success">
          <div class="card-header"><h3 class="card-title"><i class="fas fa-cogs"></i> Last Command</h3></div>
          <div class="card-body">
            <?php if ($lastCmd): ?>
              <p><strong>ID:</strong> <?= $lastCmd['id'] ?> |
              <strong>Source:</strong> <?= $lastCmd['source'] ?> |
              <strong>Time:</strong> <?= $lastCmd['created_at'] ?></p>
              <ul>
                <li>Heater: <?= $lastCmd['heater'] ? '<span class="badge badge-danger">ON</span>' : '<span class="badge badge-secondary">OFF</span>' ?></li>
                <li>Fan: <?= $lastCmd['fan'] ? '<span class="badge badge-info">ON</span>' : '<span class="badge badge-secondary">OFF</span>' ?></li>
                <li>Pump: <?= $lastCmd['pump'] ? '<span class="badge badge-primary">ON</span>' : '<span class="badge badge-secondary">OFF</span>' ?></li>
                <li>Light: <?= $lastCmd['light_act'] ? '<span class="badge badge-warning">ON</span>' : '<span class="badge badge-secondary">OFF</span>' ?></li>
              </ul>
            <?php else: ?><p>No commands yet.</p><?php endif; ?>
          </div>
        </div>

        <!-- Sensor Readings Table -->
        <div class="card card-info">
          <div class="card-header"><h3 class="card-title"><i class="fas fa-table"></i> Recent Sensor Readings</h3></div>
          <div class="card-body table-responsive p-2">
            <p class="text-muted">
              Showing <?= ($offset + 1) ?>–<?= min($offset + $limit, $totalRecords) ?> of <?= $totalRecords ?> readings
            </p>
            <table class="table table-hover table-bordered text-nowrap">
              <thead class="table-dark">
                <tr>
                  <th>Time</th><th>Temp (°C)</th><th>Humidity (%)</th><th>Soil Moisture (%)</th><th>Light</th>
                </tr>
              </thead>
              <tbody>
              <?php if ($readings): foreach ($readings as $r): ?>
                <tr>
                  <td><?= htmlspecialchars($r['created_at']) ?></td>
                  <td><?= htmlspecialchars($r['temp']) ?></td>
                  <td><?= htmlspecialchars($r['humidity']) ?></td>
                  <td><?= htmlspecialchars($r['soil_moisture']) ?></td>
                  <td><?= htmlspecialchars($r['light_intensity']) ?></td>
                </tr>
              <?php endforeach; else: ?>
                <tr><td colspan="5" class="text-center text-muted">No readings found.</td></tr>
              <?php endif; ?>
              </tbody>
            </table>

            <nav aria-label="Sensor Readings Pagination">
              <ul class="pagination justify-content-center mt-3">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                  <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                </li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                  <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                  </li>
                <?php endfor; ?>
                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                  <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                </li>
              </ul>
            </nav>
          </div>
        </div>

        <!-- Sensor Trends Chart -->
        <div class="card card-warning">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-chart-line"></i> Sensor Trends</h3>
          </div>
          <div class="card-body">
            <canvas id="sensorChart" height="100"></canvas>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="main-footer">
    <div class="float-right d-none d-sm-inline">IoT Greenhouse</div>
    <strong>&copy; <?= date('Y') ?> Greenhouse System.</strong>
  </footer>
</div>

<!-- Chart Logic -->
<script>
async function loadSensorTrends() {
  const response = await fetch('../api/latest_readings.php');
  const readings = await response.json();

  const labels = readings.map(r => r.created_at.substring(11, 16));
  const tempData = readings.map(r => parseFloat(r.temp));
  const humData = readings.map(r => parseFloat(r.humidity));
  const soilData = readings.map(r => parseFloat(r.soil_moisture));
  const lightData = readings.map(r => parseFloat(r.light_intensity));

  const ctx = document.getElementById('sensorChart').getContext('2d');
  new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [
        { label: 'Temperature (°C)', data: tempData, borderColor: 'red', fill: false, tension: 0.3 },
        { label: 'Humidity (%)', data: humData, borderColor: 'blue', fill: false, tension: 0.3 },
        { label: 'Soil Moisture (%)', data: soilData, borderColor: 'green', fill: false, tension: 0.3 },
        { label: 'Light Intensity', data: lightData, borderColor: 'orange', fill: false, tension: 0.3 }
      ]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: 'top' },
        title: { display: true, text: 'Sensor Trends (Last 20 Readings)' }
      },
      scales: {
        x: { title: { display: true, text: 'Time' } },
        y: { title: { display: true, text: 'Value' }, beginAtZero: true }
      }
    }
  });
}
loadSensorTrends();
setInterval(loadSensorTrends, 5000); // refresh every 5 seconds
</script>
</body>
</html>
