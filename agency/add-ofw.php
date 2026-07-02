<?php

/**
 * ARMAS Agency Add OFW
 */
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_auth('agency');

$page_title = 'Add OFW';
$use_dashboard_css = true;
$success = '';
$error = '';

// Always fetch agency for header
$stmt = $pdo->prepare("SELECT * FROM agencies WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$agency = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("SELECT id FROM agencies WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $agency = $stmt->fetch();

    $last_name = strtoupper(trim(htmlspecialchars($_POST['last_name'])));
    $first_name = strtoupper(trim(htmlspecialchars($_POST['first_name'])));
    $middle_name = strtoupper(trim(htmlspecialchars($_POST['middle_name'])));
    $suffix = htmlspecialchars($_POST['suffix']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['temp_password'];
    $address = strtoupper(trim(htmlspecialchars($_POST['address'])));
    $contact = htmlspecialchars($_POST['contact_number']);
    $country  = htmlspecialchars(trim($_POST['country'] ?? ''));
    $city     = htmlspecialchars(trim($_POST['city'] ?? ''));

    // Check if email exists
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        $error = 'Email already registered.';
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);

        try {
            $pdo->beginTransaction();

            $pdo->prepare("INSERT INTO users (email, password_hash, role, status) VALUES (?,?,'ofw','active')")
                ->execute([$email, $hash]);
            $user_id = $pdo->lastInsertId();

            $pdo->prepare("INSERT INTO ofws (user_id, last_name, first_name, middle_name, suffix, agency_id, address, contact_number, country, city)
                           VALUES (?,?,?,?,?,?,?,?,?,?)")
                ->execute([$user_id, $last_name, $first_name, $middle_name, $suffix, $agency['id'], $address, $contact, $country, $city]);

            $pdo->commit();

            $success = 'OFW record successfully added.';
            log_audit($pdo, $_SESSION['user_id'], 'CREATE_OFW', 'users', $user_id);
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Failed to add OFW. Please try again.';
        }
    }
}
?>
<?php
$hide_navbar = true;
include '../includes/header.php'; ?>

