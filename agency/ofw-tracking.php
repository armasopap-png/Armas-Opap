<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_auth('agency');
$page_title = 'OFW Tracking';
$use_dashboard_css = true;
$stmt = $pdo->prepare("SELECT * FROM agencies WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$agency = $stmt->fetch();
$agency_id = $agency['id'];
$stmt2 = $pdo->prepare("
    SELECT o.id, o.first_name, o.last_name, o.country, o.city,
           o.latitude, o.longitude, o.location_updated_at,
           u.email, u.last_login, u.last_login_ip, u.status
    FROM ofws o
    JOIN users u ON o.user_id = u.id
    WHERE o.agency_id = ?
    ORDER BY o.last_name ASC
");
$stmt2->execute([$agency_id]);
$ofws = $stmt2->fetchAll();
$online_threshold = date('Y-m-d H:i:s', strtotime('-5 minutes'));
$hide_navbar = true;
include '../includes/header.php';
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
  .tracking-layout {
    display: flex;
    height: calc(100vh - 72px);
    overflow: hidden
  }

  .tracking-sidebar {
    width: 370px;
    min-width: 260px;
    background: #fff;
    border-right: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    overflow: hidden
  }

  .tracking-map {
    flex: 1;
    position: relative
  }

  #map {
    width: 100%;
    height: 100%
  }

  .tracking-header {
    padding: 14px 16px;
    background: var(--primary);
    color: #fff
  }

  .tracking-header h2 {
    font-size: .95rem;
    margin: 0 0 2px
  }

  .tracking-header p {
    font-size: .75rem;
    opacity: .8;
    margin: 0
  }

  .legend {
    display: flex;
    gap: 14px;
    font-size: .72rem;
    color: #fff;
    margin-top: 6px;
    align-items: center
  }

  .legend span {
    display: flex;
    align-items: center;
    gap: 4px
  }

  .ldot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
    flex-shrink: 0
  }

  .tracking-search {
    padding: 12px 16px;
    border-bottom: 1px solid var(--border)
  }

  .tracking-search input {
    width: 100%;
    padding: 9px 13px;
    border: 2px solid var(--border);
    border-radius: var(--radius-md);
    font-size: .88rem;
    box-sizing: border-box
  }

  .tracking-search input:focus {
    outline: none;
    border-color: var(--primary)
  }

  .ofw-list-scroll {
    flex: 1;
    overflow-y: auto
  }

  .ofw-card {
    padding: 12px 16px;
    border-bottom: 1px solid var(--border);
    cursor: pointer;
    transition: background .15s;
    display: flex;
    align-items: flex-start;
    gap: 10px
  }

  .ofw-card:hover,
  .ofw-card.active {
    background: #EEF2FF
  }

  .ofw-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    flex-shrink: 0;
    margin-top: 5px
  }

  .dot-green {
    background: #22c55e;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, .25)
  }

  .dot-red {
    background: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, .2)
  }

  .dot-gray {
    background: #94a3b8;
    box-shadow: 0 0 0 3px rgba(148, 163, 184, .2)
  }

  .ofw-card-body {
    flex: 1;
    min-width: 0
  }

  .ofw-card-name {
    font-weight: 600;
    font-size: .92rem;
    color: var(--dark);
    margin-bottom: 2px;
    display: flex;
    align-items: center;
    gap: 6px
  }

  .status-pill {
    font-size: .67rem;
    font-weight: 700;
    padding: 1px 7px;
    border-radius: 99px
  }

  .pill-online {
    color: #16a34a;
    background: #dcfce7
  }

  .pill-offline {
    color: #dc2626;
    background: #fee2e2
  }

  .pill-never {
    color: #64748b;
    background: #f1f5f9
  }

  .ofw-card-meta {
    font-size: .74rem;
    color: var(--mid);
    line-height: 1.65
  }

  .ofw-card-meta span {
    display: block
  }

  .badge-ok {
    background: #D1FAE5;
    color: #065F46;
    padding: 2px 8px;
    border-radius: 99px;
    font-size: .67rem;
    font-weight: 600;
    display: inline-block;
    margin-top: 3px
  }

  .badge-no {
    background: #FEE2E2;
    color: #991B1B;
    padding: 2px 8px;
    border-radius: 99px;
    font-size: .67rem;
    font-weight: 600;
    display: inline-block;
    margin-top: 3px
  }

  .marker-online {
    background: #22c55e;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, .4), 0 2px 6px rgba(0, 0, 0, .3)
  }

  .marker-offline {
    background: #ef4444;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, .3), 0 2px 6px rgba(0, 0, 0, .25)
  }

  .marker-gray {
    background: #94a3b8;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 2px 6px rgba(0, 0, 0, .2)
  }

  @media(max-width:768px) {
    .tracking-layout {
      flex-direction: column;
      height: auto
    }

    .tracking-sidebar {
      width: 100%;
      min-width: unset;
      max-height: 340px;
      border-right: none;
      border-bottom: 1px solid var(--border)
    }

    #map {
      height: 420px
    }
  }
