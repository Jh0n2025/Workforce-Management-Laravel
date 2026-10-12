//---------------------------- Employee Sidebar Navigation Logic ----------------------------
document.addEventListener('DOMContentLoaded', () => {
    const navButtons = document.querySelectorAll('.nav-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    const pageTitle = document.getElementById('page-title');

    // Titles map — one entry per data-target in the sidebar
    const titles = {
        'my-attendance': 'My Attendance',
        'time-attendance': 'Schedule',
        'schedule': 'My Timesheet',
        'leave': 'Leave Requests',
        'analytics': 'My Growth'
    };

    // Make switchTab available globally so it can be called from inline handlers too
    window.switchTab = function (targetId) {
        // Update nav buttons (active state)
        navButtons.forEach(btn => {
            if (btn.dataset.target === targetId) {
                btn.classList.add('bg-secondary', 'text-white', 'active');
                btn.classList.remove('text-gray-400', 'hover:bg-white/10');
            } else {
                btn.classList.remove('bg-secondary', 'text-white', 'active');
                btn.classList.add('text-gray-400', 'hover:bg-white/10');
            }
        });

        // Update visible content
        tabContents.forEach(content => {
            if (content.id === targetId) {
                content.classList.add('active');
            } else {
                content.classList.remove('active');
            }
        });

        // Update page title
        if (pageTitle && titles[targetId]) {
            pageTitle.textContent = titles[targetId];
        }

        // Lazily init the attendance trend chart the first time its tab is shown
        // (Chart.js can't size a canvas correctly while its container is display:none)
        if (targetId === 'analytics') {
            initAttendanceTrendChart();
        }

        // If the newly active tab lives inside a dropdown, open that dropdown
        // and highlight its parent toggle button.
        document.querySelectorAll('.nav-group').forEach(group => {
            const toggle = group.querySelector('.nav-dropdown-toggle');
            const menu = group.querySelector('.nav-dropdown-menu');
            const arrow = group.querySelector('.nav-dropdown-arrow');
            const hasActiveChild = !!group.querySelector(`.nav-btn[data-target="${targetId}"]`);

            if (hasActiveChild) {
                menu.classList.remove('hidden');
                arrow.classList.add('rotate-180');
                toggle.classList.add('bg-white/10', 'text-white');
            } else {
                toggle.classList.remove('bg-white/10', 'text-white');
            }
        });
    };

    navButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            switchTab(btn.dataset.target);
        });
    });

    //---------------------------- Sidebar Dropdown Toggle Logic ----------------------------
    // The "My Attendance" row itself navigates to its own tab (handled by the
    // navButtons click listener above, since it's a normal .nav-btn). The
    // chevron on the right independently expands/collapses the submenu
    // without triggering navigation.
    document.querySelectorAll('.nav-dropdown-arrow').forEach(arrow => {
        arrow.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();

            const group = arrow.closest('.nav-group');
            const menu = group.querySelector('.nav-dropdown-menu');
            const isOpen = !menu.classList.contains('hidden');

            // Close other open dropdowns for a clean accordion feel
            document.querySelectorAll('.nav-group').forEach(otherGroup => {
                if (otherGroup !== group) {
                    otherGroup.querySelector('.nav-dropdown-menu')?.classList.add('hidden');
                    otherGroup.querySelector('.nav-dropdown-arrow')?.classList.remove('rotate-180');
                }
            });

            menu.classList.toggle('hidden', isOpen);
            arrow.classList.toggle('rotate-180', !isOpen);
        });
    });

    //---------------------------- Request Leave Modal Logic ----------------------------
    const openRequestLeaveBtn = document.getElementById('open-request-leave-btn');
    const closeRequestLeaveBtn = document.getElementById('close-request-leave-btn');
    const cancelRequestLeaveBtn = document.getElementById('cancel-request-leave-btn');
    const requestLeaveModal = document.getElementById('request-leave-modal');
    const requestLeaveBackdrop = document.getElementById('request-leave-backdrop');
    const requestLeaveForm = document.getElementById('request-leave-form');

    if (openRequestLeaveBtn && requestLeaveModal) {
        const openModal = () => {
            requestLeaveModal.classList.remove('hidden');
        };

        const closeModal = () => {
            requestLeaveModal.classList.add('hidden');
            requestLeaveForm?.reset();
        };

        openRequestLeaveBtn.addEventListener('click', openModal);
        closeRequestLeaveBtn?.addEventListener('click', closeModal);
        cancelRequestLeaveBtn?.addEventListener('click', closeModal);
        requestLeaveBackdrop?.addEventListener('click', closeModal);

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !requestLeaveModal.classList.contains('hidden')) {
                closeModal();
            }
        });

        requestLeaveForm?.addEventListener('submit', (event) => {
            handleEmployeeLeaveSubmit(event);
        });
    }

    //---------------------------- Shared Data: Leave + Attendance ----------------------------
    function getEmployeeSession() {
        return window.SharedData ? SharedData.getSession() : null;
    }

    function getCurrentEmployeeRecord() {
        const session = getEmployeeSession();
        if (!session || !window.SharedData) return null;
        return SharedData.findEmployeeById(session.id) || SharedData.findEmployeeByEmail(session.email);
    }

    function getEmployeeTodayAttendance() {
        const employee = getCurrentEmployeeRecord();
        if (!employee || !window.SharedData) return null;
        const today = new Date().toISOString().slice(0, 10);
        return SharedData.getAttendance().find(
            record => record.employeeId === employee.id && record.date === today
        ) || null;
    }

    function formatMinutesToTime(totalMinutes) {
        const hour = Math.floor(totalMinutes / 60) % 24;
        const minute = totalMinutes % 60;
        const suffix = hour >= 12 ? 'PM' : 'AM';
        const displayHour = hour % 12 || 12;
        return `${String(displayHour).padStart(2, '0')}:${String(minute).padStart(2, '0')} ${suffix}`;
    }

    function nowTime24() {
        const d = new Date();
        return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
    }

    function employeeTodayRecord() {
        return getEmployeeTodayAttendance();
    }

    async function renderEmployeeAttendanceShared() {
        const statusEl = document.getElementById('employee-attendance-status');
        const timeInEl = document.getElementById('employee-time-in');
        const clockOutEl = document.getElementById('employee-clock-out-time');
        const clockOutBtn = document.getElementById('clock-out-btn');
        const clockInBtn = document.getElementById('clock-in-btn');
        const historyBody = document.getElementById('employee-attendance-history-body');

        try {
            const [todayResponse, recordsResponse] = await Promise.all([
                fetch('/api/attendance/my-clock.php', {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' },
                    cache: 'no-store'
                }),
                fetch('/api/attendance/my-records.php', {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' },
                    cache: 'no-store'
                })
            ]);

            const todayResult = await todayResponse.json();
            const recordsResult = await recordsResponse.json();

            if (!todayResponse.ok || !todayResult.success) {
                throw new Error(todayResult.message || 'Unable to load today attendance.');
            }
            if (!recordsResponse.ok || !recordsResult.success) {
                throw new Error(recordsResult.message || 'Unable to load attendance history.');
            }

            const attendance = todayResult.data?.attendance || null;
            const schedule = todayResult.data?.schedule || null;
            const hasClockedIn = !!attendance?.time_in;
            const hasClockedOut = !!attendance?.time_out;
            const status = String(attendance?.status || '').toLowerCase();

            let statusLabel = 'Not Clocked In';
            if (status === 'present') statusLabel = 'Present';
            else if (status === 'late') statusLabel = 'Late';
            else if (status === 'on_leave') statusLabel = 'On Leave';
            else if (status === 'absent') statusLabel = 'Absent';

            if (statusEl) {
                const dot = status === 'present' ? 'bg-success'
                    : status === 'late' ? 'bg-amber-500'
                    : status === 'on_leave' ? 'bg-purple-500'
                    : status === 'absent' ? 'bg-red-500'
                    : 'bg-gray-400';
                statusEl.innerHTML = `<span class="h-2 w-2 rounded-full ${dot}"></span>${escapeHtmlEmployee(statusLabel)}`;
            }
            if (timeInEl) timeInEl.textContent = hasClockedIn ? formatAttendanceDbTime(attendance.time_in) : '--:--';
            if (clockOutEl) clockOutEl.textContent = schedule?.end_time ? formatAttendanceDbTime(schedule.end_time) : '--:--';
            if (clockInBtn) clockInBtn.classList.toggle('hidden', hasClockedIn);
            if (clockOutBtn) clockOutBtn.classList.toggle('hidden', !hasClockedIn || hasClockedOut);

            if (historyBody) {
                const rows = Array.isArray(recordsResult.records) ? recordsResult.records : [];
                historyBody.innerHTML = rows.length ? rows.map(record => `
                    <tr class="hover:bg-background transition-colors">
                        <td class="px-6 py-3.5 text-primary font-medium">${escapeHtmlEmployee(record.attendance_date || '')}</td>
                        <td class="px-6 py-3.5 text-gray-500">${escapeHtmlEmployee(formatAttendanceDbTime(record.time_in))}</td>
                        <td class="px-6 py-3.5 text-gray-400">${escapeHtmlEmployee(formatAttendanceDbTime(record.time_out))}</td>
                        <td class="px-6 py-3.5">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium ${employeeStatusClass(record.status)}">
                                <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                ${escapeHtmlEmployee(String(record.status || 'Unknown').replace('_', ' '))}
                            </span>
                        </td>
                    </tr>
                `).join('') : `
                    <tr><td colspan="4" class="px-6 py-8 text-center text-gray-400">No attendance records yet.</td></tr>`;
            }
        } catch (error) {
            console.error('Employee database attendance loading error:', error);
            if (historyBody) {
                historyBody.innerHTML = '<tr><td colspan="4" class="px-6 py-8 text-center text-red-500">Unable to load attendance from the server.</td></tr>';
            }
            if (statusEl) statusEl.textContent = 'Unable to load attendance';
        }
    }

    function formatAttendanceDbTime(value) {
        if (!value) return '--:--';
        const match = String(value).match(/(?:T|\s|^)(\d{2}):(\d{2})/);
        if (!match) return String(value);
        const hour = Number(match[1]);
        const minute = match[2];
        const suffix = hour >= 12 ? 'PM' : 'AM';
        const displayHour = hour % 12 || 12;
        return `${String(displayHour).padStart(2, '0')}:${minute} ${suffix}`;
    }

    function escapeHtmlEmployee(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function employeeStatusClass(status) {
        switch (String(status || '').toLowerCase()) {
            case 'present': return 'bg-green-100 text-green-700';
            case 'late': return 'bg-amber-100 text-amber-700';
            case 'on leave':
            case 'leave': return 'bg-purple-100 text-purple-700';
            case 'absent': return 'bg-red-100 text-red-700';
            default: return 'bg-gray-100 text-gray-600';
        }
    }

    async function clockInEmployee() {
        const button = document.getElementById('clock-in-btn');
        if (button) button.disabled = true;
        try {
            const response = await fetch('/api/attendance/my-clock.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ action: 'time_in' })
            });
            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Unable to clock in.');
            }
            showToastEmployee(result.message, result.status === 'late' ? 'info' : 'success');
            await renderEmployeeAttendanceShared();
        } catch (error) {
            showToastEmployee(error.message || 'Unable to clock in.', 'error');
        } finally {
            if (button) button.disabled = false;
        }
    }

    async function clockOutEmployee() {
        const button = document.getElementById('clock-out-btn');
        if (button) button.disabled = true;
        try {
            const response = await fetch('/api/attendance/my-clock.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ action: 'time_out' })
            });
            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Unable to clock out.');
            }
            showToastEmployee(result.message, 'success');
            await renderEmployeeAttendanceShared();
        } catch (error) {
            showToastEmployee(error.message || 'Unable to clock out.', 'error');
        } finally {
            if (button) button.disabled = false;
        }
    }

    async function handleEmployeeLeaveSubmit(event) {
        event.preventDefault();

        const leaveType = document.getElementById('leave-type').value;
        const startDate = document.getElementById('leave-start').value;
        const endDate = document.getElementById('leave-end').value;
        const reason = document.getElementById('leave-reason').value;

        try {
            const response = await fetch('api/leave/create.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    leave_type: leaveType,
                    start_date: startDate,
                    end_date: endDate,
                    reason: reason
                })
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                showToastEmployee(data.message || 'Unable to submit leave request.', 'error');
                return;
            }

            showToastEmployee('Leave request submitted successfully.', 'success');

            const form = event.target;
            if (form) form.reset();

        } catch (error) {
            console.error('Leave request error:', error);
            showToastEmployee('Unable to connect to the server.', 'error');
        }
    }

    function renderEmployeeLeaveRequests() {
        if (!window.SharedData) return;
        const employee = getCurrentEmployeeRecord();
        const body = document.getElementById('employee-leave-requests-body');
        if (!employee || !body) return;

        const rows = SharedData.getLeaveRequests()
            .filter(leave => leave.employeeId === employee.id)
            .sort((a, b) => Number(b.id) - Number(a.id));

        body.innerHTML = rows.length ? rows.map(leave => `
            <tr class="hover:bg-background transition-colors">
                <td class="px-6 py-3.5 text-primary font-medium">${escapeHtmlEmployee(leave.type)}</td>
                <td class="px-6 py-3.5 text-gray-500">${escapeHtmlEmployee(leave.dates)}</td>
                <td class="px-6 py-3.5 text-gray-500">—</td>
                <td class="px-6 py-3.5">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium ${leaveStatusClassEmployee(leave.status)}">
                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                        ${escapeHtmlEmployee(leave.status)}
                    </span>
                </td>
            </tr>
        `).join('') : `
            <tr><td colspan="4" class="px-6 py-8 text-center text-gray-400">No leave requests yet.</td></tr>`;
    }

    function leaveStatusClassEmployee(status) {
        switch (String(status || '').toLowerCase()) {
            case 'approved': return 'bg-green-100 text-green-700';
            case 'pending': return 'bg-amber-100 text-amber-700';
            case 'declined':
            case 'rejected': return 'bg-red-100 text-red-700';
            default: return 'bg-gray-100 text-gray-600';
        }
    }

    function showToastEmployee(message, type = 'info') {
        let toast = document.getElementById('employee-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'employee-toast';
            toast.className = 'fixed bottom-4 right-4 z-[80] rounded-xl border border-border bg-card px-4 py-3 text-sm shadow-lg text-primary';
            document.body.appendChild(toast);
        }
        toast.textContent = message;
        clearTimeout(showToastEmployee._timer);
        showToastEmployee._timer = setTimeout(() => toast.remove(), 2600);
    }

    const clockInBtn = document.getElementById('clock-in-btn');
    const clockOutBtn = document.getElementById('clock-out-btn');
    clockInBtn?.addEventListener('click', clockInEmployee);
    clockOutBtn?.addEventListener('click', clockOutEmployee);

    window.addEventListener('storage', (event) => {
        if (event.key === 'ht_v1_attendanceRecords' || event.key === 'ht_v1_leaveRequests') {
            renderEmployeeAttendanceShared();
            renderEmployeeLeaveRequests();
        }
    });

    renderEmployeeAttendanceShared();
    renderEmployeeLeaveRequests();

    //---------------------------- My Attendance Trend Chart ----------------------------
    let attendanceTrendChart = null;

    function initAttendanceTrendChart() {
        const canvas = document.getElementById('attendance-trend-chart');
        if (!canvas || typeof Chart === 'undefined' || attendanceTrendChart) return;

        attendanceTrendChart = new Chart(canvas, {
            type: 'line',
            data: {
                labels: ['W1', 'W2', 'W3', 'W4'],
                datasets: [{
                    label: 'Attendance Rate',
                    data: [95, 100, 98, 96],
                    borderColor: '#6FA9E6',
                    backgroundColor: 'rgba(111, 169, 230, 0.15)',
                    borderWidth: 2,
                    stepped: true,
                    fill: true,
                    tension: 0,
                    pointRadius: 4,
                    pointBackgroundColor: '#6FA9E6',
                    pointBorderColor: '#FFFFFF',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (context) => `${context.parsed.y}%`
                        }
                    }
                },
                scales: {
                    y: {
                        min: 90,
                        max: 100,
                        ticks: {
                            stepSize: 5,
                            callback: (value) => `${value}%`,
                            color: '#94A3B8'
                        },
                        grid: { color: '#E5E7EB' }
                    },
                    x: {
                        ticks: { color: '#94A3B8' },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    //---------------------------- Employee Dropdown & Search Logic ----------------------------
    const notificationBtn = document.getElementById('notification-btn');
    const notificationMenu = document.getElementById('notification-menu');
    const profileBtn = document.getElementById('profile-btn');
    const profileMenu = document.getElementById('profile-menu');
    const searchInput = document.getElementById('searchInput');
    const suggestionsBox = document.getElementById('suggestionsBox');
    const searchContainer = document.getElementById('searchContainer');

    if (notificationBtn && notificationMenu) {
        notificationBtn.addEventListener('click', (event) => {
            event.stopPropagation();

            // Only one dropdown may be open at a time.
            // Close the profile menu before toggling notifications.
            if (profileMenu) profileMenu.classList.add('hidden');

            notificationMenu.classList.toggle('hidden');
        });

        notificationMenu.addEventListener('click', (event) => {
            event.stopPropagation();
        });

        document.addEventListener('click', () => {
            notificationMenu.classList.add('hidden');
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                notificationMenu.classList.add('hidden');
            }
        });

        const markAllBtn = notificationMenu.querySelector('button');
        const unreadBadge = notificationBtn.querySelector('span');
        const unreadItems = notificationMenu.querySelectorAll('.bg-blue-50\\/50');

        markAllBtn?.addEventListener('click', (event) => {
            event.stopPropagation();
            if (unreadBadge) unreadBadge.classList.add('hidden');
            unreadItems.forEach(item => {
                item.classList.remove('bg-blue-50/50');
                const blueDot = item.querySelector('.bg-blue-600');
                if (blueDot) blueDot.remove();
            });
        });
    }

    if (profileBtn && profileMenu) {
        profileBtn.addEventListener('click', (event) => {
            event.stopPropagation();

            // Only one dropdown may be open at a time.
            // Close the notification menu before toggling the profile menu.
            if (notificationMenu) notificationMenu.classList.add('hidden');

            profileMenu.classList.toggle('hidden');
        });

        profileMenu.addEventListener('click', (event) => {
            event.stopPropagation();
        });

        document.addEventListener('click', () => {
            profileMenu.classList.add('hidden');
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                profileMenu.classList.add('hidden');
            }
        });
    }

    if (searchInput && suggestionsBox && searchContainer) {
        const searchableItems = [
            "Alice Smith (Staff)",
            "David Johnson (Staff)",
            "Sarah Williams (Staff)",
            "Grand Canyon Adventure Tour",
            "City Highlights Walking Tour",
            "Sunset Cruise Tour",
            "Museum Pass & Audio Guide"
        ];

        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            suggestionsBox.innerHTML = '';

            if (query.length > 0) {
                const filteredData = searchableItems.filter(item =>
                    item.toLowerCase().includes(query)
                );

                if (filteredData.length > 0) {
                    filteredData.forEach(item => {
                        const li = document.createElement('li');
                        li.textContent = item;
                        li.className = "px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-accent cursor-pointer transition-colors";

                        li.addEventListener('click', () => {
                            searchInput.value = item;
                            suggestionsBox.classList.add('hidden');
                        });

                        suggestionsBox.appendChild(li);
                    });
                    suggestionsBox.classList.remove('hidden');
                } else {
                    suggestionsBox.classList.add('hidden');
                }
            } else {
                suggestionsBox.classList.add('hidden');
            }
        });

        document.addEventListener('click', (event) => {
            if (!searchContainer.contains(event.target)) {
                suggestionsBox.classList.add('hidden');
            }
        });

        searchInput.addEventListener('focus', () => {
            if (searchInput.value.trim().length > 0 && suggestionsBox.children.length > 0) {
                suggestionsBox.classList.remove('hidden');
            }
        });
    }

    // "My Growth" (analytics) is now the default tab shown on page load,
    // so initialize its chart right away instead of waiting for a tab switch.
    initAttendanceTrendChart();
});

