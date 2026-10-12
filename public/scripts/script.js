

function logout() {
    const passInput = document.getElementById('login-password');
    const btnText = document.getElementById('login-btn-text');
    const spinner = document.getElementById('login-spinner');

    // Show the modal loading overlay right away so the user gets
    // immediate feedback that logout is underway.
    showLoadingOverlay('Logging Out in Progress...');

    if (window.SharedData) SharedData.clearSession();
    sessionStorage.clear();

    fetch('api/auth/logout.php', {
        method: 'POST',
        credentials: 'same-origin'
    }).catch(() => { });

    if (passInput) passInput.value = "";
    if (btnText) btnText.textContent = "Authenticate Credentials";
    if (spinner) spinner.classList.add('hidden');

    // Give the overlay a moment to be visible before navigating away.
    setTimeout(() => {
        const appWorkspace = document.getElementById('app-workspace');
        const loginScreen = document.getElementById('login-screen');
        if (appWorkspace) appWorkspace.classList.add('hidden');
        if (loginScreen) loginScreen.classList.remove('hidden');
        window.location.href = 'login.html';
    }, 700);
}



//-----------------------------------V1 Functions-----------------------------------------------
function initializeApplication() {
    if (window.lucide && typeof lucide.createIcons === 'function') {
        lucide.createIcons();
    }
}

function showToast(message, type = 'info') {
    let toast = document.getElementById('toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'toast';
        toast.className = 'fixed bottom-4 right-4 z-[60] rounded-xl border border-border bg-card px-4 py-2 text-sm shadow-lg text-primary';
        document.body.appendChild(toast);
    }
    toast.textContent = message;
    toast.className = 'fixed bottom-4 right-4 z-[60] rounded-xl border border-border bg-card px-4 py-2 text-sm shadow-lg text-primary';
    clearTimeout(showToast.timeout);
    showToast.timeout = setTimeout(() => {
        if (toast && toast.parentNode) {
            toast.remove();
        }
    }, 2200);
}

// --- Modal-style loading overlay (used for login / logout progress) ---
// Built dynamically, the same way showToast() is, so it works on any
// page that loads this script without needing extra markup.
function showLoadingOverlay(message = 'Loading...') {
    let overlay = document.getElementById('loading-overlay');

    if (!overlay) {
        // Inject the fade-in keyframes ourselves so this overlay looks
        // right on any page that loads script.js, even if that page's
        // own <style> block doesn't already define @keyframes fadeIn.
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
window.showLoadingOverlay = showLoadingOverlay;
window.hideLoadingOverlay = hideLoadingOverlay;

// App now loads straight into the workspace (no login gate)
initializeApplication();



// --- Navigation Logic ---
const navButtons = document.querySelectorAll('.nav-btn');
const tabContents = document.querySelectorAll('.tab-content');
const pageTitle = document.getElementById('page-title');
const mobileMenuBtn = document.getElementById('mobile-menu-btn');
const mobileMenu = document.getElementById('mobile-menu');

// Titles map
const titles = {
    'dashboard': 'Admin Overview',
    'employees': 'Employee Directory',
    'time-attendance': 'Live Attendance Monitoring',
    'schedule': 'Master Roster & Schedule',
    'timesheet': 'Timesheet Approvals',
    'leave': 'Leave Requests',
    'analytics': 'Workforce Analytics',
    'kiosk-log': 'RFID Log Entry'
};

// Make switchTab available globally so inline onclick works
window.switchTab = function (targetId) {
    // Update buttons
    navButtons.forEach(btn => {
        if (btn.dataset.target === targetId) {
            btn.classList.add('bg-secondary', 'text-white', 'active');
            btn.classList.remove('text-gray-400', 'hover:bg-white/10');
            // Inline-style fallback so the orange always renders,
            // even if the Tailwind CDN hasn't generated .bg-secondary yet
            btn.style.backgroundColor = '#F59B45';
            btn.style.color = '#FFFFFF';
        } else {
            btn.classList.remove('bg-secondary', 'text-white', 'active');
            btn.classList.add('text-gray-400', 'hover:bg-white/10');
            btn.style.backgroundColor = '';
            btn.style.color = '';
        }
    });

    // Update content
    tabContents.forEach(content => {
        if (content.id === targetId) {
            content.classList.add('active');
        } else {
            content.classList.remove('active');
        }
    });

    // Update Title
    pageTitle.textContent = titles[targetId];

    if (targetId === 'leave') {
        renderLeaves();
    }

    if (targetId === 'kiosk-log') {
        loadKioskLogEntries();
    }

    if (targetId === 'dashboard') {
        loadCriticalAlerts();
    }
    // Close mobile menu if open
    mobileMenu.classList.add('hidden');

    // Re-render charts if analytics tab is opened
    if (targetId === 'analytics') {
        setTimeout(() => {
            renderAdminCharts();
            loadAdminWorkforceAnalytics();
        }, 100);
    }
};

navButtons.forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        switchTab(btn.dataset.target);
    });
});

