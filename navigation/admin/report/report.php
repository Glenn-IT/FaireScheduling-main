<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");

if (!isset($_SESSION['userid'])) {
    header("Location: ../../../logout.php");
    exit();
}

$userid    = $_SESSION['userid'];
$firstname = $_SESSION['firstname'] ?? '';
$lastname  = $_SESSION['lastname']  ?? '';

require '../../../database/connection.php';
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// For the filter dropdowns (initial load)
$allServices = $conn->query("SELECT ID, service_name FROM services ORDER BY service_name")
                    ->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reports — Faire Scheduling</title>

  <!-- Bootstrap & FontAwesome -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="stylesheet" href="../css/style.css?<?= filemtime('../css/style.css') ?>">

  <!-- DataTables -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

  <style>
    /* ── Print ─────────────────────────────────────── */
    @media print {
      @page { size: A4 landscape; margin: 12mm; }
      * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
      #sidebar, .no-print, .toggle-button,
      .dataTables_length, .dataTables_filter,
      .dataTables_info, .dataTables_paginate,
      .dt-buttons { display: none !important; }
      #content { margin-left: 0 !important; }
      .card { break-inside: avoid; box-shadow: none !important; border: 1px solid #dee2e6 !important; }
      canvas { max-height: 220px !important; }
    }

    /* ── KPI cards ─────────────────────────────────── */
    .kpi-card { border-left: 5px solid #0038A8; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,.1); background:#fff; padding:18px 22px; }
    .kpi-label { font-size:.85rem; color:#6b7280; font-weight:600; text-transform:uppercase; letter-spacing:.04em; }
    .kpi-value { font-size:2rem; font-weight:800; color:#0038A8; line-height:1.1; }
    .kpi-icon  { font-size:2rem; opacity:.18; color:#0038A8; }

    /* Accent colours per card */
    .kpi-approved  { border-left-color:#22c55e; }  .kpi-approved  .kpi-value { color:#16a34a; }  .kpi-approved  .kpi-icon { color:#22c55e; }
    .kpi-pending   { border-left-color:#f59e0b; }  .kpi-pending   .kpi-value { color:#d97706; }  .kpi-pending   .kpi-icon { color:#f59e0b; }
    .kpi-completed { border-left-color:#3b82f6; }  .kpi-completed .kpi-value { color:#2563eb; }  .kpi-completed .kpi-icon { color:#3b82f6; }
    .kpi-denied    { border-left-color:#ef4444; }  .kpi-denied    .kpi-value { color:#dc2626; }  .kpi-denied    .kpi-icon { color:#ef4444; }
    .kpi-cancelled { border-left-color:#6b7280; }  .kpi-cancelled .kpi-value { color:#4b5563; }  .kpi-cancelled .kpi-icon { color:#6b7280; }
    .kpi-total     { border-left-color:#0038A8; }  .kpi-total     .kpi-value { color:#0038A8; }  .kpi-total     .kpi-icon { color:#0038A8; }

    /* chart cards */
    .chart-card { background:#fff; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,.08); padding:20px; height:100%; }
    .chart-card h6 { font-weight:700; color:#0038A8; margin-bottom:14px; }
    canvas { max-height:280px; }

    /* filter bar */
    .filter-bar { background:#fff; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,.07); padding:16px 20px; margin-bottom:22px; }

    /* status badges */
    .badge-Pending   { background:#fff7ed; color:#c2410c; border:1px solid #fdba74; }
    .badge-Approved  { background:#dcfce7; color:#166534; border:1px solid #86efac; }
    .badge-Completed { background:#e0e7ff; color:#3730a3; border:1px solid #c7d2fe; }
    .badge-Denied    { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
    .badge-Cancelled,
    .badge-Canceled  { background:#f3f4f6; color:#374151; border:1px solid #d1d5db; }
    .status-pill { border-radius:999px; padding:.18rem .65rem; font-size:.78rem; font-weight:700; }

    .page-title { font-weight:bold; color:#0038A8; }
  </style>
</head>
<body>

<!-- Sidebar -->
<nav id="sidebar">
  <div class="sidebar-header"><h4>Faire Scheduling</h4></div>
  <ul class="px-3">
    <li class="p-1 navbar-custom my-2"><a href="../dashboard.php"><i class="fas fa-tachometer-alt"></i><span> Dashboard</span></a></li>
    <li class="p-1 navbar-custom my-2"><a href="../account/accountpage.php"><i class="fas fa-users"></i><span> Accounts</span></a></li>
    <li class="p-1 navbar-custom mb-2"><a href="../services/servicepage.php"><i class="fas fa-wrench"></i><span> Services</span></a></li>

    <li class="p-1 navbar-custom my-2">
      <a class="dropdown-toggle d-flex align-items-center" data-bs-toggle="collapse" href="#scheduleSubmenu" role="button" aria-expanded="false" aria-controls="scheduleSubmenu">
        <i class="fas fa-calendar me-2"></i><span class="me-3">Schedules</span>
      </a>
      <ul class="collapse list-unstyled ps-3 mt-1" id="scheduleSubmenu">
        <li class="my-1 custom-hover-dropdown"><a href="../schedule/pending_page.php"   class="custom-hover-dropdown-text"><i class="fas fa-hourglass-half me-2"></i>Pending</a></li>
        <li class="my-1 custom-hover-dropdown"><a href="../schedule/approved_page.php"  class="custom-hover-dropdown-text"><i class="fas fa-check-circle me-2"></i>Approved</a></li>
        <li class="my-1 custom-hover-dropdown"><a href="../schedule/completed_page.php" class="custom-hover-dropdown-text"><i class="fas fa-clipboard-check me-2"></i>Completed</a></li>
      </ul>
    </li>

    <!-- Reports — active -->
    <li class="p-1 navbar-custom-active my-2"><a href="report.php"><i class="fas fa-chart-bar"></i><span> Reports</span></a></li>

    <hr>
    <li class="p-1 navbar-custom my-2"><a href="../../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a></li>
  </ul>
</nav>

<!-- Content -->
<div id="content">
  <button class="toggle-button d-md-none ms-2 mt-2 no-print" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
  </button>

  <h3 class="page-title my-4">Reports &amp; Analytics</h3>

  <!-- ── Filter Bar ──────────────────────────────────────────────────── -->
  <div class="filter-bar no-print">
    <div class="row g-2 align-items-end">
      <div class="col-sm-3 col-6">
        <label class="form-label mb-1" style="font-size:.85rem; font-weight:600;"><i class="fas fa-calendar-day me-1"></i>From</label>
        <input type="date" id="filterFrom" class="form-control form-control-sm" value="<?= date('Y-m-01') ?>">
      </div>
      <div class="col-sm-3 col-6">
        <label class="form-label mb-1" style="font-size:.85rem; font-weight:600;"><i class="fas fa-calendar-day me-1"></i>To</label>
        <input type="date" id="filterTo" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
      </div>
      <div class="col-sm-3 col-6">
        <label class="form-label mb-1" style="font-size:.85rem; font-weight:600;"><i class="fas fa-concierge-bell me-1"></i>Service</label>
        <select id="filterService" class="form-select form-select-sm">
          <option value="0">All Services</option>
          <?php foreach ($allServices as $sv): ?>
            <option value="<?= (int)$sv['ID'] ?>"><?= htmlspecialchars($sv['service_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-sm-3 col-6 d-flex gap-2">
        <button id="applyFilter" class="btn btn-primary btn-sm w-100">
          <i class="fas fa-filter me-1"></i> Apply
        </button>
        <button onclick="printReport()" class="btn btn-dark btn-sm w-100 no-print">
          <i class="fas fa-print me-1"></i> Print
        </button>
      </div>
    </div>
  </div>

  <!-- ── Print Header (only shows on paper) ────────────────────────── -->
  <div id="printHeader" style="display:none;" class="mb-3">
    <h4 style="color:#0038A8; font-weight:800;">Faire Church Scheduling — Booking Report</h4>
    <p id="printSubtitle" style="color:#374151; font-size:.9rem;"></p>
    <p style="color:#374151; font-size:.9rem; margin-top:4px;">
      Prepared by: <span style="display:inline-block; min-width:220px; border-bottom:1px solid #374151;">&nbsp;</span>
      &nbsp;&nbsp;&nbsp; Date: <span style="display:inline-block; min-width:140px; border-bottom:1px solid #374151;">&nbsp;</span>
    </p>
  </div>

  <!-- ── KPI Row ──────────────────────────────────────────────────────── -->
  <div class="row g-3 mb-3" id="kpiRow">
    <div class="col-xl-2 col-sm-4 col-6">
      <div class="kpi-card kpi-total d-flex justify-content-between align-items-center">
        <div><div class="kpi-label">Total</div><div class="kpi-value" id="kpi-total">—</div></div>
        <i class="fas fa-calendar-alt kpi-icon"></i>
      </div>
    </div>
    <div class="col-xl-2 col-sm-4 col-6">
      <div class="kpi-card kpi-approved d-flex justify-content-between align-items-center">
        <div><div class="kpi-label">Approved</div><div class="kpi-value" id="kpi-approved">—</div></div>
        <i class="fas fa-check-circle kpi-icon"></i>
      </div>
    </div>
    <div class="col-xl-2 col-sm-4 col-6">
      <div class="kpi-card kpi-pending d-flex justify-content-between align-items-center">
        <div><div class="kpi-label">Pending</div><div class="kpi-value" id="kpi-pending">—</div></div>
        <i class="fas fa-hourglass-half kpi-icon"></i>
      </div>
    </div>
    <div class="col-xl-2 col-sm-4 col-6">
      <div class="kpi-card kpi-completed d-flex justify-content-between align-items-center">
        <div><div class="kpi-label">Completed</div><div class="kpi-value" id="kpi-completed">—</div></div>
        <i class="fas fa-clipboard-check kpi-icon"></i>
      </div>
    </div>
    <div class="col-xl-2 col-sm-4 col-6">
      <div class="kpi-card kpi-denied d-flex justify-content-between align-items-center">
        <div><div class="kpi-label">Denied</div><div class="kpi-value" id="kpi-denied">—</div></div>
        <i class="fas fa-times-circle kpi-icon"></i>
      </div>
    </div>
    <div class="col-xl-2 col-sm-4 col-6">
      <div class="kpi-card kpi-cancelled d-flex justify-content-between align-items-center">
        <div><div class="kpi-label">Cancelled</div><div class="kpi-value" id="kpi-cancelled">—</div></div>
        <i class="fas fa-ban kpi-icon"></i>
      </div>
    </div>
  </div>

  <!-- ── Charts Row ────────────────────────────────────────────────────── -->
  <div class="row g-3 mb-3">
    <div class="col-xl-5 col-md-6">
      <div class="chart-card">
        <h6><i class="fas fa-chart-bar me-2"></i>Bookings by Status</h6>
        <canvas id="statusChart"></canvas>
      </div>
    </div>
    <div class="col-xl-4 col-md-6">
      <div class="chart-card">
        <h6><i class="fas fa-chart-pie me-2"></i>Bookings by Service</h6>
        <canvas id="serviceChart"></canvas>
      </div>
    </div>
    <div class="col-xl-3 col-md-12">
      <div class="chart-card">
        <h6><i class="fas fa-chart-line me-2"></i>Monthly Trend</h6>
        <canvas id="monthlyChart"></canvas>
      </div>
    </div>
  </div>

  <!-- ── Detail Table ──────────────────────────────────────────────────── -->
  <div class="card p-3">
    <div class="d-flex justify-content-between align-items-center mb-2 no-print">
      <h6 class="mb-0" style="color:#0038A8; font-weight:700;"><i class="fas fa-table me-2"></i>Booking Details</h6>
    </div>
    <div class="table-responsive">
      <table id="reportTable" class="table table-striped table-bordered align-middle w-100" style="font-size:.875rem;">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>Client</th>
            <th>Email</th>
            <th>Service</th>
            <th>Date</th>
            <th>Time</th>
            <th>Status</th>
            <th>Notes</th>
          </tr>
        </thead>
        <tbody id="reportTableBody"></tbody>
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
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
// ── Sidebar toggle ────────────────────────────────────────────────────────────
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('collapsed');
  document.getElementById('sidebar').classList.toggle('show');
}
document.addEventListener('click', function (e) {
  const sb  = document.getElementById('sidebar');
  const btn = document.querySelector('.toggle-button');
  if (window.innerWidth <= 768 && sb.classList.contains('show') &&
      !sb.contains(e.target) && btn && !btn.contains(e.target)) {
    sb.classList.remove('show');
  }
});

// ── Chart instances ───────────────────────────────────────────────────────────
let statusChart, serviceChart, monthlyChart, dtTable;

const STATUS_COLOURS = {
  'Pending':   '#f59e0b',
  'Approved':  '#22c55e',
  'Completed': '#3b82f6',
  'Denied':    '#ef4444',
  'Cancelled': '#6b7280',
  'Canceled':  '#6b7280',
};

function buildStatusColours(labels) {
  return labels.map(l => STATUS_COLOURS[l] || '#94a3b8');
}

// ── Init / update charts ──────────────────────────────────────────────────────
function initCharts(d) {
  const ctxStatus  = document.getElementById('statusChart').getContext('2d');
  const ctxService = document.getElementById('serviceChart').getContext('2d');
  const ctxMonthly = document.getElementById('monthlyChart').getContext('2d');

  if (statusChart)  statusChart.destroy();
  if (serviceChart) serviceChart.destroy();
  if (monthlyChart) monthlyChart.destroy();

  // Bar: by status
  statusChart = new Chart(ctxStatus, {
    type: 'bar',
    data: {
      labels: d.byStatus.labels,
      datasets: [{
        label: 'Bookings',
        data: d.byStatus.data,
        backgroundColor: buildStatusColours(d.byStatus.labels),
        borderRadius: 6,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
    }
  });

  // Doughnut: by service
  const svcColours = ['#0038A8','#FCD116','#CE1126','#22c55e','#f59e0b','#3b82f6','#a855f7','#14b8a6'];
  serviceChart = new Chart(ctxService, {
    type: 'doughnut',
    data: {
      labels: d.byService.labels,
      datasets: [{
        data: d.byService.data,
        backgroundColor: svcColours.slice(0, d.byService.labels.length),
        hoverOffset: 10,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } }
    }
  });

  // Line: monthly trend
  monthlyChart = new Chart(ctxMonthly, {
    type: 'line',
    data: {
      labels: d.monthly.labels,
      datasets: [{
        label: 'Bookings',
        data: d.monthly.data,
        tension: .35,
        fill: true,
        borderColor: '#0038A8',
        backgroundColor: 'rgba(0,56,168,.10)',
        pointBackgroundColor: '#0038A8',
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
    }
  });
}

// ── Render KPI cards ──────────────────────────────────────────────────────────
function renderKPIs(k) {
  document.getElementById('kpi-total').textContent     = k.total     || 0;
  document.getElementById('kpi-approved').textContent  = k.approved  || 0;
  document.getElementById('kpi-pending').textContent   = k.pending   || 0;
  document.getElementById('kpi-completed').textContent = k.completed || 0;
  document.getElementById('kpi-denied').textContent    = k.denied    || 0;
  document.getElementById('kpi-cancelled').textContent = k.cancelled || 0;
}

// ── Render detail table ───────────────────────────────────────────────────────
function renderTable(rows) {
  const tbody = document.getElementById('reportTableBody');
  if (!rows || !rows.length) {
    tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-3">No records found.</td></tr>';
    return;
  }
  tbody.innerHTML = rows.map((r, i) => {
    const date = r.date ? new Date(r.date + 'T00:00:00').toLocaleDateString('en-PH',
                   { year:'numeric', month:'short', day:'2-digit' }) : '—';
    const t1 = r.time_start ? fmtTime(r.time_start) : '';
    const t2 = r.time_end   ? fmtTime(r.time_end)   : '';
    const time = t1 && t2 ? `${t1} – ${t2}` : (t1 || '—');
    const st = r.status || 'Pending';
    const badge = `<span class="status-pill badge-${st}">${esc(st)}</span>`;
    return `<tr>
      <td>${i+1}</td>
      <td>${esc(r.client || '—')}</td>
      <td>${esc(r.email  || '—')}</td>
      <td>${esc(r.service)}</td>
      <td>${date}</td>
      <td style="white-space:nowrap">${time}</td>
      <td>${badge}</td>
      <td>${esc(r.notes || '—')}</td>
    </tr>`;
  }).join('');
}

function fmtTime(t) {
  const [h, m] = t.split(':').map(Number);
  const ampm = h >= 12 ? 'PM' : 'AM';
  return `${(h % 12) || 12}:${String(m).padStart(2,'0')} ${ampm}`;
}
function esc(s) {
  return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// ── DataTable init ────────────────────────────────────────────────────────────
function initDataTable() {
  // NOTE: dtTable.destroy() must be called BEFORE renderTable() fills tbody,
  // otherwise destroy() wipes out the freshly rendered rows.
  dtTable = $('#reportTable').DataTable({
    responsive: true,
    order: [[4, 'desc']],
    pageLength: 15,
    dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
         "<'row'<'col-sm-12'tr>>" +
         "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
    buttons: [
      {
        extend: 'print',
        text: '<i class="fas fa-print me-2"></i>Print Table',
        className: 'btn btn-dark btn-sm',
        title: '',
        exportOptions: { columns: ':visible' },
        customize: function (win) {
          const header = win.document.createElement('div');
          const subtitle = document.getElementById('printSubtitle').textContent;
          header.innerHTML = `
            <div style="margin-bottom:10px;">
              <div style="font-size:16px;font-weight:800;color:#0038A8;">Faire Church Scheduling — Booking Report</div>
              <div style="font-size:12px;color:#374151;">${subtitle}</div>
              <div style="font-size:12px;color:#374151;margin-top:6px;">
                Prepared by: <span style="display:inline-block;min-width:220px;border-bottom:1px solid #374151;">&nbsp;</span>
                &nbsp;&nbsp;&nbsp; Date: <span style="display:inline-block;min-width:140px;border-bottom:1px solid #374151;">&nbsp;</span>
              </div>
            </div>`;
          win.document.body.insertBefore(header, win.document.body.firstChild);
          const style = win.document.createElement('style');
          style.textContent = `@page{size:A4 landscape;margin:12mm}body{font-size:11px}table{width:100%}th,td{padding:5px 7px}`;
          win.document.head.appendChild(style);
        }
      }
    ]
  });
}

// ── Load data ─────────────────────────────────────────────────────────────────
function loadReport() {
  const dateFrom = document.getElementById('filterFrom').value;
  const dateTo   = document.getElementById('filterTo').value;
  const service  = document.getElementById('filterService').value;

  if (!dateFrom || !dateTo) {
    alert('Please select both From and To dates.');
    return;
  }
  if (dateTo < dateFrom) {
    alert('"To" date must be on or after "From" date.');
    return;
  }

  // Update print subtitle
  const svcSel  = document.getElementById('filterService');
  const svcName = svcSel.options[svcSel.selectedIndex].text;
  const fmtDate = d => { const [y,m,day]=d.split('-'); return `${m}/${day}/${y}`; };
  const subtitle = `From: ${fmtDate(dateFrom)}  →  To: ${fmtDate(dateTo)}  |  Service: ${+service === 0 ? 'All' : svcName}`;
  document.getElementById('printSubtitle').textContent = subtitle;

  fetch(`get_report_data.php?date_from=${dateFrom}&date_to=${dateTo}&service=${service}`, { cache: 'no-store' })
    .then(r => r.json())
    .then(d => {
      if (d.error) {
        console.error('Report error:', d.error);
        alert('Error loading report: ' + d.error);
        return;
      }
      renderKPIs(d.kpis);
      initCharts(d);
      // Destroy FIRST so DataTable doesn't wipe the fresh rows
      if (dtTable) { dtTable.destroy(); dtTable = null; }
      renderTable(d.details);
      initDataTable();
    })
    .catch(err => {
      console.error('Report fetch failed:', err);
      alert('Failed to load report data. Check the console for details.');
    });
}

// ── Print full page ───────────────────────────────────────────────────────────
function printReport() {
  document.getElementById('printHeader').style.display = 'block';
  window.print();
  setTimeout(() => { document.getElementById('printHeader').style.display = 'none'; }, 800);
}

// ── Bootstrap ────────────────────────────────────────────────────────────────
document.getElementById('applyFilter').addEventListener('click', loadReport);
document.addEventListener('DOMContentLoaded', loadReport);
</script>
</body>
</html>