<div class="dashboard-layout">
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
                <a href="/armas/agency/add-ofw.php" class="sidebar-link active">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="8.5" cy="7" r="4"></circle>
                            <line x1="20" y1="8" x2="20" y2="14"></line>
                            <line x1="23" y1="11" x2="17" y2="11"></line>
                        </svg>
                    </span>
                    <span class="sidebar-link-text">Add OFW</span>
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
                    <span class="sidebar-link-text">Reports</span>
                </a>
                <a href="/armas/agency/reports.php" class="sidebar-link">
                    <span class="sidebar-link-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="20" x2="18" y2="10"></line>
                            <line x1="12" y1="20" x2="12" y2="4"></line>
                            <line x1="6" y1="20" x2="6" y2="14"></line>
                        </svg>
                    </span>
                    <span class="sidebar-link-text">Analytics</span>
                </a>
                <a href="/armas/agency/ofw-tracking.php" class="sidebar-link">
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

    <main class="main-content">
        <header class="main-header">
            <div class="main-header-title" style="display:flex; align-items:center; gap:16px;">
                <button class="sidebar-toggle d-mobile-only" onclick="toggleSidebar()" title="Menu">
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

        <div class="main-body">
            <?php if ($success): ?>
                <div class="flash flash-success">
                    <span><?php echo $success; ?></span>
                    <button class="flash-close" onclick="this.parentElement.style.display='none'">&times;</button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="flash flash-error">
                    <span><?php echo $error; ?></span>
                    <button class="flash-close" onclick="this.parentElement.style.display='none'">&times;</button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3>OFW Information</h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div class="form-group">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control input-caps"
                                    oninput="this.value=this.value.toUpperCase()" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control input-caps"
                                    oninput="this.value=this.value.toUpperCase()" required>
                            </div>
                        </div>

                        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div class="form-group">
                                <label class="form-label">Middle Name</label>
                                <input type="text" name="middle_name" class="form-control input-caps"
                                    oninput="this.value=this.value.toUpperCase()">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Suffix</label>
                                <input type="text" name="suffix" class="form-control" placeholder="Jr., Sr., III">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Temporary Password</label>
                            <input type="text" name="temp_password" class="form-control" required minlength="8"
                                placeholder="Min 8 characters">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control input-caps" rows="2"
                                oninput="this.value=this.value.toUpperCase()"></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Contact Number</label>
                            <input type="text" name="contact_number" class="form-control">
                        </div>

                        <!-- Country Searchable Dropdown -->
                        <div class="form-group">
                            <label class="form-label">Country (Current Work Location)</label>
                            <div class="armas-search-select" id="cntry-wrap">
                                <div class="armas-ss-trigger" id="cntry-trigger" tabindex="0" role="combobox" aria-haspopup="listbox" aria-expanded="false">
                                    <span class="armas-ss-label" id="cntry-label">Select a country...</span>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                                </div>
                                <div class="armas-ss-panel" id="cntry-panel" role="listbox" hidden>
                                    <input class="armas-ss-search" id="cntry-search" type="text" placeholder="Type to search country..." autocomplete="off" spellcheck="false">
                                    <ul class="armas-ss-list" id="cntry-list"></ul>
                                </div>
                                <input type="hidden" name="country" id="cntry-val">
                            </div>
                        </div>

                        <!-- City Searchable Dropdown -->
                        <div class="form-group">
                            <label class="form-label">City (Current Work Location)</label>
                            <div class="armas-search-select" id="city-wrap">
                                <div class="armas-ss-trigger armas-ss-disabled" id="city-trigger" tabindex="0" role="combobox" aria-haspopup="listbox" aria-expanded="false">
                                    <span class="armas-ss-label" id="city-label">Select a country first...</span>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                                </div>
                                <div class="armas-ss-panel" id="city-panel" role="listbox" hidden>
                                    <input class="armas-ss-search" id="city-search" type="text" placeholder="Type to search city..." autocomplete="off" spellcheck="false">
                                    <ul class="armas-ss-list" id="city-list"></ul>
                                </div>
                                <input type="hidden" name="city" id="city-val">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Add OFW</button>
                        <a href="/armas/agency/ofw-list.php" class="btn btn-outline">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        sidebar.classList.toggle('mobile-open');
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
    }
</script>

<style>
/* ── ARMAS Searchable Select ── */
.armas-search-select { position: relative; }