// Legacy mobile-menu wiring is disabled on the dedicated mobile admin view.
// admin-mobile.js owns the drawer/navigation there. This prevents the shared
// desktop script from trying to find a <nav> that does not exist inside the
// new mobile drawer and throwing a runtime error before chart setup completes.
if (!document.body.hasAttribute('data-mobile-admin-view')) {
    // Copy desktop nav to the legacy mobile menu
    if (mobileMenu && mobileMenu.querySelector('nav')) {
        const desktopNav = document.querySelector('aside nav').innerHTML;
        mobileMenu.querySelector('nav').innerHTML = desktopNav;

        // Re-attach listeners to legacy mobile nav items
        mobileMenu.querySelectorAll('.nav-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                switchTab(btn.dataset.target);
            });
        });
    }

    // Legacy mobile menu toggle
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    }
}

// --- Admin Charts Implementation using Chart.js ---
let adminChartsRendered = false;

async function loadAdminWorkforceAnalytics() {

    try {

        const response =
            await fetch(
                'api/analytics/admin.php',
                {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        'Accept':
                            'application/json'
                    },
                    cache: 'no-store'
                }
            );

        const result =
            await response.json();

        if (
            !response.ok ||
            !result.success
        ) {
            throw new Error(
                result.message ||
                'Unable to load workforce analytics.'
            );
        }

        const data =
            result.data || {};

        const setText = (
            id,
            value
        ) => {

            const element =
                document.getElementById(id);

            if (element) {
                element.textContent =
                    String(value);
            }
        };

        setText(
            'admin-analytics-overtime',
            `${Number(
                data.overtime_hours || 0
            ).toFixed(2)} hrs`
        );

        const absenceRate =
            (
                Number(
                    data.absent_records || 0
                ) +
                Number(
                    data.approved_leave_days || 0
                )
            );

        const attendanceBase =
            Number(
                data.attended_records || 0
            ) +
            Number(
                data.absent_records || 0
            );

        const calculatedAbsence =
            attendanceBase > 0
                ? (
                    Number(
                        data.absent_records || 0
                    ) /
                    attendanceBase
                ) * 100
                : 0;

        setText(
            'admin-analytics-absence',
            `${calculatedAbsence.toFixed(1)}%`
        );

        setText(
            'admin-analytics-fulfillment',
            `${Number(
                data.shift_fulfillment || 0
            ).toFixed(1)}%`
        );

        const departmentContainer =
            document.getElementById(
                'admin-analytics-departments'
            );

        if (
            !departmentContainer
        ) {
            return;
        }

        const departments =
            Array.isArray(
                data.departments
            )
                ? data.departments
                : [];

        departmentContainer.innerHTML = '';

        if (!departments.length) {

            departmentContainer.innerHTML = `
                <div class="col-span-full text-center text-gray-400 py-6">
                    No active employee departments found.
                </div>
            `;

            return;
        }

        departments.forEach(
            department => {

                const card =
                    document.createElement(
                        'div'
                    );

                card.className =
                    'border border-border rounded-xl p-4 bg-background';

                card.innerHTML = `
                    <p class="text-sm font-semibold text-primary">
                        ${escapeHtml(
                    department.department
                )}
                    </p>

                    <p class="text-2xl font-bold text-primary mt-2">
                        ${Number(
                    department.employees || 0
                )}
                    </p>

                    <p class="text-xs text-gray-400 mt-1">
                        active employee${Number(
                    department.employees || 0
                ) === 1 ? '' : 's'}
                    </p>
                `;

                departmentContainer.appendChild(
                    card
                );
            }
        );

    } catch (error) {

        console.error(
            'Admin workforce analytics error:',
            error
        );
    }
}

// ============================================================
// ADMIN OVERVIEW: CRITICAL ALERTS
// ============================================================

function escapeCriticalAlertText(value) {

    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

}


