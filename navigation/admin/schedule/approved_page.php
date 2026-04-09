<?php 
session_start();
if (!isset($_SESSION['userid'])) { header("Location: ../../logout.php"); exit(); }

$userid    = (int)$_SESSION['userid'];
$lastname  = $_SESSION['lastname']   ?? '';
$firstname = $_SESSION['firstname']  ?? '';
$middlename= $_SESSION['middlename'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Approved Schedules</title>

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
    #approvedTable th, #approvedTable td { white-space: normal; word-break: break-word; }
    .fc-event.event-approved { background-color: #adb5bd !important; border-color: #adb5bd !important; color: #000 !important; }
    .fc-event.event-mine     { background-color: #0d6efd !important; border-color: #0d6efd !important; color: #fff !important; }

    /* Print styles */
    .no-print { display: initial; }
    @media print {
      @page { size: A4 landscape; margin: 12mm; }
      * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }

      /* Hide UI chrome when printing */
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
      .no-print {
        display: none !important;
      }

      /* Expand content area */
      #content,
      .container-fluid,
      .card {
        margin: 0 !important;
        padding: 0 !important;
        box-shadow: none !important;
        border: 0 !important;
      }

      /* Tight table for paper */
      table.dataTable thead th,
      table.dataTable tbody td {
        padding: 6px 8px !important;
        white-space: nowrap !important;
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
        <li class="my-1"><a class="fw-bold" href="./approved_page.php"><i class="fas fa-check-circle me-2"></i>Approved</a></li>
        <li class="my-1"><a href="./completed_page.php"><i class="fas fa-clipboard-check me-2"></i>Completed</a></li>
      </ul>
    </li>

    <hr>
    <li class="p-1 navbar-custom my-2"><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a></li>
  </ul>
</nav>

<!-- Main Content -->
<div id="content">
  <div class="container-fluid mt-4">
    <div class="d-flex align-items-center mb-3">
      <h3 class="mb-0">Approved Schedules</h3>
    </div>

    <div class="card p-3">
      <table id="approvedTable" class="table table-striped table-bordered align-middle w-100">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Client</th>
            <th>Contact</th>
            <th>Other Contact</th>
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
          <dt class="col-sm-3">Other contact</dt><dd class="col-sm-9" id="m_ocp"></dd>
          <dt class="col-sm-3">Other contact no.</dt><dd class="col-sm-9" id="m_ocp_phone"></dd>
          <dt class="col-sm-3">Date</dt><dd class="col-sm-9" id="m_date"></dd>
          <dt class="col-sm-3">Time</dt><dd class="col-sm-9" id="m_time"></dd>
          <dt class="col-sm-3">Created</dt><dd class="col-sm-9" id="m_created"></dd>
          <dt class="col-sm-3">Service Notes</dt><dd class="col-sm-9" id="m_service_desc"></dd>
        </dl>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-primary d-none" id="modalComplete">
          <i class="fas fa-flag-checkered me-1"></i>Complete
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Scripts: order matters -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<!-- Buttons for DataTables (Print) -->
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dayjs@1/plugin/localizedFormat.js"></script>
<script>dayjs.extend(window.dayjs_plugin_localizedFormat);</script>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/index.global.min.js"></script>

<script>
// Helpers
function canComplete(dateStr){
  if (!dateStr) return false;
  const d = dayjs(dateStr).startOf('day');
  const today = dayjs().startOf('day');
  return d.diff(today) <= 0; // allow if date is today or earlier
}

let table, currentRowData = null, fcCal = null;
const CURRENT_USER_ID = <?= json_encode($userid) ?>;

$(function () {
  table = $('#approvedTable').DataTable({
    ajax: { url: './fetch_pending_schedules.php?status=Approved', dataSrc: 'data' },
    processing: true,
    autoWidth: false,
    responsive: { details: { type: 'inline' } },
    order: [[0,'desc']],
    // 🔽 Show Buttons bar (Print), then filter; table; info + pagination
    dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
         "<'row'<'col-sm-12'tr>>" +
         "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
    buttons: [
      {
        extend: 'print',
        text: '<i class="fas fa-print me-2" style="font-size: 18px;"></i>Print list',
        className: 'btn btn-dark',
        title: '', // disable default big title
        exportOptions: { columns: ':not(.no-export)' }, // exclude Actions
        customize: function (win) {
          // Inject a small custom title
          const header = win.document.createElement('div');
          header.innerHTML = '<div style="font-size:14px;font-weight:600;margin:0 0 8px;">Approved Schedules</div>';
          win.document.body.insertBefore(header, win.document.body.firstChild);

          // Tight print CSS
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
    ],
    columnDefs: [
      { targets: 0, responsivePriority: 1, width: '60px' },
      { targets: 1, responsivePriority: 2 },
      { targets: 8, responsivePriority: 1, orderable:false, searchable:false, className:'no-export' } // exclude from print
    ],
    columns: [
      { data: 'ID' },
      { data: null, render: r => `${r.firstname || ''} ${r.lastname || ''}`.trim() },
      { data: null, render: r => `${r.mobilenumber || ''}<br><small>${r.email || ''}</small>` },
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
      { data: null, render: r => {
          const ok = canComplete(r.date);
          const completeBtn = ok
            ? `<button class="btn btn-success btn-sm btn-complete" data-id="${r.ID}">
                 <i class="fas fa-flag-checkered fs-6"></i> Complete
               </button>`
            : `<button class="btn btn-secondary btn-sm" disabled data-bs-toggle="tooltip"
                 title="Completable on or after the event date">
                 <i class="fas fa-flag-checkered fs-6"></i> Complete
               </button>`;
          return `
            <div class="btn-group btn-group-sm" role="group">
              <button class="btn btn-primary btn-view" data-id="${r.ID}">
                <i class="fas fa-eye fs-6"></i> View
              </button>
              ${completeBtn}
            </div>`;
        }
      }
    ],
    drawCallback: function(){
      document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
    }
  });

  // View
  $('#approvedTable').on('click', '.btn-view', function(){
    const row = table.row($(this).closest('tr')).data();
    currentRowData = row;
    fillModal(row);

    const modalEl = document.getElementById('viewModal');
    modalEl.addEventListener('shown.bs.modal', function handler() {
      renderCalendar(row);
      modalEl.removeEventListener('shown.bs.modal', handler);
    }, { once: true });

    const isToday = row.date && dayjs(row.date).isSame(dayjs(), 'day');
    document.getElementById('modalComplete').classList.toggle('d-none', !isToday);
    document.getElementById('modalComplete').setAttribute('data-id', row.ID);

    new bootstrap.Modal(modalEl).show();
  });

  // Complete from row
  $('#approvedTable').on('click', '.btn-complete', function(){
    completeSchedule($(this).data('id'));
  });

  // Complete from modal
  $('#modalComplete').on('click', function(){
    const id = this.getAttribute('data-id');
    if (id) completeSchedule(id);
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
  $('#m_client').text(`${r.firstname || ''} ${r.lastname || ''} ${r.middlename || ''}`.trim());
  $('#m_email').text(r.email || '');
  $('#m_mobile').text(r.mobilenumber || '');
  $('#m_ocp').text(r.other_contact_person || '');
  $('#m_ocp_phone').text(r.contact_phone || '');
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

  const selectedEvent = {
    id: `selected-${selected.ID}`,
    title: selected.service_name || 'Reservation',
    start: `${dateStr}T${tStart}:00`,
    end:   `${dateStr}T${tEnd}:00`,
    classNames: ['event-mine']
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
              else if ((ev.status || '') === 'Approved') classes.push('event-approved');

              return { id: `evt-${ev.id}`, title: ev.title || 'Reservation', start: ev.start, end: ev.end, classNames: classes };
            });
          success([...others, selectedEvent]);
        })
        .fail(err => failure(err));
    }
  });

  fcCal.render();
}

function completeSchedule(id){
  Swal.fire({
    icon: 'question',
    title: 'Mark as Completed?',
    text: 'This will move the reservation to Completed.',
    showCancelButton: true,
    confirmButtonText: 'Yes, complete it',
    cancelButtonText: 'Cancel'
  }).then((result)=>{
    if(!result.isConfirmed) return;

    const $btns = $('.btn-complete, #modalComplete').prop('disabled', true);

    $.post('./update_schedule_status.php', { id, status: 'Completed' }, function(resp){
      let ok = false, msg = '';
      try{
        const r = typeof resp === 'object' ? resp : JSON.parse(resp);
        ok  = !!r.success;
        msg = r.message || (ok ? `Schedule #${id} marked as Completed.` : 'Update failed.');
      }catch(e){
        msg = (resp || '').toString().slice(0, 400) || 'Update failed.';
      }

      $btns.prop('disabled', false);

      if(ok){
        const vm = bootstrap.Modal.getInstance(document.getElementById('viewModal'));
        if (vm) vm.hide();
        Swal.fire({ icon:'success', title:'Completed!', text: msg, timer:1400, showConfirmButton:false })
          .then(()=>{ if (table) table.ajax.reload(null, false); if (fcCal) fcCal.refetchEvents(); });
      }else{
        Swal.fire({ icon:'error', title:'Error', text: msg });
      }
    }).fail(xhr=>{
      $btns.prop('disabled', false);
      Swal.fire({ icon:'error', title:'Server error', text:(xhr.responseText||'').toString().slice(0,400)||'Please try again.' });
    });
  });
}
</script>
</body>
</html>
