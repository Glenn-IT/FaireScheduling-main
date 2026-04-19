<?php 
session_start();
// Prevent browser caching — stops back-button re-entry after logout
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
if (!isset($_SESSION['userid'])) { header("Location: ../../../logout.php"); exit(); }
// Store user details in session
$userid = $_SESSION['userid'];

require '../../../database/connection.php';

/* ---------- Developers data (ages computed dynamically) ---------- */
function compute_age($dob){
  try{
    $d = new DateTime($dob);
    return (new DateTime())->diff($d)->y;
  }catch(Exception $e){
    return '';
  }
}
$developers = [
  [
    'name'      => 'John Michael Llovido',
    'birthdate' => '2003-02-10',
    'address'   => 'Poblacion 02, Piat Cagayan',
    'email'     => 'llovidojohnmichael69@gmail.com',
    'image'     => '../../../img/dev1.jpg',
  ],
  [
    'name'      => 'Romney Mendoza Narag',
    'birthdate' => '2004-02-16',
    'address'   => 'Villareyno, Piat Cagayan',
    'email'     => 'romneynarag@gmail.com',
    'image'     => '../../../img/dev2.jpg',
  ],
];
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
  <!-- CSS -->
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
    /* ---------- Header bell/profile (your existing polish) ---------- */
    .nav-notif { position: relative; margin-left: .5rem; }
    .notif-btn{ background:transparent; border:0; color:#fff; font-size:18px; cursor:pointer; position:relative; padding:.25rem .35rem; line-height:1; }
    .notif-badge{ position:absolute; top:-2px; right:-2px; min-width:18px; padding:2px 6px; background:#ef4444; color:#fff; border-radius:999px; font-size:11px; font-weight:700; box-shadow:0 1px 0 rgba(0,0,0,.15); }
    .notif-panel{ display:none; position:absolute; right:0; top:36px; width:340px; max-height:420px; overflow:auto; background:#fff; color:#0f172a; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 18px 40px rgba(2,6,23,.18); z-index:1000; overflow-x:hidden; }
    .notif-panel.show{ display:block; }
    .notif-head{ display:flex; justify-content:space-between; align-items:center; padding:10px 12px; border-bottom:1px solid #e5e7eb; background:#f8fafc; }
    .markall{ background:transparent; border:0; color:#2563eb; font-weight:600; cursor:pointer; padding:4px 6px; }
    .notif-list{ padding:6px; }
    .notif-item{ display:flex; gap:10px; padding:10px; border-radius:10px; cursor:pointer; border:1px solid transparent; }
    button:focus { outline: none; }
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
    .profile-btn:focus{ outline: none; outline-offset:2px; }
    .profile-panel{ display:none; position:absolute; right:0; top:36px; width:200px; background:#fff; color:#0f172a; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 18px 40px rgba(2,6,23,.18); z-index:1000; overflow:hidden; }
    .profile-panel.show{ display:block; }
    .profile-head{ padding:10px 12px; font-weight:700; background:#f8fafc; border-bottom:1px solid #e5e7eb; }
    .profile-item{ display:flex; align-items:center; gap:.55rem; padding:10px 12px; text-decoration:none; color:#0f172a; }
    .profile-item i{ width:18px; text-align:center; opacity:.9; }
    .profile-item:hover{ background:#f8fafc; }

    /* nav alignment */
    #nav-menu-container > ul.nav-menu{ display:flex; align-items:center; margin:0; }
    #nav-menu-container > ul.nav-menu > li{ float:none; display:flex; align-items:center; }
    #nav-menu-container .nav-menu > li > a{ display:flex; align-items:center; height:40px; padding:0 8px; line-height:1; }
    .nav-notif .notif-btn, .nav-profile .profile-btn{ height:40px; display:grid; place-items:center; border-radius:999px; padding:0; }
    .nav-notif .notif-badge{ top:3px; right:3px; }
    .nav-notif{ margin-left:6px; } .nav-profile{ margin-left:6px; }
    @media (max-width: 991px){ #nav-menu-container > ul.nav-menu{ gap:14px; } }

    /* ---------- About page additions: Key Facts + Timeline ---------- */
    .facts-strip {
      display:grid;
      grid-template-columns: repeat(3,1fr);
      gap:14px;
    }
    .fact-card {
      background:#ffffff; border:1px solid #e5e7eb; border-radius:14px; padding:14px 16px;
      display:flex; gap:12px; align-items:flex-start; box-shadow:0 6px 18px rgba(2,6,23,.04);
    }
    .fact-icon {
      width:34px; height:34px; border-radius:10px; display:grid; place-items:center;
      background:#eef2ff; color:#1e3a8a; flex:0 0 34px; font-size:16px;
    }
    .fact-title { font-weight:700; margin:0; }
    .fact-desc { margin:2px 0 0; color:#475569; font-size:.95rem; }

    @media (max-width: 991px){
      .facts-strip { grid-template-columns: 1fr; }
    }

    .timeline {
      position:relative; padding-left:28px; margin-top:10px;
    }
    .timeline::before {
      content:""; position:absolute; top:0; left:14px; width:2px; height:100%;
      background:linear-gradient(#cbd5e1, #94a3b8);
    }
    .tl-item {
      position:relative; margin-bottom:20px; padding-left:12px;
      background:#ffffff; border:1px solid #e5e7eb; border-radius:14px; padding:16px;
      box-shadow:0 6px 18px rgba(2,6,23,.04);
    }
    .tl-item::before {
      content:""; position:absolute; left:-21px; top:18px; width:12px; height:12px;
      border-radius:50%; background:#0d6efd; box-shadow:0 0 0 4px #dbeafe;
    }
    .tl-title { margin:0 0 6px; font-weight:800; font-size:1.05rem; color:#0f172a; }
    .tl-sub { margin:0 0 8px; font-weight:600; color:#334155; }
    .tl-list { margin:0; padding-left:18px; color:#475569; }
    .tl-list li { margin-bottom:6px; }

    /* ---------- Developers section ---------- */
    .devs-grid {
      display:grid;
      grid-template-columns: repeat(2, 1fr);
      gap:20px;
    }
    @media (max-width: 767px){
      .devs-grid { grid-template-columns: 1fr; }
    }
    .dev-card {
      background:#fff;
      border:1px solid #e5e7eb;
      border-radius:16px;
      padding:16px;
      display:flex;
      gap:16px;
      align-items:center;
      box-shadow: 0 8px 24px rgba(2,6,23,.06);
      transition: transform .15s ease, box-shadow .15s ease;
    }
    .dev-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 28px rgba(2,6,23,.09);
    }
    .dev-photo {
      width:88px; height:88px; border-radius:50%; object-fit:cover; flex:0 0 88px;
      background:#f1f5f9;
    }
    .dev-name { font-weight:800; font-size:1.1rem; margin:0 0 2px; color:#0f172a; }
    .dev-meta { color:#64748b; font-size:.95rem; margin:0 0 8px; }
    .dev-line { margin:3px 0; color:#334155; font-size:.95rem; }
    .dev-line i { width:18px; text-align:center; margin-right:6px; opacity:.8; }
    .dev-contact a { color:#2563eb; text-decoration:none; }
    .dev-contact a:hover { text-decoration:underline; }
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
        </nav><!-- #nav-menu-container -->					      		  
      </div>
    </div>
  </header><!-- #header -->
			
  <!-- start banner Area -->
  <section class="about-banner relative">
    <div class="overlay overlay-bg"></div>
    <div class="container">				
      <div class="row d-flex align-items-center justify-content-center">
        <div class="about-content col-lg-12">
          <h1 class="text-white">About Us</h1>	
          <p class="text-white link-nav">
            <a href="../user_index.php">Home</a>
            <span class="lnr lnr-arrow-right"></span>
            <a href="../about/about.php"> About Us</a>
          </p>
        </div>	
      </div>
    </div>
  </section>
  <!-- End banner Area -->	

  <!-- Start about-info Area -->
  <section class="about-info-area section-gap">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-6 info-left">
          <img class="img-fluid" src="../../../img/bg_index1.jpg?<?= time(); ?>" alt="">
        </div>
        <div class="col-lg-6 info-right">
          <h6>About Us</h6>
          <h1>Who We Are?</h1>
          <p>
            At Faire Church, we're a community of people learning to love God and love our neighbors.
            We gather each week to worship, pray, and hear the Word, and we walk together through
            the everyday joys and challenges of life. Whether you're exploring faith or looking for a church
            family, you're welcome here.
          </p>
        </div>
      </div>
    </div>	
  </section>
  <!-- End about-info Area -->

  <!-- History Section -->
  <section class="section-gap">
    <div class="container">
      <div class="row mb-3">
        <div class="col-lg-12">
          <h6>Our Story</h6>
          <h1 class="mb-3">Sto. Niño de Faire Parish — A Brief History</h1>
          <p class="mb-4">
            The history of Sto. Niño de Faire Parish stems from the founding of the town by Ilocano immigrant
            Captain Manuel Faire, who built a church in memory of his infant son, Nino. Officially established in
            1897, the town and its patron saint were named Santo Niño. The town was later renamed “Faire” to honor
            its founder but eventually reverted to its former name, maintaining a dual identity.
          </p>
        </div>
      </div>

      <!-- Key Facts -->
      <div class="facts-strip mb-4">
        <div class="fact-card">
          <div class="fact-icon"><i class="fa fa-calendar"></i></div>
          <div>
            <p class="fact-title">Established</p>
            <p class="fact-desc">Spanish Royal Decree — <strong>November 27, 1897</strong></p>
          </div>
        </div>
        <div class="fact-card">
          <div class="fact-icon"><i class="fa fa-map-marker"></i></div>
          <div>
            <p class="fact-title">Early Names</p>
            <p class="fact-desc">Tabang → Kabarungan → Santo Niño → Faire</p>
          </div>
        </div>
        <div class="fact-card">
          <div class="fact-icon"><i class="fa fa-heart"></i></div>
          <div>
            <p class="fact-title">Patron Devotion</p>
            <p class="fact-desc">Santo Niño — comfort and identity for the community</p>
          </div>
        </div>
      </div>

      <!-- Timeline -->
      <div class="timeline">
        <div class="tl-item">
          <h3 class="tl-title">Early Origins and Founding</h3>
          <p class="tl-sub">Roots that predate the parish’s formal establishment</p>
          <ul class="tl-list">
            <li><strong>Spanish Period:</strong> The area was initially known as <em>Tabang</em> and later <em>Kabarungan</em> during the Spanish era.</li>
            <li><strong>Manuel Faire’s Arrival:</strong> Captain Manuel Faire from Dingras, Ilocos Norte founded the community.</li>
            <li><strong>The Church of Santo Niño:</strong> He built a church in memory of his infant son, Nino—an act that cemented the town’s devotion to the Santo Niño.</li>
          </ul>
        </div>
        <div class="tl-item">
          <h3 class="tl-title">Town Establishment and Naming</h3>
          <p class="tl-sub">Becoming a town and honoring its founder</p>
          <ul class="tl-list">
            <li><strong>Official Establishment:</strong> The town of <em>Santo Niño</em> was established by Spanish Royal Decree on <strong>November 27, 1897</strong>.</li>
            <li><strong>Renaming to Faire:</strong> In the early 20th century, a Municipal Council resolution renamed the town <em>“Faire”</em> in honor of Captain Manuel Faire.</li>
            <li><strong>Dual Identity:</strong> While renamed Faire, the town retained <em>Santo Niño</em>—a dual identity reflected in local history and governance.</li>
          </ul>
        </div>
        <div class="tl-item">
          <h3 class="tl-title">Devotion to the Santo Niño</h3>
          <p class="tl-sub">A faith that shaped a community</p>
          <ul class="tl-list">
            <li><strong>A Source of Comfort:</strong> The image of the Santo Niño brought solace to Faire’s wife in her grief.</li>
            <li><strong>Deeply Embedded:</strong> Devotion to the Santo Niño is a defining part of the municipality’s identity.</li>
            <li><strong>The Parish Church:</strong> The <em>Paroquia del Sto. Niño de Faire</em> stands as the spiritual center, housing the revered image.</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <!-- Developers Section -->
  <section class="section-gap">
    <div class="container">
      <div class="row mb-3">
        <div class="col-lg-12">
          <h6>Team</h6>
          <h1 class="mb-3">Meet the Developers</h1>
          <p class="mb-4">The people behind the screens who helped build and polish this system.</p>
        </div>
      </div>

      <div class="devs-grid">
        <?php foreach ($developers as $dev): 
          $name  = htmlspecialchars($dev['name'], ENT_QUOTES);
          $addr  = htmlspecialchars($dev['address'], ENT_QUOTES);
          $email = htmlspecialchars($dev['email'], ENT_QUOTES);
          $img   = htmlspecialchars($dev['image'], ENT_QUOTES);
          $bdateTs = strtotime($dev['birthdate']);
          $bdate = $bdateTs ? date('F d, Y', $bdateTs) : htmlspecialchars($dev['birthdate'], ENT_QUOTES);
          $age   = compute_age($dev['birthdate']);
        ?>
        <div class="dev-card">
          <img class="dev-photo" src="<?= $img ?>" alt="<?= $name ?>">
          <div>
            <div class="dev-name"><?= $name ?></div>
            <div class="dev-meta">Age: <strong><?= $age !== '' ? (int)$age : '' ?></strong></div>
            <div class="dev-line"><i class="fa fa-birthday-cake"></i><?= $bdate ?></div>
            <div class="dev-line"><i class="fa fa-map-marker"></i><?= $addr ?></div>
            <div class="dev-line dev-contact"><i class="fa fa-envelope"></i><a href="mailto:<?= $email ?>"><?= $email ?></a></div>
          </div>
        </div>
        <?php endforeach; ?>
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

  <!-- Scripts -->
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

    [panel, profPanel].forEach(el => el.addEventListener('click', e => e.stopPropagation()));

    function fmtTime(ts){
      if(!ts) return '';
      const [d,t] = ts.split(' ');
      const [Y,M,D] = d.split('-').map(Number);
      const [h,m]  = (t||'').split(':').map(Number);
      const dt = new Date(Y, (M||1)-1, D||1, h||0, m||0);
      return dt.toLocaleString(undefined,{ month:'short', day:'2-digit', hour:'numeric', minute:'2-digit' });
    }
    function escAttr(s=''){ return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;'); }
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
      try{ await fetch('../includes/mark_notifications_read.php',{method:'POST'}); await fetchCount(); }catch(e){}
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
      window.location.href = NOTIF_PAGE;
    });
    markAll.addEventListener('click', async ()=>{ await markAllRead(); await fetchList(); });
    fetchCount();
    setInterval(fetchCount, 60000);
  </script>
</body>
</html>
