<?php require_once '../auth.php'; ?>

<?php
require_once __DIR__ . '/../config.php';

$readings = $pdo->query("SELECT * FROM sensor_readings ORDER BY id DESC LIMIT 20")->fetchAll();
$lastCmd = $pdo->query("SELECT * FROM commands ORDER BY id DESC LIMIT 1")->fetch();

$latest = $readings ? $readings[0] : null; // newest row

// prepare chart data
$timestamps = [];
$tempData = [];
$humData = [];
$soilData = [];
$lightData = [];

foreach (array_reverse($readings) as $r) {
    $timestamps[] = $r['created_at'];
    $tempData[] = (float)$r['temp'];
    $humData[] = (float)$r['humidity'];
    $soilData[] = (float)$r['soil_moisture'];
    $lightData[] = (float)$r['light_intensity'];
}
?>

<?php require_once '../auth.php'; ?>
<?php
require_once __DIR__ . '/../config.php';

// Pagination setup
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

$lastCmd = $pdo->query("SELECT * FROM commands ORDER BY id DESC LIMIT 1")->fetch();
$latest = $readings ? $readings[0] : null; // newest row

// Prepare chart data
$timestamps = [];
$tempData = [];
$humData = [];
$soilData = [];
$lightData = [];

foreach (array_reverse($readings) as $r) {
    $timestamps[] = $r['created_at'];
    $tempData[] = (float)$r['temp'];
    $humData[] = (float)$r['humidity'];
    $soilData[] = (float)$r['soil_moisture'];
    $lightData[] = (float)$r['light_intensity'];
}
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
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>


    <style>
body {
  background: url('background.jpg') no-repeat center center fixed;
  background-size: cover;
}
.content-wrapper {
  background: rgba(255, 255, 255, 0.85); /* white overlay for readability */
  backdrop-filter: blur(4px); /* smooth blur effect */
}
</style>

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
  <!-- Right navbar links -->
<ul class="navbar-nav ml-auto">

  <!-- User Dropdown Menu -->
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-expanded="false">
      <i class="fas fa-user-circle"></i> <?php echo $_SESSION['username']; ?>
    </a>
    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
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

  
</ul>

  </nav>

  <!-- Sidebar -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="index.php" class="brand-link">
  <img src="orglogo.jpg" class="brand-image img-circle elevation-3" style="opacity:.9; background-color:white;">
  <span class="brand-text font-weight-light">Greenhouse</span>
</a>

    <div class="sidebar">
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" role="menu">
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
          <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box bg-danger">
              <span class="info-box-icon"><i class="fas fa-thermometer-half"></i></span>
              <div class="info-box-content">
                <span class="info-box-text">Temperature</span>
                <span class="info-box-number" id="tempVal"><?= $latest ? $latest['temp']." °C" : "N/A" ?></span>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box bg-primary">
              <span class="info-box-icon"><i class="fas fa-tint"></i></span>
              <div class="info-box-content">
                <span class="info-box-text">Humidity</span>
                <span class="info-box-number" id="humVal"><?= $latest ? $latest['humidity']." %" : "N/A" ?></span>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box bg-success">
              <span class="info-box-icon"><i class="fas fa-seedling"></i></span>
              <div class="info-box-content">
                <span class="info-box-text">Soil Moisture</span>
                <span class="info-box-number" id="soilVal"><?= $latest ? $latest['soil_moisture']." %" : "N/A" ?></span>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6 col-12">
            <div class="info-box bg-warning">
              <span class="info-box-icon"><i class="fas fa-sun"></i></span>
              <div class="info-box-content">
                <span class="info-box-text">Light</span>
                <span class="info-box-number" id="lightVal"><?= $latest ? $latest['light_intensity'] : "N/A" ?></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Last Command -->
