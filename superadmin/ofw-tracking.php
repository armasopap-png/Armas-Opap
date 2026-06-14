<?php

/**
 * ARMAS Super Admin — OFW Location Tracking
 */
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_auth('superadmin');

$page_title = 'OFW Tracking';
$use_dashboard_css = true;

// Fetch all OFWs with location and last login
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

$hide_navbar = true;
include '../includes/header.php';
?>
<style>
    .tracking-layout { display: flex; gap: 0; height: calc(100vh - 72px); overflow: hidden; }
    .tracking-sidebar { width: 360px; min-width: 280px; background: var(--white); border-right: 1px solid var(--border); display: flex; flex-direction: column; overflow: hidden; }
    .tracking-map { flex: 1; position: relative; }
    #map { width: 100%; height: 100%; }
    .tracking-search { padding: 16px; border-bottom: 1px solid var(--border); }
    .tracking-search input { width: 100%; padding: 10px 14px; border: 2px solid var(--border); border-radius: var(--radius-md); font-size: 0.9rem; box-sizing: border-box; }
    .tracking-search input:focus { outline: none; border-color: var(--primary); }
    .ofw-list-scroll { flex: 1; overflow-y: auto; }
    .ofw-card { padding: 14px 16px; border-bottom: 1px solid var(--border); cursor: pointer; transition: background 0.15s; }
    .ofw-card:hover, .ofw-card.active { background: #EEF2FF; }
    .ofw-card-name { font-weight: 600; font-size: 0.95rem; color: var(--dark); margin-bottom: 4px; }
    .ofw-card-meta { font-size: 0.78rem; color: var(--mid); line-height: 1.5; }
    .ofw-card-meta span { display: block; }
    .badge-located { background: #D1FAE5; color: #065F46; padding: 2px 8px; border-radius: 99px; font-size: 0.7rem; font-weight: 600; display: inline-block; margin-top: 4px; }
    .badge-no-location { background: #FEE2E2; color: #991B1B; padding: 2px 8px; border-radius: 99px; font-size: 0.7rem; font-weight: 600; display: inline-block; margin-top: 4px; }
    .tracking-header { padding: 16px; border-bottom: 1px solid var(--border); background: var(--primary); color: #fff; }
    .tracking-header h2 { font-size: 1rem; margin: 0 0 2px; }
    .tracking-header p { font-size: 0.78rem; opacity: 0.8; margin: 0; }
    .map-no-key { display: flex; align-items: center; justify-content: center; height: 100%; background: #f8fafc; flex-direction: column; gap: 12px; color: var(--mid); text-align: center; padding: 32px; }
    @media (max-width: 768px) {
        .tracking-layout { flex-direction: column; height: auto; }
        .tracking-sidebar { width: 100%; min-width: unset; max-height: 340px; border-right: none; border-bottom: 1px solid var(--border); }
        .tracking-map { height: 420px; }
    }
</style>

<div class="dashboard-layout" id="dashboardLayout">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="/armas/assets/img/armas.png" alt="ARMAS" class="sidebar-logo">
            <div class="sidebar-brand-text"><span class="logo-text">ARMAS</span><span class="brand-subtitle">Super Admin</span></div>
        </div>
        <nav class="sidebar-nav">
            <div class="sidebar-section">
                <div class="sidebar-section-title">Main Menu</div>
                <a href="/armas/superadmin/dashboard.php" class="sidebar-link"><span class="sidebar-link-icon">📊</span><span class="sidebar-link-text">Dashboard</span></a>
                <a href="/armas/superadmin/users-ofw.php" class="sidebar-link"><span class="sidebar-link-icon">👥</span><span class="sidebar-link-text">OFW Users</span></a>
                <a href="/armas/superadmin/users-agency.php" class="sidebar-link"><span class="sidebar-link-icon">🏢</span><span class="sidebar-link-text">Agencies</span></a>
                <a href="/armas/superadmin/users-admin.php" class="sidebar-link"><span class="sidebar-link-icon">⚙️</span><span class="sidebar-link-text">Admins</span></a>
                <a href="/armas/superadmin/agency-cases.php" class="sidebar-link"><span class="sidebar-link-icon">📋</span><span class="sidebar-link-text">Agency Cases</span></a>
                <a href="/armas/superadmin/ofw-tracking.php" class="sidebar-link active"><span class="sidebar-link-icon">📍</span><span class="sidebar-link-text">OFW Tracking</span></a>
                <a href="/armas/superadmin/audit-logs.php" class="sidebar-link"><span class="sidebar-link-icon">📝</span><span class="sidebar-link-text">Audit Logs</span></a>
            </div>
        </nav>
        <div class="sidebar-footer"><a href="/armas/pages/logout.php" class="btn btn-outline btn-sm w-100">Logout</a></div>
    </aside>

    <main class="main-content" style="padding:0; overflow:hidden;">
        <header class="main-header" style="padding: 0 24px;">
            <div class="main-header-title"><h1>OFW Tracking</h1></div>
        </header>

        <div class="tracking-layout">
            <!-- Sidebar List -->
            <div class="tracking-sidebar">
                <div class="tracking-header">
                    <h2>📍 OFW Locations</h2>
                    <p><?php echo count($ofws); ?> OFWs registered</p>
                </div>
                <div class="tracking-search">
                    <input type="text" id="searchOfw" placeholder="Search by name, country..." onkeyup="filterCards()">
                </div>
                <div class="ofw-list-scroll" id="ofwCards">
                    <?php foreach ($ofws as $ofw): ?>
                    <div class="ofw-card"
                         data-lat="<?php echo $ofw['latitude'] ?? ''; ?>"
                         data-lng="<?php echo $ofw['longitude'] ?? ''; ?>"
                         data-name="<?php echo htmlspecialchars($ofw['first_name'] . ' ' . $ofw['last_name']); ?>"
                         data-search="<?php echo strtolower($ofw['first_name'] . ' ' . $ofw['last_name'] . ' ' . $ofw['country'] . ' ' . $ofw['agency_name']); ?>"
                         onclick="focusOfw(this)">
                        <div class="ofw-card-name"><?php echo htmlspecialchars($ofw['last_name'] . ', ' . $ofw['first_name']); ?></div>
                        <div class="ofw-card-meta">
                            <span>📧 <?php echo htmlspecialchars($ofw['email']); ?></span>
                            <span>🏢 <?php echo htmlspecialchars($ofw['agency_name'] ?? 'Unassigned'); ?></span>
                            <?php if ($ofw['country']): ?>
                            <span>🌍 <?php echo htmlspecialchars($ofw['city'] ? $ofw['city'] . ', ' . $ofw['country'] : $ofw['country']); ?></span>
                            <?php endif; ?>
                            <span>🕒 Last Login: <?php echo $ofw['last_login'] ? date('M d, Y h:i A', strtotime($ofw['last_login'])) : 'Never'; ?></span>
                            <?php if ($ofw['last_login_ip']): ?>
                            <span>🌐 IP: <?php echo htmlspecialchars($ofw['last_login_ip']); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if ($ofw['latitude'] && $ofw['longitude']): ?>
                            <span class="badge-located">📍 Location Available</span>
                            <span style="font-size:0.7rem; color:var(--mid); display:block; margin-top:2px;">Updated: <?php echo $ofw['location_updated_at'] ? date('M d, Y h:i A', strtotime($ofw['location_updated_at'])) : 'Unknown'; ?></span>
                        <?php else: ?>
                            <span class="badge-no-location">No GPS Data</span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Map -->
            <div class="tracking-map">
                <div id="map"></div>
            </div>
        </div>
    </main>
</div>

<script>
const ofwData = <?php echo json_encode(array_map(function($o) {
    return [
        'name'       => $o['first_name'] . ' ' . $o['last_name'],
        'email'      => $o['email'],
        'agency'     => $o['agency_name'] ?? 'Unassigned',
        'country'    => $o['country'] ?? '',
        'city'       => $o['city'] ?? '',
        'lat'        => $o['latitude'] ? floatval($o['latitude']) : null,
        'lng'        => $o['longitude'] ? floatval($o['longitude']) : null,
        'loc_time'   => $o['location_updated_at'],
        'last_login' => $o['last_login'],
        'ip'         => $o['last_login_ip'] ?? '',
        'status'     => $o['status'],
    ];
}, $ofws)); ?>;

let map, markers = [], infoWindow;

function initMap() {
    map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: 20, lng: 0 },
        zoom: 2,
        styles: [{ featureType: 'poi', stylers: [{ visibility: 'off' }] }]
    });
    infoWindow = new google.maps.InfoWindow();

    ofwData.forEach(function(ofw, idx) {
        if (ofw.lat && ofw.lng) {
            const marker = new google.maps.Marker({
                position: { lat: ofw.lat, lng: ofw.lng },
                map: map,
                title: ofw.name,
                icon: {
                    url: 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png'
                }
            });

            const loginTime = ofw.last_login
                ? new Date(ofw.last_login).toLocaleString('en-PH', { timeZone: 'Asia/Manila' })
                : 'Never';
            const locTime = ofw.loc_time
                ? new Date(ofw.loc_time).toLocaleString('en-PH', { timeZone: 'Asia/Manila' })
                : 'Unknown';

            const content = `
                <div style="font-family:sans-serif; min-width:220px; padding:4px;">
                    <strong style="font-size:1rem;">👤 ${ofw.name}</strong><br>
                    <span style="color:#555; font-size:0.82rem;">📧 ${ofw.email}</span><br>
                    <span style="color:#555; font-size:0.82rem;">🏢 ${ofw.agency}</span><br>
                    ${ofw.city || ofw.country ? `<span style="color:#555; font-size:0.82rem;">🌍 ${[ofw.city, ofw.country].filter(Boolean).join(', ')}</span><br>` : ''}
                    <hr style="margin:6px 0; border:none; border-top:1px solid #eee;">
                    <span style="font-size:0.82rem;">🕒 <b>Last Login:</b> ${loginTime}</span><br>
                    ${ofw.ip ? `<span style="font-size:0.82rem;">🌐 IP: ${ofw.ip}</span><br>` : ''}
                    <span style="font-size:0.82rem;">📍 <b>Location As Of:</b> ${locTime}</span>
                </div>`;

            marker.addListener('click', function() {
                infoWindow.setContent(content);
                infoWindow.open(map, marker);
                highlightCard(idx);
            });

            markers.push({ marker, idx });
        } else {
            markers.push({ marker: null, idx });
        }
    });
}

function focusOfw(card) {
    document.querySelectorAll('.ofw-card').forEach(c => c.classList.remove('active'));
    card.classList.add('active');
    const lat = parseFloat(card.dataset.lat);
    const lng = parseFloat(card.dataset.lng);
    if (!isNaN(lat) && !isNaN(lng)) {
        map.setCenter({ lat, lng });
        map.setZoom(14);
        const idx = Array.from(card.parentElement.children).indexOf(card);
        if (markers[idx] && markers[idx].marker) {
            google.maps.event.trigger(markers[idx].marker, 'click');
        }
    }
}

function highlightCard(idx) {
    const cards = document.querySelectorAll('.ofw-card');
    cards.forEach(c => c.classList.remove('active'));
    if (cards[idx]) {
        cards[idx].classList.add('active');
        cards[idx].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

function filterCards() {
    const q = document.getElementById('searchOfw').value.toLowerCase();
    document.querySelectorAll('.ofw-card').forEach(card => {
        card.style.display = card.dataset.search.includes(q) ? '' : 'none';
    });
}
</script>

<!-- Replace YOUR_GOOGLE_MAPS_API_KEY below with your actual key -->
<script async defer
    src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&callback=initMap">
</script>

<?php include '../includes/footer.php'; ?>