function formatCriticalAlertDate(dateValue) {

    if (!dateValue) {
        return '';
    }

    const parts =
        String(dateValue)
            .split('-')
            .map(Number);

    if (parts.length !== 3) {
        return String(dateValue);
    }

    const date =
        new Date(
            parts[0],
            parts[1] - 1,
            parts[2]
        );

    if (Number.isNaN(date.getTime())) {
        return String(dateValue);
    }

    return date.toLocaleDateString(
        'en-US',
        {
            month: 'long',
            day: 'numeric',
            year: 'numeric'
        }
    );
}


function formatCriticalAlertTime(timeValue) {

    if (!timeValue) {
        return '';
    }

    const parts =
        String(timeValue)
            .split(':')
            .map(Number);

    if (
        parts.length < 2 ||
        Number.isNaN(parts[0]) ||
        Number.isNaN(parts[1])
    ) {
        return String(timeValue);
    }

    let hours = parts[0];

    const minutes = parts[1];

    const period =
        hours >= 12
            ? 'PM'
            : 'AM';

    hours =
        hours % 12;

    if (hours === 0) {
        hours = 12;
    }

    return `${hours}:${String(minutes).padStart(2, '0')} ${period}`;
}


async function loadCriticalAlerts() {

    const container =
        document.getElementById(
            'critical-alerts-container'
        );

    if (!container) {
        return;
    }


    try {

        const response =
            await fetch(
                'api/overview/critical-alerts.php',
                {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json'
                    },
                    cache: 'no-store'
                }
            );


        const result =
            await response.json();


        if (
            !response.ok ||
            !result.success
        ) {

            throw new Error(
                result.message ||
                'Unable to load critical alerts.'
            );
        }


        const alerts =
            Array.isArray(result.data)
                ? result.data
                : [];


        container.innerHTML = '';


        if (!alerts.length) {

            container.innerHTML = `
                <div
                    class="p-3 bg-background rounded-2xl border border-border text-center">

                    <p class="text-xs text-gray-400">
                        No manager sick call-outs requiring replacement.
                    </p>

                </div>
            `;

            return;
        }


        alerts.forEach(alert => {

            const employeeName =
                escapeCriticalAlertText(
                    alert.employee_name ||
                    'Manager'
                );


            const assignment =
                escapeCriticalAlertText(
                    alert.remarks ||
                    alert.work_type ||
                    'scheduled duty'
                );


            const date =
                formatCriticalAlertDate(
                    alert.schedule_date
                );


            const time =
                formatCriticalAlertTime(
                    alert.start_time
                );


            const status =
                String(
                    alert.leave_status || ''
                ).toLowerCase();


            const statusText =
                status === 'approved'
                    ? 'Sick leave approved.'
                    : 'Sick leave filed.';


            const card =
                document.createElement(
                    'div'
                );


            card.className =
                'flex items-start space-x-3 p-3 bg-red-50 rounded-2xl border border-red-100';


            card.innerHTML = `
                <i
                    class="fa-solid fa-user-nurse text-red-500 mt-1">
                </i>

                <div class="flex-1">

                    <p class="text-sm font-bold text-red-900">
                        Sick Call-out
                    </p>

                    <p class="text-xs text-red-700 mt-1">
                        ${employeeName} filed a sick leave.
                        ${statusText}
                        He/She is assigned to
                        "${assignment}"
                        on ${date}${time ? ` at ${time}` : ''}.
                    </p>

                    <button
                        type="button"
                        onclick="openCriticalReplacement(${Number(alert.schedule_id)})"
                        class="font-button mt-2 text-xs font-semibold bg-red-100 text-red-800 px-2 py-1 rounded hover:bg-red-200">

                        Find Replacement

                    </button>

                </div>
            `;


            container.appendChild(
                card
            );

        });


    } catch (error) {

        console.error(
            'Critical alerts loading error:',
            error
        );


        container.innerHTML = `
            <div
                class="p-3 bg-red-50 rounded-2xl border border-red-100">

                <p class="text-xs text-red-700">
                    Unable to load critical alerts.
                </p>

            </div>
        `;
    }
}


function openCriticalReplacement(
    scheduleId
) {

    const id =
        Number(scheduleId);


    if (
        !id ||
        !Number.isFinite(id)
    ) {
        return;
    }


    /*
     * Feature 1:
     * Open the existing Re-assign Schedule modal
     * for this exact schedule.
     */
    if (
        typeof window.openReassignScheduleModal ===
        'function'
    ) {

        window.openReassignScheduleModal(
            id
        );

        return;
    }


    console.error(
        'openReassignScheduleModal() is not available.'
    );

}

