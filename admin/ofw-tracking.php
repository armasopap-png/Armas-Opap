<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_auth('admin');
$page_title = 'OFW Tracking';
$use_dashboard_css = true;
$ofws = $pdo->query("
    SELECT o.id, o.first_name, o.last_name, o.country, o.city,
           o.latitude, o.longitude, o.location_updated_at,
           u.email, u.last_login, u.last_login_ip, u.status,
           a.name as agency_name
    FROM ofws o
    JOIN users u ON o.user_id = u.id
    LEFT JOIN agencies a ON o.agency_id = a.id
    ORDER BY o.last_name ASC
")->fetchAll();
$online_threshold = date('Y-m-d H:i:s', strtotime('-5 minutes'));
$hide_navbar = true;
include '../includes/header.php';
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
.tracking-layout{display:flex;height:calc(100vh - 72px);overflow:hidden}
.tracking-sidebar{width:370px;min-width:260px;background:#fff;border-right:1px solid var(--border);display:flex;flex-direction:column;overflow:hidden}
.tracking-map{flex:1;position:relative}
#map{width:100%;height:100%}
.tracking-header{padding:14px 16px;background:var(--primary);color:#fff}
.tracking-header h2{font-size:.95rem;margin:0 0 2px}
.tracking-header p{font-size:.75rem;opacity:.8;margin:0}
.legend{display:flex;gap:14px;font-size:.72rem;color:#fff;margin-top:6px;align-items:center}
.legend span{display:flex;align-items:center;gap:4px}
.ldot{width:10px;height:10px;border-radius:50%;display:inline-block;flex-shrink:0}
.tracking-search{padding:12px 16px;border-bottom:1px solid var(--border)}
.tracking-search input{width:100%;padding:9px 13px;border:2px solid var(--border);border-radius:var(--radius-md);font-size:.88rem;box-sizing:border-box}
.tracking-search input:focus{outline:none;border-color:var(--primary)}
.ofw-list-scroll{flex:1;overflow-y:auto}
.ofw-card{padding:12px 16px;border-bottom:1px solid var(--border);cursor:pointer;transition:background .15s;display:flex;align-items:flex-start;gap:10px}
.ofw-card:hover,.ofw-card.active{background:#EEF2FF}
.ofw-dot{width:12px;height:12px;border-radius:50%;flex-shrink:0;margin-top:5px}
.dot-green{background:#22c55e;box-shadow:0 0 0 3px rgba(34,197,94,.25)}
.dot-red{background:#ef4444;box-shadow:0 0 0 3px rgba(239,68,68,.2)}
.dot-gray{background:#94a3b8;box-shadow:0 0 0 3px rgba(148,163,184,.2)}
.ofw-card-body{flex:1;min-width:0}
.ofw-card-name{font-weight:600;font-size:.92rem;color:var(--dark);margin-bottom:2px;display:flex;align-items:center;gap:6px}
.status-pill{font-size:.67rem;font-weight:700;padding:1px 7px;border-radius:99px}
.pill-online{color:#16a34a;background:#dcfce7}
.pill-offline{color:#dc2626;background:#fee2e2}
.pill-never{color:#64748b;background:#f1f5f9}
.ofw-card-meta{font-size:.74rem;color:var(--mid);line-height:1.65}
.ofw-card-meta span{display:block}
.badge-ok{background:#D1FAE5;color:#065F46;padding:2px 8px;border-radius:99px;font-size:.67rem;font-weight:600;display:inline-block;margin-top:3px}
.badge-no{background:#FEE2E2;color:#991B1B;padding:2px 8px;border-radius:99px;font-size:.67rem;font-weight:600;display:inline-block;margin-top:3px}
.marker-online{background:#22c55e;width:16px;height:16px;border-radius:50%;border:3px solid #fff;box-shadow:0 0 0 3px rgba(34,197,94,.4),0 2px 6px rgba(0,0,0,.3)}
.marker-offline{background:#ef4444;width:16px;height:16px;border-radius:50%;border:3px solid #fff;box-shadow:0 0 0 3px rgba(239,68,68,.3),0 2px 6px rgba(0,0,0,.25)}
.marker-gray{background:#94a3b8;width:16px;height:16px;border-radius:50%;border:3px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.2)}
/* OFW Info Panel */
#ofwInfoPanel{position:absolute;top:16px;right:16px;z-index:1000;background:#fff;border-radius:14px;box-shadow:0 4px 24px rgba(0,0,0,.13);width:270px;display:none;overflow:hidden;font-family:sans-serif}
#ofwInfoPanel.show{display:block;animation:slideIn .2s ease}
@keyframes slideIn{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:translateY(0)}}
.oip-header{padding:14px 16px 10px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:10px}
.oip-avatar{width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;font-weight:700;color:#fff}
.oip-avatar.av-online{background:linear-gradient(135deg,#22c55e,#16a34a)}
.oip-avatar.av-offline{background:linear-gradient(135deg,#ef4444,#dc2626)}
.oip-avatar.av-never{background:linear-gradient(135deg,#94a3b8,#64748b)}
.oip-name{font-weight:700;font-size:.92rem;color:#1e293b;line-height:1.3}
.oip-status{display:flex;align-items:center;gap:5px;font-size:.73rem;font-weight:600;margin-top:3px}
.oip-status .sdot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
.sdot-green{background:#22c55e}
.sdot-red{background:#ef4444}
.sdot-gray{background:#94a3b8}
.oip-status .stxt-online{color:#16a34a}
.oip-status .stxt-offline{color:#dc2626}
.oip-status .stxt-never{color:#64748b}
.oip-close{margin-left:auto;cursor:pointer;color:#94a3b8;font-size:1.1rem;line-height:1;padding:2px 4px;flex-shrink:0}
.oip-close:hover{color:#64748b}
.oip-body{padding:12px 16px}
.oip-row{display:flex;align-items:flex-start;gap:8px;font-size:.78rem;color:#475569;margin-bottom:7px}
.oip-row:last-child{margin-bottom:0}
.oip-icon{width:16px;flex-shrink:0;text-align:center;margin-top:1px}
.oip-val{line-height:1.4}
.oip-coords{font-size:.7rem;color:#94a3b8;margin-top:2px}
.oip-nogps{padding:16px;text-align:center;color:#94a3b8;font-size:.82rem}
.oip-nogps .nogps-icon{font-size:2rem;margin-bottom:6px}
.oip-footer{padding:10px 16px;border-top:1px solid #f1f5f9;display:flex;gap:8px}
.oip-btn{flex:1;padding:7px;border-radius:8px;border:none;font-size:.75rem;font-weight:600;cursor:pointer;transition:background .15s}
.oip-btn-primary{background:#4F46E5;color:#fff}
.oip-btn-primary:hover{background:#4338ca}
.oip-btn-secondary{background:#f1f5f9;color:#475569}
.oip-btn-secondary:hover{background:#e2e8f0}
@media(max-width:768px){
  .tracking-layout{flex-direction:column;height:auto}
  .tracking-sidebar{width:100%;min-width:unset;max-height:340px;border-right:none;border-bottom:1px solid var(--border)}
  #map{height:420px}
}
</style>

<div class="dashboard-layout" id="dashboardLayout">
  <aside class="sidebar">
    <div class="sidebar-brand"><img src="/armas/assets/img/armas.png" alt="ARMAS" class="sidebar-logo">
      <div class="sidebar-brand-text"><span class="logo-text">ARMAS</span><span class="brand-subtitle">Admin Portal</span></div>
    </div>
    <nav class="sidebar-nav">
      <div class="sidebar-section">
        <div class="sidebar-section-title">Main Menu</div>
        <a href="/armas/admin/dashboard.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></span><span class="sidebar-link-text">Dashboard</span></a>
        <a href="/armas/admin/create-ofw.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="8" r="4"/><line x1="19" y1="3" x2="19" y2="9"/><line x1="16" y1="6" x2="22" y2="6"/></svg></span><span class="sidebar-link-text">Create OFW</span></a>
        <a href="/armas/admin/create-agency.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-4h6v4"/><rect x="9" y="9" width="2" height="2"/><rect x="13" y="9" width="2" height="2"/><line x1="19" y1="1" x2="19" y2="7"/><line x1="16" y1="4" x2="22" y2="4"/></svg></span><span class="sidebar-link-text">Create Agency</span></a>
        <a href="/armas/admin/agency-list.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-4h6v4"/><rect x="9" y="9" width="2" height="2"/><rect x="13" y="9" width="2" height="2"/></svg></span><span class="sidebar-link-text">Agencies</span></a>
        <a href="/armas/admin/agency-cases.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="16" y2="17"/><line x1="8" y1="9" x2="10" y2="9"/></svg></span><span class="sidebar-link-text">Agency Cases</span></a>
        <a href="/armas/admin/reports.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/></svg></span><span class="sidebar-link-text">Reports</span></a>
        <a href="/armas/admin/manage-accounts.php" class="sidebar-link"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span><span class="sidebar-link-text">Accounts</span></a>
        <a href="/armas/admin/ofw-tracking.php" class="sidebar-link active"><span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></span><span class="sidebar-link-text">OFW Tracking</span></a>
      </div>
    </nav>
    <div class="sidebar-footer"><a href="/armas/pages/logout.php" class="btn btn-outline btn-sm w-100">Logout</a></div>
  </aside>

  <main class="main-content" style="padding:0;overflow:hidden">
    <header class="main-header" style="padding:0 24px">
      <div class="main-header-title"><h1>OFW Tracking</h1></div>
    </header>
    <div class="tracking-layout">
      <div class="tracking-sidebar">
        <div class="tracking-header">
          <h2>📍 OFW Locations</h2>
          <p><?php
            $online_count = 0;
            foreach($ofws as $o){
              if(!empty($o['last_login']) && strtotime($o['last_login']) >= strtotime('-5 minutes')) $online_count++;
            }
            echo count($ofws).' OFWs · '.$online_count.' online';
          ?></p>
          <div class="legend">
            <span><span class="ldot" style="background:#22c55e"></span> Online</span>
            <span><span class="ldot" style="background:#ef4444"></span> Offline</span>
            <span><span class="ldot" style="background:#94a3b8"></span> Never</span>
          </div>
        </div>
        <div class="tracking-search">
          <input type="text" id="searchOfw" placeholder="Search name, country, agency..." onkeyup="filterCards()">
        </div>
        <div class="ofw-list-scroll" id="ofwCards">
          <?php foreach ($ofws as $i => $ofw):
            // FIX: Use strtotime('-5 minutes') directly for reliable comparison
            $is_online = !empty($ofw['last_login']) && strtotime($ofw['last_login']) >= strtotime('-5 minutes');
            $never = empty($ofw['last_login']);
            $dot_class = $never ? 'dot-gray' : ($is_online ? 'dot-green' : 'dot-red');
            $pill_class = $never ? 'pill-never' : ($is_online ? 'pill-online' : 'pill-offline');
            $pill_text = $never ? 'Never' : ($is_online ? 'Online' : 'Offline');
          ?>
          <div class="ofw-card"
               data-idx="<?php echo $i; ?>"
               data-search="<?php echo strtolower($ofw['first_name'].' '.$ofw['last_name'].' '.($ofw['country']??'').' '.($ofw['agency_name']??'')); ?>"
               onclick="focusOfw(this)">
            <div class="ofw-dot <?php echo $dot_class; ?>"></div>
            <div class="ofw-card-body">
              <div class="ofw-card-name">
                <?php echo htmlspecialchars($ofw['last_name'].', '.$ofw['first_name']); ?>
                <span class="status-pill <?php echo $pill_class; ?>"><?php echo $pill_text; ?></span>
              </div>
              <div class="ofw-card-meta">
                <span>📧 <?php echo htmlspecialchars($ofw['email']); ?></span>
                <span>🏢 <?php echo htmlspecialchars($ofw['agency_name'] ?? 'Unassigned'); ?></span>
                <?php if($ofw['country']): ?><span>🌍 <?php echo htmlspecialchars(($ofw['city'] ? $ofw['city'].', ' : '').$ofw['country']); ?></span><?php endif; ?>
                <span>🕒 Last Login: <?php echo $ofw['last_login'] ? date('M d, Y h:i A', strtotime($ofw['last_login'])) : 'Never'; ?></span>
                <?php if($ofw['last_login_ip']): ?><span>🌐 IP: <?php echo htmlspecialchars($ofw['last_login_ip']); ?></span><?php endif; ?>
              </div>
              <?php if($ofw['latitude'] && $ofw['longitude']): ?>
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
        <!-- OFW Info Panel -->
        <div id="ofwInfoPanel">
          <div class="oip-header">
            <div class="oip-avatar" id="oipAvatar"></div>
            <div style="flex:1;min-width:0">
              <div class="oip-name" id="oipName"></div>
              <div class="oip-status">
                <span class="sdot" id="oipStatusDot"></span>
                <span id="oipStatusTxt"></span>
              </div>
            </div>
            <span class="oip-close" onclick="closeInfoPanel()">✕</span>
          </div>
          <div id="oipBody"></div>
          <div class="oip-footer" id="oipFooter"></div>
        </div>
      </div>
    </div>
  </main>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const ofwData = <?php echo json_encode(array_map(function($o){
  // FIX: Recalculate strtotime('-5 minutes') fresh inside the closure
  // instead of relying on the pre-built $online_threshold string variable
  $is_online = !empty($o['last_login']) && strtotime($o['last_login']) >= strtotime('-5 minutes');
  return [
    'name'       => $o['first_name'].' '.$o['last_name'],
    'email'      => $o['email'],
    'agency'     => $o['agency_name'] ?? '',
    'country'    => $o['country'] ?? '',
    'city'       => $o['city'] ?? '',
    'lat'        => $o['latitude']  ? floatval($o['latitude'])  : null,
    'lng'        => $o['longitude'] ? floatval($o['longitude']) : null,
    'loc_time'   => $o['location_updated_at'],
    'last_login' => $o['last_login'],
    'ip'         => $o['last_login_ip'] ?? '',
    'is_online'  => $is_online,
    'never'      => empty($o['last_login']),
  ];
}, $ofws)); ?>;

const map = L.map('map').setView([20, 0], 2);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',maxZoom:19}).addTo(map);

function makeIcon(ofw){
    let cls = ofw.never ? 'marker-gray' : (ofw.is_online ? 'marker-online' : 'marker-offline');
    return L.divIcon({className:'',html:'<div class="'+cls+'"></div>',iconSize:[16,16],iconAnchor:[8,8],popupAnchor:[0,-14]});
}

const markers = [];
ofwData.forEach(function(ofw, idx){
    if(!ofw.lat || !ofw.lng){ markers.push(null); return; }
    const loginTime = ofw.last_login ? new Date(ofw.last_login).toLocaleString('en-PH',{timeZone:'Asia/Manila'}) : 'Never';
    const locTime   = ofw.loc_time   ? new Date(ofw.loc_time).toLocaleString('en-PH',{timeZone:'Asia/Manila'})   : 'Unknown';
    const statusDot = ofw.never ? '⚪' : (ofw.is_online ? '🟢' : '🔴');
    const statusTxt = ofw.never ? 'Never logged in' : (ofw.is_online ? 'Online now' : 'Offline');
    const popup = `<div style="min-width:210px;font-family:sans-serif;font-size:13px;line-height:1.7">
        <div style="font-weight:700;font-size:14px;margin-bottom:4px">👤 ${ofw.name}</div>
        <div>${statusDot} <b>${statusTxt}</b></div>
        <div>📧 ${ofw.email}</div>
        ${ofw.agency ? '<div>🏢 '+ofw.agency+'</div>' : ''}
        ${ofw.city||ofw.country ? '<div>🌍 '+[ofw.city,ofw.country].filter(Boolean).join(', ')+'</div>' : ''}
        <hr style="margin:6px 0;border:none;border-top:1px solid #e2e8f0">
        <div>🕒 <b>Last Login:</b> ${loginTime}</div>
        ${ofw.ip ? '<div>🌐 IP: '+ofw.ip+'</div>' : ''}
        <div>📍 <b>Location As Of:</b> ${locTime}</div>
        <div style="margin-top:5px;font-size:11px;color:#94a3b8">📌 ${ofw.lat.toFixed(5)}, ${ofw.lng.toFixed(5)}</div>
    </div>`;
    const m = L.marker([ofw.lat, ofw.lng], {icon: makeIcon(ofw)}).addTo(map).bindPopup(popup, {maxWidth:270});
    m.on('click', function(){ highlightCard(idx); });
    markers.push(m);
});

function showInfoPanel(ofw){
    const panel   = document.getElementById('ofwInfoPanel');
    const avatar  = document.getElementById('oipAvatar');
    const nameEl  = document.getElementById('oipName');
    const dot     = document.getElementById('oipStatusDot');
    const stxt    = document.getElementById('oipStatusTxt');
    const body    = document.getElementById('oipBody');
    const footer  = document.getElementById('oipFooter');

    // Initials avatar
    const initials = ofw.name.split(' ').map(n=>n[0]).slice(0,2).join('').toUpperCase();
    avatar.textContent = initials;
    avatar.className = 'oip-avatar ' + (ofw.never ? 'av-never' : (ofw.is_online ? 'av-online' : 'av-offline'));

    nameEl.textContent = ofw.name;

    // Status
    if(ofw.never){
        dot.className = 'sdot sdot-gray';
        stxt.className = 'stxt-never'; stxt.textContent = 'Never logged in';
    } else if(ofw.is_online){
        dot.className = 'sdot sdot-green';
        stxt.className = 'stxt-online'; stxt.textContent = 'Online now';
    } else {
        dot.className = 'sdot sdot-red';
        stxt.className = 'stxt-offline'; stxt.textContent = 'Offline';
    }

    // Body content
    const loginTime = ofw.last_login ? new Date(ofw.last_login).toLocaleString('en-PH',{timeZone:'Asia/Manila'}) : 'Never';
    const locTime   = ofw.loc_time   ? new Date(ofw.loc_time).toLocaleString('en-PH',{timeZone:'Asia/Manila'})   : null;

    let bodyHtml = '<div class="oip-body">';
    bodyHtml += `<div class="oip-row"><span class="oip-icon">📧</span><span class="oip-val">${ofw.email}</span></div>`;
    if(ofw.agency) bodyHtml += `<div class="oip-row"><span class="oip-icon">🏢</span><span class="oip-val">${ofw.agency}</span></div>`;
    if(ofw.city||ofw.country) bodyHtml += `<div class="oip-row"><span class="oip-icon">🌍</span><span class="oip-val">${[ofw.city,ofw.country].filter(Boolean).join(', ')}</span></div>`;
    bodyHtml += `<div class="oip-row"><span class="oip-icon">🕒</span><span class="oip-val"><b>Last Login:</b><br>${loginTime}</span></div>`;
    if(ofw.ip) bodyHtml += `<div class="oip-row"><span class="oip-icon">🌐</span><span class="oip-val">IP: ${ofw.ip}</span></div>`;

    if(ofw.lat && ofw.lng){
        bodyHtml += `<div class="oip-row"><span class="oip-icon">📍</span><span class="oip-val"><b>Location as of:</b><br>${locTime||'Unknown'}<div class="oip-coords">${ofw.lat.toFixed(5)}, ${ofw.lng.toFixed(5)}</div></span></div>`;
    } else {
        bodyHtml += `</div><div class="oip-nogps"><div class="nogps-icon">📡</div>No GPS data available for this OFW.</div><div class="oip-body-pad" style="padding-bottom:4px">`;
    }
    bodyHtml += '</div>';
    body.innerHTML = bodyHtml;

    // Footer buttons
    if(ofw.lat && ofw.lng){
        footer.style.display = 'flex';
        footer.innerHTML = `
            <button class="oip-btn oip-btn-primary" onclick="zoomToOfw(currentPanelIdx)">🗺️ Zoom In</button>
            <button class="oip-btn oip-btn-secondary" onclick="openGoogleMaps(${ofw.lat},${ofw.lng})">↗ Google Maps</button>`;
    } else {
        footer.style.display = 'none';
        footer.innerHTML = '';
    }

    panel.classList.add('show');
}

function closeInfoPanel(){
    document.getElementById('ofwInfoPanel').classList.remove('show');
    document.querySelectorAll('.ofw-card').forEach(c=>c.classList.remove('active'));
}

let currentPanelIdx = null;

function focusOfw(card){
    document.querySelectorAll('.ofw-card').forEach(c=>c.classList.remove('active'));
    card.classList.add('active');
    const idx = parseInt(card.dataset.idx);
    currentPanelIdx = idx;
    const ofw = ofwData[idx];

    // Show info panel
    showInfoPanel(ofw);

    // Fly to marker or just show panel if no GPS
    if(markers[idx]){
        map.flyTo(markers[idx].getLatLng(), 15, {animate:true, duration:1});
        setTimeout(()=>{ markers[idx].openPopup(); }, 800);
    }
}

function zoomToOfw(idx){
    if(markers[idx]){
        map.flyTo(markers[idx].getLatLng(), 17, {animate:true, duration:1});
        setTimeout(()=>{ markers[idx].openPopup(); }, 800);
    }
}

function openGoogleMaps(lat, lng){
    window.open('https://www.google.com/maps?q='+lat+','+lng, '_blank');
}

function highlightCard(idx){
    const cards = document.querySelectorAll('.ofw-card');
    cards.forEach(c=>c.classList.remove('active'));
    if(cards[idx]){
        cards[idx].classList.add('active');
        cards[idx].scrollIntoView({behavior:'smooth',block:'nearest'});
        currentPanelIdx = idx;
        showInfoPanel(ofwData[idx]);
    }
}
function filterCards(){
    const q = document.getElementById('searchOfw').value.toLowerCase();
    document.querySelectorAll('.ofw-card').forEach(c=>{ c.style.display = c.dataset.search.includes(q)?'':'none'; });
}
</script>
<?php include '../includes/footer.php'; ?>