<div class="card card-success">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-cogs"></i> Last Command</h3>
          </div>
          <div class="card-body">
            <?php if($lastCmd): ?>
              <p><strong>ID:</strong> <?= $lastCmd['id'] ?> | 
                 <strong>Source:</strong> <?= $lastCmd['source'] ?> | 
                 <strong>Time:</strong> <?= $lastCmd['created_at'] ?></p>
              <ul>
                <li>Heater: <?= $lastCmd['heater'] ? '<span class="badge badge-danger">ON</span>' : '<span class="badge badge-secondary">OFF</span>' ?></li>
                <li>Fan: <?= $lastCmd['fan'] ? '<span class="badge badge-info">ON</span>' : '<span class="badge badge-secondary">OFF</span>' ?></li>
                <li>Pump: <?= $lastCmd['pump'] ? '<span class="badge badge-primary">ON</span>' : '<span class="badge badge-secondary">OFF</span>' ?></li>
                <li>Light: <?= $lastCmd['light_act'] ? '<span class="badge badge-warning">ON</span>' : '<span class="badge badge-secondary">OFF</span>' ?></li>
              </ul>
            <?php else: ?>
              <p>No commands yet.</p>
            <?php endif; ?>
          </div>
        </div>


        <!-- Manual Controls -->
        <div class="card card-primary">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-sliders-h"></i> Manual Controls</h3>
          </div>
          <div class="card-body">
            <form id="manualForm">
              <div class="form-check">
                <input type="checkbox" class="form-check-input" id="heater">
                <label class="form-check-label" for="heater">Heater</label>
              </div>
              <div class="form-check">
                <input type="checkbox" class="form-check-input" id="fan">
                <label class="form-check-label" for="fan">Fan</label>
              </div>
              <div class="form-check">
                <input type="checkbox" class="form-check-input" id="pump">
                <label class="form-check-label" for="pump">Pump</label>
              </div>
              <div class="form-check">
                <input type="checkbox" class="form-check-input" id="light_act">
                <label class="form-check-label" for="light_act">Light</label>
              </div>
              <button type="button" class="btn btn-success mt-3" onclick="sendManual()">Send Manual Command</button>
            </form>
          </div>
        </div>

        <!-- Sensor Readings Table -->
        <!-- Sensor Readings Table -->
<div class="card card-info">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-table"></i> Recent Sensor Readings</h3>
  </div>
  <div class="card-body table-responsive p-2">

    <p class="text-muted">
      Showing <?= ($offset + 1) ?>–<?= min($offset + $limit, $totalRecords) ?> of <?= $totalRecords ?> readings
    </p>

    <table class="table table-hover table-bordered text-nowrap">
      <thead class="table-dark">
        <tr>
          <th>Time</th>
          <th>Temp (°C)</th>
          <th>Humidity (%)</th>
          <th>Soil Moisture (%)</th>
          <th>Light</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($readings): ?>
          <?php foreach ($readings as $r): ?>
            <tr>
              <td><?= htmlspecialchars($r['created_at']) ?></td>
              <td><?= htmlspecialchars($r['temp']) ?></td>
              <td><?= htmlspecialchars($r['humidity']) ?></td>
              <td><?= htmlspecialchars($r['soil_moisture']) ?></td>
              <td><?= htmlspecialchars($r['light_intensity']) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="5" class="text-center text-muted">No readings found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- Pagination Controls -->
    <nav aria-label="Sensor Readings Pagination">
  <ul class="pagination justify-content-center mt-3">
    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
      <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
    </li>

    <?php
      // Limit visible pages to 10
      $maxLinks = 10;
      $startPage = max(1, $page - floor($maxLinks / 2));
      $endPage = min($totalPages, $startPage + $maxLinks - 1);
      if ($endPage - $startPage + 1 < $maxLinks) {
          $startPage = max(1, $endPage - $maxLinks + 1);
      }

      for ($i = $startPage; $i <= $endPage; $i++):
    ?>
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


        <!-- Charts -->
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

