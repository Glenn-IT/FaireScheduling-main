<?php
declare(strict_types=1);
session_start();
// Prevent browser caching — stops back-button re-entry after logout
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
if (!isset($_SESSION['userid'])) { header("Location: ../../../logout.php"); exit; }
$uid = (int)$_SESSION['userid'];

require '../../../database/connection.php';
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* ------------------------------
   Notifications count (this user)
------------------------------ */
$notifCount = 0;
try {
  $q = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :u AND is_read = 0");
  $q->execute([':u' => $uid]);
  $notifCount = (int)$q->fetchColumn();
} catch (Throwable $e) {
  $notifCount = 0;
}

/* ------------------------------
   Load this user's bookings
   Tables used: schedules, services
------------------------------ */
$sql = "
  SELECT
    s.id,
    s.userID,
    s.serviceID,
    s.date,
    s.time_start,
    s.time_end,
    s.other_contact_person,
    s.contact_phone,
    s.notes,
    s.date_created,
    s.status,
    sv.service_name
  FROM schedules s
  LEFT JOIN services sv ON sv.id = s.serviceID
  WHERE s.userID = :u
  ORDER BY s.date DESC, s.time_start DESC, s.id DESC
";
$st = $conn->prepare($sql);
$st->execute([':u' => $uid]);
$bookings = $st->fetchAll(PDO::FETCH_ASSOC);

