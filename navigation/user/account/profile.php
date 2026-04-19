<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
if (!isset($_SESSION['userid'])) { header("Location: ../../../logout.php"); exit; }

$userid = (int)$_SESSION['userid'];
require '../../../database/connection.php';

$stmt = $conn->prepare("SELECT * FROM tblusers WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $userid]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) { header("Location: ../../../logout.php"); exit; }
?>
<!DOCTYPE html>
<html lang="zxx" class="no-js">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="shortcut icon" href="../img/fav.png">
  <meta charset="UTF-8">
  <title>My Profile — Faire Church</title>

  <link href="https://fonts.googleapis.com/css?family=Poppins:100,200,400,300,500,600,700" rel="stylesheet">
  <link rel="stylesheet" href="../css/linearicons.css">
  <link rel="stylesheet" href="../css/font-awesome.min.css">
  <link rel="stylesheet" href="../css/bootstrap.css">
  <link rel="stylesheet" href="../css/magnific-popup.css">
  <link rel="stylesheet" href="../css/animate.min.css">
  <link rel="stylesheet" href="../css/owl.carousel.css">
  <link rel="stylesheet" href="../css/main.css">

  <style>
    /* ── Bell ── */
    .nav-notif { position: relative; margin-left: .5rem; }
    .notif-btn { background:transparent; border:0; color:#fff; font-size:18px; cursor:pointer; position:relative; padding:.25rem .35rem; line-height:1; height:40px; display:grid; place-items:center; border-radius:999px; }
    .notif-badge { position:absolute; top:3px; right:3px; min-width:18px; padding:2px 6px; background:#ef4444; color:#fff; border-radius:999px; font-size:11px; font-weight:700; }
    .notif-panel { display:none; position:absolute; right:0; top:40px; width:340px; max-height:420px; overflow:auto; background:#fff; color:#0f172a; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 18px 40px rgba(2,6,23,.18); z-index:1000; overflow-x:hidden; }
    .notif-panel.show { display:block; }
    .notif-head { display:flex; justify-content:space-between; align-items:center; padding:10px 12px; border-bottom:1px solid #e5e7eb; background:#f8fafc; }
    .markall { background:transparent; border:0; color:#2563eb; font-weight:600; cursor:pointer; padding:4px 6px; }
    .notif-list { padding:6px; }
    .notif-item { display:flex; gap:10px; padding:10px; border-radius:10px; cursor:pointer; border:1px solid transparent; }
    .notif-item:hover { background:#f8fafc; border-color:#e5e7eb; }
    .notif-icon { min-width:28px; height:28px; display:inline-grid; place-items:center; border-radius:8px; background:#eef2ff; color:#1e3a8a; flex:0 0 28px; }
    .notif-body { flex:1; min-width:0; }
    .notif-title { font-weight:700; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .notif-msg { color:#475569; font-size:.92rem; overflow:hidden; display:-webkit-box; -webkit-box-orient:vertical; -webkit-line-clamp:2; }
    .notif-time { color:#64748b; font-size:.82rem; margin-top:2px; }
    .notif-empty { padding:20px; text-align:center; color:#64748b; }
    .notif-footer { display:flex; align-items:center; justify-content:center; gap:.25rem; padding:10px 12px; border-top:1px solid #e5e7eb; color:#2563eb; text-decoration:none; }

    /* ── Profile dropdown ── */
    .nav-profile { position: relative; margin-left: .5rem; }
    .profile-btn { background:transparent; border:0; color:#fff; font-size:18px; cursor:pointer; width:34px; height:40px; border-radius:999px; display:grid; place-items:center; }
    .profile-btn:focus, .notif-btn:focus { outline:none; }
    .profile-panel { display:none; position:absolute; right:0; top:40px; width:200px; background:#fff; color:#0f172a; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 18px 40px rgba(2,6,23,.18); z-index:1000; overflow:hidden; }
    .profile-panel.show { display:block; }
    .profile-head { padding:10px 12px; font-weight:700; background:#f8fafc; border-bottom:1px solid #e5e7eb; }
    .profile-item { display:flex; align-items:center; gap:.55rem; padding:10px 12px; text-decoration:none; color:#0f172a; }
    .profile-item i { width:18px; text-align:center; opacity:.9; }
    .profile-item:hover { background:#f8fafc; }

    /* ── Nav alignment ── */
    #nav-menu-container > ul.nav-menu { display:flex; align-items:center; margin:0; }
    #nav-menu-container > ul.nav-menu > li { float:none; display:flex; align-items:center; }
    #nav-menu-container .nav-menu > li > a { display:flex; align-items:center; height:40px; padding:0 8px; line-height:1; }
    .nav-notif .notif-btn, .nav-profile .profile-btn { height:40px; display:grid; place-items:center; border-radius:999px; padding:0; }
    .nav-notif { margin-left:6px; }
    .nav-profile { margin-left:6px; }

    /* ── Banner ── */
    .profile-banner-section {
      background: linear-gradient(120deg, #1a3c6e 0%, #2a6fad 100%);
      padding: 28px 0 60px;
      position: relative;
    }
    .profile-banner-section h1 { color:#fff; font-size:1.6rem; font-weight:700; margin:0; }
    .profile-banner-section p  { color:rgba(255,255,255,.75); font-size:.9rem; margin:4px 0 0; }

    /* ── Card ── */
    .profile-page-wrap { max-width:780px; margin: -40px auto 60px; padding:0 16px; position:relative; z-index:2; }
    .profile-card { background:#fff; border-radius:14px; box-shadow:0 4px 28px rgba(0,0,0,.10); overflow:hidden; }
    .avatar-row { display:flex; align-items:center; gap:18px; padding:28px 32px 0; }
    .avatar-circle { width:72px; height:72px; border-radius:50%; background:#e8f0fe; display:grid; place-items:center; font-size:2rem; color:#1a3c6e; flex-shrink:0; border:3px solid #fff; box-shadow:0 2px 10px rgba(0,0,0,.12); }
    .avatar-info .user-fullname { font-size:1.1rem; font-weight:700; color:#0f172a; }
    .avatar-info .user-meta { font-size:.82rem; color:#64748b; margin-top:2px; }

    .card-tabs { display:flex; gap:0; border-bottom:2px solid #e5e7eb; padding:0 32px; margin-top:18px; }
    .tab-btn { background:transparent; border:0; padding:10px 18px; font-size:.88rem; font-weight:600; color:#64748b; cursor:pointer; border-bottom:2px solid transparent; margin-bottom:-2px; transition:color .15s, border-color .15s; }
    .tab-btn.active { color:#1a3c6e; border-bottom-color:#1a3c6e; }
    .tab-btn:focus { outline:none; }

    .tab-pane { display:none; padding:28px 32px 32px; }
    .tab-pane.active { display:block; }

    .section-label { font-weight:700; font-size:.8rem; color:#1a3c6e; text-transform:uppercase; letter-spacing:.07em; margin-bottom:16px; }
    .form-label { font-size:.83rem; font-weight:600; color:#374151; }
    .form-control { border-radius:8px; font-size:.88rem; border:1px solid #d1d5db; padding:.45rem .75rem; }
    .form-control:focus { border-color:#1a3c6e; box-shadow:0 0 0 3px rgba(26,60,110,.12); }
    .btn-primary-custom { background:#1a3c6e; color:#fff; border:0; border-radius:8px; font-weight:600; padding:.48rem 1.6rem; font-size:.88rem; }
    .btn-primary-custom:hover { background:#15325d; color:#fff; }
    .divider { border-top:1px solid #e5e7eb; margin:24px 0; }

    .flash-ok  { background:#dcfce7; border:1px solid #86efac; color:#166534; border-radius:8px; padding:.6rem 1rem; font-size:.86rem; margin-bottom:14px; }
    .flash-err { background:#fee2e2; border:1px solid #fca5a5; color:#991b1b; border-radius:8px; padding:.6rem 1rem; font-size:.86rem; margin-bottom:14px; }
  </style>
</head>
<body>

<header id="header">
  <div class="container main-menu">
    <div class="row align-items-center justify-content-between d-flex">
      <div id="logo">
        <a href="../user_index.php">
          <img src="../../../img/bg_index1.jpg" alt="" style="width:42px;border-radius:50px;height:40px;">
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
            <button id="notifBtn" class="notif-btn" type="button" aria-label="Notifications">
              <i class="fa fa-bell"></i>
            </button>
            <div id="notifDropdown" class="notif-panel">
              <div class="notif-head">
                <strong>Notifications</strong>
                <button id="markAllBtn" class="markall" type="button">Mark all read</button>
              </div>
              <div id="notifList" class="notif-list"><div class="notif-empty">Loading…</div></div>
              <a class="notif-footer" href="../schedule/schedule.php">Go to schedules <i class="fa fa-chevron-right" style="margin-left:.35rem"></i></a>
            </div>
          </li>

          <!-- Profile -->
          <li class="nav-profile">
            <button id="profileBtn" class="profile-btn" type="button" aria-label="Account menu">
              <i class="fa fa-user-circle"></i>
            </button>
            <div id="profileDropdown" class="profile-panel">
              <div class="profile-head">Account</div>
              <a class="profile-item" href="profile.php"><i class="fa fa-user"></i><span>My Profile</span></a>
              <a class="profile-item" href="../../../logout.php"><i class="fa fa-sign-out"></i><span>Logout</span></a>
            </div>
          </li>
        </ul>
      </nav>
    </div>
  </div>
</header>

<!-- Page Banner -->
<section class="profile-banner-section">
  <div class="container">
    <h1>My Profile</h1>
    <p><a href="../user_index.php" style="color:rgba(255,255,255,.75);">Home</a> &rsaquo; My Profile</p>
  </div>
</section>

<!-- Profile Card -->
<div class="profile-page-wrap">
  <div id="flashMsg"></div>

  <div class="profile-card">
    <!-- Avatar row -->
    <div class="avatar-row">
      <div class="avatar-circle"><i class="fa fa-user"></i></div>
      <div class="avatar-info">
        <div class="user-fullname" id="displayName">
          <?= htmlspecialchars(trim($user['firstname'].' '.$user['middlename'].' '.$user['lastname'])) ?>
        </div>
        <div class="user-meta">
          <?= htmlspecialchars($user['user_role']) ?> &nbsp;·&nbsp;
          Member since <?= date('F Y', strtotime($user['datecreated'])) ?>
        </div>
      </div>
    </div>

    <!-- Tabs -->
    <div class="card-tabs">
      <button class="tab-btn active" data-tab="info"><i class="fa fa-id-card" style="margin-right:6px"></i>Personal Info</button>
      <button class="tab-btn" data-tab="password"><i class="fa fa-lock" style="margin-right:6px"></i>Change Password</button>
    </div>

    <!-- Tab: Personal Info -->
    <div class="tab-pane active" id="tab-info">
      <div class="section-label">Update Your Information</div>
      <form id="infoForm">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">First Name <span style="color:#dc2626">*</span></label>
            <input type="text" class="form-control" name="firstname" value="<?= htmlspecialchars($user['firstname']) ?>" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Middle Name</label>
            <input type="text" class="form-control" name="middlename" value="<?= htmlspecialchars($user['middlename']) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Last Name <span style="color:#dc2626">*</span></label>
            <input type="text" class="form-control" name="lastname" value="<?= htmlspecialchars($user['lastname']) ?>" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Birthday <span style="color:#dc2626">*</span></label>
            <input type="date" class="form-control" name="birthday" value="<?= htmlspecialchars($user['birthday']) ?>" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Mobile Number</label>
            <input type="tel" class="form-control" name="mobilenumber" value="<?= htmlspecialchars($user['mobilenumber']) ?>" maxlength="11" placeholder="09xxxxxxxxx">
          </div>
          <div class="col-md-4">
            <label class="form-label">Email <span style="color:#dc2626">*</span></label>
            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
          </div>
        </div>
        <div class="mt-4 text-end">
          <button type="submit" class="btn btn-primary-custom"><i class="fa fa-save" style="margin-right:6px"></i>Save Changes</button>
        </div>
      </form>
    </div>

    <!-- Tab: Change Password -->
    <div class="tab-pane" id="tab-password">
      <div class="section-label">Change Your Password</div>
      <form id="passForm">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Current Password <span style="color:#dc2626">*</span></label>
            <input type="password" class="form-control" name="current_password" placeholder="••••••••" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">New Password <span style="color:#dc2626">*</span></label>
            <input type="password" class="form-control" name="new_password" placeholder="••••••••" minlength="6" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Confirm New Password <span style="color:#dc2626">*</span></label>
            <input type="password" class="form-control" name="confirm_password" placeholder="••••••••" required>
          </div>
        </div>
        <div class="mt-4 text-end">
          <button type="submit" class="btn btn-primary-custom"><i class="fa fa-key" style="margin-right:6px"></i>Update Password</button>
        </div>
      </form>
    </div>
  </div><!-- /.profile-card -->
</div><!-- /.profile-page-wrap -->

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

<script src="../js/vendor/jquery-2.2.4.min.js"></script>
<script src="../js/popper.min.js"></script>
<script src="../js/vendor/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ── Dropdowns ──────────────────────────────────────────────────────────────
const notifBtn   = document.getElementById('notifBtn');
const notifPanel = document.getElementById('notifDropdown');
const profBtn    = document.getElementById('profileBtn');
const profPanel  = document.getElementById('profileDropdown');

notifBtn.addEventListener('click', e => { e.stopPropagation(); notifPanel.classList.toggle('show'); profPanel.classList.remove('show'); });
profBtn.addEventListener('click',  e => { e.stopPropagation(); profPanel.classList.toggle('show');  notifPanel.classList.remove('show'); });
document.addEventListener('click', () => { notifPanel.classList.remove('show'); profPanel.classList.remove('show'); });

// ── Tabs ───────────────────────────────────────────────────────────────────
document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    this.classList.add('active');
    document.getElementById('tab-' + this.dataset.tab).classList.add('active');
  });
});

// ── Flash messages ─────────────────────────────────────────────────────────
function showMsg(msg, ok) {
  const el = document.getElementById('flashMsg');
  el.innerHTML = `<div class="${ok ? 'flash-ok' : 'flash-err'}">${msg}</div>`;
  setTimeout(() => el.innerHTML = '', 4500);
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ── Update info ────────────────────────────────────────────────────────────
document.getElementById('infoForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const fd = new FormData(this);
  fd.append('action', 'update_info');
  fetch('update_profile.php', { method:'POST', body:fd })
    .then(r => r.json())
    .then(d => {
      showMsg(d.message, d.success);
      if (d.success) {
        const fn = fd.get('firstname'), mn = fd.get('middlename'), ln = fd.get('lastname');
        document.getElementById('displayName').textContent = [fn,mn,ln].filter(Boolean).join(' ');
      }
    });
});

// ── Update password ────────────────────────────────────────────────────────
document.getElementById('passForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const fd = new FormData(this);
  if (fd.get('new_password') !== fd.get('confirm_password')) { showMsg('New passwords do not match.', false); return; }
  fd.append('action', 'update_password');
  fetch('update_profile.php', { method:'POST', body:fd })
    .then(r => r.json())
    .then(d => { showMsg(d.message, d.success); if (d.success) this.reset(); });
});
</script>
</body>
</html>