.armas-ss-trigger {
    display: flex; align-items: center; justify-content: space-between;
    height: 42px; padding: 0 14px;
    border: 1px solid #d1d5db; border-radius: 8px;
    background: #fff; cursor: pointer;
    font-size: 0.9rem; color: #374151;
    transition: border-color .2s, box-shadow .2s;
    user-select: none;
}
.armas-ss-trigger:hover { border-color: #a5b4fc; }
.armas-ss-trigger.armas-ss-open,
.armas-ss-trigger:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99,102,241,.18);
}
.armas-ss-trigger.armas-ss-disabled {
    background: #f3f4f6; color: #9ca3af;
    cursor: not-allowed; pointer-events: none;
}
.armas-ss-trigger svg { flex-shrink:0; color:#9ca3af; transition: transform .2s; }
.armas-ss-trigger.armas-ss-open svg { transform: rotate(180deg); }
.armas-ss-label { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

.armas-ss-panel {
    position: absolute; top: calc(100% + 4px); left: 0; right: 0;
    background: #fff; border: 1px solid #d1d5db; border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0,0,0,.13);
    z-index: 9999; overflow: hidden;
}
.armas-ss-panel[hidden] { display: none !important; }

.armas-ss-search {
    display: block; width: 100%; box-sizing: border-box;
    padding: 10px 14px; border: none; border-bottom: 1px solid #f0f0f0;
    font-size: 0.875rem; color: #374151;
    outline: none; background: #fafafa;
}
.armas-ss-search:focus { background: #fff; border-bottom-color: #a5b4fc; }

.armas-ss-list {
    list-style: none; margin: 0; padding: 4px 0;
    max-height: 230px; overflow-y: auto;
}
.armas-ss-list li {
    padding: 9px 16px; font-size: 0.875rem; color: #374151;
    cursor: pointer; transition: background .1s;
}
.armas-ss-list li:hover,
.armas-ss-list li.armas-ss-active { background: #eef2ff; color: #4f46e5; }
.armas-ss-list li.armas-ss-muted  { color: #9ca3af; cursor: default; font-style: italic; }
</style>

<script>
(function () {
    /* ─── All countries hardcoded — no network needed ─── */
    var COUNTRIES = [
        "Afghanistan","Albania","Algeria","Andorra","Angola","Antigua and Barbuda",
        "Argentina","Armenia","Australia","Austria","Azerbaijan","Bahamas","Bahrain",
        "Bangladesh","Barbados","Belarus","Belgium","Belize","Benin","Bhutan",
        "Bolivia","Bosnia and Herzegovina","Botswana","Brazil","Brunei","Bulgaria",
        "Burkina Faso","Burundi","Cabo Verde","Cambodia","Cameroon","Canada",
        "Central African Republic","Chad","Chile","China","Colombia","Comoros",
        "Congo","Costa Rica","Croatia","Cuba","Cyprus","Czech Republic","Denmark",
        "Djibouti","Dominica","Dominican Republic","Ecuador","Egypt","El Salvador",
        "Equatorial Guinea","Eritrea","Estonia","Eswatini","Ethiopia","Fiji",
        "Finland","France","Gabon","Gambia","Georgia","Germany","Ghana","Greece",
        "Grenada","Guatemala","Guinea","Guinea-Bissau","Guyana","Haiti","Honduras",
        "Hungary","Iceland","India","Indonesia","Iran","Iraq","Ireland","Israel",
        "Italy","Jamaica","Japan","Jordan","Kazakhstan","Kenya","Kiribati","Kuwait",
        "Kyrgyzstan","Laos","Latvia","Lebanon","Lesotho","Liberia","Libya",
        "Liechtenstein","Lithuania","Luxembourg","Madagascar","Malawi","Malaysia",
        "Maldives","Mali","Malta","Marshall Islands","Mauritania","Mauritius",
        "Mexico","Micronesia","Moldova","Monaco","Mongolia","Montenegro","Morocco",
        "Mozambique","Myanmar","Namibia","Nauru","Nepal","Netherlands","New Zealand",
        "Nicaragua","Niger","Nigeria","North Korea","North Macedonia","Norway",
        "Oman","Pakistan","Palau","Palestine","Panama","Papua New Guinea","Paraguay",
        "Peru","Philippines","Poland","Portugal","Qatar","Romania","Russia","Rwanda",
        "Saint Kitts and Nevis","Saint Lucia","Saint Vincent and the Grenadines",
        "Samoa","San Marino","Sao Tome and Principe","Saudi Arabia","Senegal",
        "Serbia","Seychelles","Sierra Leone","Singapore","Slovakia","Slovenia",
        "Solomon Islands","Somalia","South Africa","South Korea","South Sudan",
        "Spain","Sri Lanka","Sudan","Suriname","Sweden","Switzerland","Syria",
        "Taiwan","Tajikistan","Tanzania","Thailand","Timor-Leste","Togo","Tonga",
        "Trinidad and Tobago","Tunisia","Turkey","Turkmenistan","Tuvalu","Uganda",
        "Ukraine","United Arab Emirates","United Kingdom","United States","Uruguay",
        "Uzbekistan","Vanuatu","Vatican City","Venezuela","Vietnam","Yemen",
        "Zambia","Zimbabwe"
    ];

    /* ─── Generic searchable dropdown ─── */
    function SearchSelect(opts) {
        var trigger = document.getElementById(opts.triggerId);
        var panel   = document.getElementById(opts.panelId);
        var search  = document.getElementById(opts.searchId);
        var list    = document.getElementById(opts.listId);
        var hidden  = document.getElementById(opts.hiddenId);
        var label   = document.getElementById(opts.labelId);
        var onPick  = opts.onPick || function(){};
        var items   = [];

        function open() {
            if (trigger.classList.contains('armas-ss-disabled')) return;
            panel.hidden = false;
            trigger.classList.add('armas-ss-open');
            trigger.setAttribute('aria-expanded','true');
            search.value = '';
            renderItems(items);
            search.focus();
        }
        function close() {
            panel.hidden = true;
            trigger.classList.remove('armas-ss-open');
            trigger.setAttribute('aria-expanded','false');
        }
        function toggle() { panel.hidden ? open() : close(); }

        function renderItems(arr) {
            var q = search.value.toLowerCase();
            var filtered = arr.filter(function(i){ return i.toLowerCase().indexOf(q) !== -1; });
            list.innerHTML = '';
            if (!filtered.length) {
                list.innerHTML = '<li class="armas-ss-muted">No results found</li>';
                return;
            }
            filtered.forEach(function(item) {
                var li = document.createElement('li');
                li.textContent = item;
                li.addEventListener('mousedown', function(e){ e.preventDefault(); pick(item); });
                list.appendChild(li);
            });
        }

        function pick(val) {
            hidden.value = val;
            label.textContent = val;
            label.style.color = '#374151';
            close();
            onPick(val);
        }

        function setItems(arr) {
            items = arr;
        }
        function enable() {
            trigger.classList.remove('armas-ss-disabled');
        }
        function disable(msg) {
            trigger.classList.add('armas-ss-disabled');
            label.textContent = msg || 'Select a country first...';
            hidden.value = '';
        }
        function setLoading(msg) {
            list.innerHTML = '<li class="armas-ss-muted">' + msg + '</li>';
        }

        // Events
        trigger.addEventListener('click', toggle);
        trigger.addEventListener('keydown', function(e){
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggle(); }
            if (e.key === 'Escape') close();
        });
        search.addEventListener('input', function(){ renderItems(items); });
        document.addEventListener('mousedown', function(e){
            if (!trigger.contains(e.target) && !panel.contains(e.target)) close();
        });

        return { setItems: setItems, enable: enable, disable: disable, setLoading: setLoading, open: open, close: close };
    }

    /* ─── Init country ─── */
    var countryDD = SearchSelect({
        triggerId: 'cntry-trigger',
        panelId:   'cntry-panel',
        searchId:  'cntry-search',
        listId:    'cntry-list',
        hiddenId:  'cntry-val',
        labelId:   'cntry-label',
        onPick: function(country) {
            // reset city
            cityDD.disable('Loading cities...');
            cityDD.enable();
            document.getElementById('city-panel').hidden = true;

            var cityList = document.getElementById('city-list');
            cityList.innerHTML = '<li class="armas-ss-muted">Loading cities...</li>';
            document.getElementById('city-label').textContent = 'Loading cities...';

            fetch('https://countriesnow.space/api/v0.1/countries/cities', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ country: country })
            })
            .then(function(r){ return r.json(); })
            .then(function(data){
                if (!data.error && data.data && data.data.length) {
                    var sorted = data.data.slice().sort();
                    cityDD.setItems(sorted);
                    document.getElementById('city-label').textContent = 'Select a city...';
                    document.getElementById('city-val').value = '';
                } else {
                    cityDD.setItems([]);
                    document.getElementById('city-label').textContent = 'No cities found';
                }
            })
            .catch(function(){
                cityDD.setItems([]);
                document.getElementById('city-label').textContent = 'Failed to load cities';
            });
        }
    });
    countryDD.setItems(COUNTRIES);

    /* ─── Init city ─── */
    var cityDD = SearchSelect({
        triggerId: 'city-trigger',
        panelId:   'city-panel',
        searchId:  'city-search',
        listId:    'city-list',
        hiddenId:  'city-val',
        labelId:   'city-label'
    });
    // city starts disabled
    document.getElementById('city-trigger').classList.add('armas-ss-disabled');

})();
</script>


</div><!-- end main-body -->
    </main>
</div><!-- end dashboard-layout -->

<?php include '../includes/footer.php'; ?>