/* ------------------------------
   Helpers
------------------------------ */
function friendly_date_main($ymd) {
  if (!$ymd) return '—';
  $dt = DateTime::createFromFormat('Y-m-d', $ymd);
  return $dt ? $dt->format('M d, Y') : htmlspecialchars($ymd);
}
function friendly_day($ymd) {
  if (!$ymd) return '';
  $dt = DateTime::createFromFormat('Y-m-d', $ymd);
  return $dt ? $dt->format('D') : '';
}
function friendly_time($his) {
  if (!$his) return '';
  $fmt = substr_count($his, ':') === 2 ? 'H:i:s' : (substr_count($his, ':') === 1 ? 'H:i' : 'H');
  $dt = DateTime::createFromFormat($fmt, $his);
  return $dt ? $dt->format('g:i A') : htmlspecialchars($his);
}
function friendly_datetime($str) {
  if (!$str) return '—';
  $dt = DateTime::createFromFormat('Y-m-d H:i:s', $str);
  return $dt ? $dt->format('M d, Y · g:i A') : htmlspecialchars($str);
}
function status_badge($status) {
  $s = strtolower(trim((string)$status));
  $icon = 'fa-info-circle'; $class = 'badge-soft-light';
  if ($s === 'pending')   { $icon='fa-hourglass-half';  $class='badge-soft-warning'; }
  if ($s === 'approved')  { $icon='fa-check-circle';    $class='badge-soft-success'; }
  if ($s === 'completed') { $icon='fa-flag-checkered';  $class='badge-soft-secondary'; }
  if ($s === 'cancelled' || $s === 'denied') {
    $icon='fa-times-circle'; $class='badge-soft-danger';
  }
  return '<span class="badge '.$class.'"><i class="fa '.$icon.' mr-1"></i>'.htmlspecialchars($status ?: '—').'</span>';
}
?>
<!DOCTYPE html>
<html lang="en" class="no-js">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>My Schedules • Faire Church</title>

  <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" rel="stylesheet">
  <link rel="stylesheet" href="../css/linearicons.css">
  <link rel="stylesheet" href="../css/font-awesome.min.css">
  <link rel="stylesheet" href="../css/bootstrap.css">
  <link rel="stylesheet" href="../css/magnific-popup.css">
  <link rel="stylesheet" href="../css/jquery-ui.css">
  <link rel="stylesheet" href="../css/nice-select.css">
  <link rel="stylesheet" href="../css/animate.min.css">
  <link rel="stylesheet" href="../css/owl.carousel.css">
  <link rel="stylesheet" href="../css/main.css?<?= time(); ?>">

  <style>
    :root{
      --ink:#0f172a; --text:#111827; --muted:#6b7280; --chip:#eef2f7; --line:#e5e7eb;
      --card:#ffffff; --tableHead:#f8fafc; --brand:#2563eb;
    }
    body { color:var(--text); background:#f5f7fb; }
    h1, h5, .font-weight-bold { color:var(--ink); }
    .section-gap .container{ max-width:1100px; }
    .table-card{ background:var(--card); border-radius:14px; box-shadow:0 10px 30px rgba(16,24,40,.08); overflow:hidden; border:1px solid var(--line); }
    .table thead th{ background:var(--tableHead); color:var(--ink); font-weight:700; border-top:0; border-bottom:1px solid var(--line); }
    .table tbody tr{ background:#fff; transition:background .12s ease; }
    .table tbody tr:hover{ background:#fafcff; }
    .table td, .table th{ vertical-align:middle; }
    .text-muted{ color:var(--muted) !important; }
    .chip{ display:inline-flex; align-items:center; gap:.4rem; padding:.28rem .6rem; border-radius:999px; background:var(--chip); font-size:.85rem; color:#0b1220; border:1px solid #e6ebf3; }
    .chip i{opacity:.85}
    .badge{font-weight:700;padding:.42em .7em;border-radius:999px;letter-spacing:.02em;}
    .badge-soft-success{background:#d1fae5;color:#065f46;}
    .badge-soft-warning{background:#fef3c7;color:#92400e;}
    .badge-soft-danger{background:#fee2e2;color:#991b1b;}
    .badge-soft-secondary{background:#e5e7eb;color:#374151;}
    .badge-soft-light{background:#e0e7ff;color:#1e3a8a;}
    .date-stack .main{font-weight:700;color:var(--ink);}
    .date-stack .sub{font-size:.88rem;color:#475569;}
    .actions .btn{border-radius:10px;padding:.38rem .65rem;}
    .event-title{ font-weight:600; color:var(--ink); }
    .notes-card{ border:1px solid var(--line); background:#fbfdff; border-radius:10px; padding:.75rem .9rem; }
    .notes-empty{ color:#64748b; }

    /* Notification & profile (same as your other pages) */
    .nav-notif { position: relative; margin-left: .5rem; }
    .notif-btn{ background:transparent; border:0; color:#fff; font-size:18px; cursor:pointer; position:relative; padding:.25rem .35rem; line-height:1; }
    .notif-badge{ position:absolute; top:-2px; right:-2px; min-width:18px; padding:2px 6px; background:#ef4444; color:#fff; border-radius:999px; font-size:11px; font-weight:700; box-shadow:0 1px 0 rgba(0,0,0,.15); }
    .notif-panel{ display:none; position:absolute; right:0; top:36px; width:340px; max-height:420px; overflow:auto; background:#fff; color:#0f172a; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 18px 40px rgba(2,6,23,.18); z-index:1000; overflow-x:hidden; }
    .notif-panel.show{ display:block; }
    .notif-head{ display:flex; justify-content:space-between; align-items:center; padding:10px 12px; border-bottom:1px solid #e5e7eb; background:#f8fafc; }
    .markall{ background:transparent; border:0; color:#2563eb; font-weight:600; cursor:pointer; padding:4px 6px; }
    .notif-list{ padding:6px; }
    .notif-item{ display:flex; gap:10px; padding:10px; border-radius:10px; cursor:pointer; border:1px solid transparent; }
    .notif-item:hover{ background:#f8fafc; border-color:#e5e7eb; }
    .notif-icon{ min-width:28px; height:28px; display:inline-grid; place-items:center; border-radius:8px; background:#eef2ff; color:#1e3a8a; flex:0 0 28px; }
    .notif-body{ flex:1; min-width:0; }
    .notif-title{ font-weight:700; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .notif-msg{ color:#475569; font-size:.92rem; overflow:hidden; display:-webkit-box; -webkit-box-orient:vertical; -webkit-line-clamp:2; }
    @supports not (-webkit-line-clamp:2) { .notif-msg{ white-space:nowrap; text-overflow:ellipsis; } }
    .notif-time{ color:#64748b; font-size:.82rem; margin-top:2px; }
    .notif-empty{ padding:20px; text-align:center; color:#64748b; }
    .notif-footer{ display:flex; align-items:center; justify-content:center; gap:.25rem; padding:10px 12px; border-top:1px solid #e5e7eb; color:#2563eb; text-decoration:none; }

    .nav-profile { position: relative; margin-left: .5rem; }
    .profile-btn{ background:transparent; border:0; color:#fff; font-size:18px; cursor:pointer; width:34px; height:34px; border-radius:999px; display:grid; place-items:center; }
    .profile-panel{ display:none; position:absolute; right:0; top:36px; width:200px; background:#fff; color:#0f172a; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 18px 40px rgba(2,6,23,.18); z-index:1000; overflow:hidden; }
    .profile-panel.show{ display:block; }
    .profile-head{ padding:10px 12px; font-weight:700; background:#f8fafc; border-bottom:1px solid #e5e7eb; }
    .profile-item{ display:flex; align-items:center; gap:.55rem; padding:10px 12px; text-decoration:none; color:#0f172a; }
    .profile-item:hover{ background:#f8fafc; }

    #nav-menu-container > ul.nav-menu{ display:flex; align-items:center; margin:0; }
    #nav-menu-container > ul.nav-menu > li{ float:none; display:flex; align-items:center; }
    #nav-menu-container .nav-menu > li > a{ display:flex; align-items:center; height:40px; padding:0 8px; line-height:1; }
    .nav-notif .notif-btn, .nav-profile .profile-btn{ height:40px; display:grid; place-items:center; border-radius:999px; padding:0; }
    .nav-notif .notif-badge{ top:3px; right:3px; }
    .nav-notif{ margin-left:6px; } .nav-profile{ margin-left:6px; }
    @media (max-width: 991px){ #nav-menu-container > ul.nav-menu{ gap:14px; } }
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

<section class="about-banner relative">
  <div class="overlay overlay-bg"></div>
  <div class="container">
    <div class="row d-flex align-items-center justify-content-center">
      <div class="about-content col-lg-12">
        <h1 class="text-white">My Schedules</h1>
        <p class="text-white link-nav">
          <a href="../user_index.php">Home</a>
          <span class="lnr lnr-arrow-right"></span>
          <a href="schedule.php">My Schedules</a>
        </p>
      </div>
    </div>
  </div>
</section>

<section class="section-gap">
  <div class="container">
    <?php if (!$bookings): ?>
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="notes-card">You don’t have any bookings yet.</div>
        </div>
      </div>
    <?php else: ?>
      <div class="table-card">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>#</th>
                <th>Event</th>
                <th>Date</th>
                <th>Time</th>
                <th>Other contact</th>
                <th>Contact #</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($bookings as $b):
              $dateMain = friendly_date_main($b['date']);
              $dateDay  = friendly_day($b['date']);
              $tStart   = friendly_time($b['time_start']);
              $tEnd     = friendly_time($b['time_end']);
            ?>
              <tr>
                <td><?= (int)$b['id'] ?></td>
                <td><div class="event-title"><?= htmlspecialchars($b['service_name'] ?: '—') ?></div></td>
                <td class="date-stack">
                  <div class="main"><?= $dateMain ?></div>
                  <div class="sub"><?= $dateDay ? '(' . $dateDay . ')' : '' ?></div>
                </td>
                <td>
                  <?php if ($tStart): ?><span class="chip"><i class="fa fa-clock"></i><?= $tStart ?></span><?php endif; ?>
                  <?php if ($tEnd): ?><span class="mx-1">–</span><span class="chip"><i class="fa fa-clock"></i><?= $tEnd ?></span><?php endif; ?>
                </td>
                <td><?= htmlspecialchars($b['other_contact_person'] ?? '', ENT_QUOTES) ?></td>
                <td><?= htmlspecialchars($b['contact_phone'] ?? '', ENT_QUOTES) ?></td>
                <td><?= status_badge($b['status']) ?></td>
                <td><?= friendly_datetime($b['date_created']) ?></td>
                <td class="actions">
                  <button
                    class="btn btn-info btn-sm view-btn"
                    title="View details"
                    data-id="<?= (int)$b['id'] ?>"
                    data-event="<?= htmlspecialchars($b['service_name'] ?: '—', ENT_QUOTES) ?>"
                    data-date-main="<?= htmlspecialchars($dateMain, ENT_QUOTES) ?>"
                    data-date-day="<?= htmlspecialchars($dateDay, ENT_QUOTES) ?>"
                    data-start="<?= htmlspecialchars($tStart, ENT_QUOTES) ?>"
                    data-end="<?= htmlspecialchars($tEnd, ENT_QUOTES) ?>"
                    data-ocp="<?= htmlspecialchars($b['other_contact_person'] ?? '', ENT_QUOTES) ?>"
                    data-phone="<?= htmlspecialchars($b['contact_phone'] ?? '', ENT_QUOTES) ?>"
                    data-notes="<?= htmlspecialchars($b['notes'] ?? '', ENT_QUOTES) ?>"
                    data-status="<?= htmlspecialchars($b['status'] ?? '—', ENT_QUOTES) ?>"
                  >
                    <i class="fa fa-eye"></i> View
                  </button>

                  <?php if (strtolower($b['status']) === 'pending'): ?>
                    <button class="btn btn-outline-danger btn-sm cancel-btn" data-id="<?= (int)$b['id'] ?>">
                      <i class="fa fa-times"></i> Cancel
                    </button>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-receipt mr-2"></i> Booking Details</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <div class="row" style="row-gap:12px">
          <div class="col-md-6"><div class="notes-card"><strong>Event:</strong> <span id="vEvent"></span></div></div>
          <div class="col-md-6"><div class="notes-card"><strong>Date:</strong> <span id="vDate"></span></div></div>
          <div class="col-md-6"><div class="notes-card"><strong>Time:</strong> <span id="vTime"></span></div></div>
          <div class="col-md-6"><div class="notes-card"><strong>Other contact:</strong> <span id="vOcp"></span></div></div>
          <div class="col-md-6"><div class="notes-card"><strong>Contact #:</strong> <span id="vPhone"></span></div></div>
          <div class="col-md-6"><div class="notes-card"><strong>Status:</strong> <span id="vStatus"></span></div></div>
          <div class="col-md-12">
            <div class="notes-card"><strong>Notes:</strong><div id="vNotes" style="margin-top:6px"></div></div>
          </div>
        </div>
      </div>
      <div class="modal-footer"><button class="btn btn-secondary" data-dismiss="modal">Close</button></div>
    </div>
  </div>
</div>

  <footer class="footer-area section-gap">
    <div class="container">
      <div class="row footer-bottom d-flex justify-content-between align-items-center">
        <p class="col-lg-8 col-sm-12 footer-text m-0 text-white">&copy; <script>document.write(new Date().getFullYear())</script> Faire Church</p>
        <div class="col-lg-4 col-sm-12 footer-social text-lg-end">
          <a href="#"><i class="fa fa-facebook"></i></a>
          <a href="#"><i class="fa fa-twitter"></i></a>
        </div>
      </div>
    </div>
  </footer>

<!-- JS -->
<script src="../js/vendor/jquery-2.2.4.min.js"></script>
<script src="../js/popper.min.js"></script>
<script src="../js/vendor/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  const esc = s => (s??'').toString().replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));

  // View modal fill
  $(document).on('click', '.view-btn', function(){
    const d = this.dataset;
    $('#vEvent').html(esc(d.event));
    $('#vDate').html(esc(d.dateMain + (d.dateDay ? ' ('+d.dateDay+')' : '')));
    $('#vTime').html((d.start||'') + (d.end ? ' – ' + d.end : ''));
    $('#vOcp').html(esc(d.ocp||'—'));
    $('#vPhone').html(esc(d.phone||'—'));
    $('#vStatus').html(esc(d.status||'—'));
    $('#vNotes').html(esc(d.notes||'No additional notes.'));
    $('#viewModal').modal('show');
  });

  // Cancel booking
  $(document).on('click', '.cancel-btn', function(){
    const id = $(this).data('id');
    Swal.fire({
      title: 'Cancel Booking?',
      text: 'This action cannot be undone.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, cancel it'
    }).then(res => {
      if (!res.isConfirmed) return;
      $.post('cancel_booking.php', { id }, function(resp){
        if (resp && resp.success) {
          Swal.fire({ icon:'success', title:'Cancelled!', text:resp.success, timer:1500, showConfirmButton:false })
            .then(()=>location.reload());
        } else {
          Swal.fire({ icon:'error', title:'Error', text:(resp && resp.error) ? resp.error : 'Failed to cancel booking.' });
        }
      }, 'json').fail(()=> Swal.fire({ icon:'error', title:'Request Failed', text:'Network error.' }));
    });
  });

  /* ---------- Notifications (unchanged UI) ---------- */
  const NOTIF_PAGE = '../schedule/schedule.php';
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
    const dt = new Date(Y,(M||1)-1,D||1,h||0,m||0);
    return dt.toLocaleString(undefined,{ month:'short', day:'2-digit', hour:'numeric', minute:'2-digit' });
  }
  function escAttr(s=''){ return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;'); }
  function renderList(items){
    if (!items || !items.length){ listEl.innerHTML = '<div class="notif-empty">No notifications.</div>'; return; }
    listEl.innerHTML = items.map(n => `
      <div class="notif-item" role="menuitem" data-id="${n.id}">
        <div class="notif-icon"><i class="fa ${n.type === 'warning' ? 'fa-exclamation-triangle' : 'fa-bell'}"></i></div>
        <div class="notif-body">
          <div class="notif-title" title="${escAttr(n.title)}">${escAttr(n.title)}</div>
          <div class="notif-msg"   title="${escAttr(n.message)}">${escAttr(n.message)}</div>
          <div class="notif-time">${ fmtTime(n.created_at) }</div>
        </div>
      </div>`).join('');
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
      }else if (badge){ badge.remove(); }
    }catch(e){}
  }
  async function fetchList(){
    listEl.innerHTML = '<div class="notif-empty">Loading…</div>';
    try{
      const r = await fetch('../includes/get_notifications.php?limit=10',{cache:'no-store'});
      const {items} = await r.json(); renderList(items||[]);
    }catch(e){ listEl.innerHTML = '<div class="notif-empty">Failed to load.</div>'; }
  }
  async function markAllRead(){
    try{ await fetch('../includes/mark_notifications_read.php',{method:'POST'}); await fetchCount(); }catch(e){}
  }
  function closeAll(){
    panel.classList.remove('show'); profPanel.classList.remove('show');
    bellBtn.setAttribute('aria-expanded','false'); panel.setAttribute('aria-hidden','true');
    profBtn.setAttribute('aria-expanded','false'); profPanel.setAttribute('aria-hidden','true');
  }
  bellBtn.addEventListener('click', async (e)=>{
    e.stopPropagation(); const willShow = !panel.classList.contains('show'); closeAll();
    if (willShow){ panel.classList.add('show'); bellBtn.setAttribute('aria-expanded','true'); panel.setAttribute('aria-hidden','false'); await fetchList(); }
  });
  profBtn.addEventListener('click', (e)=>{ e.stopPropagation(); const willShow = !profPanel.classList.contains('show'); closeAll(); if (willShow){ profPanel.classList.add('show'); profBtn.setAttribute('aria-expanded','true'); profPanel.setAttribute('aria-hidden','false'); } });
  document.addEventListener('click', closeAll);
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeAll(); });
  listEl.addEventListener('click', async (e)=>{ const item = e.target.closest('.notif-item'); if (!item) return; const id = item.dataset.id; try{ await fetch('../includes/mark_notifications_read.php?id='+encodeURIComponent(id), {method:'POST'}); }catch(e){} window.location.href = NOTIF_PAGE; });
  markAll.addEventListener('click', async ()=>{ await markAllRead(); await fetchList(); });
  fetchCount(); setInterval(fetchCount, 60000);
</script>

</body>
</html>
