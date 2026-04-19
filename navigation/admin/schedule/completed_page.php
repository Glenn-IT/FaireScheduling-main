<?php 
session_start();
if (!isset($_SESSION['userid'])) { header("Location: ../../logout.php"); exit(); }

$userid     = $_SESSION['userid'];
$lastname   = $_SESSION['lastname'];
$firstname  = $_SESSION['firstname'];
$middlename = $_SESSION['middlename'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Completed Schedules</title>

  <!-- Bootstrap & FontAwesome -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

  <!-- Your styles -->
  <link rel="stylesheet" href="../css/style.css?<?= filemtime('../css/style.css'); ?>">

  <!-- DataTables (Bootstrap 5 + Responsive) -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

  <!-- DataTables Buttons (for Print) -->
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

  <!-- FullCalendar -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/index.global.min.css">

  <style>
    #completedTable th, #completedTable td { white-space: normal; word-break: break-word; }

    /* Calendar coloring */
    .fc-event.event-approved { background-color: #adb5bd !important; border-color: #adb5bd !important; color: #000 !important; }
    .fc-event.event-mine     { background-color: #0d6efd !important; border-color: #0d6efd !important; color: #fff !important; }

    /* Print styles */
    .no-print { display: initial; }
    @media print {
      @page { size: A4 landscape; margin: 12mm; }
      * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }

      #sidebar,
      .dataTables_length,
      .dataTables_filter,
      .dataTables_info,
      .dataTables_paginate,
      .dt-buttons,
      .btn,
      nav,
      header,
      footer,
      .modal,
      .no-print { display: none !important; }

      #content, .container-fluid, .card {
        margin: 0 !important; padding: 0 !important; box-shadow: none !important; border: 0 !important;
      }

      table.dataTable thead th, table.dataTable tbody td {
        padding: 6px 8px !important; white-space: nowrap !important;
      }
      table { width: 100% !important; }
    }
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
      <a class="dropdown-toggle d-flex align-items-center" data-bs-toggle="collapse" href="#scheduleSubmenu" role="button" aria-expanded="true" aria-controls="scheduleSubmenu">
        <i class="fas fa-calendar me-2"></i><span>Schedules</span>
      </a>
      <ul class="collapse show list-unstyled ps-3 mt-1" id="scheduleSubmenu">
        <li class="my-1"><a href="./pending_page.php"><i class="fas fa-hourglass-half me-2"></i>Pending</a></li>
        <li class="my-1"><a href="./approved_page.php"><i class="fas fa-check-circle me-2"></i>Approved</a></li>
        <li class="my-1"><a class="fw-bold" href="./completed_page.php"><i class="fas fa-clipboard-check me-2"></i>Completed</a></li>
      </ul>
    </li>

    <hr>
    <li class="p-1 navbar-custom my-2"><a href="../report/report.php"><i class="fas fa-chart-bar"></i><span> Reports</span></a></li>
    <hr>
    <li class="p-1 navbar-custom my-2"><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a></li>
  </ul>
</nav>

<!-- Main Content -->
<div id="content">
  <div class="container-fluid mt-4">
    <div class="d-flex align-items-center mb-3">
      <h3 class="mb-0">Completed Schedules</h3>
    </div>

    <div class="card p-3">
      <table id="completedTable" class="table table-striped table-bordered align-middle w-100">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Client</th>
            <th>Contact</th>
            <th>Other Contact</th> <!-- NEW -->
            <th>Service</th>
            <th>Date</th>
            <th>Time</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-calendar me-2"></i>Schedule Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <h6 class="mb-2">Reservation Calendar</h6>
        <div id="miniCalendar"></div>

        <hr class="my-3">
        <dl class="row mb-0">
          <dt class="col-sm-3">Schedule ID</dt><dd class="col-sm-9" id="m_id"></dd>
          <dt class="col-sm-3">Status</dt><dd class="col-sm-9" id="m_status"></dd>
          <dt class="col-sm-3">Service</dt><dd class="col-sm-9" id="m_service"></dd>
          <dt class="col-sm-3">Client</dt><dd class="col-sm-9" id="m_client"></dd>
          <dt class="col-sm-3">Email</dt><dd class="col-sm-9" id="m_email"></dd>
          <dt class="col-sm-3">Mobile</dt><dd class="col-sm-9" id="m_mobile"></dd>
          <dt class="col-sm-3">Date</dt><dd class="col-sm-9" id="m_date"></dd>
          <dt class="col-sm-3">Time</dt><dd class="col-sm-9" id="m_time"></dd>
          <dt class="col-sm-3">Created</dt><dd class="col-sm-9" id="m_created"></dd>
          <dt class="col-sm-3">Service Notes</dt><dd class="col-sm-9" id="m_service_desc"></dd>
        </dl>
      </div>

      <div class="modal-footer"></div>
    </div>
  </div>
</div>

<!-- Scripts: order matters -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables core + Bootstrap 5 adapter -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- DataTables Responsive plugin + Bootstrap 5 adapter -->
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<!-- DataTables Buttons (Print) -->
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Day.js -->
<script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dayjs@1/plugin/localizedFormat.js"></script>
<script>dayjs.extend(window.dayjs_plugin_localizedFormat);</script>

<!-- FullCalendar -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/index.global.min.js"></script>

<script>
let table, currentRowData = null, fcCal = null;
const CURRENT_USER_ID = <?= json_encode($userid) ?>;

$(function () {
  table = $('#completedTable').DataTable({
    ajax: { url: './fetch_pending_schedules.php?status=Completed', dataSrc: 'data' },
    processing: true,
    autoWidth: false,
    responsive: { details: { type: 'inline' } },
    order: [[0,'desc']],
    dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
         "<'row'<'col-sm-12'tr>>" +
         "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
    buttons: [
      {
        extend: 'print',
        text: '<i class="fas fa-print me-2"></i>Print All',
        className: 'btn btn-dark',
        title: '',
        exportOptions: { columns: ':not(.no-export)' },
        customize: function (win) {
          const header = win.document.createElement('div');
          header.innerHTML = '<div style="font-size:14px;font-weight:600;margin:0 0 8px;">Completed Schedules</div>';
          win.document.body.insertBefore(header, win.document.body.firstChild);
          const css = `@page{size:A4 landscape;margin:12mm;}body{font-size:12px!important;}table{width:100%!important;}table.dataTable thead th,table.dataTable tbody td{padding:6px 8px!important;}`;
          const style = win.document.createElement('style');
          style.type = 'text/css';
          style.appendChild(win.document.createTextNode(css));
          (win.document.head || win.document.getElementsByTagName('head')[0]).appendChild(style);
        }
      },
      {
        extend: 'print',
        text: '<i class="fas fa-print me-2"></i>Print Mass',
        className: 'btn btn-info',
        title: '',
        exportOptions: {
          columns: ':not(.no-export)',
          rows: function(idx) {
            const d = table.row(idx).data();
            return d && (d.service_name || '').toLowerCase() === 'mass';
          }
        },
        customize: function (win) {
          const header = win.document.createElement('div');
          header.innerHTML = '<div style="font-size:14px;font-weight:600;margin:0 0 8px;">Completed Schedules — Mass</div>';
          win.document.body.insertBefore(header, win.document.body.firstChild);
          const css = `@page{size:A4 landscape;margin:12mm;}body{font-size:12px!important;}table{width:100%!important;}table.dataTable thead th,table.dataTable tbody td{padding:6px 8px!important;}`;
          const style = win.document.createElement('style');
          style.type = 'text/css';
          style.appendChild(win.document.createTextNode(css));
          (win.document.head || win.document.getElementsByTagName('head')[0]).appendChild(style);
        }
      },
      {
        extend: 'print',
        text: '<i class="fas fa-print me-2"></i>Print Wedding',
        className: 'btn btn-warning',
        title: '',
        exportOptions: {
          columns: ':not(.no-export)',
          rows: function(idx) {
            const d = table.row(idx).data();
            return d && (d.service_name || '').toLowerCase() === 'wedding';
          }
        },
        customize: function (win) {
          const header = win.document.createElement('div');
          header.innerHTML = '<div style="font-size:14px;font-weight:600;margin:0 0 8px;">Completed Schedules — Wedding</div>';
          win.document.body.insertBefore(header, win.document.body.firstChild);
          const css = `@page{size:A4 landscape;margin:12mm;}body{font-size:12px!important;}table{width:100%!important;}table.dataTable thead th,table.dataTable tbody td{padding:6px 8px!important;}`;
          const style = win.document.createElement('style');
          style.type = 'text/css';
          style.appendChild(win.document.createTextNode(css));
          (win.document.head || win.document.getElementsByTagName('head')[0]).appendChild(style);
        }
      }
    ],
    columnDefs: [
      { targets: 0, responsivePriority: 1, width: '60px' },                 // ID
      { targets: 1, responsivePriority: 2 },                                 // Client
      { targets: 8, responsivePriority: 1, orderable:false, searchable:false, className:'no-export' } // Actions (exclude from print)
    ],
    columns: [
      { data: 'ID' },
      { data: null, render: r => `${r.firstname} ${r.lastname}` },
      { data: null, render: r => `${r.mobilenumber || ''}<br><small>${r.email || ''}</small>` },

      // NEW: Other Contact (name · number)
      { data: null, render: r => {
          const p  = (r.other_contact_person || '').toString().trim();
          const ph = (r.contact_phone || '').toString().trim();
          if (!p && !ph) return '<span class="text-muted">—</span>';
          return `${p || ''}${p && ph ? ' · ' : ''}${ph || ''}`;
        }
      },

      { data: 'service_name' },
      { data: 'date', render: d => d ? dayjs(d).format('MMM D, YYYY') : '' },
      { data: null, render: r => {
          const t1 = r.time_start ? dayjs(`1970-01-01 ${r.time_start}`).format('h:mm A') : '';
          const t2 = r.time_end   ? dayjs(`1970-01-01 ${r.time_end}`).format('h:mm A')   : '';
          return t1 && t2 ? `${t1} – ${t2}` : (t1 || t2 || '');
        }
      },
      { data: 'date_created', render: d => d ? dayjs(d).format('MMM D, YYYY h:mm A') : '' },

      { data: null, render: r => `
          <button class="btn btn-primary btn-sm btn-view" data-id="${r.ID}">
            <i class="fas fa-eye fs-6"></i> View
          </button>`
      }
    ]
  });

  // View (modal)
  $('#completedTable').on('click', '.btn-view', function(){
    const row = table.row($(this).closest('tr')).data();
    currentRowData = row;
    fillModal(row);

    const modalEl = document.getElementById('viewModal');
    modalEl.addEventListener('shown.bs.modal', function handler() {
      renderCalendar(row);
      modalEl.removeEventListener('shown.bs.modal', handler);
    }, { once: true });

    new bootstrap.Modal(modalEl).show();
  });
});

function fillModal(r){
  const niceDate      = r.date ? dayjs(r.date).format('MMM D, YYYY') : '';
  const niceStartTime = r.time_start ? dayjs(`1970-01-01 ${r.time_start}`).format('h:mm A') : '';
  const niceEndTime   = r.time_end   ? dayjs(`1970-01-01 ${r.time_end}`).format('h:mm A')   : '';
  const niceCreated   = r.date_created ? dayjs(r.date_created).format('MMM D, YYYY h:mm A') : '';
  const niceTimeRange = (niceStartTime && niceEndTime) ? `${niceStartTime} – ${niceEndTime}` : (niceStartTime || niceEndTime || '');

  $('#m_id').text(r.ID);
  $('#m_status').text(r.status || '');
  $('#m_service').text(r.service_name || '');
  $('#m_client').text(`${r.firstname} ${r.lastname} ${r.middlename || ''}`.trim());
  $('#m_email').text(r.email || '');
  $('#m_mobile').text(r.mobilenumber || '');
  $('#m_date').text(niceDate);
  $('#m_time').text(niceTimeRange);
  $('#m_created').text(niceCreated);
  $('#m_service_desc').text(r.service_description || '');
}

function renderCalendar(selected){
  if (fcCal) { fcCal.destroy(); fcCal = null; }
  const container = document.getElementById('miniCalendar');
  container.innerHTML = '';

  const dateStr = selected.date || dayjs().format('YYYY-MM-DD');
  const padTime = (t) => (t || '').slice(0,5) || '09:00';
  const tStart = padTime(selected.time_start);
  const tEnd   = padTime(selected.time_end || selected.time_start || '10:00');

  const selectedClasses = (String(selected.userID) === String(CURRENT_USER_ID))
    ? ['event-mine'] : ['event-approved'];

  const selectedEvent = {
    id: `selected-${selected.ID}`,
    title: selected.service_name || 'Reservation',
    start: `${dateStr}T${tStart}:00`,
    end:   `${dateStr}T${tEnd}:00`,
    classNames: selectedClasses
  };

  fcCal = new FullCalendar.Calendar(container, {
    initialView: 'dayGridMonth',
    initialDate: dateStr,
    height: 'auto',
    handleWindowResize: true,
    headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay' },
    displayEventTime: false,
    events: (fetchInfo, success, failure) => {
      $.getJSON('./fetch_calendar_events.php', { start: fetchInfo.startStr, end: fetchInfo.endStr })
        .done(list => {
          const others = (list || [])
            .filter(ev => String(ev.id) !== String(selected.ID))
            .map(ev => {
              const isMine = String(ev.userID) === String(CURRENT_USER_ID);
              const classes = [];
              if (isMine) classes.push('event-mine');
              else classes.push('event-approved');
              return { id: `evt-${ev.id}`, title: ev.title || 'Reservation', start: ev.start, end: ev.end, classNames: classes };
            });
          success([...others, selectedEvent]);
        })
        .fail(err => failure(err));
    }
  });

  fcCal.render();
}

// ── Auto-update: cancel overdue pending, complete overdue approved ──
(function autoUpdateSchedules() {
  fetch('./auto_update_schedules.php')
    .then(r => r.json())
    .then(data => {
      if (data.cancelled > 0 || data.completed > 0) {
        if (typeof table !== 'undefined' && table) table.ajax.reload(null, false);
      }
    })
    .catch(() => {});
})();
</script>
</body>
</html>
