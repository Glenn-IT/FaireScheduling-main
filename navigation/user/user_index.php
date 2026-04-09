<?php 
session_start();
// Store user details in session
$userid = $_SESSION['userid'];

require '../../database/connection.php';



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
							<form id="bookingForm" class="form-wrap" method="get" action="packages/book.php">
							<!-- Event / Service -->
							<select class="form-control mb-2 py-2" name="service_id" id="serviceSelect" required>
								<option value="" hidden>Select Event</option>
								<?php foreach ($services as $sid => $title): ?>
								<option value="<?= (int)$sid; ?>"><?= htmlspecialchars($title); ?></option>
								<?php endforeach; ?>
							</select>
							
							<!-- Package (dependent on Event) -->
							<select class="form-control mb-1 d-none py-2" name="package_id" id="packageSelect" disabled required>
								<option value="">Select Package</option>
							</select>
							<div id="pkgError" class="text-danger small mb-2" style="display:none;"></div>


							<!-- Optional: live details for chosen package -->
							<div id="packageInfo" class="small text-dark"></div>

							<!-- Date (no past dates) -->
							<input type="date" class="form-control mb-2" name="date" id="date" required>

							<div style="text-align:start;"><label for="timeStart" style="font-weight:600;">Time Start</label></div>
							<input type="time" class="form-control mb-2" name="time_start" id="timeStart" required step="60">

							<div style="text-align:start;"><label for="timeEnd" style="font-weight:600;">Time End</label></div>
							<input type="time" class="form-control mb-2" name="time_end" id="timeEnd" required step="60">

							<button type="submit" class="mt-3 primary-btn text-uppercase">Search Event</button>
							</form>

							  </div>
							</div>
						</div>
					</div>
				</div>					
			</section>
			<!-- End banner Area -->
			

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
// PHP → JS map: { service_id: [ {id, name, price, guest}, ... ] }
			const pkgMap = <?=
			json_encode($pkgMap, JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
			?>;

			const serviceSelect = document.getElementById('serviceSelect');
			const packageSelect = document.getElementById('packageSelect');
			const packageInfo   = document.getElementById('packageInfo');

			function resetPackage() {
			packageSelect.innerHTML = '<option value="" hidden>Select Package</option>';
			packageSelect.disabled = true;
			packageSelect.classList.add('d-none');
			if (packageInfo) packageInfo.innerHTML = '';
			}

			serviceSelect.addEventListener('change', function () {
			const sid = this.value;
			resetPackage();

			if (sid && pkgMap[sid] && pkgMap[sid].length) {
				pkgMap[sid].forEach(p => {
				const opt = document.createElement('option');
				opt.value = p.id;
				opt.textContent = p.name;
				opt.dataset.price = (p.price ?? '');
				opt.dataset.guest = (p.guest ?? '');
				packageSelect.appendChild(opt);
				});
				packageSelect.disabled = false;
				packageSelect.classList.remove('d-none');
			}
			});

			packageSelect.addEventListener('change', function () {
			const opt = this.selectedOptions[0];
			if (!opt || !opt.value) { if (packageInfo) packageInfo.innerHTML = ''; return; }

			const price = opt.dataset.price;
			const guest = opt.dataset.guest;
			if (packageInfo) {
				packageInfo.innerHTML =
				(price ? ("<div class='row py-2'><div class='col-lg-6 col-12'>Price: " + Number(price).toLocaleString() + " PHP </div>") : '') +
				(guest ? ("<div class='col-lg-6 col-12'>Guests: " + guest + "</div></div>") : '');
			}
			});
</script>
<script>
// ----- UTILITIES -----
function parseTimeToMinutes(str) {
  if (!str) return NaN;
  let s = String(str).trim().toLowerCase();
  const ap = /am|pm/.test(s) ? (s.includes('pm') ? 'pm' : 'am') : null;
  s = s.replace(/[^0-9:]/g, '');
  let [hh, mm] = s.split(':');
  let h = parseInt(hh || '0', 10);
  let m = parseInt(mm || '0', 10);
  if (ap) { if (h === 12) h = 0; if (ap === 'pm') h += 12; }
  if (isNaN(h) || isNaN(m)) return NaN;
  return h * 60 + m;
}
function to24hString(str) {
  const mins = parseTimeToMinutes(str);
  if (isNaN(mins)) return '';
  const h = String(Math.floor(mins/60)).padStart(2,'0');
  const m = String(mins%60).padStart(2,'0');
  return `${h}:${m}`;
}
function todayISO() {
  const d = new Date();
  // lock to local midnight
  d.setHours(0,0,0,0);
  const y = d.getFullYear();
  const m = String(d.getMonth()+1).padStart(2,'0');
  const day = String(d.getDate()).padStart(2,'0');
  return `${y}-${m}-${day}`;
}

// ----- ELEMENTS -----
const form        = document.getElementById('bookingForm');
const dateInput   = document.getElementById('date');
const timeStart   = document.getElementById('timeStart');
const timeEnd     = document.getElementById('timeEnd');
const packageSel  = document.getElementById('packageSelect');
const serviceSel  = document.getElementById('serviceSelect');
const pkgError    = document.getElementById('pkgError');


// ----- INIT: disable past dates -----
dateInput.min = todayISO();

// ----- TIME VALIDATION -----
function syncEndMin() {
  const startVal24 = to24hString(timeStart.value);
  if (startVal24) timeEnd.min = startVal24; else timeEnd.removeAttribute('min');
}
function validateTimes() {
  timeEnd.setCustomValidity('');
  const s = parseTimeToMinutes(timeStart.value);
  const e = parseTimeToMinutes(timeEnd.value);
  if (!isNaN(s) && !isNaN(e) && e <= s) {
    timeEnd.setCustomValidity('End time must be later than Start time.');
  }
}

// ----- PACKAGE VALIDATION -----
function validatePackage() {
  // If the select is hidden/disabled or empty, block submit.
  packageSel.setCustomValidity('');
  const hasValue = packageSel && !packageSel.disabled && packageSel.value !== '';
  if (!hasValue) {
    packageSel.setCustomValidity('Please select a package.');
  }
}

// Keep validations reactive
['input','change','blur'].forEach(evt => {
  timeStart.addEventListener(evt, () => { syncEndMin(); validateTimes(); });
  timeEnd.addEventListener(evt, validateTimes);
  packageSel.addEventListener(evt, validatePackage);
  dateInput.addEventListener(evt, () => {
    // ensure chosen date is not before today (extra guard)
    if (dateInput.value && dateInput.value < dateInput.min) {
      dateInput.setCustomValidity('Past dates are not allowed.');
    } else {
      dateInput.setCustomValidity('');
    }
  });
});

// ---- HARD GUARD on submit ----
form.addEventListener('submit', (ev) => {
  // Keep time & date constraints in sync
  syncEndMin();
  validateTimes();

  // Build errors
  const errors = [];

  // Service selected?
  if (!serviceSel.value) {
    errors.push('Please select an event.');
  }

  // Package selected? (don’t rely on "required" because the select may be disabled/hidden)
  const hasPackageValue = packageSel && !packageSel.disabled && !packageSel.classList.contains('d-none') && packageSel.value !== '';
  if (!hasPackageValue) {
    errors.push('Please select a package.');
  }

  // Date present and not in the past
  if (!dateInput.value) {
    errors.push('Please choose a date.');
  } else if (dateInput.value < dateInput.min) {
    errors.push('Past dates are not allowed.');
  }

  // Time logic
  if (!timeStart.value || !timeEnd.value) {
    errors.push('Please set both start and end times.');
  } else if (timeEnd.validity.customError) { // set by validateTimes()
    errors.push('End time must be later than Start time.');
  }

  // Show errors and block submit if any
if (errors.length) {
  ev.preventDefault();

  // Inline error for package
  if (!hasPackageValue) {
    pkgError.textContent = 'Please select a package.';
    pkgError.style.display = 'block';
    packageSel.classList.add('is-invalid');
  } else {
    pkgError.textContent = '';
    pkgError.style.display = 'none';
    packageSel.classList.remove('is-invalid');
  }

  // Show native messages (date/time, etc.)
  form.reportValidity();
  // Optionally focus the first invalid field
  const firstInvalid = form.querySelector(':invalid');
  if (firstInvalid) firstInvalid.focus();
} else {
  // clear any prior inline error
  pkgError.textContent = '';
  pkgError.style.display = 'none';
  packageSel.classList.remove('is-invalid');
}

});
// First run
syncEndMin();
validateTimes();
validatePackage();



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
		</body>
	</html>