async function renderAdminCharts() {
    if (adminChartsRendered) return;

    const costCanvas = document.getElementById('costChart');
    const utilCanvas = document.getElementById('utilizationChart');

    if (!costCanvas || !utilCanvas || typeof Chart === 'undefined') {
        return;
    }

    try {
        const response = await fetch('/api/analytics/admin.php', {
            method: 'GET',
            credentials: 'same-origin',
            headers: { Accept: 'application/json' },
            cache: 'no-store'
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(
                result.message || 'Unable to load workforce charts.'
            );
        }

        const data = result.data || {};

        const attended = Number(data.attended_records || 0);
        const late = Number(data.late_records || 0);
        const absent = Number(data.absent_records || 0);

        // Chart 1: Actual attendance records in the reporting period
        new Chart(costCanvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Present', 'Late', 'Absent'],
                datasets: [{
                    label: 'Attendance Records',
                    data: [
                        Math.max(0, attended - late),
                        late,
                        absent
                    ],
                    backgroundColor: [
                        '#10b981',
                        '#f59e0b',
                        '#ef4444'
                    ],
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });

        // Chart 2: Actual active employees by department
        const departments = Array.isArray(data.departments)
            ? data.departments
            : [];

        new Chart(utilCanvas.getContext('2d'), {
            type: 'polarArea',
            data: {
                labels: departments.map(item => item.department),
                datasets: [{
                    label: 'Active Employees',
                    data: departments.map(
                        item => Number(item.employees || 0)
                    ),
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.7)',
                        'rgba(245, 158, 11, 0.7)',
                        'rgba(16, 185, 129, 0.7)',
                        'rgba(139, 92, 246, 0.7)',
                        'rgba(100, 116, 139, 0.7)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                },
                plugins: {
                    legend: { position: 'right' }
                }
            }
        });

        adminChartsRendered = true;

    } catch (error) {
        console.error('Admin workforce charts error:', error);
    }
}

// --- Leave Registry ---
let leaveRequests = window.SharedData ? SharedData.getLeaveRequests() : [];

let leaveEmployeeOptions = [];
const leaveAvatarColors = ['#EF4444', '#6FA9E6', '#F59B45', '#8B5CF6', '#10B981', '#EC4899'];

async function populateLeaveEmployeeSelect() {

    const select =
        document.getElementById(
            'leave-form-emp'
        );

    if (!select) {
        return;
    }

    try {

        const response = await fetch(
            'api/employees/roster.php',
            {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json'
                },
                cache: 'no-store'
            }
        );

        const result =
            await response.json();

        if (
            !response.ok ||
            !result.success
        ) {
            throw new Error(
                result.message ||
                'Unable to load employees.'
            );
        }

        const employees =
            Array.isArray(result.data)
                ? result.data.filter(employee =>
                    ['employee', 'manager']
                        .includes(
                            String(
                                employee.accountRole || ''
                            ).toLowerCase()
                        )
                )
                : [];

        leaveEmployeeOptions =
            employees;

        select.innerHTML = '';

        employees.forEach(employee => {

            const option =
                document.createElement(
                    'option'
                );

            option.value =
                employee.id;

            option.textContent =
                `${employee.name} — ${employee.role}`;

            select.appendChild(option);
        });

        if (!employees.length) {

            const option =
                document.createElement(
                    'option'
                );

            option.value = '';
            option.textContent =
                'No employees available';

            select.appendChild(option);
        }

    } catch (error) {

        console.error(
            'Leave applicant loading error:',
            error
        );

        select.innerHTML = '';

        const option =
            document.createElement(
                'option'
            );

        option.value = '';
        option.textContent =
            'Unable to load employees';

        select.appendChild(option);
    }
}

function leaveStatusBadgeClasses(status) {
    switch (status) {
        case 'Approved': return 'bg-emerald-100 text-emerald-600';
        case 'Pending': return 'bg-amber-100 text-amber-600';
        case 'Declined': return 'bg-red-100 text-red-600';
        default: return 'bg-gray-100 text-gray-500';
    }
}

async function renderLeaves() {

    const container =
        document.getElementById('leaves-grid-container');

    if (!container) return;

    try {

        const response = await fetch(
            'api/leave/list.php',
            {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json'
                },
                cache: 'no-store'
            }
        );

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(
                result.message ||
                'Unable to load leave requests.'
            );
        }

        container.innerHTML = '';

        result.data.forEach(leave => {

            const initials =
                leave.name
                    .split(/\s+/)
                    .map(part => part[0])
                    .join('')
                    .substring(0, 2)
                    .toUpperCase();

            let badgeClass =
                'bg-gray-100 text-gray-500';

            if (leave.status === 'Pending') {
                badgeClass =
                    'bg-amber-100 text-amber-700';
            }

            if (leave.status === 'Approved') {
                badgeClass =
                    'bg-emerald-100 text-emerald-700';
            }

            if (leave.status === 'Declined') {
                badgeClass =
                    'bg-red-100 text-red-600';
            }

            const card =
                document.createElement('div');

            card.className =
                'bg-card rounded-2xl border border-border shadow-sm p-5 flex flex-col';

            card.dataset.leaveId = leave.id;

            card.innerHTML = `
                <div class="flex items-start justify-between mb-4">

                    <div class="flex items-center gap-3">

                        <div
    class="w-10 h-10 rounded-full overflow-hidden flex items-center justify-center bg-accent/15 flex-shrink-0">

    ${leave.avatar_url
                    ? `
                <img
                    src="${escapeHtml(leave.avatar_url)}"
                    alt="${escapeHtml(leave.name)} profile photo"
                    class="w-full h-full object-cover"
                    onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');">

                <span
                    class="hidden w-full h-full items-center justify-center bg-[#6FA9E6] text-white font-bold text-sm">
                    ${escapeHtml(initials)}
                </span>
              `
                    : `
                <span
                    class="w-full h-full flex items-center justify-center bg-[#6FA9E6] text-white font-bold text-sm">
                    ${escapeHtml(initials)}
                </span>
              `
                }

</div>

                        <div>

                            <p class="font-heading font-bold text-primary leading-tight">
                                ${leave.name}
                            </p>

                            <span class="inline-block mt-1 px-2 py-0.5 bg-gray-100 text-gray-500 text-[10px] font-bold uppercase tracking-wider rounded">
                                ${leave.type}
                            </span>

                        </div>

                    </div>

                    <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider whitespace-nowrap ${badgeClass}">
                        ${leave.status}
                    </span>

                </div>

                <div class="flex items-center gap-2 text-xs text-gray-500 mb-3">

                    <i
                        data-lucide="calendar"
                        class="w-3.5 h-3.5 text-accent">
                    </i>

                    <span>
                        Requested dates: ${leave.dates}
                    </span>

                </div>

                <div class="bg-background border border-border rounded-lg p-3 text-xs text-gray-500 italic">
                    "${leave.reason}"
                </div>

                ${leave.status === 'Pending'
                    ? `
                    <div class="mt-4 pt-4 border-t border-border flex gap-2">

                        <button
                            onclick="approveLeave(${leave.id})"
                            class="flex-1 py-2 bg-success hover:bg-emerald-600 text-white font-button font-medium text-sm rounded-xl transition-all-300">

                            Approve

                        </button>

                        <button
                            onclick="declineLeave(${leave.id})"
                            class="flex-1 py-2 border border-error text-error hover:bg-error/10 font-button font-medium text-sm rounded-xl transition-all-300">

                            Decline

                        </button>

                    </div>
                    `
                    : ''
                }
            `;

            container.appendChild(card);
        });

        if (
            window.lucide &&
            typeof lucide.createIcons === 'function'
        ) {
            lucide.createIcons();
        }

    } catch (error) {

        console.error(
            'Leave loading error:',
            error
        );

        container.innerHTML = `
            <div class="col-span-full text-center text-red-500 py-8">
                Unable to load leave requests.
            </div>
        `;
    }
}

