<style>
    /* --- Main Layout Wrapper --- */
    .dashboard-layout { 
        display: flex; 
        min-height: 100vh; 
        position: relative; 
    }
    
    /* --- Desktop Mini Sidebar (Icons Only by Default) --- */
    .sidebar {
        width: 70px;
        transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow-x: hidden;
        white-space: nowrap;
        position: fixed;
        top: 0; 
        left: 0; 
        height: 100vh; 
        z-index: 1040;
        background: #1a2e5c; /* Adjusted to match your deep blue theme */
        box-shadow: 2px 0 10px rgba(0,0,0,0.05);
    }

    /* --- Desktop Expand on Hover --- */
    .sidebar:hover { 
        width: 260px; 
        box-shadow: 4px 0 20px rgba(0,0,0,0.15); 
    }

    /* --- Content Pushing Wrapper --- */
    .main-content {
        flex-grow: 1;
        margin-left: 70px;
        width: calc(100% - 70px);
        transition: margin-left 0.3s ease, width 0.3s ease;
    }

    /* --- Structural Text Fading Controllers --- */
    .sidebar .sidebar-brand-text, .sidebar .sidebar-link-text, 
    .sidebar .sidebar-section-title, .sidebar .badge, .sidebar .sidebar-footer {
        opacity: 0; 
        transition: opacity 0.2s ease; 
        display: inline-block; 
        pointer-events: none;
    }

    .sidebar:hover .sidebar-brand-text, .sidebar:hover .sidebar-link-text, 
    .sidebar:hover .sidebar-section-title, .sidebar:hover .badge, .sidebar:hover .sidebar-footer {
        opacity: 1; 
        pointer-events: auto;
    }

    /* --- Layout Component Stylings --- */
    .sidebar-brand { display: flex; align-items: center; gap: 12px; padding: 15px; }
    .sidebar-logo { width: 40px; height: 40px; flex-shrink: 0; border-radius: 50%; }
    .sidebar-link { display: flex; align-items: center; padding: 14px 20px; gap: 20px; text-decoration: none; color: #94a3b8; }
    .sidebar-link.active, .sidebar-link:hover { color: #fff; background-color: #243e7a; }
    .sidebar-link-icon { display: flex; justify-content: center; align-items: center; width: 24px; flex-shrink: 0; }

    /* --- Top Mobile Header Utility Strip --- */
    .mobile-header-bar {
        display: none; 
        background-color: #132247; 
        padding: 12px 16px;
        align-items: center; 
        color: #fff; 
        position: sticky; 
        top: 0; 
        z-index: 1050;
    }
    .mobile-left-group { display: flex; align-items: center; gap: 12px; }
    .mobile-menu-btn { background: transparent; border: none; color: white; cursor: pointer; padding: 4px; }
    .sidebar-overlay { display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.4); z-index: 1030; }

    /* --- Breakpoint Responsive Engine --- */
    @media (max-width: 992px) {
        .mobile-header-bar { display: flex; }
        .sidebar { width: 260px !important; transform: translateX(-100%); transition: transform 0.3s ease; }
        .sidebar .sidebar-brand-text, .sidebar .sidebar-link-text, .sidebar .sidebar-section-title, .sidebar .badge, .sidebar .sidebar-footer { opacity: 1 !important; pointer-events: auto !important; }
        .main-content { margin-left: 0 !important; width: 100% !important; }
        .dashboard-layout.mobile-open .sidebar { transform: translateX(0); }
        .dashboard-layout.mobile-open .sidebar-overlay { display: block; }
    }
</style>

<div class="mobile-header-bar">
    <div class="mobile-left-group">
        <button class="mobile-menu-btn" id="mobileMenuToggle" aria-label="Open Navigation">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
        <img src="/armas/assets/img/armas.jpg" alt="ARMAS" style="width: 32px; height: 32px; border-radius: 50%;">
        <span style="font-weight: bold; letter-spacing: 0.5px; font-size: 1.1rem;">ARMAS Portal</span>
    </div>
</div>

<div class="dashboard-layout" id="dashboardLayout">
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="/armas/assets/img/armas.jpg" alt="ARMAS" class="sidebar-logo">
            <div class="sidebar-brand-text">
                <span class="logo-text" style="font-weight:700; color:#fff; font-size:1.2rem;">ARMAS</span>
                <span class="brand-subtitle" style="display:block; font-size:0.75rem; color:#94a3b8;">OFW Portal</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="sidebar-section">
                <div class="sidebar-section-title" style="padding: 10px 20px; font-size:0.75rem; text-transform:uppercase; color:#64748b; font-weight:600;">Main Menu</div>
                
                <a href="dashboard.php" class="sidebar-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
                    <span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9" rx="1"></rect><rect x="14" y="3" width="7" height="5" rx="1"></rect><rect x="14" y="12" width="7" height="9" rx="1"></rect><rect x="3" y="16" width="7" height="5" rx="1"></rect></svg></span>
                    <span class="sidebar-link-text">Dashboard</span>
                </a>
                
                <a href="submit-case.php" class="sidebar-link <?php echo (basename($_SERVER['PHP_SELF']) == 'submit-case.php') ? 'active' : ''; ?>">
                    <span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg></span>
                    <span class="sidebar-link-text">Submit Report</span>
                </a>
                
                <a href="track-case.php" class="sidebar-link <?php echo (basename($_SERVER['PHP_SELF']) == 'track-case.php') ? 'active' : ''; ?>">
                    <span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg></span>
                    <span class="sidebar-link-text">Track Report</span>
                </a>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-section-title" style="padding: 10px 20px; font-size:0.75rem; text-transform:uppercase; color:#64748b; font-weight:600;">Account</div>
                
                <a href="notifications.php" class="sidebar-link <?php echo (basename($_SERVER['PHP_SELF']) == 'notifications.php') ? 'active' : ''; ?>">
                    <span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg></span>
                    <span class="sidebar-link-text">Notifications</span>
                </a>
                
                <a href="profile.php" class="sidebar-link <?php echo (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'active' : ''; ?>">
                    <span class="sidebar-link-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg></span>
                    <span class="sidebar-link-text">Profile</span>
                </a>
            </div>
        </nav>

        <div class="sidebar-footer" style="position: absolute; bottom: 20px; left: 0; width: 100%; padding: 0 15px; box-sizing: border-box;">
            <a href="/armas/pages/logout.php" class="btn btn-outline btn-sm w-100">Logout</a>
        </div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <main class="main-content">

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.getElementById('mobileMenuToggle');
            const layout = document.getElementById('dashboardLayout');
            const overlay = document.getElementById('sidebarOverlay');
            if(btn) btn.addEventListener('click', () => layout.classList.toggle('mobile-open'));
            if(overlay) overlay.addEventListener('click', () => layout.classList.remove('mobile-open'));
        });
    </script>