</style>

<div class="dashboard-layout" id="dashboardLayout">
  <aside class="sidebar">
    <div class="sidebar-brand">
      <img src="/armas/assets/img/armas.jpg" alt="ARMAS" class="sidebar-logo">
      <div class="sidebar-brand-text">
        <span class="logo-text">ARMAS</span>
        <span class="brand-subtitle">Agency Portal</span>
      </div>
    </div>

    <nav class="sidebar-nav">
      <div class="sidebar-section">
        <div class="sidebar-section-title">Main Menu</div>
        <a href="/armas/agency/dashboard.php" class="sidebar-link">
          <span class="sidebar-link-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="3" width="7" height="9" rx="1"></rect>
              <rect x="14" y="3" width="7" height="5" rx="1"></rect>
              <rect x="14" y="12" width="7" height="9" rx="1"></rect>
              <rect x="3" y="16" width="7" height="5" rx="1"></rect>
            </svg>
          </span>
          <span class="sidebar-link-text">Dashboard</span>
        </a>
        <a href="/armas/agency/ofw-list.php" class="sidebar-link">
          <span class="sidebar-link-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
              <circle cx="9" cy="7" r="4"></circle>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
              <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
          </span>
          <span class="sidebar-link-text">OFW List</span>
        </a>
        <a href="/armas/agency/case-list.php" class="sidebar-link">
          <span class="sidebar-link-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
              <polyline points="14 2 14 8 20 8"></polyline>
              <line x1="16" y1="13" x2="8" y2="13"></line>
              <line x1="16" y1="17" x2="8" y2="17"></line>
              <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
          </span>
          <span class="sidebar-link-text">Cases</span>
        </a>
        <a href="/armas/agency/reports.php" class="sidebar-link">
          <span class="sidebar-link-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="20" x2="18" y2="10"></line>
              <line x1="12" y1="20" x2="12" y2="4"></line>
              <line x1="6" y1="20" x2="6" y2="14"></line>
            </svg>
          </span>
          <span class="sidebar-link-text">Reports</span>
        </a>
        <a href="/armas/agency/ofw-tracking.php" class="sidebar-link active">
          <span class="sidebar-link-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
              <circle cx="12" cy="10" r="3"></circle>
            </svg>
          </span>
          <span class="sidebar-link-text">OFW Tracking</span>
        </a>
      </div>

      <div class="sidebar-section">
        <div class="sidebar-section-title">Account</div>
        <a href="/armas/agency/profile.php" class="sidebar-link">
          <span class="sidebar-link-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
              <circle cx="12" cy="7" r="4"></circle>
            </svg>
          </span>
          <span class="sidebar-link-text">Profile</span>
        </a>
      </div>
    </nav>

    <div class="sidebar-footer">
      <a href="/armas/pages/logout.php" class="btn btn-outline btn-sm w-100" style="display:flex; align-items:center; justify-content:center; gap:8px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
          <polyline points="16 17 21 12 16 7"></polyline>
          <line x1="21" y1="12" x2="9" y2="12"></line>
        </svg>
        <span class="sidebar-link-text">Logout</span>
      </a>
    </div>
  </aside>

  <main class="main-content" style="padding:0;overflow:hidden">
    <header class="main-header">
      <div class="main-header-title" style="display:flex; align-items:center; gap:16px;">
        <button class="sidebar-toggle" onclick="toggleSidebar()" title="Toggle Sidebar">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
          </svg>
        </button>
      </div>
      <div class="main-header-actions">
        <div class="user-info">
          <div class="user-avatar"><?php echo substr($agency['name'], 0, 1); ?></div>
          <div class="user-details">
            <div class="user-name"><?php echo htmlspecialchars($agency['name']); ?></div>
            <div class="user-role">Agency</div>
          </div>
        </div>
      </div>
    </header>

    <div class="tracking-layout">
      <div class="tracking-sidebar">
        <div class="tracking-header">
          <h2>📍 OFW Locations</h2>
          <p><?php
              $online_count = 0;
              foreach ($ofws as $o) {
                if ($o['last_login'] && strtotime($o['last_login']) >= strtotime($online_threshold)) $online_count++;
              }
              echo count($ofws) . ' OFWs · ' . $online_count . ' online';
              ?></p>
          <div class="legend">
            <span><span class="ldot" style="background:#22c55e"></span> Online</span>
            <span><span class="ldot" style="background:#ef4444"></span> Offline</span>
            <span><span class="ldot" style="background:#94a3b8"></span> Never</span>
          </div>
        </div>
        <div class="tracking-search">
          <input type="text" id="searchOfw" placeholder="Search name or country..." onkeyup="filterCards()">
        </div>
        <div class="ofw-list-scroll" id="ofwCards">
          <?php foreach ($ofws as $i => $ofw):
            $is_online = $ofw['last_login'] && strtotime($ofw['last_login']) >= strtotime($online_threshold);
            $never = !$ofw['last_login'];
            $dot_class = $never ? 'dot-gray' : ($is_online ? 'dot-green' : 'dot-red');
            $pill_class = $never ? 'pill-never' : ($is_online ? 'pill-online' : 'pill-offline');
            $pill_text = $never ? 'Never' : ($is_online ? 'Online' : 'Offline');
          ?>
            <div class="ofw-card"
              data-idx="<?php echo $i; ?>"
              data-search="<?php echo strtolower($ofw['first_name'] . ' ' . $ofw['last_name'] . ' ' . ($ofw['country'] ?? '')); ?>"
              onclick="focusOfw(this)">
              <div class="ofw-dot <?php echo $dot_class; ?>"></div>
              <div class="ofw-card-body">
                <div class="ofw-card-name">
                  <?php echo htmlspecialchars($ofw['last_name'] . ', ' . $ofw['first_name']); ?>
                  <span class="status-pill <?php echo $pill_class; ?>"><?php echo $pill_text; ?></span>
                </div>
                <div class="ofw-card-meta">
                  <span>📧 <?php echo htmlspecialchars($ofw['email']); ?></span>
                  <?php if ($ofw['country']): ?><span>🌍 <?php echo htmlspecialchars(($ofw['city'] ? $ofw['city'] . ', ' : '') . $ofw['country']); ?></span><?php endif; ?>
                  <span>🕒 Last Login: <?php echo $ofw['last_login'] ? date('M d, Y h:i A', strtotime($ofw['last_login'])) : 'Never'; ?></span>
                  <?php if ($ofw['last_login_ip']): ?><span>🌐 IP: <?php echo htmlspecialchars($ofw['last_login_ip']); ?></span><?php endif; ?>
                </div>
                <?php if ($ofw['latitude'] && $ofw['longitude']): ?>
                  <span class="badge-ok">📍 Location Available</span>
                  <span style="font-size:.67rem;color:var(--mid);display:block;margin-top:2px">Updated: <?php echo $ofw['location_updated_at'] ? date('M d, Y h:i A', strtotime($ofw['location_updated_at'])) : 'Unknown'; ?></span>
                <?php else: ?>
                  <span class="badge-no">No GPS Data</span>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="tracking-map">
        <div id="map"></div>
      </div>
    </div>
  </main>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
  const ofwData = <?php echo json_encode(array_map(function ($o) use ($online_threshold) {
                    return [
                      'name'       => $o['first_name'] . ' ' . $o['last_name'],
                      'email'      => $o['email'],
                      'country'    => $o['country'] ?? '',
                      'city'       => $o['city'] ?? '',
                      'lat'        => $o['latitude']  ? floatval($o['latitude'])  : null,
                      'lng'        => $o['longitude'] ? floatval($o['longitude']) : null,
                      'loc_time'   => $o['location_updated_at'],
                      'last_login' => $o['last_login'],
                      'ip'         => $o['last_login_ip'] ?? '',
                      'is_online'  => $o['last_login'] && strtotime($o['last_login']) >= strtotime($online_threshold),
                      'never'      => !$o['last_login'],
                    ];
                  }, $ofws)); ?>;

  const map = L.map('map').setView([20, 0], 2);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    maxZoom: 19
  }).addTo(map);

  function makeIcon(ofw) {
    let cls = ofw.never ? 'marker-gray' : (ofw.is_online ? 'marker-online' : 'marker-offline');
    return L.divIcon({
      className: '',
      html: '<div class="' + cls + '"></div>',
      iconSize: [16, 16],
      iconAnchor: [8, 8],
      popupAnchor: [0, -14]
    });
  }

  const markers = [];
  ofwData.forEach(function(ofw, idx) {
    if (!ofw.lat || !ofw.lng) {
      markers.push(null);
      return;
    }
    const loginTime = ofw.last_login ? new Date(ofw.last_login).toLocaleString('en-PH', {
      timeZone: 'Asia/Manila'
    }) : 'Never';
    const locTime = ofw.loc_time ? new Date(ofw.loc_time).toLocaleString('en-PH', {
      timeZone: 'Asia/Manila'
    }) : 'Unknown';
    const statusDot = ofw.never ? '⚪' : (ofw.is_online ? '🟢' : '🔴');
    const statusTxt = ofw.never ? 'Never logged in' : (ofw.is_online ? 'Online now' : 'Offline');
    const popup = `<div style="min-width:210px;font-family:sans-serif;font-size:13px;line-height:1.7">
        <div style="font-weight:700;font-size:14px;margin-bottom:4px">👤 ${ofw.name}</div>
        <div>${statusDot} <b>${statusTxt}</b></div>
        <div>📧 ${ofw.email}</div>
        ${ofw.city||ofw.country ? '<div>🌍 '+[ofw.city,ofw.country].filter(Boolean).join(', ')+'</div>' : ''}
        <hr style="margin:6px 0;border:none;border-top:1px solid #e2e8f0">
        <div>🕒 <b>Last Login:</b> ${loginTime}</div>
        ${ofw.ip ? '<div>🌐 IP: '+ofw.ip+'</div>' : ''}
        <div>📍 <b>Location As Of:</b> ${locTime}</div>
        <div style="margin-top:5px;font-size:11px;color:#94a3b8">📌 ${ofw.lat.toFixed(5)}, ${ofw.lng.toFixed(5)}</div>
    </div>`;
    const m = L.marker([ofw.lat, ofw.lng], {
      icon: makeIcon(ofw)
    }).addTo(map).bindPopup(popup, {
      maxWidth: 270
    });
    m.on('click', function() {
      highlightCard(idx);
    });
    markers.push(m);
  });

  function focusOfw(card) {
    document.querySelectorAll('.ofw-card').forEach(c => c.classList.remove('active'));
    card.classList.add('active');
    const idx = parseInt(card.dataset.idx);
    if (markers[idx]) {
      map.setView(markers[idx].getLatLng(), 14);
      markers[idx].openPopup();
    }
  }

  function highlightCard(idx) {
    const cards = document.querySelectorAll('.ofw-card');
    cards.forEach(c => c.classList.remove('active'));
    if (cards[idx]) {
      cards[idx].classList.add('active');
      cards[idx].scrollIntoView({
        behavior: 'smooth',
        block: 'nearest'
      });
    }
  }

  function filterCards() {
    const q = document.getElementById('searchOfw').value.toLowerCase();
    document.querySelectorAll('.ofw-card').forEach(c => {
      c.style.display = c.dataset.search.includes(q) ? '' : 'none';
    });
  }
</script>

<script>
  function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const isMobile = window.innerWidth <= 768;

    if (isMobile) {
      sidebar.classList.toggle('mobile-open');
      // Add/remove overlay
      let overlay = document.getElementById('sidebarOverlay');
      if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'sidebarOverlay';
        overlay.className = 'sidebar-overlay';
        overlay.onclick = () => {
          sidebar.classList.remove('mobile-open');
          overlay.classList.remove('active');
        };
        document.body.appendChild(overlay);
      }
      overlay.classList.toggle('active', sidebar.classList.contains('mobile-open'));
    } else {
      sidebar.classList.toggle('collapsed');
      localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    }
  }

  document.addEventListener('DOMContentLoaded', function() {
    if (window.innerWidth > 768 && localStorage.getItem('sidebarCollapsed') === 'true') {
      document.querySelector('.sidebar').classList.add('collapsed');
    }
  });
</script>

<?php include '../includes/footer.php'; ?>