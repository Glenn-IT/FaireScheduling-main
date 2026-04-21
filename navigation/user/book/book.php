<?php 
session_start();
// Prevent browser caching — stops back-button re-entry after logout
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
if (!isset($_SESSION['userid'])) { header("Location: ../../../logout.php"); exit; }
$userid = (int)$_SESSION['userid'];

require '../../../database/connection.php';

/* Optional service (type) filter: ?service=1 (e.g., Wedding, Christening, etc.) */
$sid = isset($_GET['service']) ? (int)$_GET['service'] : 0;

/* Load services for the dropdown */
try {
  $svcStmt = $conn->query("SELECT id, service_name FROM services ORDER BY service_name");
  $services = $svcStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  $services = [];
}

$titleText = 'Book a Schedule';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Faire Church • Book</title>

  <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" rel="stylesheet"> 
  <link rel="stylesheet" href="../css/bootstrap.css">
  <link rel="stylesheet" href="../css/font-awesome.min.css">
  <link rel="stylesheet" href="../css/main.css">

  <!-- FullCalendar v5 -->
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet"/>

  <style>
    /* Calendar basics */
    #calendar { max-width: 1100px; margin: 0 auto; }
    .fc .fc-toolbar-title { font-size: 1.1rem; }
    .fc-daygrid-day-frame { cursor: pointer; }
    .fc-timegrid-slot, .fc-timegrid-axis-cushion { cursor: pointer; }
    .required::after { content:' *'; color:#dc3545; }

    /* ===== Notifications & Profile dropdowns ===== */
    .nav-notif { position: relative; margin-left: .5rem; }
    .notif-btn{
      background:transparent; border:0; color:#fff; font-size:18px; cursor:pointer;
      position:relative; padding:.25rem .35rem; line-height:1;
      height:40px; display:grid; place-items:center; border-radius:999px;
    }
    .notif-badge{
      position:absolute; top:3px; right:3px; min-width:18px; padding:2px 6px;
      background:#ef4444; color:#fff; border-radius:999px; font-size:11px; font-weight:700;
      box-shadow:0 1px 0 rgba(0,0,0,.15);
    }
    .notif-panel{
      display:none; position:absolute; right:0; top:40px; width:340px; max-height:420px; overflow:auto;
      background:#fff; color:#0f172a; border:1px solid #e5e7eb; border-radius:12px;
      box-shadow:0 18px 40px rgba(2,6,23,.18); z-index:1000; overflow-x:hidden;
    }
    .notif-panel.show{ display:block; }
    .notif-head{
      display:flex; justify-content:space-between; align-items:center;
      padding:10px 12px; border-bottom:1px solid #e5e7eb; background:#f8fafc;
    }
    .markall{
      background:transparent; border:0; color:#2563eb; font-weight:600; cursor:pointer; padding:4px 6px;
    }
    .notif-list{ padding:6px; }
    .notif-item{
      display:flex; gap:10px; padding:10px; border-radius:10px; cursor:pointer;
      border:1px solid transparent;
    }
    .notif-item:hover{ background:#f8fafc; border-color:#e5e7eb; }
    .notif-icon{
      min-width:28px; height:28px; display:inline-grid; place-items:center;
      border-radius:8px; background:#eef2ff; color:#1e3a8a; flex:0 0 28px;
    }
    .notif-body{ flex:1; min-width:0; }
    .notif-title{
      font-weight:700; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
    }
    .notif-msg{
      color:#475569; font-size:.92rem;
      overflow:hidden; display:-webkit-box; -webkit-box-orient:vertical; -webkit-line-clamp:2;
    }
    @supports not (-webkit-line-clamp:2) { .notif-msg{ white-space:nowrap; text-overflow:ellipsis; } }
    .notif-time{ color:#64748b; font-size:.82rem; margin-top:2px; }
    .notif-empty{ padding:20px; text-align:center; color:#64748b; }
    .notif-footer{
      display:flex; align-items:center; justify-content:center; gap:.25rem;
      padding:10px 12px; border-top:1px solid #e5e7eb; color:#2563eb; text-decoration:none;
    }

    .nav-profile { position: relative; margin-left: .5rem; }
    .profile-btn{
      background:transparent; border:0; color:#fff; font-size:18px; cursor:pointer;
      width:40px; height:40px; border-radius:999px; display:grid; place-items:center;
    }
    .profile-panel{
      display:none; position:absolute; right:0; top:40px; width:200px;
      background:#fff; color:#0f172a; border:1px solid #e5e7eb; border-radius:12px;
      box-shadow:0 18px 40px rgba(2,6,23,.18); z-index:1000; overflow:hidden;
    }
    .profile-panel.show{ display:block; }
    .profile-head{
      padding:10px 12px; font-weight:700; background:#f8fafc; border-bottom:1px solid #e5e7eb;
    }
    .profile-item{
      display:flex; align-items:center; gap:.55rem;
      padding:10px 12px; text-decoration:none; color:#0f172a;
    }
    .profile-item i{ width:18px; text-align:center; opacity:.9; }
    .profile-item:hover{ background:#f8fafc; }

    /* Align main nav, bell, and profile icon */
    #nav-menu-container > ul.nav-menu{ display:flex; align-items:center; margin:0; }
    #nav-menu-container > ul.nav-menu > li{ float:none; display:flex; align-items:center; }
    #nav-menu-container .nav-menu > li > a{
      display:flex; align-items:center; height:40px; padding:0 8px; line-height:1;
    }

    @media (max-width: 991px){
      #nav-menu-container > ul.nav-menu{ gap:14px; }
    }
  </style>
</head>
<body>
  <header id="header">
    <div class="container main-menu">
      <div class="row align-items-center justify-content-between d-flex">
        <div id="logo"><a href="../user_index.php">
            <img src="../../../img/bg_index1.jpg" alt="" style="width: 42px; border-radius: 50px; height: 40px;"></a></div>
        <nav id="nav-menu-container">
          <ul class="nav-menu">
            <li><a href="../user_index.php">Home</a></li>
            <li><a href="../about/about.php">About</a></li>
            <li><a href="../book/book.php">Book</a></li>
            <li><a href="../schedule/schedule.php">My Schedules</a></li>
            <li><a href="../contact/contact.php">Contact</a></li>

            <!-- Notification bell -->
            <li class="nav-notif">
              <button id="notifBtn" class="notif-btn" type="button"
                      aria-label="Notifications" aria-haspopup="true" aria-expanded="false"
                      aria-controls="notifDropdown">
                <i class="fa fa-bell"></i>
              </button>

              <div id="notifDropdown" class="notif-panel" role="menu" aria-hidden="true">
                <div class="notif-head">
                  <strong>Notifications</strong>
                  <button id="markAllBtn" class="markall" type="button">Mark all read</button>
                </div>
                      
                <div id="notifList" class="notif-list">
                  <div class="notif-empty">Loading…</div>
                </div>
              </div>
            </li>

            <!-- Profile menu -->
            <li class="nav-profile">
              <button id="profileBtn" class="profile-btn" type="button"
                      aria-label="Account menu" aria-haspopup="true" aria-expanded="false"
                      aria-controls="profileDropdown">
                <i class="fa fa-user-circle"></i>
              </button>

              <div id="profileDropdown" class="profile-panel" role="menu" aria-hidden="true">
                <div class="profile-head">Account</div>
                <a class="profile-item py-2" role="menuitem" href="../account/profile.php">
                  <i class="fa fa-user"></i>
                  <span class="text-dark">My Profile</span>
                </a>
                <a class="profile-item py-2" role="menuitem" href="../../../logout.php">
                  <i class="fa fa-sign-out"></i>
                  <span class="text-dark">Logout</span>
                </a>
              </div>
            </li>

          </ul>
        </nav>
      </div>
    </div>
  </header>

  <!-- Banner -->
  <section class="about-banner relative">
    <div class="overlay overlay-bg"></div>
    <div class="container">
      <div class="row d-flex align-items-center justify-content-center">
        <div class="about-content col-lg-12">
          <h1 class="text-white"><?=$titleText?></h1>
          <p class="text-white link-nav">
            <a href="../user_index.php">Home</a> <span class="lnr lnr-arrow-right"></span> 
            <a href="#">Book</a>
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- Booking / Calendar -->
  <section class="section-gap" style="background:#f8fafc;">
    <div class="container">
      <div class="row g-3 align-items-end mb-3">
        <div class="col-md-6">
          <h4 class="mb-1">Pick your date &amp; time</h4>
          <small class="text-muted">Existing bookings are shown as busy. Click a day to propose a time window.</small>
        </div>

        <!-- Service Filter -->
        <div class="col-md-6 text-md-end" style="justify-content:end; align-items:end; display:flex;">
          <label for="serviceFilter" class="form-label mb-1 me-2">Service Type</label>
          <select id="serviceFilter" class="form-select d-inline-block" style="max-width:320px;">
            <option value="">All Services</option>
            <?php foreach ($services as $svc): ?>
              <option value="<?= (int)$svc['id']; ?>" <?= $sid===(int)$svc['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($svc['service_name'] ?: ('Service '.$svc['id']), ENT_QUOTES); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- Legend (matching user_index) -->
      <div style="display:flex; flex-wrap:wrap; gap:10px; margin-bottom:16px;">
        <span class="cal-legend" style="background:#f59e0b; color:#fff; font-size:.78rem; font-weight:600; padding:3px 12px; border-radius:999px;">Pending</span>
        <span class="cal-legend" style="background:#22c55e; color:#fff; font-size:.78rem; font-weight:600; padding:3px 12px; border-radius:999px;">Approved</span>
        <span class="cal-legend" style="background:#3b82f6; color:#fff; font-size:.78rem; font-weight:600; padding:3px 12px; border-radius:999px;">Completed</span>
        <span class="cal-legend" style="background:#ef4444; color:#fff; font-size:.78rem; font-weight:600; padding:3px 12px; border-radius:999px;">Denied</span>
      </div>

      <div style="background:#fff; border-radius:16px; box-shadow:0 4px 24px rgba(30,58,138,.10); padding:24px;">
        <div id="calendar"></div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer-area section-gap">
    <div class="container">
      <div class="row footer-bottom d-flex justify-content-between align-items-center">
        <p class="col-lg-8 col-sm-12 footer-text m-0">&copy; <script>document.write(new Date().getFullYear())</script> Faire Church</p>
        <div class="col-lg-4 col-sm-12 footer-social text-lg-end">
          <a href="#"><i class="fa fa-facebook"></i></a>
          <a href="#"><i class="fa fa-twitter"></i></a>
        </div>
      </div>
    </div>
  </footer>

  <!-- Booking Modal (includes service type + contact person/number & notes) -->
  <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form id="bookingForm" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="bookingLabel">Book time on <span id="modalDate"></span></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="date" id="date">

          <div class="mb-3">
            <label class="form-label required">Service Type</label>
            <select class="form-select" id="serviceIDSelect" name="serviceID" required>
              <option value="">Select a service…</option>
              <?php foreach ($services as $svc): ?>
                <option value="<?= (int)$svc['id']; ?>" <?= $sid===(int)$svc['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($svc['service_name'] ?: ('Service '.$svc['id']), ENT_QUOTES); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label required">Time start</label>
              <input type="time" class="form-control" name="time_start" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label required">Time end</label>
              <input type="time" class="form-control" name="time_end" required>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Other contact person</label>
              <input type="text" class="form-control" name="other_contact_person" placeholder="Name">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Other contact number</label>
              <input type="tel" class="form-control" name="contact_phone" id="contact_phone"
                     placeholder="09xxxxxxxxx" maxlength="11"
                     pattern="^09\d{9}$"
                     title="Must be an 11-digit Philippine number starting with 09">
              <div class="invalid-feedback">Enter a valid PH number (09xxxxxxxxx, 11 digits).</div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label required">Address</label>
            <input type="text" class="form-control" name="address" id="address" placeholder="Enter your full address" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Notes (optional)</label>
            <textarea class="form-control" name="notes" rows="3" placeholder="Any details you want us to know"></textarea>
          </div>

          <div class="alert alert-info small mb-0">
            Tip: Please make sure your times don’t overlap another booking on the same day.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Submit Booking</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Scripts -->
  <script src="../js/vendor/jquery-2.2.4.min.js"></script>
  <script src="../js/popper.min.js"></script>
  <script src="../js/vendor/bootstrap.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>

  <script>
  document.addEventListener('DOMContentLoaded', function () {
    const initialService = <?=json_encode($sid ?: null)?>;

    const svcFilterEl = document.getElementById('serviceFilter');
    const svcSelectEl = document.getElementById('serviceIDSelect');

    /* ---------- FullCalendar ---------- */
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      height: 'auto',
      selectable: true,
      selectOverlap: false,
      headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay' },
      eventTimeFormat: { hour: 'numeric', minute: '2-digit', hour12: true },
      events: function(fetchInfo, success, failure){
        const serviceID = svcFilterEl.value || '';
        $.get('fetch_schedules.php', {
          start: fetchInfo.startStr,
          end: fetchInfo.endStr,
          serviceID: serviceID || ''
        })
        .done(list => success(list))
        .fail(err => failure(err));
      },
      dateClick: function(info) {
        const today = new Date(); today.setHours(0,0,0,0);
        const clicked = new Date(info.dateStr);
        if (clicked < today) {
          Swal.fire({ icon: 'warning', title: 'Pick a future date', text: 'Please choose today or a future date.' });
          return;
        }
        // Ensure modal service matches filter (so user knows what they’re booking)
        if (svcFilterEl.value) {
          svcSelectEl.value = svcFilterEl.value;
        }
        $('#modalDate').text(info.dateStr);
        $('#date').val(info.dateStr);
        $('#bookingModal').modal('show');
      }
    });
    calendar.render();

    // Change calendar when filter changes
    svcFilterEl.addEventListener('change', () => calendar.refetchEvents());

    // Enforce digits-only on contact phone
    document.getElementById('contact_phone').addEventListener('input', function(){
      this.value = this.value.replace(/\D/g, '').slice(0, 11);
    });

    /* ---------- Save booking ---------- */
    $('#bookingForm').on('submit', function(e){
      e.preventDefault();
      const form = new FormData(this);

      // Make sure a service is selected
      if (!form.get('serviceID')) {
        Swal.fire({icon:'error', title:'Select a service', text:'Please choose a service type.'});
        return;
      }

      const t1 = form.get('time_start'); const t2 = form.get('time_end');
      if (!t1 || !t2 || t2 <= t1) {
        Swal.fire({icon:'error', title:'Invalid time', text:'End time must be after start time.'});
        return;
      }

      // Philippine phone number validation (optional field)
      const phone = (form.get('contact_phone') || '').trim();
      if (phone !== '' && !/^09\d{9}$/.test(phone)) {
        Swal.fire({icon:'error', title:'Invalid phone number', text:'Contact number must be an 11-digit Philippine number starting with 09 (e.g. 09xxxxxxxxx).'});
        document.getElementById('contact_phone').classList.add('is-invalid');
        return;
      }
      document.getElementById('contact_phone').classList.remove('is-invalid');

      // Disable submit button to prevent double-submission
      const $submitBtn = $(this).find('[type=submit]');
      $submitBtn.prop('disabled', true).text('Submitting…');

      $.ajax({
        url: 'save_schedule.php',
        method: 'POST',
        data: form,
        processData: false,
        contentType: false,
        dataType: 'json'
      }).done(function(resp){
        if (resp.success) {
          $('#bookingModal').modal('hide');
          calendar.refetchEvents();
          Swal.fire({ icon: 'success', title: 'Booking submitted', text: 'We will review and confirm your schedule.', timer: 2200, showConfirmButton: false });
        } else {
          Swal.fire({ icon: 'error', title: 'Cannot save', text: resp.message || 'Unable to save booking.' });
        }
      }).fail(function(){
        Swal.fire({ icon: 'error', title: 'Network error', text: 'Please try again.' });
      }).always(function(){
        $submitBtn.prop('disabled', false).text('Submit Booking');
      });
    });

    /* ---------- Notifications + Profile (unchanged below) ---------- */
    const NOTIF_FALLBACK_LINK = '../schedule/schedule.php';

    const bellBtn = document.getElementById('notifBtn');
    const panel   = document.getElementById('notifDropdown');
    const listEl  = document.getElementById('notifList');
    const markAll = document.getElementById('markAllBtn');

    const profBtn   = document.getElementById('profileBtn');
    const profPanel = document.getElementById('profileDropdown');

    [panel, profPanel].forEach(el => el.addEventListener('click', e => e.stopPropagation()));

    function fmtTime(ts){
      if(!ts) return '';
      const [d,t] = ts.split(' ');
      const [Y,M,D] = d.split('-').map(Number);
      const [h,m]  = (t||'').split(':').map(Number);
      const dt = new Date(Y, (M||1)-1, D||1, h||0, m||0);
      return dt.toLocaleString(undefined,{ month:'short', day:'2-digit', hour:'numeric', minute:'2-digit' });
    }
    function escAttr(s=''){
      return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;');
    }
    function renderList(items){
      if (!items || !items.length){
        listEl.innerHTML = '<div class="notif-empty">No notifications.</div>';
        return;
      }
      listEl.innerHTML = items.map(n => `
        <div class="notif-item" role="menuitem" data-id="${n.id}" data-link="${escAttr(n.link || '')}">
          <div class="notif-icon"><i class="fa ${
            n.type === 'warning' ? 'fa-exclamation-triangle' :
            (n.type === 'success' ? 'fa-check-circle' :
            (n.type === 'error' ? 'fa-times-circle' : 'fa-bell'))}"></i></div>
          <div class="notif-body">
            <div class="notif-title" title="${escAttr(n.title)}">${escAttr(n.title)}</div>
            <div class="notif-msg"   title="${escAttr(n.message)}">${escAttr(n.message)}</div>
            <div class="notif-time">${ fmtTime(n.created_at) }</div>
          </div>
        </div>
      `).join('');
    }
    async function fetchCount(){
      try{
        const r = await fetch('../includes/get_unread_count.php',{cache:'no-store'});
        const {count} = await r.json();
        let badge = bellBtn.querySelector('.notif-badge');
        if (count > 0){
          if (!badge){ badge = document.createElement('span'); badge.className='notif-badge'; bellBtn.appendChild(badge); }
          badge.textContent = (count>99)?'99+':count;
          bellBtn.setAttribute('aria-expanded','false');
        }else if (badge){
          badge.remove();
        }
      }catch(e){}
    }
    async function fetchList(){
      listEl.innerHTML = '<div class="notif-empty">Loading…</div>';
      try{
        const r = await fetch('../includes/get_notifications.php?limit=10',{cache:'no-store'});
        const {items} = await r.json();
        renderList(items||[]);
      }catch(e){
        listEl.innerHTML = '<div class="notif-empty">Failed to load.</div>';
      }
    }
    async function markAllRead(){
      try{
        await fetch('../includes/mark_notifications_read.php',{method:'POST'});
        await fetchCount();
      }catch(e){}
    }
    function closeAll(){
      panel.classList.remove('show');
      profPanel.classList.remove('show');
      bellBtn.setAttribute('aria-expanded','false');
      panel.setAttribute('aria-hidden','true');
      profBtn.setAttribute('aria-expanded','false');
      profPanel.setAttribute('aria-hidden','true');
    }

    bellBtn.addEventListener('click', async (e)=>{
      e.stopPropagation();
      const willShow = !panel.classList.contains('show');
      closeAll();
      if (willShow){
        panel.classList.add('show');
        bellBtn.setAttribute('aria-expanded','true');
        panel.setAttribute('aria-hidden','false');
        await fetchList();
      }
    });

    profBtn.addEventListener('click', (e)=>{
      e.stopPropagation();
      const willShow = !profPanel.classList.contains('show');
      closeAll();
      if (willShow){
        profPanel.classList.add('show');
        profBtn.setAttribute('aria-expanded','true');
        profPanel.setAttribute('aria-hidden','false');
      }
    });

    document.addEventListener('click', closeAll);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeAll(); });

    listEl.addEventListener('click', async (e)=>{
      const item = e.target.closest('.notif-item'); if (!item) return;
      const id = item.dataset.id;
      try{ await fetch('../includes/mark_notifications_read.php?id='+encodeURIComponent(id), {method:'POST'}); }catch(e){}
      const link = item.dataset.link;
      window.location.href = link && link !== 'null' ? link : '../schedule/schedule.php';
    });

    document.getElementById('markAllBtn').addEventListener('click', async ()=>{ await markAllRead(); await fetchList(); });

    fetchCount();
    setInterval(fetchCount, 60000);
  });
  </script>
</body>
</html>
