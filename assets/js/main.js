/**
 * ARMAS Main JavaScript
 * Shared behaviors and utilities
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // 1. AUTO-CAPS enforcement on all .input-caps fields
    document.querySelectorAll('.input-caps').forEach(field => {
        field.addEventListener('input', function() {
            const pos = this.selectionStart;
            this.value = this.value.toUpperCase();
            this.setSelectionRange(pos, pos);
        });
    });
    
    // 2. Flash message auto-dismiss after 5 seconds
    document.querySelectorAll('.flash').forEach(flash => {
        setTimeout(() => {
            flash.style.display = 'none';
        }, 5000);
        
        flash.querySelector('.flash-close')?.addEventListener('click', function() {
            flash.style.display = 'none';
        });
    });
    
    // 3. OTP input auto-advance and backspace
    document.querySelectorAll('.otp-input').forEach((box, i, boxes) => {
        box.addEventListener('input', function() {
            if (box.value && boxes[i + 1]) {
                boxes[i + 1].focus();
            }
        });
        
        box.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && !box.value && boxes[i - 1]) {
                boxes[i - 1].focus();
            }
        });
    });
    
    // 4. OTP countdown timer
    const timerEl = document.getElementById('otp-timer');
    const resendEl = document.getElementById('resend-link');
    
    if (timerEl) {
        let seconds = 600; // 10 minutes
        const interval = setInterval(() => {
            seconds--;
            const m = String(Math.floor(seconds / 60)).padStart(2, '0');
            const s = String(seconds % 60).padStart(2, '0');
            timerEl.textContent = `${m}:${s}`;
            
            if (seconds <= 0) {
                clearInterval(interval);
                if (resendEl) {
                    resendEl.classList.remove('disabled');
                }
            }
        }, 1000);
    }
    
    // 5. Sidebar active link highlight
    const currentPath = window.location.pathname;
    document.querySelectorAll('.sidebar-link').forEach(link => {
        const href = link.getAttribute('href');
        if (href && currentPath.includes(href) && href !== '/armas/pages/landing.php') {
            link.classList.add('active');
        }
    });
    
    // 6. Confirmation modal functions
    window.openModal = function(id) {
        document.getElementById(id).style.display = 'flex';
    };
    
    window.closeModal = function(id) {
        document.getElementById(id).style.display = 'none';
    };
    
    // Close modal on backdrop click
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
    });
    
    // 7. Tab switching
    window.switchTab = function(tabId) {
        document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        
        document.getElementById(tabId).classList.add('active');
        event.target.classList.add('active');
    };
    
    // 8. Password strength indicator
    const pwField = document.getElementById('password');
    const strengthBar = document.getElementById('strength-bar');
    
    if (pwField && strengthBar) {
        pwField.addEventListener('input', function() {
            const val = this.value;
            let strength = 'weak';
            
            if (val.length >= 8 && /[A-Z]/.test(val) && /[0-9]/.test(val)) {
                strength = 'strong';
            } else if (val.length >= 6) {
                strength = 'medium';
            }
            
            strengthBar.className = 'strength-bar strength-' + strength;
        });
    }
    
    // 9. Terms accordion
    document.querySelectorAll('.accordion-header').forEach(header => {
        header.addEventListener('click', function() {
            this.nextElementSibling.classList.toggle('open');
            this.classList.toggle('active');
        });
    });
    
    // 10. Mobile hamburger menu
    const hamburger = document.getElementById('hamburger');
    const navMenu = document.getElementById('nav-menu');
    
    window.toggleMobileMenu = function() {
        hamburger?.classList.toggle('open');
        navMenu?.classList.toggle('open');
    };
    
    if (hamburger) {
        hamburger.addEventListener('click', toggleMobileMenu);
    }
    
    // 11. Activate/Deactivate toggle via fetch
    window.toggleStatus = function(userId, newStatus, modalId) {
        closeModal(modalId);
        
        fetch('/armas/api/toggle-status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `user_id=${userId}&new_status=${newStatus}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    };
    
    // 12. Agency reports dropdown - reload charts
    const agencySelect = document.getElementById('agency-select');
    
    if (agencySelect) {
        agencySelect.addEventListener('change', function() {
            fetch(`/armas/api/get-reports.php?agency_id=${this.value}`)
                .then(response => response.json())
                .then(data => {
                    if (typeof updateCharts === 'function') {
                        updateCharts(data);
                    }
                })
                .catch(error => console.error('Error loading reports:', error));
        });
    }
    
    // 13. Password show/hide toggle
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.textContent = type === 'password' ? '👁' : '🙈';
        });
    });
    
    // 14. Mark notification as read
    document.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('click', function() {
            const notifId = this.dataset.id;
            if (notifId && this.classList.contains('unread')) {
                fetch('/armas/api/mark-notification-read.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `notification_id=${notifId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.classList.remove('unread');
                        this.classList.add('read');
                    }
                });
            }
        });
    });
    
    // 15. Mark all notifications as read
    const markAllReadBtn = document.getElementById('mark-all-read');
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function() {
            fetch('/armas/api/mark-all-notifications-read.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelectorAll('.notification-item.unread').forEach(item => {
                        item.classList.remove('unread');
                        item.classList.add('read');
                    });
                    const badge = document.querySelector('.notification-badge');
                    if (badge) badge.style.display = 'none';
                }
            });
        });
    }
    
    // 16. Search functionality
    const searchInput = document.getElementById('search-input');
    const searchForm = document.getElementById('search-form');
    
    if (searchInput && searchForm) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchForm.submit();
            }, 500);
        });
    }
    
    // 17. Confirm action helper
    window.confirmAction = function(message, callback) {
        const modal = document.getElementById('confirmModal');
        const msgEl = document.getElementById('confirmMessage');
        const confirmBtn = document.getElementById('confirmBtn');
        
        msgEl.textContent = message;
        
        // Remove old event listener
        const newConfirmBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
        
        newConfirmBtn.addEventListener('click', function() {
            callback();
            closeModal('confirmModal');
        });
        
        openModal('confirmModal');
    };
    
    // 18. Auto-dismiss alerts
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        });
    }, 5000);
});