async function approveLeave(id) {

    try {

        const response = await fetch(
            'api/leave/update-status.php',
            {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    id: id,
                    action: 'approve'
                })
            }
        );

        const result =
            await response.json();

        if (!response.ok || !result.success) {
            throw new Error(
                result.message ||
                'Unable to approve leave request.'
            );
        }

        await renderLeaves();

    } catch (error) {

        console.error(error);

        showToast(
            error.message,
            'error'
        );
    }
}

async function declineLeave(id) {

    try {

        const response = await fetch(
            'api/leave/update-status.php',
            {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    id: id,
                    action: 'decline'
                })
            }
        );

        const result =
            await response.json();

        if (!response.ok || !result.success) {
            throw new Error(
                result.message ||
                'Unable to decline leave request.'
            );
        }

        await renderLeaves();

    } catch (error) {

        console.error(error);

        showToast(
            error.message,
            'error'
        );
    }
}

function updateLeaveReasonAvailability() {

    const typeSelect =
        document.getElementById(
            'leave-form-type'
        );

    const reason =
        document.getElementById(
            'leave-form-reason'
        );

    const reasonWrap =
        document.getElementById(
            'leave-form-reason-wrap'
        );

    const disabledMessage =
        document.getElementById(
            'leave-form-reason-disabled-message'
        );


    if (
        !typeSelect ||
        !reason ||
        !reasonWrap
    ) {
        return;
    }


    const type =
        typeSelect.value;


    const noReasonRequired =
        type === 'Maternity Leave' ||
        type === 'Paternity Leave';


    if (noReasonRequired) {

        reason.value = '';

        reason.disabled = true;

        reason.removeAttribute(
            'required'
        );

        reason.classList.add(
            'bg-gray-100',
            'text-gray-400',
            'cursor-not-allowed'
        );

        if (disabledMessage) {
            disabledMessage.classList.remove(
                'hidden'
            );
        }

    } else {

        reason.disabled = false;

        reason.setAttribute(
            'required',
            ''
        );

        reason.classList.remove(
            'bg-gray-100',
            'text-gray-400',
            'cursor-not-allowed'
        );

        if (disabledMessage) {
            disabledMessage.classList.add(
                'hidden'
            );
        }
    }
}

