<?php 
session_start();
// Prevent browser caching — stops back-button re-entry after logout
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");

if (!isset($_SESSION['userid'])) {
    header("Location: ../../logout.php");
    exit();
}
// Store user details in session
$userid = $_SESSION['userid'];

require '../../database/connection.php';

// Load services for the search form
try {
    $svcStmt = $conn->query("SELECT ID, service_name FROM services ORDER BY service_name");
    $services = $svcStmt->fetchAll(PDO::FETCH_KEY_PAIR); // [ID => service_name]
} catch (Throwable $e) {
    $services = [];
}
$pkgMap = []; // no packages table — kept for JS compatibility



?>
    
	
	
	<!DOCTYPE html>
	<html lang="zxx" class="no-js">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<link rel="shortcut icon" href="img/fav.png">
		<meta name="author" content="colorlib">
		<meta name="description" content="">
		<meta name="keywords" content="">
		<meta charset="UTF-8">
		<title>Faire Church</title>

		<link href="https://fonts.googleapis.com/css?family=Poppins:100,200,400,300,500,600,700" rel="stylesheet"> 
			<link rel="stylesheet" href="css/linearicons.css">
			<link rel="stylesheet" href="css/font-awesome.min.css">
			<link rel="stylesheet" href="css/bootstrap.css">
			<link rel="stylesheet" href="css/magnific-popup.css">
			<link rel="stylesheet" href="css/jquery-ui.css">				
			<link rel="stylesheet" href="css/nice-select.css">							
			<link rel="stylesheet" href="css/animate.min.css">
			<link rel="stylesheet" href="css/owl.carousel.css">				
			<link rel="stylesheet" href="css/main.css?<?= time(); ?>">
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">

		<style>
			
    /* Bell + dropdown */
    .nav-notif { position: relative; margin-left: .5rem; }
    .notif-btn{
      background:transparent; border:0; color:#fff; font-size:18px; cursor:pointer;
      position:relative; padding:.25rem .35rem; line-height:1;
    }
    .notif-badge{
      position:absolute; top:-2px; right:-2px; min-width:18px; padding:2px 6px;
      background:#ef4444; color:#fff; border-radius:999px; font-size:11px; font-weight:700;
      box-shadow:0 1px 0 rgba(0,0,0,.15);
    }
    .notif-panel{
      display:none; position:absolute; right:0; top:36px; width:340px; max-height:420px; overflow:auto;
      background:#fff; color:#0f172a; border:1px solid #e5e7eb; border-radius:12px;
      box-shadow:0 18px 40px rgba(2,6,23,.18); z-index:1000;
      overflow-x:hidden;
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
    button:focus {
      outline: none;
    }
    .notif-item:hover{ background:#f8fafc; border-color:#e5e7eb; }
    .notif-icon{
      min-width:28px; height:28px; display:inline-grid; place-items:center;
      border-radius:8px; background:#eef2ff; color:#1e3a8a;
      flex:0 0 28px;
    }
    .notif-body{ flex:1; min-width:0; }
    .notif-title{
      font-weight:700;
      overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
    }
    .notif-msg{
      color:#475569; font-size:.92rem;
      overflow:hidden; display:-webkit-box; -webkit-box-orient:vertical; -webkit-line-clamp:2;
    }
    @supports not (-webkit-line-clamp:2) {
      .notif-msg{ white-space:nowrap; text-overflow:ellipsis; }
    }
    .notif-time{ color:#64748b; font-size:.82rem; margin-top:2px; }
    .notif-empty{ padding:20px; text-align:center; color:#64748b; }
    .notif-footer{
      display:flex; align-items:center; justify-content:center; gap:.25rem;
      padding:10px 12px; border-top:1px solid #e5e7eb; color:#2563eb; text-decoration:none;
    }

    /* Profile dropdown */
    .nav-profile { position: relative; margin-left: .5rem; }
    .profile-btn{
      background:transparent; border:0; color:#fff; font-size:18px; cursor:pointer;
      width:34px; height:34px; border-radius:999px; display:grid; place-items:center;
    }
    .profile-btn:focus{ outline: none; outline-offset:2px; }

    .profile-panel{
      display:none; position:absolute; right:0; top:36px; width:200px;
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

    /* ===== Align main nav, bell, and profile icon ===== */
#nav-menu-container > ul.nav-menu{
  /* cancel the theme's floats and use flex for clean centering */
  display:flex;
  align-items:center;
  margin:0;
}
#nav-menu-container > ul.nav-menu > li{
  float:none;                 /* override template */
  display:flex;
  align-items:center;
}

/* make links the same height as the icon buttons */
#nav-menu-container .nav-menu > li > a{
  display:flex;
  align-items:center;
  height:40px;                /* keep in sync with buttons below */
  padding:0 8px;
  line-height:1;              /* avoid baseline wobble */
}

/* bell + profile: same box size & centering */
.nav-notif .notif-btn,
.nav-profile .profile-btn{
  height:40px;
  display:grid;
  place-items:center;
  border-radius:999px;
  padding:0;
}

/* badge position after the size change */
.nav-notif .notif-badge{
  top:3px;
  right:3px;
}

/* tighten the right-side spacing a bit */
.nav-notif{ margin-left:6px; }
.nav-profile{ margin-left:6px; }

/* optional: keep things tidy on narrow viewports */
@media (max-width: 991px){
  #nav-menu-container > ul.nav-menu{ gap:14px; }
}

		</style>
		</head>
		<body>	
			<header id="header">
				<div class="container main-menu">
					<div class="row align-items-center justify-content-between d-flex">
				      <div id="logo">
				        <a href="user_index.php">
                  <img src="../../img/bg_index1.jpg" alt="" style="width: 42px; border-radius: 50px; height: 40px;;">
                </a>
				      </div>
				      <nav id="nav-menu-container">
				        <ul class="nav-menu">
				          <li><a href="user_index.php">Home</a></li>
				          <li><a href="about/about.php">About</a></li>
				          <li><a href="book/book.php">Book</a></li>
						  <li><a href="schedule/schedule.php">Schedules</a></li>        					          		                    					          		          
				          <li><a href="contact/contact.php">Contact</a></li>     	          					          		          
				                    <!-- Notification bell -->
						<li class="nav-notif">
							<button id="notifBtn" class="notif-btn" type="button"
									aria-label="Notifications" aria-haspopup="true" aria-expanded="false"
									aria-controls="notifDropdown">
							<i class="fa fa-bell"></i>
							<?php if (!empty($notifCount)): ?>
								<span class="notif-badge"><?= $notifCount > 99 ? '99+' : (int)$notifCount ?></span>
							<?php endif; ?>
							</button>

							<div id="notifDropdown" class="notif-panel" role="menu" aria-hidden="true">
							<div class="notif-head">
								<strong>Notifications</strong>
								<button id="markAllBtn" class="markall" type="button">Mark all read</button>
							</div>

							<div id="notifList" class="notif-list">
								<div class="notif-empty">Loading…</div>
							</div>

							<a class="notif-footer" href="schedule/schedule.php">
								Go to schedules
								<i class="fa fa-chevron-right" style="margin-left:.35rem"></i>
							</a>
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
							<a class="profile-item py-2" role="menuitem" href="account/profile.php">
								<i class="fa fa-user"></i>
								<span>My Profile</span>
							</a>
							<a class="profile-item py-2" role="menuitem" href="../../logout.php">
								<i class="fa fa-sign-out"></i>
								<span class="text-dark">Logout</span>
							</a>
							</div>
						</li>
            
				        </ul>
				      </nav><!-- #nav-menu-container -->					      		  
					</div>
				</div>
			</header><!-- #header -->
			
			<!-- start banner Area -->
			<section class="banner-area relative">
				<div class="overlay overlay-bg"></div>				
				<div class="container">
					<div class="row fullscreen align-items-center justify-content-between">
            <div class="col-lg-6 col-md-6 banner-left">
                <h6 class="text-white">Plan your sacred moments with ease</h6>
                <h1 class="text-white">Faire Church</h1>
                <p class="text-white">
                    Book your wedding, christening, or other church services online and secure the perfect date and time.
                </p>
                <a href="#" class="primary-btn text-uppercase">Book Now</a>
            </div>

						<div class="col-lg-4 col-md-6 banner-right">
							<div class="tab-content" id="myTabContent">
							  <div class="tab-pane fade show active" id="flight" role="tabpanel" aria-labelledby="flight-tab">
						<form id="bookingForm" class="form-wrap" onsubmit="return handleSearchEvent(event)">
						<!-- Event / Service -->
						<select class="form-control mb-2 py-2" name="service_id" id="serviceSelect" required>
							<option value="" hidden>Select Event</option>
							<?php foreach ($services as $sid => $title): ?>
							<option value="<?= (int)$sid; ?>"><?= htmlspecialchars($title); ?></option>
							<?php endforeach; ?>
						</select>

						<!-- Date (no past dates) -->
						<input type="date" class="form-control mb-2" name="date" id="date" required>

						<div style="text-align:start;"><label for="timeStart" style="font-weight:600;">Time Start</label></div>
						<input type="time" class="form-control mb-2" name="time_start" id="timeStart" required step="60">

						<div style="text-align:start;"><label for="timeEnd" style="font-weight:600;">Time End</label></div>
						<input type="time" class="form-control mb-2" name="time_end" id="timeEnd" required step="60">

						<div id="searchFormError" class="text-danger small mb-2" style="display:none;"></div>

						<button type="submit" class="mt-3 primary-btn text-uppercase">Search Event</button>
						</form>							  </div>
							</div>
						</div>
					</div>
				</div>					
			</section>
			<!-- End banner Area -->

			<!-- ===== Booking Calendar Section ===== -->
			<section class="booking-calendar-section" style="padding: 60px 0; background: #f8fafc;">
				<div class="container">
					<div class="row justify-content-center mb-4">
						<div class="col-12 text-center">
							<h2 style="font-weight:700; color:#1e3a8a;">Church Booking Calendar</h2>
							<p style="color:#64748b; margin-top:8px;">Click on any event to view booking details.</p>
							<!-- Legend -->
							<div style="display:flex; flex-wrap:wrap; justify-content:center; gap:10px; margin-top:12px;">
								<span class="cal-legend" style="background:#f59e0b;">Pending</span>
								<span class="cal-legend" style="background:#22c55e;">Approved</span>
								<span class="cal-legend" style="background:#3b82f6;">Completed</span>
								<span class="cal-legend" style="background:#ef4444;">Denied</span>
								<span class="cal-legend" style="background:#6b7280;">Cancelled</span>
							</div>
						</div>
					</div>
					<div class="row justify-content-center">
						<div class="col-lg-10 col-12">
							<div style="background:#fff; border-radius:16px; box-shadow:0 4px 24px rgba(30,58,138,.10); padding:24px;">
								<div id="bookingCalendar"></div>
							</div>
						</div>
					</div>
				</div>
			</section>

			<!-- Booking Detail Modal -->
			<div id="bookingDetailModal" style="
				display:none; position:fixed; inset:0; z-index:9999;
				background:rgba(15,23,42,.55); backdrop-filter:blur(3px);
				align-items:center; justify-content:center;">
				<div style="
					background:#fff; border-radius:16px; width:100%; max-width:460px;
					margin:16px; box-shadow:0 24px 64px rgba(15,23,42,.22);
					overflow:hidden; animation:fadeUp .22s ease;">
					<!-- Modal header -->
					<div id="modalHeader" style="padding:18px 22px 14px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:flex-start;">
						<div>
							<div id="modalService" style="font-size:1.2rem; font-weight:700; color:#1e3a8a;"></div>
							<div id="modalDate"    style="color:#64748b; font-size:.92rem; margin-top:2px;"></div>
						</div>
						<button id="modalCloseBtn" style="background:none;border:none;font-size:1.4rem;cursor:pointer;color:#64748b;line-height:1;padding:0 4px;">&times;</button>
					</div>
					<!-- Status badge -->
					<div style="padding:10px 22px 0;">
						<span id="modalStatusBadge" style="display:inline-block; padding:3px 14px; border-radius:999px; font-size:.82rem; font-weight:700; color:#fff;"></span>
					</div>
					<!-- Modal body -->
					<div style="padding:14px 22px 22px;">
						<table style="width:100%; border-collapse:collapse; font-size:.93rem;">
							<tr>
								<td style="padding:7px 0; color:#64748b; width:44%; vertical-align:top;"><i class="fa fa-clock-o" style="margin-right:6px;"></i>Time</td>
								<td id="modalTime" style="padding:7px 0; font-weight:600; color:#0f172a;"></td>
							</tr>
							<tr>
								<td style="padding:7px 0; color:#64748b; vertical-align:top;"><i class="fa fa-user" style="margin-right:6px;"></i>Booked by</td>
								<td id="modalBooker" style="padding:7px 0; font-weight:600; color:#0f172a;"></td>
							</tr>
							<tr>
								<td style="padding:7px 0; color:#64748b; vertical-align:top;"><i class="fa fa-envelope" style="margin-right:6px;"></i>Email</td>
								<td id="modalEmail" style="padding:7px 0; color:#0f172a; word-break:break-word;"></td>
							</tr>
							<tr id="modalContactRow">
								<td style="padding:7px 0; color:#64748b; vertical-align:top;"><i class="fa fa-phone" style="margin-right:6px;"></i>Contact Person</td>
								<td id="modalContact" style="padding:7px 0; color:#0f172a;"></td>
							</tr>
							<tr id="modalPhoneRow">
								<td style="padding:7px 0; color:#64748b; vertical-align:top;"><i class="fa fa-mobile" style="margin-right:6px;"></i>Phone</td>
								<td id="modalPhone" style="padding:7px 0; color:#0f172a;"></td>
							</tr>
							<tr id="modalNotesRow">
								<td style="padding:7px 0; color:#64748b; vertical-align:top;"><i class="fa fa-sticky-note-o" style="margin-right:6px;"></i>Notes</td>
								<td id="modalNotes" style="padding:7px 0; color:#0f172a;"></td>
							</tr>
						</table>
					</div>
				</div>
			</div>

			<style>
			@keyframes fadeUp {
				from { opacity:0; transform:translateY(18px); }
				to   { opacity:1; transform:translateY(0); }
			}
			.cal-legend {
				color:#fff; font-size:.78rem; font-weight:600;
				padding:3px 12px; border-radius:999px;
			}
			#bookingCalendar .fc-toolbar-title { font-size: 1.1rem; font-weight: 700; }
			#bookingCalendar .fc-event { cursor: pointer; border-radius: 6px !important; border:none !important; font-size:.82rem; }
			#bookingCalendar .fc-daygrid-event { white-space: normal !important; }
			</style>
			<!-- End Booking Calendar Section -->

			<!-- start footer Area -->		
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
			<!-- End footer Area -->	

			<script>
// Search Event form — validate then redirect to book.php with service pre-selected
function handleSearchEvent(ev) {
  ev.preventDefault();
  const errEl = document.getElementById('searchFormError');
  errEl.style.display = 'none';

  const svcVal   = document.getElementById('serviceSelect').value;
  const dateVal  = document.getElementById('date').value;
  const startVal = document.getElementById('timeStart').value;
  const endVal   = document.getElementById('timeEnd').value;

  const todayStr = new Date().toISOString().slice(0, 10);

  if (!svcVal)   { errEl.textContent = 'Please select an event.';           errEl.style.display='block'; return false; }
  if (!dateVal)  { errEl.textContent = 'Please choose a date.';             errEl.style.display='block'; return false; }
  if (dateVal < todayStr) { errEl.textContent = 'Past dates are not allowed.'; errEl.style.display='block'; return false; }
  if (!startVal || !endVal) { errEl.textContent = 'Please set start and end times.'; errEl.style.display='block'; return false; }
  if (endVal <= startVal)   { errEl.textContent = 'End time must be after start time.'; errEl.style.display='block'; return false; }

  // Redirect to the booking calendar with the service pre-selected
  window.location.href = 'book/book.php?service=' + encodeURIComponent(svcVal);
  return false;
}

// Prevent past dates on the date field
document.addEventListener('DOMContentLoaded', function () {
  const dateInput = document.getElementById('date');
  if (dateInput) {
    const today = new Date();
    const y = today.getFullYear();
    const m = String(today.getMonth() + 1).padStart(2, '0');
    const d = String(today.getDate()).padStart(2, '0');
    dateInput.min = `${y}-${m}-${d}`;
  }
});
</script>
<script>
 /* ---------- Notifications + Profile ---------- */
  const NOTIF_PAGE = 'schedule/schedule.php';

  const bellBtn = document.getElementById('notifBtn');
  const panel   = document.getElementById('notifDropdown');
  const listEl  = document.getElementById('notifList');
  const markAll = document.getElementById('markAllBtn');

  const profBtn   = document.getElementById('profileBtn');
  const profPanel = document.getElementById('profileDropdown');

  // Keep clicks inside panels from bubbling to document
  [panel, profPanel].forEach(el => el.addEventListener('click', e => e.stopPropagation()));

  function fmtTime(ts){
    // server sends "YYYY-MM-DD HH:MM:SS"
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
      <div class="notif-item" role="menuitem" data-id="${n.id}">
        <div class="notif-icon"><i class="fa ${n.type === 'warning' ? 'fa-exclamation-triangle' : 'fa-bell'}"></i></div>
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
      const r = await fetch('includes/get_unread_count.php',{cache:'no-store'});
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
      const r = await fetch('includes/get_notifications.php?limit=10',{cache:'no-store'});
      const {items} = await r.json();
      renderList(items||[]);
    }catch(e){
      listEl.innerHTML = '<div class="notif-empty">Failed to load.</div>';
    }
  }
  async function markAllRead(){
    try{
      await fetch('includes/mark_notifications_read.php',{method:'POST'});
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

  // Open/close: Notifications
  bellBtn.addEventListener('click', async (e)=>{
    e.stopPropagation();
    const willShow = !panel.classList.contains('show');
    closeAll();
    if (willShow){
      panel.classList.add('show');
      bellBtn.setAttribute('aria-expanded','true');
      panel.setAttribute('aria-hidden','false');
      await fetchList();               // do NOT auto-mark read; users can click "Mark all read"
    }
  });

  // Open/close: Profile
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

  // Global close
  document.addEventListener('click', closeAll);
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeAll(); });

  // Click a notification -> mark single read then go to schedules page
  listEl.addEventListener('click', async (e)=>{
    const item = e.target.closest('.notif-item'); if (!item) return;
    const id = item.dataset.id;
    try{ await fetch('includes/mark_notifications_read.php?id='+encodeURIComponent(id), {method:'POST'}); }catch(e){}
    window.location.href = NOTIF_PAGE;
  });

  // Mark all read button
  markAll.addEventListener('click', async ()=>{
    await markAllRead();
    await fetchList();
  });

  // live count
  fetchCount();
  setInterval(fetchCount, 60000);
</script>
			<script src="js/vendor/jquery-2.2.4.min.js"></script>
			<script src="js/popper.min.js"></script>
			<script src="js/vendor/bootstrap.min.js"></script>			
			<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBhOdIF3Y9382fqJYt5I_sswSrEw5eihAA"></script>		
 			<script src="js/jquery-ui.js"></script>					
  			<script src="js/easing.min.js"></script>			
			<script src="js/hoverIntent.js"></script>
			<script src="js/superfish.min.js"></script>	
			<script src="js/jquery.ajaxchimp.min.js"></script>
			<script src="js/jquery.magnific-popup.min.js"></script>						
			<script src="js/jquery.nice-select.min.js"></script>					
			<script src="js/owl.carousel.min.js"></script>							
			<script src="js/mail-script.js"></script>	
			<script src="js/main.js"></script>

		<!-- FullCalendar -->
		<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
		<script>
		document.addEventListener('DOMContentLoaded', function () {
			var calEl = document.getElementById('bookingCalendar');
			var modal = document.getElementById('bookingDetailModal');

			var statusColours = {
				'Pending':   '#f59e0b',
				'Approved':  '#22c55e',
				'Completed': '#3b82f6',
				'Denied':    '#ef4444',
				'Cancelled': '#6b7280',
				'Canceled':  '#6b7280',
			};

			var calendar = new FullCalendar.Calendar(calEl, {
				initialView: 'dayGridMonth',
				headerToolbar: {
					left:   'prev,next today',
					center: 'title',
					right:  'dayGridMonth,timeGridWeek,listMonth'
				},
				height: 'auto',
				buttonText: { today:'Today', month:'Month', week:'Week', list:'List' },
				events: {
					url: 'includes/get_calendar_events.php',
					failure: function () {
						console.warn('Could not load calendar events.');
					}
				},
				eventClick: function (info) {
					var p = info.event.extendedProps;

					document.getElementById('modalService').textContent     = p.service  || 'Booking';
					document.getElementById('modalDate').textContent        = p.date_fmt || '';
					document.getElementById('modalTime').textContent        = p.time_start + ' – ' + p.time_end;
					document.getElementById('modalBooker').textContent      = p.booker   || '—';
					document.getElementById('modalEmail').textContent       = p.email    || '—';

					// Contact person (optional)
					if (p.contact) {
						document.getElementById('modalContact').textContent = p.contact;
						document.getElementById('modalContactRow').style.display = '';
					} else {
						document.getElementById('modalContactRow').style.display = 'none';
					}
					// Phone (optional)
					if (p.phone) {
						document.getElementById('modalPhone').textContent = p.phone;
						document.getElementById('modalPhoneRow').style.display = '';
					} else {
						document.getElementById('modalPhoneRow').style.display = 'none';
					}
					// Notes (optional)
					if (p.notes) {
						document.getElementById('modalNotes').textContent = p.notes;
						document.getElementById('modalNotesRow').style.display = '';
					} else {
						document.getElementById('modalNotesRow').style.display = 'none';
					}

					// Status badge
					var badge = document.getElementById('modalStatusBadge');
					badge.textContent = p.status || '';
					badge.style.background = statusColours[p.status] || '#94a3b8';

					modal.style.display = 'flex';
				},

				// Clicking a date that has no event: optionally open book page
				dateClick: function (info) {
					// only open if no event was clicked (eventClick fires first)
				}
			});

			calendar.render();

			// Close modal
			document.getElementById('modalCloseBtn').addEventListener('click', function () {
				modal.style.display = 'none';
			});
			modal.addEventListener('click', function (e) {
				if (e.target === modal) modal.style.display = 'none';
			});
			document.addEventListener('keydown', function (e) {
				if (e.key === 'Escape') modal.style.display = 'none';
			});
		});
		</script>
		</body>
	</html>