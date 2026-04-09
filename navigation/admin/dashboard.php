<?php 
  session_start();
  if (!isset($_SESSION['userid'])) {
      header("Location: ../../logout.php");
      exit();
  }

  $userid     = $_SESSION['userid'];
  $lastname   = $_SESSION['lastname']   ?? '';
  $firstname  = $_SESSION['firstname']  ?? '';
  $middlename = $_SESSION['middlename'] ?? '';

  require '../../database/connection.php';
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // ---- KPI cards ----
  $totalMembers = (int)$conn->query("SELECT COUNT(*) FROM tblusers")->fetchColumn();

  $activeBookings = (int)$conn->query("
      SELECT COUNT(*) 
      FROM schedules 
      WHERE status='Approved' AND date >= CURDATE()
  ")->fetchColumn();

  $pendingBookings = (int)$conn->query("
      SELECT COUNT(*) 
      FROM schedules 
      WHERE status='Pending'
  ")->fetchColumn();

  // ---- Status breakdown for bar chart ----
  $statusRows = $conn->query("
      SELECT status, COUNT(*) AS c 
      FROM schedules 
      GROUP BY status
  ")->fetchAll(PDO::FETCH_KEY_PAIR);
  $allStatuses = ['Pending','Approved','Completed','Denied'];
  $statusData = [];
  foreach ($allStatuses as $st) {
    $statusData[$st] = isset($statusRows[$st]) ? (int)$statusRows[$st] : 0;
  }

  // ---- Bookings per month (last 12 months) ----
  $monthlyRows = $conn->query("
      SELECT DATE_FORMAT(date,'%Y-%m') ym, COUNT(*) c
      FROM schedules
      WHERE date >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
      GROUP BY ym
      ORDER BY ym
  ")->fetchAll(PDO::FETCH_ASSOC);

  $monthLabels = [];
  $monthCounts = [];
  $start = new DateTime(date('Y-m-01', strtotime('-11 months')));
  for ($i=0; $i<12; $i++){
    $key = $start->format('Y-m');
    $label = $start->format('M Y');
    $monthLabels[] = $label;
    $found = 0;
    foreach ($monthlyRows as $r){
      if ($r['ym'] === $key){ $found = (int)$r['c']; break; }
    }
    $monthCounts[] = $found;
    $start->modify('+1 month');
  }

  // ---- Upcoming 10 schedules table ----
  $upcoming = $conn->query("
    SELECT 
      s.ID,
      s.date,
      s.time_start,
      s.time_end,
      s.status,
      u.firstname, u.lastname,
      sv.service_name
    FROM schedules s
    LEFT JOIN tblusers u  ON u.id  = s.userID
    LEFT JOIN services sv ON sv.id = s.serviceID
    WHERE s.date >= CURDATE()
    ORDER BY s.date, s.time_start
    LIMIT 10
  ")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>

  <!-- Bootstrap & FontAwesome -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="stylesheet" href="css/style.css?<?= filemtime('css/style.css'); ?>">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
  <!-- DataTables Buttons (for Print) -->
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

  <style>
    .card .card-title{ font-size:.95rem; color:#6b7280; }
    .card .card-value{ font-size:1.6rem; font-weight:700; }
    .card i{ font-size:28px; opacity:.9; color:#0d6efd; }
    .status-badge{ border-radius:999px; padding:.2rem .6rem; font-weight:700; font-size:.8rem; }
    .status-Pending{ background:#fff7ed; color:#c2410c; border:1px solid #fdba74; }
    .status-Approved{ background:#dcfce7; color:#166534; border:1px solid #86efac; }
    .status-Completed{ background:#e0e7ff; color:#3730a3; border:1px solid #c7d2fe; }
    .status-Denied{ background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
    .toggle-button{ background:#0d6efd; color:#fff; border:0; padding:.45rem .6rem; border-radius:.375rem; }
    #chartsRow .card{ height:100%; }
    canvas{ max-height: 340px; }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <nav id="sidebar">
    <div class="sidebar-header">
      <h4>Faire Scheduling</h4>
    </div>
    <ul class="px-3">
      <li class="p-1 navbar-custom-active"><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i><span> Dashboard</span></a></li>
      <li class="p-1 navbar-custom my-2"><a href="account/accountpage.php"><i class="fas fa-users"></i><span> Accounts</span></a></li>
      <li class="p-1 navbar-custom mb-2"><a href="services/servicepage.php"><i class="fas fa-wrench"></i><span> Services</span></a></li>
      <li class="p-1 navbar-custom my-2">
        <a class="dropdown-toggle d-flex align-items-center" data-bs-toggle="collapse" href="#scheduleSubmenu" role="button" aria-expanded="false" aria-controls="scheduleSubmenu">
          <i class="fas fa-calendar me-2"></i><span class="me-3">Schedules</span>
        </a>
        <ul class="collapse list-unstyled ps-3 mt-1" id="scheduleSubmenu">
          <li class="my-1 custom-hover-dropdown">
            <a href="schedule/pending_page.php" class="custom-hover-dropdown-text"><i class="fas fa-hourglass-half me-2"></i>Pending</a>
          </li>
          <li class="my-1 custom-hover-dropdown">
            <a href="schedule/approved_page.php" class="custom-hover-dropdown-text"><i class="fas fa-check-circle me-2"></i>Approved</a>
          </li>
          <li class="my-1 custom-hover-dropdown">
            <a href="schedule/completed_page.php" class="custom-hover-dropdown-text"><i class="fas fa-clipboard-check me-2"></i>Completed</a>
          </li>
        </ul>
      </li>
      <hr>
      <li class="p-1 navbar-custom my-2"><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a></li>
    </ul>
  </nav>

  <!-- Content -->
  <div id="content">
    <button class="toggle-button d-md-none ms-2 mt-2" onclick="toggleSidebar()">
      <i class="fas fa-bars"></i>
    </button>

    <h3 class="page-title my-4">Welcome to the Dashboard</h3>

    <!-- KPI Cards -->
    <div class="row g-3">
      <div class="col-md-4">
        <div class="card p-3">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="card-title">Total Members</div>
              <div class="card-value"><?= number_format($totalMembers) ?></div>
            </div>
            <i class="fas fa-user-friends"></i>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card p-3">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="card-title">Active Bookings (Upcoming Approved)</div>
              <div class="card-value"><?= number_format($activeBookings) ?></div>
            </div>
            <i class="fas fa-network-wired"></i>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card p-3">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div class="card-title">Pending Bookings</div>
              <div class="card-value"><?= number_format($pendingBookings) ?></div>
            </div>
            <i class="fas fa-file-alt"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts -->
    <div id="chartsRow" class="row g-3 mt-1">
      <div class="col-xl-6">
        <div class="card p-3">
          <h6 class="mb-3">Bookings by Status</h6>
          <canvas id="statusChart"></canvas>
        </div>
      </div>
      <div class="col-xl-6">
        <div class="card p-3">
          <h6 class="mb-3">Bookings per Month (Last 12 months)</h6>
          <canvas id="monthlyChart"></canvas>
        </div>
      </div>
    </div>

    <!-- Upcoming Table -->
    <div class="card p-3 mt-3">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0">Upcoming Schedules (next 10)</h6>
        <a class="btn btn-sm btn-outline-primary" href="schedule/approved_page.php">Manage</a>
      </div>
      <div class="table-responsive">
        <table id="upcomingTable" class="table table-striped table-bordered align-middle w-100">
          <thead class="table-dark">
            <tr>
              <th>ID</th>
              <th>Client</th>
              <th>Service</th>
              <th>Date</th>
              <th>Time</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($upcoming as $r): 
              $dateNice = $r['date'] ? date('M d, Y', strtotime($r['date'])) : '';
              $t1 = $r['time_start'] ? date('g:i A', strtotime($r['time_start'])) : '';
              $t2 = $r['time_end']   ? date('g:i A', strtotime($r['time_end']))   : '';
              $timeNice = trim($t1 . ($t2 ? " – $t2" : ''));
              $client = trim(($r['firstname'] ?? '').' '.($r['lastname'] ?? ''));
              $svc = $r['service_name'] ?: '';
              $st  = $r['status'] ?: 'Pending';
            ?>
            <tr>
              <td><?= (int)$r['ID'] ?></td>
              <td><?= htmlspecialchars($client, ENT_QUOTES) ?></td>
              <td><?= htmlspecialchars($svc, ENT_QUOTES) ?></td>
              <td><?= $dateNice ?></td>
              <td><?= $timeNice ?></td>
              <td><span class="status-badge status-<?= htmlspecialchars($st) ?>"><?= htmlspecialchars($st) ?></span></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div><!-- /content -->

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
  <!-- DataTables Buttons (Print) -->
  <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      sidebar.classList.toggle('collapsed');
      sidebar.classList.toggle('show');
    }

    document.addEventListener('click', function (e) {
      const sidebar = document.getElementById('sidebar');
      const toggleBtn = document.querySelector('.toggle-button');
      if (window.innerWidth <= 768 && sidebar.classList.contains('show')) {
        if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
          sidebar.classList.remove('show');
        }
      }
    });

    // ---- Charts (data from PHP) ----
    const statusLabels = <?= json_encode(array_keys($statusData)) ?>;
    const statusCounts = <?= json_encode(array_values($statusData)) ?>;

    const monthLabels  = <?= json_encode($monthLabels) ?>;
    const monthCounts  = <?= json_encode($monthCounts) ?>;

    // Bar: status
    new Chart(document.getElementById('statusChart'), {
      type: 'bar',
      data: { labels: statusLabels, datasets: [{ label: 'Count', data: statusCounts }] },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { precision:0 } } }
      }
    });

    // Line: monthly
    new Chart(document.getElementById('monthlyChart'), {
      type: 'line',
      data: { labels: monthLabels, datasets: [{ label: 'Bookings', data: monthCounts, tension: .35, fill: false }] },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { precision:0 } } }
      }
    });

    // Upcoming table with a single Print button
    $(function(){
      $('#upcomingTable').DataTable({
        responsive: true,
        order: [[3,'asc'],[4,'asc']],
        pageLength: 10,
        dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        buttons: [
          {
            extend: 'print',
            text: '<i class="fas fa-print me-2" style="font-size:18px;"></i>Print list',
            className: 'btn btn-dark',
            title: '', // no giant default title
            customize: function (win) {
              // small custom title
              const header = win.document.createElement('div');
              header.innerHTML = '<div style="font-size:14px;font-weight:600;margin:0 0 8px;">Upcoming Schedules</div>';
              win.document.body.insertBefore(header, win.document.body.firstChild);

              // tighten layout on paper
              const css = `
@page { size: A4 landscape; margin: 12mm; }
body { font-size: 12px !important; -webkit-print-color-adjust: exact; }
table { width: 100% !important; }
table.dataTable thead th, table.dataTable tbody td { padding: 6px 8px !important; }
              `;
              const head = win.document.head || win.document.getElementsByTagName('head')[0];
              const style = win.document.createElement('style');
              style.type = 'text/css';
              style.appendChild(win.document.createTextNode(css));
              head.appendChild(style);
            }
          }
        ]
      });
    });
  </script>
</body>
</html>