//---------------------------- Logout Loading Animation ----------------------------
// Same modal-style loading overlay used on the login/logout flow elsewhere
// in the app, reproduced here since this page loads its own employee.js
// rather than the shared script.js.
function showLoadingOverlay(message = 'Loading...') {
    let overlay = document.getElementById('loading-overlay');

    if (!overlay) {
        if (!document.getElementById('loading-overlay-styles')) {
            const style = document.createElement('style');
            style.id = 'loading-overlay-styles';
            style.textContent = `
                @keyframes loadingOverlayFadeIn {
                    from { opacity: 0; transform: translateY(5px) scale(0.98); }
                    to { opacity: 1; transform: translateY(0) scale(1); }
                }
            `;
            document.head.appendChild(style);
        }

        overlay = document.createElement('div');
        overlay.id = 'loading-overlay';
        overlay.className = 'fixed inset-0 z-[70] flex items-center justify-center bg-primary/40 backdrop-blur-sm transition-opacity duration-200';
        overlay.innerHTML = `
            <div class="bg-card rounded-2xl shadow-xl border border-border px-8 py-7 mx-4 max-w-xs w-full flex flex-col items-center gap-4 text-center" style="animation: loadingOverlayFadeIn 0.25s ease-in-out;">
                <div class="w-10 h-10 border-4 border-accent border-t-transparent rounded-full animate-spin"></div>
                <p id="loading-overlay-text" class="text-sm font-medium text-primary"></p>
            </div>
        `;
        document.body.appendChild(overlay);
    }

    const text = document.getElementById('loading-overlay-text');
    if (text) text.textContent = message;
    overlay.classList.remove('hidden');
}

function hideLoadingOverlay() {
    const overlay = document.getElementById('loading-overlay');
    if (overlay) overlay.classList.add('hidden');
}

function handleEmployeeLogout(event) {
    event.preventDefault();
    showLoadingOverlay('Logging Out in Progress...');
    if (window.SharedData) SharedData.clearSession();
    sessionStorage.clear();

    fetch('api/auth/logout.php', {
        method: 'POST',
        credentials: 'same-origin'
    }).catch(() => { });

    setTimeout(() => {
        window.location.href = 'login.html';
    }, 700);
}
window.handleEmployeeLogout = handleEmployeeLogout;