async function openRequestLeaveModal() {

    await populateLeaveEmployeeSelect();

    const modal =
        document.getElementById(
            'modal-request-leave'
        );

    if (modal) {

        modal.classList.remove(
            'hidden'
        );

        updateLeaveReasonAvailability();
    }
}

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const leaveType =
            document.getElementById(
                'leave-form-type'
            );

        if (leaveType) {

            leaveType.addEventListener(
                'change',
                updateLeaveReasonAvailability
            );

            updateLeaveReasonAvailability();
        }

    }
);

function closeRequestLeaveModal() {
    const modal = document.getElementById('modal-request-leave');
    if (!modal) return;
    modal.classList.add('hidden');
    const form = modal.querySelector('form');
    if (form) form.reset();
}

async function handleRequestLeaveSubmit(event) {

    event.preventDefault();


    const employeeId =
        document.getElementById(
            'leave-form-emp'
        ).value;

    const type =
        document.getElementById(
            'leave-form-type'
        ).value;

    const startDate =
        document.getElementById(
            'leave-form-start-date'
        ).value;

    const endDate =
        document.getElementById(
            'leave-form-end-date'
        ).value;

    const reasonField =
        document.getElementById(
            'leave-form-reason'
        );


    const reason =
        reasonField.disabled
            ? ''
            : reasonField.value.trim();


    /*
     * Basic required-field validation.
     */
    if (
        !employeeId ||
        !type ||
        !startDate ||
        !endDate
    ) {

        showToast(
            'Please complete all leave request fields.',
            'error'
        );

        return;
    }


    /*
     * Make sure the ending date is not
     * earlier than the starting date.
     */
    if (endDate < startDate) {

        showToast(
            'The To date cannot be earlier than the From date.',
            'error'
        );

        return;
    }


    /*
     * Reason is required for every leave category
     * except Maternity Leave and Paternity Leave.
     */
    const noReasonRequired =
        type === 'Maternity Leave' ||
        type === 'Paternity Leave';


    if (
        !noReasonRequired &&
        !reason
    ) {

        showToast(
            'Please enter a reason or note.',
            'error'
        );

        return;
    }


    try {

        const response =
            await fetch(
                'api/leave/admin-create.php',
                {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type':
                            'application/json',
                        'Accept':
                            'application/json'
                    },
                    body: JSON.stringify({

                        employee_id:
                            employeeId,

                        leave_type:
                            type,

                        start_date:
                            startDate,

                        end_date:
                            endDate,

                        reason:
                            reason

                    })
                }
            );


        const result =
            await response.json();


        if (
            !response.ok ||
            !result.success
        ) {

            throw new Error(
                result.message ||
                'Unable to file leave request.'
            );
        }


        await renderLeaves();


        closeRequestLeaveModal();


        showToast(
            'Leave request filed successfully.',
            'success'
        );


    } catch (error) {

        console.error(
            'Admin leave request error:',
            error
        );


        showToast(
            error.message ||
            'Unable to file leave request.',
            'error'
        );
    }
}

