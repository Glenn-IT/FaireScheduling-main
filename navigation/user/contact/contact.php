	<!DOCTYPE html>
	<html lang="zxx" class="no-js">
	<head>
		<!-- Mobile Specific Meta -->
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<!-- Favicon-->
		<link rel="shortcut icon" href="img/fav.png">
		<!-- Author Meta -->
		<meta name="author" content="colorlib">
		<!-- Meta Description -->
		<meta name="description" content="">
		<!-- Meta Keyword -->
		<meta name="keywords" content="">
		<!-- meta character set -->
		<meta charset="UTF-8">
		<!-- Site Title -->
		<title>Faire Church</title>

		<link href="https://fonts.googleapis.com/css?family=Poppins:100,200,400,300,500,600,700" rel="stylesheet"> 
			<!--
			CSS
			============================================= -->
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
				        <a href="../user_index.php">
            <img src="../../../img/bg_index1.jpg" alt="" style="width: 42px; border-radius: 50px; height: 40px;">
                        </a>
				      </div>
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
				      </nav><!-- #nav-menu-container -->					      		  
					</div>
				</div>
			</header><!-- #header -->
	  
			<!-- start banner Area -->
			<section class="relative about-banner">	
				<div class="overlay overlay-bg"></div>
				<div class="container">				
					<div class="row d-flex align-items-center justify-content-center">
						<div class="about-content col-lg-12">
							<h1 class="text-white">
								Contact Us				
							</h1>	
							<p class="text-white link-nav"><a href="../user_index.php">Home </a>  <span class="lnr lnr-arrow-right"></span>  <a href="../contact/contact.php"> Contact Us</a></p>
						</div>	
					</div>
				</div>
			</section>
			<!-- End banner Area -->				  

			<!-- Start contact-page Area -->
			<section class="contact-page-area section-gap">
				<div class="container">
					<div class="row">
						<div class="map-wrap" style="width:100%; height: 445px;" id="map"></div>
						<div class="col-lg-4 d-flex flex-column address-wrap">
							<div class="single-contact-address d-flex flex-row">
								<div class="icon">
									<span class="lnr lnr-home"></span>
								</div>
								<div class="contact-details">
									<h5>Piat, Cagayan</h5>
									<p>
										Brgy. Palusao
									</p>
								</div>
							</div>
							<div class="single-contact-address d-flex flex-row">
								<div class="icon">
									<span class="lnr lnr-phone-handset"></span>
								</div>
								<div class="contact-details">
									<h5>+63 912 123 1234</h5>
									<p>Mon to Fri 9am to 6 pm</p>
								</div>
							</div>
							<div class="single-contact-address d-flex flex-row">
								<div class="icon">
									<span class="lnr lnr-envelope"></span>
								</div>
								<div class="contact-details">
									<h5>FaireChurch@gmail.com</h5>
									<p>Send us your query anytime!</p>
								</div>
							</div>														
						</div>
						<div class="col-lg-8">
							<form class="form-area contact-form text-right" id="myForm" action="mail.php" method="post">
								<div class="row">	
									<div class="col-lg-6 form-group">
										<input name="name" placeholder="Enter your name" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Enter your name'" class="common-input mb-20 form-control" required="" type="text">
									
										<input name="email" placeholder="Enter email address" pattern="[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{1,63}$" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Enter email address'" class="common-input mb-20 form-control" required="" type="email">

										<input name="subject" placeholder="Enter subject" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Enter subject'" class="common-input mb-20 form-control" required="" type="text">
									</div>
									<div class="col-lg-6 form-group">
										<textarea class="common-textarea form-control" name="message" placeholder="Enter Messege" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Enter Messege'" required=""></textarea>				
									</div>
									<div class="col-lg-12">
										<div class="alert-msg" style="text-align: left;"></div>
										<button class="genric-btn primary" style="float: right;">Send Message</button>											
									</div>
								</div>
							</form>	
						</div>
					</div>
				</div>	
			</section>
			<!-- End contact-page Area -->

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
			<script src="../js/vendor/jquery-2.2.4.min.js"></script>
			<script src="../js/popper.min.js"></script>
			<script src="../js/vendor/bootstrap.min.js"></script>			
			<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBhOdIF3Y9382fqJYt5I_sswSrEw5eihAA"></script>		
 			<script src="../js/jquery-ui.js"></script>					
  			<script src="../js/easing.min.js"></script>			
			<script src="../js/hoverIntent.js"></script>
			<script src="../js/superfish.min.js"></script>	
			<script src="../js/jquery.ajaxchimp.min.js"></script>
			<script src="../js/jquery.magnific-popup.min.js"></script>						
			<script src="../js/jquery.nice-select.min.js"></script>					
			<script src="../js/owl.carousel.min.js"></script>							
			<script src="../js/mail-script.js"></script>	
			<script src="../js/main.js"></script>
			
			<script>
				
  /* ---------- Notifications + Profile ---------- */
  const NOTIF_PAGE = '../schedule/schedule.php';

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
    try{ await fetch('../includes/mark_notifications_read.php?id='+encodeURIComponent(id), {method:'POST'}); }catch(e){}
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
		</body>
	</html>