<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<style>
.flash { animation: flashGreen 0.4s ease-in-out; }
@keyframes flashGreen { from { background-color: #d4edda; } to { background-color: transparent; } }
</style>

<script>
const REFRESH_INTERVAL = 5000;

// === 1️⃣ Update Last Command ===
async function updateLastCommand() {
  try {
    const res = await fetch('../api/latest_command.php?nocache=' + Date.now());
    const data = await res.json();

    if (!data || !data.id) return;

    const html = `
      <p><strong>ID:</strong> ${data.id} |
      <strong>Source:</strong> ${data.source} |
      <strong>Time:</strong> ${data.created_at}</p>
      <ul>
        <li>Heater: ${data.heater == 1 ? '<span class="badge badge-danger">ON</span>' : '<span class="badge badge-secondary">OFF</span>'}</li>
        <li>Fan: ${data.fan == 1 ? '<span class="badge badge-info">ON</span>' : '<span class="badge badge-secondary">OFF</span>'}</li>
        <li>Pump: ${data.pump == 1 ? '<span class="badge badge-primary">ON</span>' : '<span class="badge badge-secondary">OFF</span>'}</li>
        <li>Light: ${data.light_act == 1 ? '<span class="badge badge-warning">ON</span>' : '<span class="badge badge-secondary">OFF</span>'}</li>
      </ul>
    `;
    document.querySelector('.card-success .card-body').innerHTML = html;
  } catch (err) {
    console.error("Error fetching last command:", err);
  }
}

// === 2️⃣ Update Sensor Summary Cards ===
async function updateSensorCards() {
  try {
    const res = await fetch('../api/latest_reading.php?nocache=' + Date.now());
    const data = await res.json();

    if (!data || !data.id) return;

    document.getElementById('tempVal').innerHTML = data.temp + ' °C';
    document.getElementById('humVal').innerHTML = data.humidity + ' %';
    document.getElementById('soilVal').innerHTML = data.soil_moisture + ' %';
    document.getElementById('lightVal').innerHTML = data.light_intensity;

    ['tempVal', 'humVal', 'soilVal', 'lightVal'].forEach(id => {
      const el = document.getElementById(id);
      el.classList.add('flash');
      setTimeout(() => el.classList.remove('flash'), 400);
    });
  } catch (err) {
    console.error('Error updating sensor cards:', err);
  }
}

// === 3️⃣ Live Sensor Chart ===
const ctx = document.getElementById('sensorChart').getContext('2d');
let sensorChart = new Chart(ctx, {
  type: 'line',
  data: {
    labels: [],
    datasets: [
      { label: 'Temp (°C)', data: [], borderColor: 'red', fill: false },
      { label: 'Humidity (%)', data: [], borderColor: 'blue', fill: false },
      { label: 'Soil Moisture (%)', data: [], borderColor: 'green', fill: false },
      { label: 'Light', data: [], borderColor: 'orange', fill: false }
    ]
  },
  options: {
    responsive: true,
    animation: false,
    scales: { y: { beginAtZero: true } }
  }
});

async function updateChart() {
  try {
    const res = await fetch('../api/latest_readings.php?nocache=' + Date.now());
    const data = await res.json();

    const labels = data.map(d => d.created_at.substring(11, 16));
    sensorChart.data.labels = labels;
    sensorChart.data.datasets[0].data = data.map(d => parseFloat(d.temp));
    sensorChart.data.datasets[1].data = data.map(d => parseFloat(d.humidity));
    sensorChart.data.datasets[2].data = data.map(d => parseFloat(d.soil_moisture));
    sensorChart.data.datasets[3].data = data.map(d => parseFloat(d.light_intensity));
    sensorChart.update();
  } catch (err) {
    console.error('Chart update error:', err);
  }
}

const MODE_CHECK_INTERVAL = 5000;
let overrideEndTime = null;

// Mode display check
async function checkMode() {
  const res = await fetch('../api/get_command.php');
  const cmd = await res.json();

  if (cmd.source === 'manual') {
    const createdAt = new Date(cmd.created_at);
    const expires = new Date(createdAt.getTime() + 5 * 60000);
    const now = new Date();
    const diffMs = expires - now;

    if (diffMs > 0) {
      const mins = Math.floor(diffMs / 60000);
      const secs = Math.floor((diffMs % 60000) / 1000);
      document.getElementById('modeDisplay').innerHTML =
        `<span class="override-active">Manual Override Active</span> (expires in ${mins}:${secs.toString().padStart(2, '0')})`;
      document.getElementById('cancelOverrideBtn').classList.remove('d-none');
      overrideEndTime = expires;
    } else {
      document.getElementById('modeDisplay').innerHTML = `<span class="auto-mode">Auto Mode (KNN)</span>`;
      document.getElementById('cancelOverrideBtn').classList.add('d-none');
    }
  } else {
    document.getElementById('modeDisplay').innerHTML = `<span class="auto-mode">Auto Mode (KNN)</span>`;
    document.getElementById('cancelOverrideBtn').classList.add('d-none');
  }
}
setInterval(checkMode, MODE_CHECK_INTERVAL);
checkMode();

// Manual command sender
async function sendManual() {
  const data = {
    heater: document.getElementById('heater').checked ? 1 : 0,
    fan: document.getElementById('fan').checked ? 1 : 0,
    pump: document.getElementById('pump').checked ? 1 : 0,
    light_act: document.getElementById('light_act').checked ? 1 : 0
  };
  const res = await fetch('../api/manual_command.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify(data)
  });
  const json = await res.json();
  if (json.status === 'ok') {
    alert('Manual override sent!');
    checkMode();
  }
}

// Cancel override
async function cancelOverride() {
  const res = await fetch('../api/save_reading.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({"temp":25,"humidity":50,"soil_moisture":50,"light_intensity":500})
  });
  await res.json();
  alert('Manual override cancelled. System back to KNN mode.');
  checkMode();
}
    

// === 4️⃣ Run All Periodically ===
function refreshAll() {
  updateLastCommand();
  updateSensorCards();
  updateChart();
}

refreshAll();
setInterval(refreshAll, REFRESH_INTERVAL);
</script>




</body>
</html>