// Make modal handlers available globally for inline onclick/onsubmit attributes
window.openRequestLeaveModal = openRequestLeaveModal;
window.closeRequestLeaveModal = closeRequestLeaveModal;
window.handleRequestLeaveSubmit = handleRequestLeaveSubmit;
window.updateLeaveReasonAvailability = updateLeaveReasonAvailability;
window.approveLeave = approveLeave;
window.declineLeave = declineLeave;

window.addEventListener('storage', (event) => {
    if (event.key === 'ht_v1_leaveRequests') renderLeaves();
});

// Initialize on load, same as the rest of this file
populateLeaveEmployeeSelect();
renderLeaves();

let assignManagerEmployees = [];
let assignManagerManagers = [];

async function openAssignManagerModal() {

    const modal =
        document.getElementById(
            'modal-assign-manager'
        );

    if (!modal) {
        return;
    }

    const employeeSelect =
        document.getElementById(
            'assign-manager-employee'
        );

    const managerSelect =
        document.getElementById(
            'assign-manager-manager'
        );

    const message =
        document.getElementById(
            'assign-manager-message'
        );

    try {

        const response =
            await fetch(
                'api/employees/assign-manager.php',
                {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        'Accept':
                            'application/json'
                    },
                    cache: 'no-store'
                }
            );

        const result =
            await response.json();

        if (
            !response.ok ||
            !result.success
        ) {
            throw new Error(
                result.message ||
                'Unable to load employees and managers.'
            );
        }

        assignManagerEmployees =
            Array.isArray(
                result.employees
            )
                ? result.employees
                : [];

        assignManagerManagers =
            Array.isArray(
                result.managers
            )
                ? result.managers
                : [];

        employeeSelect.innerHTML =
            '<option value="">Select employee</option>';

        assignManagerEmployees.forEach(
            employee => {

                const option =
                    document.createElement(
                        'option'
                    );

                option.value =
                    employee.employee_id;

                option.textContent =
                    employee.employee_name;

                employeeSelect.appendChild(
                    option
                );
            }
        );

        managerSelect.innerHTML =
            '<option value="">Select manager</option>';

        assignManagerManagers.forEach(
            manager => {

                const option =
                    document.createElement(
                        'option'
                    );

                option.value =
                    manager.manager_id;

                option.textContent =
                    `${manager.manager_name} — ${manager.employee_id}`;

                managerSelect.appendChild(
                    option
                );
            }
        );

        message.classList.add(
            'hidden'
        );

        modal.classList.remove(
            'hidden'
        );

        if (window.lucide) {
            lucide.createIcons();
        }

    } catch (error) {

        console.error(
            'Assign manager load error:',
            error
        );

        message.textContent =
            error.message ||
            'Unable to load employees and managers.';

        message.className =
            'text-sm rounded-xl px-4 py-3 bg-red-50 text-red-600';

        modal.classList.remove(
            'hidden'
        );
    }
}

function closeAssignManagerModal() {

    const modal =
        document.getElementById(
            'modal-assign-manager'
        );

    if (modal) {
        modal.classList.add(
            'hidden'
        );
    }
}

async function submitAssignManagerForm(
    event
) {

    event.preventDefault();

    const employeeId =
        document.getElementById(
            'assign-manager-employee'
        )?.value || '';

    const managerId =
        document.getElementById(
            'assign-manager-manager'
        )?.value || '';

    const message =
        document.getElementById(
            'assign-manager-message'
        );

    if (!employeeId || !managerId) {

        message.textContent =
            'Please select both an employee and a manager.';

        message.className =
            'text-sm rounded-xl px-4 py-3 bg-red-50 text-red-600';

        return;
    }

    const submitButton =
        document.getElementById(
            'assign-manager-submit'
        );

    if (submitButton) {
        submitButton.disabled = true;
        submitButton.textContent =
            'Assigning...';
    }

    try {

        const response =
            await fetch(
                'api/employees/assign-manager.php',
                {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type':
                            'application/json',
                        'Accept':
                            'application/json'
                    },
                    body: JSON.stringify({
                        employee_id:
                            employeeId,

                        manager_id:
                            Number(managerId)
                    })
                }
            );

        const result =
            await response.json();

        if (
            !response.ok ||
            !result.success
        ) {
            throw new Error(
                result.message ||
                'Unable to assign employee.'
            );
        }

        closeAssignManagerModal();

        if (
            typeof showToast ===
            'function'
        ) {
            showToast(
                result.message,
                'success'
            );
        }

        /*
         * Refresh the Employees table.
         */
        if (
            typeof loadEmployeesFromDatabase ===
            'function'
        ) {
            await loadEmployeesFromDatabase();
        }

    } catch (error) {

        console.error(
            'Assign manager error:',
            error
        );

        message.textContent =
            error.message ||
            'Unable to assign employee.';

        message.className =
            'text-sm rounded-xl px-4 py-3 bg-red-50 text-red-600';

    } finally {

        if (submitButton) {
            submitButton.disabled = false;
            submitButton.textContent =
                'Assign Employee';
        }
    }
}

document.addEventListener(
    'DOMContentLoaded',
    function () {

        document
            .getElementById(
                'assign-manager-form'
            )
            ?.addEventListener(
                'submit',
                submitAssignManagerForm
            );

    }
);

async function loadKioskLogEntries() {

    const tableBody =
        document.getElementById(
            'kiosk-log-table-body'
        );

    if (!tableBody) {
        return;
    }

    try {

        const response =
            await fetch(
                'api/kiosk/logs.php',
                {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        'Accept':
                            'application/json'
                    },
                    cache: 'no-store'
                }
            );

        const result =
            await response.json();

        if (
            !response.ok ||
            !result.success
        ) {
            throw new Error(
                result.message ||
                'Unable to load kiosk logs.'
            );
        }

        const logs =
            Array.isArray(result.data)
                ? result.data
                : [];

        tableBody.innerHTML = '';

        if (!logs.length) {

            tableBody.innerHTML = `
                <tr>
                    <td
                        colspan="7"
                        class="px-5 py-8 text-center text-gray-400">
                        No RFID log entries found.
                    </td>
                </tr>
            `;

            return;
        }

        logs.forEach(log => {

            const row =
                document.createElement(
                    'tr'
                );

            const statusClass =
                log.scanStatus === 'APPROVED'
                    ? 'bg-emerald-100 text-emerald-700'
                    : 'bg-red-100 text-red-600';

            const scannedAt =
                new Date(
                    String(
                        log.scannedAt
                    ).replace(
                        ' ',
                        'T'
                    )
                );

            const scannedText =
                Number.isNaN(
                    scannedAt.getTime()
                )
                    ? log.scannedAt
                    : scannedAt.toLocaleString(
                        'en-US',
                        {
                            month: 'short',
                            day: 'numeric',
                            year: 'numeric',
                            hour: 'numeric',
                            minute: '2-digit',
                            second: '2-digit'
                        }
                    );

            row.innerHTML = `
                <td class="px-5 py-4 font-medium text-primary">
                    #${Number(log.id)}
                </td>

                <td class="px-5 py-4 font-semibold text-primary">
                    ${escapeHtml(log.employee)}
                </td>

                <td class="px-5 py-4 text-gray-600">
                    ${escapeHtml(log.employeeId)}
                </td>

                <td class="px-5 py-4 text-gray-600 font-mono text-xs">
                    ${escapeHtml(log.rfidUid)}
                </td>

                <td class="px-5 py-4 text-gray-600">
                    ${escapeHtml(log.kioskName)}
                </td>

                <td class="px-5 py-4">
                    <span
                        class="inline-flex items-center ${statusClass}
                        text-xs font-semibold px-3 py-1 rounded-full">
                        ${escapeHtml(log.scanStatus)}
                    </span>
                </td>

                <td class="px-5 py-4 text-gray-600">
                    ${escapeHtml(scannedText)}
                </td>
            `;

            tableBody.appendChild(
                row
            );
        });

    } catch (error) {

        console.error(
            'Kiosk log loading error:',
            error
        );

        tableBody.innerHTML = `
            <tr>
                <td
                    colspan="7"
                    class="px-5 py-8 text-center text-red-500">
                    Unable to load RFID log entries.
                </td>
            </tr>
        `;
    }
}

document.addEventListener(
    'DOMContentLoaded',
    function () {

        document
            .getElementById(
                'refresh-kiosk-log-btn'
            )
            ?.addEventListener(
                'click',
                loadKioskLogEntries
            );

    }
);

loadCriticalAlerts();