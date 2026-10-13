//---------------------------- Employee Sidebar Navigation Logic ----------------------------
document.addEventListener('DOMContentLoaded', () => {
    const navButtons = document.querySelectorAll('.nav-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    const pageTitle = document.getElementById('page-title');

    // Titles map — one entry per data-target in the sidebar
    const titles = {
        'dashboard': 'Attendance',
        'time-attendance': 'Team Schedule',
        'schedule': 'Timesheets',
        'leave': 'Leave Management',
        'analytics': 'Workforce Analytics'
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
        if (targetId === 'leave') {
            loadMyLeaveRequests();
            renderManagerLeaves();
        }
    };

    navButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            switchTab(btn.dataset.target);
        });
    });

    //---------------------------- Employee Dropdown & Search Logic ----------------------------
    const notificationBtn = document.getElementById('notification-btn');
    const notificationMenu = document.getElementById('notification-menu');
    const profileBtn = document.getElementById('profile-btn');
    const profileMenu = document.getElementById('profile-menu');
    const searchInput = document.getElementById('searchInput');
    const suggestionsBox = document.getElementById('suggestionsBox');
    const searchContainer = document.getElementById('searchContainer');


    if (profileBtn && profileMenu) {
        profileBtn.addEventListener('click', (event) => {
            event.stopPropagation();
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
});

//---------------------------- Logout Loading Animation ----------------------------
// Same modal-style loading overlay used elsewhere in the app (login/employee
// logout), reproduced here since this page loads its own manager.js.
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

function handleManagerLogout(event) {
    event.preventDefault();
    showLoadingOverlay('Logging Out in Progress...');
    if (window.SharedData) SharedData.clearSession();
    sessionStorage.clear();

    fetch('/api/auth/logout.php', {
        method: 'POST',
        credentials: 'same-origin'
    }).catch(() => { });

    setTimeout(() => {
        window.location.replace('/login');
    }, 700);
}
window.handleManagerLogout = handleManagerLogout;

// ============================================================================
// Manager data + rendering logic
// ============================================================================

let managerTeamChart = null;

function managerSession() {
    return window.SharedData ? SharedData.getSession() : null;
}

function getManagerTeam() {
    // Laravel's authenticated session is the source of truth. Do not require
    // a separate sessionStorage entry, which may be absent after Laravel login.
    try {
        const xhr = new XMLHttpRequest();

        xhr.open(
            'GET',
            '/api/employees/manager-team.php',
            false
        );

        xhr.setRequestHeader(
            'Accept',
            'application/json'
        );

        xhr.send(null);

        if (
            xhr.status < 200 ||
            xhr.status >= 300
        ) {
            return [];
        }

        const result =
            JSON.parse(xhr.responseText || '{}');

        if (
            !result.success ||
            !Array.isArray(result.data)
        ) {
            return [];
        }

        return result.data;

    } catch (error) {

        console.error(
            'Manager team loading error:',
            error
        );

        return [];
    }
}

function getTeamAttendanceForToday() {
    const teamIds = new Set(getManagerTeam().map(emp => emp.id));
    const today = new Date().toISOString().slice(0, 10);
    return SharedData.getAttendance().filter(
        record => record.date === today && teamIds.has(record.employeeId)
    );
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function statusBadge(status) {
    const normalized = String(status || '').toLowerCase();

    const classes = {
        'present': 'bg-emerald-100 text-emerald-800',
        'late': 'bg-amber-100 text-amber-800',
        'absent': 'bg-rose-100 text-rose-800',
        'on leave': 'bg-purple-100 text-purple-800',
        'leave': 'bg-purple-100 text-purple-800',
        'off': 'bg-slate-100 text-slate-500'
    };

    return classes[normalized] || 'bg-slate-100 text-slate-600';
}

async function renderManagerAttendance() {
    const body = document.getElementById('manager-attendance-body');
    if (!body) return;

    try {
        const response = await fetch(
            'api/attendance/team-today.php',
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
                result.message || 'Unable to load team attendance.'
            );
        }

        const records = result.data || [];

        const counts = {
            present: records.filter(
                r => String(r.status).toLowerCase() === 'present'
            ).length,

            late: records.filter(
                r => String(r.status).toLowerCase() === 'late'
            ).length,

            absent: records.filter(
                r => String(r.status).toLowerCase() === 'absent'
            ).length,

            leave: records.filter(
                r => ['leave', 'on leave'].includes(
                    String(r.status).toLowerCase()
                )
            ).length
        };

        document
            .getElementById('present-count')
            ?.replaceChildren(
                document.createTextNode(String(counts.present))
            );

        document
            .getElementById('late-count')
            ?.replaceChildren(
                document.createTextNode(String(counts.late))
            );

        document
            .getElementById('absent-count')
            ?.replaceChildren(
                document.createTextNode(String(counts.absent))
            );

        document
            .getElementById('leave-count')
            ?.replaceChildren(
                document.createTextNode(String(counts.leave))
            );

        document
            .getElementById('team-count')
            ?.replaceChildren(
                document.createTextNode(`/ ${records.length}`)
            );

        if (!records.length) {
            body.innerHTML = `
                <tr>
                    <td colspan="5"
                        class="py-8 text-center text-slate-400">
                        No employees are assigned to this manager.
                    </td>
                </tr>
            `;
            return;
        }

        body.innerHTML = records.map(record => `
            <tr class="hover:bg-slate-50/80 transition-colors">
                <td class="py-3.5 pr-6 font-medium text-slate-900">
                    ${escapeHtml(record.name)}
                    <span class="block text-[11px] text-slate-400">
                        ${escapeHtml(record.position)}
                    </span>
                </td>

                <td class="py-3.5 pr-6 text-slate-700">
                    ${escapeHtml(record.schedule)}
                </td>

                <td class="py-3.5 pr-6 text-slate-700">
                    ${escapeHtml(record.clockIn)}
                </td>

                <td class="py-3.5 pr-6 text-slate-700">
                    ${escapeHtml(record.clockOut)}
                </td>

                <td class="py-3.5">
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusBadge(record.status.toLowerCase())}">
                        ${escapeHtml(record.status)}
                    </span>
                </td>
            </tr>
        `).join('');

    } catch (error) {
        console.error(
            'Manager attendance loading error:',
            error
        );

        body.innerHTML = `
            <tr>
                <td colspan="5"
                    class="py-8 text-center text-red-500">
                    Unable to load team attendance.
                </td>
            </tr>
        `;
    }
}

function scheduleCell(value) {
    const normalized = String(value || '').toUpperCase();

    if (['OFF', 'ABSENT', 'LEAVE'].includes(normalized)) {
        const cls = {
            OFF: 'bg-slate-100 text-slate-500',
            ABSENT: 'bg-red-100 text-red-800',
            LEAVE: 'bg-purple-100 text-purple-800'
        }[normalized];

        return `<span class="inline-block px-2 py-0.5 rounded text-xs font-medium ${cls}">${normalized}</span>`;
    }

    return escapeHtml(value || '—');
}

function managerWeekStartDate(value) {
    const text = String(value || '')
        .trim()
        .replace(/\u2013|\u2014/g, '-');

    const monthNames = [
        'January',
        'February',
        'March',
        'April',
        'May',
        'June',
        'July',
        'August',
        'September',
        'October',
        'November',
        'December'
    ];

    const getMonthIndex = name =>
        monthNames.findIndex(month =>
            month.toLowerCase().startsWith(
                String(name || '').toLowerCase()
            )
        );

    /*
     * Supports:
     * Sep 21-27, 2026
     * September 21-27, 2026
     * September 28-October 4, 2026
     * Sep 28-Oct 4, 2026
     */
    let match = text.match(
        /^([A-Za-z]{3,9})\s+(\d{1,2})\s*-\s*(?:([A-Za-z]{3,9})\s+)?(\d{1,2}),\s*(\d{4})$/
    );

    if (!match) {
        match = text.match(
            /^([A-Za-z]{3,9})\s+(\d{1,2}),\s*(\d{4})$/
        );

        if (match) {
            const monthIndex = getMonthIndex(match[1]);

            if (monthIndex < 0) {
                return '';
            }

            const year = Number(match[3]);
            const day = Number(match[2]);

            const date = new Date(
                year,
                monthIndex,
                day
            );

            if (Number.isNaN(date.getTime())) {
                return '';
            }

            const weekday = date.getDay();

            const diff =
                weekday === 0
                    ? -6
                    : 1 - weekday;

            date.setDate(
                date.getDate() + diff
            );

            return [
                date.getFullYear(),
                String(
                    date.getMonth() + 1
                ).padStart(2, '0'),
                String(
                    date.getDate()
                ).padStart(2, '0')
            ].join('-');
        }

        return '';
    }

    const startMonthIndex =
        getMonthIndex(match[1]);

    if (startMonthIndex < 0) {
        return '';
    }

    const year = Number(match[5]);
    const startDay = Number(match[2]);

    const date = new Date(
        year,
        startMonthIndex,
        startDay
    );

    if (Number.isNaN(date.getTime())) {
        return '';
    }

    const weekday = date.getDay();

    const diff =
        weekday === 0
            ? -6
            : 1 - weekday;

    date.setDate(
        date.getDate() + diff
    );

    return [
        date.getFullYear(),
        String(
            date.getMonth() + 1
        ).padStart(2, '0'),
        String(
            date.getDate()
        ).padStart(2, '0')
    ].join('-');
}

async function renderManagerSchedules(weekLabel = '') {
    const body =
        document.getElementById(
            'manager-schedule-body'
        );

    if (!body) {
        return;
    }

    let weekStart = '';

    if (weekLabel) {
        weekStart =
            managerWeekStartDate(weekLabel);
    }

    try {

        const url =
            weekStart
                ? `api/schedules/manager.php?week_start=${encodeURIComponent(weekStart)}`
                : 'api/schedules/manager.php';

        const response = await fetch(
            url,
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
                'Unable to load team schedule.'
            );
        }

        const schedules =
            Array.isArray(result.data)
                ? result.data
                : [];

        const weekLabelEl =
            document.getElementById(
                'schedule-week-label'
            );

        if (
            weekLabelEl &&
            result.week_label
        ) {
            weekLabelEl.textContent =
                result.week_label;
        }

        if (!schedules.length) {

            body.innerHTML = `
                <tr>
                    <td colspan="6"
                        class="py-8 text-center text-slate-400">
                        No schedules have been assigned to your team yet.
                    </td>
                </tr>
            `;

            return;
        }

        body.innerHTML =
            schedules.map(employee => `
                <tr class="hover:bg-slate-50/80 transition-colors">

                    <td class="py-3.5 pr-6 font-medium text-slate-900">
                        ${escapeHtml(employee.name)}

                        <span class="block text-[11px] text-slate-400">
                            ${escapeHtml(employee.position)}
                        </span>
                    </td>

                    <td class="py-3.5 px-4 text-center">
                        ${scheduleCell(employee.mon)}
                    </td>

                    <td class="py-3.5 px-4 text-center">
                        ${scheduleCell(employee.tue)}
                    </td>

                    <td class="py-3.5 px-4 text-center">
                        ${scheduleCell(employee.wed)}
                    </td>

                    <td class="py-3.5 px-4 text-center">
                        ${scheduleCell(employee.thu)}
                    </td>

                    <td class="py-3.5 px-4 text-center">
                        ${scheduleCell(employee.fri)}
                    </td>

                </tr>
            `).join('');

    } catch (error) {

        console.error(
            'Manager schedule loading error:',
            error
        );

        body.innerHTML = `
            <tr>
                <td colspan="6"
                    class="py-8 text-center text-red-500">
                    Unable to load team schedule.
                </td>
            </tr>
        `;
    }
}

function openManagerLeaveRequestModal() {

    const modal =
        document.getElementById(
            'manager-leave-request-modal'
        );

    if (modal) {
        modal.classList.remove(
            'hidden'
        );
    }
}

function closeManagerLeaveRequestModal() {

    const modal =
        document.getElementById(
            'manager-leave-request-modal'
        );

    if (modal) {
        modal.classList.add(
            'hidden'
        );
    }
}

async function submitManagerLeaveRequest(
    event
) {

    event.preventDefault();

    const leaveType =
        document.getElementById(
            'manager-leave-type'
        )?.value || '';

    const startDate =
        document.getElementById(
            'manager-leave-start'
        )?.value || '';

    const endDate =
        document.getElementById(
            'manager-leave-end'
        )?.value || '';

    const reason =
        document.getElementById(
            'manager-leave-reason'
        )?.value.trim() || '';

    if (
        !leaveType ||
        !startDate ||
        !endDate ||
        !reason
    ) {
        showToast(
            'Please complete all leave request fields.',
            'info'
        );

        return;
    }

    if (endDate < startDate) {
        showToast(
            'End date cannot be before start date.',
            'info'
        );

        return;
    }

    try {

        const response =
            await fetch(
                'api/leave/create.php',
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
                        leave_type:
                            leaveType,

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
                'Unable to submit leave request.'
            );
        }

        document
            .getElementById(
                'manager-leave-request-form'
            )
            ?.reset();

        closeManagerLeaveRequestModal();

        showToast(
            'Leave request submitted successfully. Admin approval is required.',
            'success'
        );

        await loadMyLeaveRequests();
        await renderManagerLeaves();

    } catch (error) {

        console.error(
            'Manager leave request error:',
            error
        );

        showToast(
            error.message ||
            'Unable to submit leave request.',
            'info'
        );
    }
}

async function renderManagerLeaves() {
    const container =
        document.getElementById('manager-leaves-grid');

    const pendingBadge =
        document.getElementById('pending-leave-count');

    if (!container) return;

    try {
        const response = await fetch(
            'api/leave/team-list.php',
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
                'Unable to load team leave requests.'
            );
        }

        const leaves = result.data || [];

        const pending = leaves.filter(
            leave =>
                String(leave.status).toLowerCase() ===
                'pending'
        ).length;

        if (pendingBadge) {
            pendingBadge.textContent =
                `${pending} pending`;
        }

        if (!leaves.length) {
            container.innerHTML = `
                <div class="md:col-span-2 xl:col-span-3 bg-white border border-slate-200 rounded-[16px] p-8 text-center text-slate-400">
                    No leave requests for your team.
                </div>
            `;
            return;
        }

        container.innerHTML = leaves.map(leave => {

            const status =
                String(leave.status || '');

            return `
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 flex flex-col"
                     data-leave-id="${Number(leave.id)}">

                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm flex-shrink-0"
                                 style="background-color:#163B6D">
                                ${escapeHtml(
                String(leave.name || '')
                    .split(/\s+/)
                    .map(part => part.charAt(0))
                    .slice(0, 2)
                    .join('')
                    .toUpperCase()
            )}
                            </div>

                            <div>
                                <p class="font-bold text-[#0c2340] leading-tight">
                                    ${escapeHtml(leave.name)}
                                </p>

                                <span class="inline-block mt-1 px-2 py-0.5 bg-gray-100 text-gray-500 text-[10px] font-bold uppercase tracking-wider rounded">
                                    ${escapeHtml(leave.type)}
                                </span>
                            </div>
                        </div>

                        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider whitespace-nowrap ${statusBadge(status)}">
                            ${escapeHtml(status)}
                        </span>
                    </div>

                    <div class="flex items-center gap-2 text-xs text-gray-500 mb-3">
                        <i class="fa-solid fa-calendar text-[#6FA9E6]"></i>
                        <span>
                            Requested dates:
                            ${escapeHtml(leave.dates)}
                        </span>
                    </div>

                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 text-xs text-gray-500 italic">
                        "${escapeHtml(leave.reason)}"
                    </div>

                    ${status.toLowerCase() === 'pending'
                    ? `
                        <div class="mt-4 pt-4 border-t border-slate-100 flex gap-2">

                            <button
                                onclick="approveManagerLeave(${Number(leave.id)})"
                                class="flex-1 py-2 bg-emerald-500 hover:bg-emerald-600 text-white font-medium text-sm rounded-xl transition">
                                Approve
                            </button>

                            <button
                                onclick="declineManagerLeave(${Number(leave.id)})"
                                class="flex-1 py-2 border border-red-500 text-red-500 hover:bg-red-50 font-medium text-sm rounded-xl transition">
                                Decline
                            </button>

                        </div>
                        `
                    : ''
                }

                </div>
            `;
        }).join('');

    } catch (error) {

        console.error(
            'Manager leave loading error:',
            error
        );

        if (pendingBadge) {
            pendingBadge.textContent = '0 pending';
        }

        container.innerHTML = `
            <div class="md:col-span-2 xl:col-span-3 bg-white border border-red-200 rounded-[16px] p-8 text-center text-red-500">
                Unable to load leave requests.
            </div>
        `;
    }
}

async function loadMyLeaveRequests() {

    const container =
        document.getElementById(
            'manager-my-leave-grid'
        );

    const countBadge =
        document.getElementById(
            'manager-my-leave-count'
        );

    if (!container) {
        return;
    }

    try {

        const response = await fetch(
            'api/leave/my-requests.php',
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
                'Unable to load your leave requests.'
            );
        }

        const requests =
            Array.isArray(result.requests)
                ? result.requests
                : [];

        if (countBadge) {
            countBadge.textContent =
                `${requests.length} request${requests.length === 1 ? '' : 's'
                }`;
        }

        if (!requests.length) {

            container.innerHTML = `
                <div class="md:col-span-2 xl:col-span-3
                    bg-background border border-border
                    rounded-xl p-8 text-center text-gray-400">
                    You have no leave requests.
                </div>
            `;

            return;
        }

        container.innerHTML =
            requests.map(request => {

                const rawStatus =
                    String(
                        request.status || ''
                    ).toLowerCase();

                let statusLabel =
                    'Pending';

                let statusClass =
                    'bg-amber-100 text-amber-700';

                if (
                    rawStatus === 'approved'
                ) {
                    statusLabel =
                        'Approved';

                    statusClass =
                        'bg-emerald-100 text-emerald-700';
                }

                if (
                    rawStatus === 'rejected' ||
                    rawStatus === 'declined'
                ) {
                    statusLabel =
                        'Declined';

                    statusClass =
                        'bg-red-100 text-red-600';
                }

                if (
                    rawStatus === 'cancelled'
                ) {
                    statusLabel =
                        'Cancelled';

                    statusClass =
                        'bg-gray-100 text-gray-600';
                }

                const startDate =
                    request.start_date
                        ? new Date(
                            request.start_date +
                            'T00:00:00'
                        ).toLocaleDateString(
                            'en-US',
                            {
                                month: 'short',
                                day: 'numeric',
                                year: 'numeric'
                            }
                        )
                        : '—';

                const endDate =
                    request.end_date
                        ? new Date(
                            request.end_date +
                            'T00:00:00'
                        ).toLocaleDateString(
                            'en-US',
                            {
                                month: 'short',
                                day: 'numeric',
                                year: 'numeric'
                            }
                        )
                        : '—';

                return `
                    <div
                        class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">

                        <div class="flex items-start justify-between gap-3 mb-4">

                            <div>
                                <p class="font-bold text-[#0c2340]">
                                    ${escapeHtml(
                    request.leave_type ||
                    'Leave Request'
                )}
                                </p>

                                <p class="text-xs text-gray-400 mt-1">
                                    My Leave
                                </p>
                            </div>

                            <span
                                class="px-3 py-1 rounded-full text-[10px]
                                font-bold uppercase tracking-wider
                                whitespace-nowrap ${statusClass}">
                                ${statusLabel}
                            </span>

                        </div>

                        <div class="flex items-center gap-2
                            text-xs text-gray-500 mb-3">

                            <i class="fa-solid fa-calendar text-[#6FA9E6]"></i>

                            <span>
                                ${escapeHtml(startDate)}
                                -
                                ${escapeHtml(endDate)}
                            </span>

                        </div>

                        <div
                            class="bg-slate-50 border border-slate-200
                            rounded-lg p-3 text-xs text-gray-500">

                            <span class="font-semibold text-gray-600">
                                Reason:
                            </span>

                            ${escapeHtml(
                    request.reason || '—'
                )}

                        </div>

                    </div>
                `;
            }).join('');

    } catch (error) {

        console.error(
            'My leave loading error:',
            error
        );

        if (countBadge) {
            countBadge.textContent =
                'Unable to load';
        }

        container.innerHTML = `
            <div class="md:col-span-2 xl:col-span-3
                bg-white border border-red-200
                rounded-[16px] p-8 text-center text-red-500">
                Unable to load your leave requests.
            </div>
        `;
    }
}



async function approveManagerLeave(id) {
    try {
        const response = await fetch(
            'api/leave/manager-update-status.php',
            {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    id: Number(id),
                    action: 'approve'
                })
            }
        );

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(
                result.message ||
                'Unable to approve leave request.'
            );
        }

        await renderManagerLeaves();
        showToast(
            'Leave request approved',
            'success'
        );

    } catch (error) {
        console.error(
            'Approve manager leave error:',
            error
        );

        showToast(
            error.message ||
            'Unable to approve leave request.',
            'info'
        );
    }
}

async function declineManagerLeave(id) {
    try {
        const response = await fetch(
            'api/leave/manager-update-status.php',
            {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    id: Number(id),
                    action: 'decline'
                })
            }
        );

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(
                result.message ||
                'Unable to decline leave request.'
            );
        }

        await renderManagerLeaves();

        showToast(
            'Leave request declined',
            'info'
        );

    } catch (error) {
        console.error(
            'Decline manager leave error:',
            error
        );

        showToast(
            error.message ||
            'Unable to decline leave request.',
            'info'
        );
    }
}

// ---------------------------- Schedule Actions ----------------------------

function teamEmployeeChoices() {
    return getManagerTeam().map(emp => `${emp.id} — ${emp.name}`);
}

function parseEmployeeChoice(value) {
    const id = String(value || '')
        .split(' — ')[0]
        .trim();

    return getManagerTeam().find(
        employee => String(employee.id) === id
    ) || null;
}

// ---------------------------- Team Schedule Helpers ----------------------------

const MANAGER_SCHEDULE_DAYS = [
    ['mon', 'Monday'],
    ['tue', 'Tuesday'],
    ['wed', 'Wednesday'],
    ['thu', 'Thursday'],
    ['fri', 'Friday']
];

const MANAGER_SHIFT_PRESETS = ['8-5', '9-6', '10-7', 'OFF', 'LEAVE', 'ABSENT'];

function managerCurrentScheduleWeek() {
    const label = document
        .getElementById('schedule-week-label')
        ?.textContent
        ?.trim();

    if (
        label &&
        label !== 'Team week' &&
        label !== 'No schedule week'
    ) {
        return label;
    }

    const today = new Date();
    const day = today.getDay();

    const monday = new Date(today);
    monday.setDate(
        today.getDate() - ((day + 6) % 7)
    );

    const sunday = new Date(monday);
    sunday.setDate(
        monday.getDate() + 6
    );

    const formatDate = date =>
        date.toLocaleDateString(
            'en-US',
            {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            }
        );

    const mondayText = formatDate(monday);
    const sundayText = formatDate(sunday);

    return `${mondayText}–${sundayText}`;
}

function managerEscapeAttr(value) {
    return escapeHtml(value).replace(/`/g, '&#096;');
}

function managerShiftOptions(selected = '8-5', includeCustom = true) {
    const values = Array.from(new Set([
        ...MANAGER_SHIFT_PRESETS,
        ...(selected && !MANAGER_SHIFT_PRESETS.includes(String(selected).toUpperCase()) ? [String(selected)] : [])
    ]));

    return values.map(value => `
        <option value="${managerEscapeAttr(value)}" ${String(value) === String(selected) ? 'selected' : ''}>
            ${escapeHtml(value)}
        </option>`).join('') + (includeCustom ? '<option value="__custom__">Custom shift...</option>' : '');
}

function managerNormalizeShift(value) {
    return String(value || '').trim();
}



function managerRequestEmployee(request) {
    const team = getManagerTeam();
    const employeeId = String(
        request.employeeId ?? request.employee_id ?? request.staffId ?? request.employee_id_ref ?? ''
    ).trim();

    return team.find(emp => String(emp.id) === employeeId) || null;
}

function managerRequestDetails(request) {
    let day = String(
        request.day ?? request.requestedDay ?? request.shiftDay ?? request.shift_date_day ?? ''
    ).trim().toLowerCase();

    if (!MANAGER_SCHEDULE_DAYS.some(([key]) => key === day)) {
        const dateCandidate = request.date ?? request.requestedDate ?? request.shiftDate ?? request.shift_date;
        if (dateCandidate) {
            const parsed = new Date(dateCandidate);
            if (!Number.isNaN(parsed.getTime())) {
                day = MANAGER_SCHEDULE_DAYS[(parsed.getDay() + 6) % 7]?.[0] || '';
            }
        }
    }

    const requestedShift = managerNormalizeShift(
        request.requestedShift ?? request.newShift ?? request.shift ?? request.toShift ?? request.requested ?? ''
    );
    const currentShift = managerNormalizeShift(
        request.currentShift ?? request.current ?? request.fromShift ?? ''
    );
    const week = managerNormalizeShift(request.week ?? request.scheduleWeek ?? '') || managerCurrentScheduleWeek();
    const reason = managerNormalizeShift(request.reason ?? request.message ?? request.notes ?? request.request ?? 'Shift change requested.');

    return { day, requestedShift, currentShift, week, reason };
}

function ensureManagerScheduleActionModal() {
    let modal = document.getElementById('manager-schedule-action-modal');
    if (modal) return modal;

    modal = document.createElement('div');
    modal.id = 'manager-schedule-action-modal';
    modal.className = 'hidden fixed inset-0 z-[70] flex items-center justify-center bg-primary/40 backdrop-blur-sm p-4';
    modal.setAttribute('role', 'dialog');
    modal.setAttribute('aria-modal', 'true');
    modal.innerHTML = '<div id="manager-schedule-action-panel" class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-3xl max-h-[90vh] overflow-hidden"></div>';
    document.body.appendChild(modal);

    modal.addEventListener('click', event => {
        if (event.target === modal || event.target.closest('[data-manager-schedule-close]')) {
            closeManagerScheduleActionModal();
            return;
        }

        const submit = event.target.closest('[data-manager-schedule-submit]');
        if (submit) {
            event.preventDefault();
            handleManagerScheduleModalSubmit();
            return;
        }

        const requestAction = event.target.closest('[data-shift-request-action]');
        if (requestAction && !requestAction.disabled) {
            event.preventDefault();
            const id = requestAction.dataset.requestId;
            const action = requestAction.dataset.shiftRequestAction;
            handleShiftRequestDecision(id, action);
        }
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') closeManagerScheduleActionModal();
    });

    return modal;
}

function closeManagerScheduleActionModal() {
    document.getElementById('manager-schedule-action-modal')?.classList.add('hidden');
}

function openManagerScheduleActionModal(title, subtitle, bodyHtml, submitLabel = 'Save') {
    const modal = ensureManagerScheduleActionModal();
    const panel = document.getElementById('manager-schedule-action-panel');
    if (!panel) return;

    panel.innerHTML = `
        <div class="flex items-start justify-between gap-4 px-6 py-5 border-b border-slate-100">
            <div>
                <h3 class="text-lg font-bold text-[#0c2340]">${escapeHtml(title)}</h3>
                <p class="text-xs text-slate-500 mt-1">${escapeHtml(subtitle)}</p>
            </div>
            <button type="button" data-manager-schedule-close aria-label="Close" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form id="manager-schedule-action-form">
            <div class="px-6 py-5 max-h-[68vh] overflow-y-auto">${bodyHtml}</div>
            <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-2 bg-slate-50/70">
                <button type="button" data-manager-schedule-close class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-200 hover:bg-slate-100 rounded-lg transition-colors">Cancel</button>
                <button type="submit" data-manager-schedule-submit class="px-4 py-2 text-sm font-semibold text-white bg-[#0c2340] hover:bg-slate-800 rounded-lg transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-check text-xs"></i> ${escapeHtml(submitLabel)}
                </button>
            </div>
        </form>`;

    modal.classList.remove('hidden');
    panel.querySelector('input, select, textarea, button')?.focus();
}

function managerFieldLabel(text, required = false) {
    return `<label class="block text-xs font-semibold text-slate-600 mb-1.5">${escapeHtml(text)}${required ? ' <span class="text-red-500">*</span>' : ''}</label>`;
}

function managerFormControlClass() {
    return 'w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-accent/40 focus:border-accent';
}

async function loadManagerScheduleWeekData(weekLabel) {
    const weekStart = managerWeekStartDate(
        weekLabel
    );

    if (!weekStart) {
        return [];
    }

    const response = await fetch(
        `api/schedules/manager.php?week_start=${encodeURIComponent(weekStart)}`,
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
            'Unable to load schedule data.'
        );
    }

    return Array.isArray(result.data)
        ? result.data
        : [];
}

async function createSchedule() {
    const team = getManagerTeam();
    if (!team.length) {
        showToast('No employees are assigned to this manager.', 'info');
        return;
    }

    const week = managerCurrentScheduleWeek();

    let scheduleRows = [];

    try {
        scheduleRows =
            await loadManagerScheduleWeekData(week);
    } catch (error) {
        console.error(
            'Unable to load existing manager schedule:',
            error
        );
    }

    const first =
        scheduleRows.find(
            row =>
                String(row.employeeId) ===
                String(team[0].id)
        ) || {};

    const employeeOptions = team.map(emp => `
        <option value="${managerEscapeAttr(emp.id)}">${escapeHtml(emp.name)} — ${escapeHtml(emp.position)}</option>
    `).join('');

    const dayFields = MANAGER_SCHEDULE_DAYS.map(([key, label]) => `
        <div>
            ${managerFieldLabel(label, true)}
            <select class="${managerFormControlClass()}" data-schedule-day="${key}">
                ${managerShiftOptions(first?.[key] || '8-5')}
            </select>
        </div>
    `).join('');

    openManagerScheduleActionModal(
        'Create Schedule',
        'Create or replace one employee\'s Monday-Friday schedule for a specific week.',
        `
            <input type="hidden" id="manager-schedule-action-type" value="create">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    ${managerFieldLabel('Employee', true)}
                    <select id="manager-schedule-employee" class="${managerFormControlClass()}">
                        ${employeeOptions}
                    </select>
                </div>
                <div>
                    ${managerFieldLabel('Schedule Week', true)}
                    <input id="manager-schedule-week" type="text" value="${managerEscapeAttr(week)}" maxlength="80" placeholder="e.g. Aug 10-16, 2026" class="${managerFormControlClass()}">
                </div>
            </div>

            <div class="mt-5">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-bold text-[#0c2340]">Daily Shifts</p>
                    <span class="text-[11px] text-slate-400">Choose a preset or use a custom shift.</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">${dayFields}</div>
            </div>

            <div class="mt-5 rounded-xl bg-slate-50 border border-slate-200 p-3 text-xs text-slate-500 flex gap-2">
                <i class="fa-solid fa-circle-info text-accent mt-0.5"></i>
                <span>Existing schedules for the same employee and week will be updated. Other employees and other weeks are not changed.</span>
            </div>
        `,
        'Save Schedule'
    );

    document
        .getElementById('manager-schedule-employee')
        ?.addEventListener('change', async event => {

            const selected =
                event.target.value;

            const selectedWeek =
                document.getElementById(
                    'manager-schedule-week'
                )?.value || week;

            try {
                const rows =
                    await loadManagerScheduleWeekData(
                        selectedWeek
                    );

                const selectedSchedule =
                    rows.find(
                        row =>
                            String(row.employeeId) ===
                            String(selected)
                    ) || {};

                document
                    .querySelectorAll(
                        '[data-schedule-day]'
                    )
                    .forEach(select => {

                        const day =
                            select.dataset.scheduleDay;

                        const value =
                            selectedSchedule[day] ||
                            '8-5';

                        if (
                            !Array.from(
                                select.options
                            ).some(
                                option =>
                                    option.value === value
                            )
                        ) {
                            const option =
                                document.createElement(
                                    'option'
                                );

                            option.value = value;
                            option.textContent = value;

                            select.insertBefore(
                                option,
                                select.querySelector(
                                    'option[value="__custom__"]'
                                )
                            );
                        }

                        select.value = value;
                    });

            } catch (error) {
                console.error(
                    'Unable to load selected employee schedule:',
                    error
                );
            }
        });

    document.querySelectorAll('[data-schedule-day]').forEach(select => {
        select.addEventListener('change', () => {
            if (select.value === '__custom__') {
                const value = prompt(`Enter ${select.dataset.scheduleDay.toUpperCase()} shift (example: 8-5 or 08:00-17:00):`, '8-5');
                if (value && managerNormalizeShift(value)) {
                    const normalized = managerNormalizeShift(value);
                    const existingOption = Array.from(select.options).find(option => option.value === normalized);
                    if (!existingOption) {
                        const option = document.createElement('option');
                        option.value = normalized;
                        option.textContent = normalized;
                        select.insertBefore(option, select.querySelector('option[value="__custom__"]'));
                    }
                    select.value = normalized;
                } else {
                    select.value = '8-5';
                }
            }
        });
    });
}

async function assignShift() {
    const team = getManagerTeam();
    if (!team.length) {
        showToast('No employees are assigned to this manager.', 'info');
        return;
    }

    const teamIds =
        new Set(team.map(emp => emp.id));

    const week =
        managerCurrentScheduleWeek();

    const defaultEmployee =
        team[0];

    let scheduleRows = [];

    try {
        scheduleRows =
            await loadManagerScheduleWeekData(week);
    } catch (error) {
        console.error(
            'Unable to load existing manager schedule:',
            error
        );
    }

    const defaultSchedule =
        scheduleRows.find(
            row =>
                String(row.employeeId) ===
                String(defaultEmployee.id)
        ) || {};

    const employeeOptions = team.map(emp => `
        <option value="${managerEscapeAttr(emp.id)}">${escapeHtml(emp.name)} — ${escapeHtml(emp.position)}</option>
    `).join('');

    openManagerScheduleActionModal(
        'Assign Shift',
        'Assign or update one shift for a team member without affecting the other days.',
        `
            <input type="hidden" id="manager-schedule-action-type" value="assign">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    ${managerFieldLabel('Employee', true)}
                    <select id="manager-assign-employee" class="${managerFormControlClass()}">${employeeOptions}</select>
                </div>
                <div>
                    ${managerFieldLabel('Schedule Week', true)}
                    <input id="manager-assign-week" type="text" value="${managerEscapeAttr(week)}" maxlength="80" placeholder="e.g. Aug 10-16, 2026" class="${managerFormControlClass()}">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    ${managerFieldLabel('Day', true)}
                    <select id="manager-assign-day" class="${managerFormControlClass()}">
                        ${MANAGER_SCHEDULE_DAYS.map(([key, label]) => `<option value="${key}">${label}</option>`).join('')}
                    </select>
                </div>
                <div>
                    ${managerFieldLabel('Shift', true)}
                    <select id="manager-assign-shift" class="${managerFormControlClass()}">
                        ${managerShiftOptions(defaultSchedule.mon || '8-5')}
                    </select>
                </div>
            </div>

            <div class="mt-4 hidden" id="manager-assign-custom-wrap">
                ${managerFieldLabel('Custom Shift', true)}
                <input id="manager-assign-custom" type="text" maxlength="40" placeholder="e.g. 7:30-16:30" class="${managerFormControlClass()}">
            </div>

            <div class="mt-5 rounded-xl bg-blue-50 border border-blue-100 p-3 text-xs text-blue-700 flex gap-2">
                <i class="fa-solid fa-calendar-check mt-0.5"></i>
                <span>The selected day is updated inside the chosen week. The manager can assign only employees in the current team.</span>
            </div>
        `,
        'Assign Shift'
    );

    const refreshDefaultShift = async () => {
        const employee =
            document.getElementById(
                'manager-assign-employee'
            )?.value;

        const assignWeek =
            document.getElementById(
                'manager-assign-week'
            )?.value || week;

        const day =
            document.getElementById(
                'manager-assign-day'
            )?.value || 'mon';

        const shiftSelect =
            document.getElementById(
                'manager-assign-shift'
            );

        if (!shiftSelect) {
            return;
        }

        try {
            const rows =
                await loadManagerScheduleWeekData(
                    assignWeek
                );

            const schedule =
                rows.find(
                    row =>
                        String(row.employeeId) ===
                        String(employee)
                ) || {};

            const value =
                schedule[day] || '8-5';

            if (
                !Array.from(
                    shiftSelect.options
                ).some(
                    option =>
                        option.value === value
                )
            ) {
                const option =
                    document.createElement(
                        'option'
                    );

                option.value = value;
                option.textContent = value;

                shiftSelect.insertBefore(
                    option,
                    shiftSelect.querySelector(
                        'option[value="__custom__"]'
                    )
                );
            }

            shiftSelect.value = value;

        } catch (error) {
            console.error(
                'Unable to refresh manager shift:',
                error
            );
        }
    };

    document.getElementById('manager-assign-employee')?.addEventListener('change', refreshDefaultShift);
    document.getElementById('manager-assign-day')?.addEventListener('change', refreshDefaultShift);
    document.getElementById('manager-assign-week')?.addEventListener('change', refreshDefaultShift);

    document.getElementById('manager-assign-shift')?.addEventListener('change', event => {
        const customWrap = document.getElementById('manager-assign-custom-wrap');
        if (!customWrap) return;
        customWrap.classList.toggle('hidden', event.target.value !== '__custom__');
        if (event.target.value === '__custom__') document.getElementById('manager-assign-custom')?.focus();
    });

    // Keep the teamIds reference alive for validation in submit handling without changing shared data.
    document.getElementById('manager-schedule-action-modal').dataset.teamIds = Array.from(teamIds).join(',');
}

function getManagerShiftRequests() {
    return Array.isArray(managerShiftRequestDbRecords)
        ? managerShiftRequestDbRecords
        : [];
}

async function loadManagerShiftRequestsFromDatabase() {
    if (managerShiftRequestDbLoaded) {
        return true;
    }

    try {
        const response = await fetch(
            'api/shift-requests/manager.php',
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
                'Unable to load shift requests.'
            );
        }

        managerShiftRequestDbRecords =
            Array.isArray(result.data)
                ? result.data
                : [];

        managerShiftRequestDbLoaded = true;

        return true;

    } catch (error) {

        console.error(
            'Manager shift request loading error:',
            error
        );

        return false;
    }
}

function updateShiftRequestBadge() {
    const pending =
        getManagerShiftRequests().filter(
            request =>
                String(
                    request.status || ''
                ).toLowerCase() === 'pending'
        ).length;

    const badge =
        document.getElementById(
            'shift-request-badge'
        );

    if (!badge) return;

    badge.textContent =
        pending > 9
            ? '9+'
            : '';

    badge.classList.toggle(
        'hidden',
        pending === 0
    );

    badge.title =
        pending
            ? `${pending} pending shift request${pending === 1 ? '' : 's'}`
            : 'No pending shift requests';
}

function renderShiftRequestsModal() {
    const modal = ensureManagerScheduleActionModal();
    const panel = document.getElementById('manager-schedule-action-panel');
    if (!panel) return;

    const teamIds = new Set(getManagerTeam().map(emp => String(emp.id)));
    const requests = getManagerShiftRequests()
        .map((request, index) => ({ request, originalIndex: index }))
        .filter(item => teamIds.has(String(item.request.employeeId ?? item.request.employee_id ?? item.request.staffId ?? '')))
        .sort((a, b) => {
            const aPending = String(a.request.status || '').toLowerCase() === 'pending';
            const bPending = String(b.request.status || '').toLowerCase() === 'pending';
            return Number(bPending) - Number(aPending);
        });

    if (!requests.length) {
        panel.innerHTML = `
            <div class="px-6 py-5 border-b border-slate-100 flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-bold text-[#0c2340]">Shift Requests</h3>
                    <p class="text-xs text-slate-500 mt-1">Review shift-change requests from your team.</p>
                </div>
                <button type="button" data-manager-schedule-close class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="px-6 py-12 text-center text-slate-400">
                <i class="fa-regular fa-calendar-check text-3xl mb-3"></i>
                <p class="font-medium text-slate-600">No shift requests for your team.</p>
                <p class="text-xs mt-1">New employee requests will appear here.</p>
            </div>`;
        modal.classList.remove('hidden');
        return;
    }

    const requestCards = requests.map(({ request }) => {
        const employee = managerRequestEmployee(request);
        if (!employee) return '';
        const details = managerRequestDetails(request);
        const pending = String(request.status || '').toLowerCase() === 'pending';
        const status = String(request.status || 'Pending');
        const statusClass = pending ? 'bg-amber-100 text-amber-700' : status.toLowerCase() === 'approved' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700';
        const dayLabel = MANAGER_SCHEDULE_DAYS.find(([key]) => key === details.day)?.[1] || 'Day not specified';
        const requestedShift = details.requestedShift || 'Not specified';
        const currentShift = details.currentShift || 'Current shift not specified';
        const requestId = request.id ?? request.requestId ?? request.key ?? `${employee.id}_${details.week}_${details.day}_${requestedShift}`;

        return `
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" data-shift-request-card="${managerEscapeAttr(requestId)}">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-full bg-[#eaf2fb] text-[#0c2340] flex items-center justify-center font-bold text-sm flex-shrink-0">
                            ${escapeHtml((employee.name || '').split(' ').map(part => part[0]).join('').slice(0, 2).toUpperCase())}
                        </div>
                        <div class="min-w-0">
                            <p class="font-bold text-[#0c2340] truncate">${escapeHtml(employee.name)}</p>
                            <p class="text-[11px] text-slate-400">${escapeHtml(employee.position)} · ${escapeHtml(employee.id)}</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider whitespace-nowrap ${statusClass}">${escapeHtml(status)}</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4">
                    <div class="rounded-xl bg-slate-50 border border-slate-100 p-3">
                        <p class="text-[10px] uppercase tracking-wide text-slate-400 font-semibold">Week</p>
                        <p class="text-xs font-semibold text-slate-700 mt-1">${escapeHtml(details.week)}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 border border-slate-100 p-3">
                        <p class="text-[10px] uppercase tracking-wide text-slate-400 font-semibold">Day</p>
                        <p class="text-xs font-semibold text-slate-700 mt-1">${escapeHtml(dayLabel)}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 border border-slate-100 p-3">
                        <p class="text-[10px] uppercase tracking-wide text-slate-400 font-semibold">Current</p>
                        <p class="text-xs font-semibold text-slate-700 mt-1">${escapeHtml(currentShift)}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 border border-slate-100 p-3">
                        <p class="text-[10px] uppercase tracking-wide text-slate-400 font-semibold">Requested</p>
                        <p class="text-xs font-semibold text-indigo-700 mt-1">${escapeHtml(requestedShift)}</p>
                    </div>
                </div>

                <div class="mt-3 rounded-xl bg-slate-50 border border-slate-100 p-3">
                    <p class="text-[10px] uppercase tracking-wide text-slate-400 font-semibold mb-1">Reason</p>
                    <p class="text-xs text-slate-600">${escapeHtml(details.reason)}</p>
                </div>

                ${pending ? `
                    <div class="mt-4 pt-4 border-t border-slate-100 flex gap-2">
                        <button type="button" data-shift-request-action="approve" data-request-id="${managerEscapeAttr(String(requestId))}" class="flex-1 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-semibold text-sm rounded-xl transition-colors">
                            <i class="fa-solid fa-check text-xs mr-1.5"></i>Approve
                        </button>
                        <button type="button" data-shift-request-action="decline" data-request-id="${managerEscapeAttr(String(requestId))}" class="flex-1 py-2.5 border border-red-300 text-red-600 hover:bg-red-50 font-semibold text-sm rounded-xl transition-colors">
                            <i class="fa-solid fa-xmark text-xs mr-1.5"></i>Decline
                        </button>
                    </div>` : ''}
            </div>`;
    }).join('');

    panel.innerHTML = `
        <div class="px-6 py-5 border-b border-slate-100 flex items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-lg font-bold text-[#0c2340]">Shift Requests</h3>
                    <span class="px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 text-[10px] font-bold">${requests.length}</span>
                </div>
                <p class="text-xs text-slate-500 mt-1">Review, approve, or decline requests submitted by your team.</p>
            </div>
            <button type="button" data-manager-schedule-close class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="px-6 py-5 max-h-[72vh] overflow-y-auto space-y-4">
            ${requestCards || '<div class="py-8 text-center text-slate-400">No readable shift requests.</div>'}
        </div>`;

    modal.classList.remove('hidden');
}

async function showShiftRequests() {
    managerShiftRequestDbLoaded = false;

    const loaded =
        await loadManagerShiftRequestsFromDatabase();

    if (!loaded) {
        showToast(
            'Unable to load shift requests.',
            'info'
        );
        return;
    }

    updateShiftRequestBadge();
    renderShiftRequestsModal();
}

async function handleShiftRequestDecision(
    requestKey,
    action
) {
    const requests =
        getManagerShiftRequests();

    const targetIndex =
        requests.findIndex(request =>
            String(
                request.id ??
                request.requestId ??
                request.key ??
                ''
            ) === String(requestKey)
        );

    if (targetIndex < 0) {
        showToast(
            'Shift request was not found.',
            'info'
        );
        return;
    }

    const request =
        requests[targetIndex];

    const currentStatus =
        String(
            request.status || ''
        ).toLowerCase();

    if (currentStatus !== 'pending') {
        showToast(
            'This shift request has already been processed.',
            'info'
        );
        return;
    }

    const employee =
        managerRequestEmployee(request);

    const person =
        employee?.name ||
        request.name ||
        request.employeeId ||
        'Employee';

    try {

        const response = await fetch(
            'api/shift-requests/manager.php',
            {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    id: Number(request.id),
                    action:
                        action === 'approve'
                            ? 'approve'
                            : 'decline'
                })
            }
        );

        const result =
            await response.json();

        if (!response.ok || !result.success) {
            throw new Error(
                result.message ||
                'Unable to process shift request.'
            );
        }

        /*
         * Force fresh MySQL data next time
         * the modal is opened.
         */
        managerShiftRequestDbLoaded = false;

        await loadManagerShiftRequestsFromDatabase();

        updateShiftRequestBadge();

        renderShiftRequestsModal();

        /*
         * Refresh the Team Schedule from MySQL.
         */
        await renderManagerSchedules();

        updateManagerAnalytics();

        showToast(
            action === 'approve'
                ? `${person}'s shift request approved`
                : `${person}'s shift request declined`,
            action === 'approve'
                ? 'success'
                : 'info'
        );

    } catch (error) {

        console.error(
            'Shift request decision error:',
            error
        );

        showToast(
            error.message ||
            'Unable to process shift request.',
            'info'
        );
    }
}

async function handleManagerScheduleModalSubmit() {
    const type = document.getElementById('manager-schedule-action-type')?.value;
    if (!type) return;

    if (type === 'create') {
        const employeeId =
            document.getElementById(
                'manager-schedule-employee'
            )?.value || '';

        const week =
            managerNormalizeShift(
                document.getElementById(
                    'manager-schedule-week'
                )?.value
            );

        const team = getManagerTeam();

        const employee = team.find(
            emp => String(emp.id) === String(employeeId)
        );

        if (!employee) {
            showToast(
                'Please select an employee from your team.',
                'info'
            );
            return;
        }

        if (!week) {
            showToast(
                'Please enter the schedule week.',
                'info'
            );
            return;
        }

        const weekStart =
            managerWeekStartDate(week);

        console.log(
            'MANAGER CREATE SCHEDULE WEEK:',
            week,
            '=>',
            weekStart
        );

        if (!weekStart) {
            showToast(
                'Please use the week format shown in the field.',
                'info'
            );
            return;
        }

        const values = {};

        for (const [key] of MANAGER_SCHEDULE_DAYS) {

            const select =
                document.querySelector(
                    `[data-schedule-day="${key}"]`
                );

            const value =
                managerNormalizeShift(
                    select?.value
                );

            if (!value || value === '__custom__') {
                showToast(
                    `Please choose a shift for ${key.toUpperCase()}.`,
                    'info'
                );
                return;
            }

            values[key] = value;
        }

        try {

            console.log(
                'SENDING SCHEDULE TO DATABASE:',
                {
                    employee_id: employee.id,
                    week_start: weekStart,
                    schedules: values
                }
            );

            const response = await fetch(
                'api/schedules/manager.php',
                {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        employee_id: employee.id,
                        week_start: weekStart,
                        schedules: values
                    })
                }
            );

            const result = await response.json();

            console.log(
                'DATABASE RESPONSE:',
                result
            );

            if (!response.ok || !result.success) {
                throw new Error(
                    result.message ||
                    'Unable to save schedule.'
                );
            }

            closeManagerScheduleActionModal();

            await renderManagerSchedules(week);

            updateShiftRequestBadge();

            showToast(
                `Schedule saved for ${employee.name}`,
                'success'
            );

        } catch (error) {
            console.error(
                'Create schedule error:',
                error
            );

            showToast(
                error.message ||
                'Unable to save schedule.',
                'info'
            );
        }

        return;
    }

    if (type === 'assign') {
        const employeeId =
            document.getElementById(
                'manager-assign-employee'
            )?.value || '';

        const week =
            managerNormalizeShift(
                document.getElementById(
                    'manager-assign-week'
                )?.value
            );

        const day =
            document.getElementById(
                'manager-assign-day'
            )?.value || '';

        const shiftSelect =
            document.getElementById(
                'manager-assign-shift'
            );

        let shift =
            managerNormalizeShift(
                shiftSelect?.value
            );

        const custom =
            managerNormalizeShift(
                document.getElementById(
                    'manager-assign-custom'
                )?.value
            );

        const employee =
            getManagerTeam().find(
                emp => String(emp.id) === String(employeeId)
            );

        if (!employee) {
            showToast(
                'Please select an employee from your team.',
                'info'
            );
            return;
        }

        if (!week) {
            showToast(
                'Please enter the schedule week.',
                'info'
            );
            return;
        }

        const weekStart =
            managerWeekStartDate(week);

        console.log(
            'MANAGER ASSIGN SHIFT WEEK:',
            week,
            '=>',
            weekStart
        );

        if (!weekStart) {
            showToast(
                'Please use the week format shown in the field.',
                'info'
            );
            return;
        }

        if (
            !MANAGER_SCHEDULE_DAYS.some(
                ([key]) => key === day
            )
        ) {
            showToast(
                'Please select a valid weekday.',
                'info'
            );
            return;
        }

        if (shift === '__custom__') {
            shift = custom;
        }

        if (!shift) {
            showToast(
                'Please enter a valid shift.',
                'info'
            );
            return;
        }

        try {
            const response = await fetch(
                'api/schedules/manager.php',
                {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        employee_id: employee.id,
                        week_start: weekStart,
                        day: day,
                        shift: shift
                    })
                }
            );

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(
                    result.message ||
                    'Unable to assign shift.'
                );
            }

            closeManagerScheduleActionModal();

            await renderManagerSchedules(week);

            updateShiftRequestBadge();

            showToast(
                `${employee.name}'s ${day.toUpperCase()} shift updated`,
                'success'
            );

        } catch (error) {
            console.error(
                'Assign shift error:',
                error
            );

            showToast(
                error.message ||
                'Unable to assign shift.',
                'info'
            );
        }

        return;
    }
}

// ---------------------------- Team Analytics ----------------------------
async function loadManagerAnalyticsSummary() {

    try {

        const response = await fetch(
            'api/analytics/manager-summary.php',
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
                'Unable to load analytics summary.'
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
            'analytics-team-members',
            data.team_members || 0
        );

        setText(
            'analytics-attendance-rate',
            `${Number(
                data.attendance_rate || 0
            ).toFixed(1)}%`
        );

        setText(
            'analytics-late-arrivals',
            data.late_arrivals || 0
        );

        setText(
            'analytics-approved-leave-days',
            data.approved_leave_days || 0
        );

        setText(
            'analytics-regular-hours',
            `${Number(
                data.regular_hours || 0
            ).toFixed(2)} hrs`
        );

        setText(
            'analytics-overtime-hours',
            `${Number(
                data.overtime_hours || 0
            ).toFixed(2)} hrs`
        );

    } catch (error) {

        console.error(
            'Manager analytics summary error:',
            error
        );
    }
}

async function initManagerAttendanceTrendChart() {
    const canvas = document.getElementById('team-attendance-trend-chart');

    if (!canvas || typeof Chart === 'undefined') {
        return;
    }

    try {
        const response = await fetch(
            'api/analytics/manager.php',
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
                'Unable to load workforce analytics.'
            );
        }

        const days = Array.isArray(result.days)
            ? result.days
            : [];

        const labels = days.map(day => day.label);
        const values = days.map(day => Number(day.rate || 0));

        const teamLabel = document.getElementById(
            'analytics-team-label'
        );

        if (teamLabel) {
            const count = Number(result.team_count || 0);

            teamLabel.textContent =
                `${count} team member${count === 1 ? '' : 's'}`;
        }

        if (managerTeamChart) {
            managerTeamChart.data.labels = labels;
            managerTeamChart.data.datasets[0].data = values;
            managerTeamChart.update();
            return;
        }

        managerTeamChart = new Chart(canvas, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Team Attendance Rate',
                    data: values,
                    borderColor: '#6FA9E6',
                    backgroundColor: 'rgba(111, 169, 230, 0.15)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.25,
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
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: context =>
                                `${context.parsed.y}%`
                        }
                    }
                },
                scales: {
                    y: {
                        min: 0,
                        max: 100,
                        ticks: {
                            stepSize: 20,
                            callback: value => `${value}%`
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

    } catch (error) {

        console.error(
            'Manager analytics error:',
            error
        );

        const label = document.getElementById(
            'analytics-team-label'
        );

        if (label) {
            label.textContent = 'Unable to load';
        }
    }
}

async function updateManagerAnalytics() {

    await initManagerAttendanceTrendChart();

    await loadManagerAnalyticsSummary();
}


// ---------------------------- Initialization ----------------------------

async function refreshManagerData() {
    await renderManagerAttendance();
    await renderManagerSchedules();
    await renderManagerLeaves();
    await loadMyLeaveRequests();

    managerTimesheetDbLoaded = false;
    await loadManagerTimesheetsFromDatabase();
    renderManagerTimesheets();

    updateShiftRequestBadge();
    updateManagerAnalytics();
}

document.addEventListener('DOMContentLoaded', () => {
    refreshManagerData();

    // Refresh visible manager data whenever another page/tab action updates the
    // shared localStorage data and this tab receives a storage event.
    window.addEventListener('storage', (event) => {
        if (event.key && event.key.startsWith('ht_v1_')) {
            refreshManagerData();
        }
    });
});

window.createSchedule = createSchedule;
window.assignShift = assignShift;
window.showShiftRequests = showShiftRequests;
window.approveManagerLeave = approveManagerLeave;
window.declineManagerLeave = declineManagerLeave;
window.refreshManagerData = refreshManagerData;

// ============================================================================
// Manager Timesheets (team-based)
// ----------------------------------------------------------------------------
// Everything below only powers the Timesheets tab (#schedule). Hours, overtime
// and status are always CALCULATED from Time In / Time Out / Break / Schedule,
// never typed in by hand. The only thing persisted is the manager's own
// review decisions (SharedData collection "timesheetReviews").
// ============================================================================

const TS_LATE_GRACE_MIN = 10;      // minutes after shift start before "Late"
const TS_BREAK_MIN = 60;           // standard unpaid break
const TS_COMPLETE_STATUSES = ['Completed', 'Late', 'Overtime'];

// Sample team roster for timesheets. Only people whose managerId matches the
// logged-in manager are shown. Liza Ramos reports to a different manager, so
// she never appears (this demonstrates the team-only rule).
const TIMESHEET_ROSTER = [
    { id: 'EMP-101', name: 'Juan Dela Cruz', position: 'Tour Guide', managerId: 'MGR-001', start: '08:00', end: '17:00', cycleOffset: 0 },
    { id: 'EMP-102', name: 'Maria Santos', position: 'Front Desk', managerId: 'MGR-001', start: '09:00', end: '18:00', cycleOffset: 3 },
    { id: 'EMP-103', name: 'Carlo Reyes', position: 'Tour Guide', managerId: 'MGR-001', start: '08:00', end: '17:00', cycleOffset: 6 },
    { id: 'EMP-104', name: 'Ana Garcia', position: 'Front Desk', managerId: 'MGR-001', start: '10:00', end: '19:00', cycleOffset: 9 },
    { id: 'EMP-105', name: 'Mark Lopez', position: 'Operations Coordinator', managerId: 'MGR-001', start: '08:00', end: '17:00', cycleOffset: 12 },
    { id: 'EMP-201', name: 'Liza Ramos', position: 'Tour Guide', managerId: 'MGR-002', start: '08:00', end: '17:00', cycleOffset: 2 }
];

// inOff / outOff are minutes relative to shift start / end (null = not recorded).
const TS_SCENARIOS = {
    onTime: { inOff: 2, outOff: 2, remark: 'Regular shift, no issues reported.' },
    early: { inOff: -6, outOff: -6, remark: 'Regular shift, no issues reported.' },
    exact: { inOff: 0, outOff: 0, remark: 'Shift completed exactly as scheduled.' },
    late: { inOff: 22, outOff: 5, remark: 'Arrived late, employee cited heavy traffic.' },
    lateMakeUp: { inOff: 15, outOff: 15, remark: 'Arrived late but stayed to make up the time.' },
    ot45: { inOff: 0, outOff: 45, remark: 'Stayed past shift to clear guest check-outs.' },
    ot90: { inOff: 0, outOff: 90, remark: 'Tour ran long; guest drop-off finished after shift.' },
    ot120: { inOff: 0, outOff: 120, remark: 'Airport transfer delayed; stayed to complete pickup.' },
    absent: { inOff: null, outOff: null, remark: 'No attendance record for this date.' },
    noOut: { inOff: -4, outOff: null, remark: 'Time Out was not recorded.', remarkToday: 'Currently on shift, no Time Out yet.' },
    noIn: { inOff: null, outOff: 3, remark: 'Time In was not recorded; needs correction.' }
};

// Scenario pattern used for regular days (picked per employee + calendar date,
// so a given date always produces the same record).
const TS_CYCLE = ['onTime', 'exact', 'early', 'late', 'onTime', 'ot45', 'early', 'noOut', 'exact',
    'ot90', 'onTime', 'absent', 'lateMakeUp', 'noIn', 'early', 'onTime', 'ot120'];

// The two most recent shifts are pinned so every status is visible in the demo.
const TS_PINNED = [
    { 'EMP-101': 'noOut', 'EMP-102': 'onTime', 'EMP-103': 'late', 'EMP-104': 'absent', 'EMP-105': 'ot90', 'EMP-201': 'onTime' },
    { 'EMP-101': 'onTime', 'EMP-102': 'noOut', 'EMP-103': 'exact', 'EMP-104': 'late', 'EMP-105': 'onTime', 'EMP-201': 'onTime' }
];

let timesheetView = [];
let managerTimesheetDbRecords = [];
let managerTimesheetDbLoaded = false;
let managerShiftRequestDbRecords = [];
let managerShiftRequestDbLoaded = false;

// ---- Date / time helpers ----
function tsISO(d) {
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
}
function tsParseISO(iso) {
    const [y, m, d] = iso.split('-').map(Number);
    return new Date(y, m - 1, d);
}
function tsAddDays(d, n) {
    const c = new Date(d.getFullYear(), d.getMonth(), d.getDate());
    c.setDate(c.getDate() + n);
    return c;
}
function tsMonday(d) {
    const c = new Date(d.getFullYear(), d.getMonth(), d.getDate());
    c.setDate(c.getDate() - ((c.getDay() + 6) % 7));
    return c;
}
function tsToday() {
    const t = new Date();
    return new Date(t.getFullYear(), t.getMonth(), t.getDate());
}
function tsToMin(t) {
    if (!t) return null;
    const [h, m] = t.split(':').map(Number);
    return h * 60 + m;
}
function tsFromMin(min) {
    return `${String(Math.floor(min / 60)).padStart(2, '0')}:${String(min % 60).padStart(2, '0')}`;
}
function tsFmtTime(t) {
    if (!t) return '';
    const [h, m] = t.split(':').map(Number);
    return `${h % 12 || 12}:${String(m).padStart(2, '0')} ${h >= 12 ? 'PM' : 'AM'}`;
}
function tsFmtDate(iso, withYear) {
    return tsParseISO(iso).toLocaleDateString('en-US', {
        weekday: 'short', month: 'short', day: 'numeric', ...(withYear ? { year: 'numeric' } : {})
    });
}
function tsRound(n) { return Math.round(n * 100) / 100; }
function tsHrs(n) { return tsRound(n).toFixed(2); }

// ---- Core calculation ----
// Total = Time Out - Time In - Break.  Regular is capped at the scheduled
// working hours (shift length minus break); anything above is Overtime.
function computeTimesheet(rec, todayISO) {
    const startMin = tsToMin(rec.scheduleStart);
    const endMin = tsToMin(rec.scheduleEnd);
    const inMin = tsToMin(rec.timeIn);
    const outMin = tsToMin(rec.timeOut);
    const scheduledHours = Math.max(0, (endMin - startMin - rec.breakMin) / 60);

    const result = { scheduledHours, regular: 0, overtime: 0, total: 0, lateMin: 0, hasHours: false, status: 'Incomplete' };

    if (inMin === null && outMin === null) {
        result.status = 'Absent';
        return result;
    }
    if (inMin === null || outMin === null || outMin <= inMin) {
        result.status = (inMin !== null && outMin === null && rec.date === todayISO) ? 'In Progress' : 'Incomplete';
        return result;
    }

    const worked = Math.max(0, (outMin - inMin - rec.breakMin) / 60);
    result.total = tsRound(worked);
    result.regular = tsRound(Math.min(worked, scheduledHours));
    result.overtime = tsRound(result.total - result.regular);
    result.lateMin = Math.max(0, inMin - startMin);
    result.hasHours = true;
    result.status = result.lateMin > TS_LATE_GRACE_MIN ? 'Late'
        : result.overtime > 0 ? 'Overtime'
            : 'Completed';
    return result;
}

// ---- Data ----
function getTimesheetTeam() {

    const team =
        getManagerTeam();

    return team.map(emp => ({
        id: String(emp.id),
        name: emp.name,
        position: emp.position
    }));

}

function loadTimesheetReviews() {
    return new Map();
}

function saveTimesheetReviews(map) {
    return;
}

// Builds raw records (last week + this week) for the given employees.
function buildTimesheetRecords(employees) {
    const today = tsToday();
    const todayISO = tsISO(today);
    const lastMonday = tsAddDays(tsMonday(today), -7);

    const dates = [];
    for (let d = today; d >= lastMonday; d = tsAddDays(d, -1)) {
        const weekend = d.getDay() === 0 || d.getDay() === 6;
        if (!weekend || tsISO(d) === todayISO) dates.push(d);
    }

    const records = [];
    employees.forEach(emp => {
        dates.forEach((d, rank) => {
            const dayNumber = Math.floor(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()) / 86400000);
            const key = (TS_PINNED[rank] && TS_PINNED[rank][emp.id]) || TS_CYCLE[(dayNumber + emp.cycleOffset) % TS_CYCLE.length];
            const scenario = TS_SCENARIOS[key];
            const iso = tsISO(d);
            const startMin = tsToMin(emp.start);
            const endMin = tsToMin(emp.end);

            records.push({
                id: `${emp.id}_${iso}`,
                employeeId: emp.id,
                employeeName: emp.name,
                position: emp.position,
                date: iso,
                scheduleStart: emp.start,
                scheduleEnd: emp.end,
                timeIn: scenario.inOff === null ? null : tsFromMin(startMin + scenario.inOff),
                timeOut: scenario.outOff === null ? null : tsFromMin(endMin + scenario.outOff),
                breakMin: TS_BREAK_MIN,
                remarks: (iso === todayISO && scenario.remarkToday) || scenario.remark
            });
        });
    });
    return records;
}

// Team records with calculations + the manager's review decisions applied.
function getTeamTimesheetRecords() {
    const todayISO = tsISO(tsToday());
    const reviews = loadTimesheetReviews();

    return managerTimesheetDbRecords.map(rec => {

        const calc =
            computeTimesheet(
                rec,
                todayISO
            );

        const review =
            reviews.get(rec.id) || {};

        const flagged =
            Boolean(review.flagged);

        return {
            ...rec,
            ...calc,
            calculatedStatus: calc.status,
            status:
                flagged
                    ? 'Incomplete'
                    : calc.status,
            flagged: flagged || Boolean(rec.flagged),
            review: review.review || rec.review || 'Pending',
            managerNote: review.note || rec.managerNote || ''
        };
    });
}

async function loadManagerTimesheetsFromDatabase() {

    try {

        const response = await fetch(
            'api/timesheet/manager.php',
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
                'Unable to load manager timesheets.'
            );
        }

        managerTimesheetDbRecords =
            Array.isArray(result.data)
                ? result.data
                : [];

        managerTimesheetDbLoaded = true;

        return true;

    } catch (error) {

        console.error(
            'Manager timesheet database loading error:',
            error
        );

        return false;
    }
}

// ---- Filters ----
function tsPeriodRange(period, dateValue) {
    const today = tsToday();
    const monday = tsMonday(today);

    if (period === 'today') return { from: tsISO(today), to: tsISO(today) };
    if (period === 'week') return { from: tsISO(monday), to: tsISO(tsAddDays(monday, 6)) };
    if (period === 'lastweek') {
        const lastMonday = tsAddDays(monday, -7);
        return { from: tsISO(lastMonday), to: tsISO(tsAddDays(lastMonday, 6)) };
    }
    if (period === 'date' && dateValue) return { from: dateValue, to: dateValue };
    return null;
}

function tsRangeLabel(range) {
    if (!range) return 'All available records';
    const short = iso => tsParseISO(iso).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    const year = tsParseISO(range.to).getFullYear();
    return range.from === range.to ? tsFmtDate(range.from, true) : `${short(range.from)} – ${short(range.to)}, ${year}`;
}

function getTimesheetFilters() {
    const value = id => document.getElementById(id)?.value || '';
    return {
        search: value('timesheet-search').trim().toLowerCase(),
        period: value('timesheet-period') || 'week',
        date: value('timesheet-date'),
        status: value('timesheet-status'),
        member: value('timesheet-member')
    };
}

function applyTimesheetFilters(records, filters) {
    const range = tsPeriodRange(filters.period, filters.date);

    return records
        .filter(rec => !range || (rec.date >= range.from && rec.date <= range.to))
        .filter(rec => !filters.member || rec.employeeId === filters.member)
        .filter(rec => !filters.status || rec.status === filters.status)
        .filter(rec => !filters.search ||
            rec.employeeName.toLowerCase().includes(filters.search) ||
            rec.employeeId.toLowerCase().includes(filters.search))
        .sort((a, b) => b.date.localeCompare(a.date) || a.employeeName.localeCompare(b.employeeName));
}

// ---- Rendering ----
function timesheetStatusClass(status) {
    return {
        'Completed': 'bg-emerald-100 text-emerald-800',
        'Late': 'bg-amber-100 text-amber-800',
        'In Progress': 'bg-sky-100 text-sky-800',
        'Absent': 'bg-rose-100 text-rose-800',
        'Overtime': 'bg-purple-100 text-purple-800',
        'Incomplete': 'bg-orange-100 text-orange-800'
    }[status] || 'bg-slate-100 text-slate-600';
}

function tsReviewLabel(rec) {
    if (rec.flagged) return 'Flagged';
    return rec.review === 'Pending' ? 'Pending Review' : rec.review;
}

function tsReviewTag(rec) {
    if (rec.flagged) return '<span class="block mt-1 text-[10px] font-semibold text-red-600"><i class="fa-solid fa-flag mr-1"></i>Flagged</span>';
    if (rec.review === 'Approved') return '<span class="block mt-1 text-[10px] font-semibold text-emerald-600"><i class="fa-solid fa-check mr-1"></i>Approved</span>';
    if (rec.review === 'Reviewed') return '<span class="block mt-1 text-[10px] font-semibold text-slate-500"><i class="fa-solid fa-eye mr-1"></i>Reviewed</span>';
    return '';
}

// Cell text for Time In / Time Out: real time, "Missing" (needs fixing) or a dash.
function tsTimeCell(rec, value) {
    if (value) return escapeHtml(tsFmtTime(value));
    if (rec.status === 'Incomplete') return '<span class="text-rose-500 font-medium">Missing</span>';
    return '<span class="text-slate-400">—</span>';
}

function setText(id, text) {
    const el = document.getElementById(id);
    if (el) el.textContent = text;
}

function populateTimesheetMemberFilter(team) {
    const select = document.getElementById('timesheet-member');
    if (!select) return;
    const previous = select.value;
    select.innerHTML = '<option value="">All Team Members</option>' +
        team.map(emp => `<option value="${escapeHtml(emp.id)}">${escapeHtml(emp.name)}</option>`).join('');
    select.value = team.some(emp => emp.id === previous) ? previous : '';
}

function renderManagerTimesheets() {
    const body = document.getElementById(
        'manager-timesheet-body'
    );

    if (!body) {
        return;
    }

    const team = getTimesheetTeam();
    populateTimesheetMemberFilter(team);
    setText('timesheet-team-label', `${team.length} team member${team.length === 1 ? '' : 's'}`);

    const filters = getTimesheetFilters();
    const all = getTeamTimesheetRecords();
    const rows = applyTimesheetFilters(all, filters);
    timesheetView = rows;

    // Summary cards reflect whatever is currently displayed.
    const sum = key => tsRound(rows.reduce((acc, r) => acc + (r[key] || 0), 0));
    setText('timesheet-total-hours', sum('total').toLocaleString(undefined, { maximumFractionDigits: 2 }));
    setText('timesheet-completed-shifts', String(rows.filter(r => TS_COMPLETE_STATUSES.includes(r.status)).length));
    setText('timesheet-open-shifts', String(rows.filter(r => r.status === 'In Progress').length));
    setText('timesheet-overtime-hours', sum('overtime').toLocaleString(undefined, { maximumFractionDigits: 2 }));
    setText('timesheet-missing-count', String(rows.filter(r => r.status === 'Absent' || r.status === 'Incomplete').length));

    setText('timesheet-range-label', tsRangeLabel(tsPeriodRange(filters.period, filters.date)));
    setText('timesheet-result-count', `Showing ${rows.length} of ${all.length} records`);

    if (!team.length) {
        body.innerHTML = `<tr><td colspan="11" class="py-8 text-center text-slate-400">No employees are assigned to this manager.</td></tr>`;
        return;
    }
    if (!rows.length) {
        body.innerHTML = `<tr><td colspan="11" class="py-8 text-center text-slate-400">No timesheet records match your filters.</td></tr>`;
        return;
    }

    const todayISO = tsISO(tsToday());
    const dash = '<span class="text-slate-400">—</span>';

    body.innerHTML = rows.map(rec => {
        const noBreak = rec.status === 'Absent';
        const hours = value => rec.hasHours ? tsHrs(value) : dash;

        return `
            <tr class="hover:bg-slate-50/80 transition-colors">
                <td class="py-3.5 pr-4 font-medium text-slate-900 whitespace-nowrap">
                    ${escapeHtml(rec.employeeName)}
                    <span class="block text-[11px] text-slate-400">${escapeHtml(rec.employeeId)} · ${escapeHtml(rec.position)}</span>
                </td>
                <td class="py-3.5 pr-4 text-slate-700 whitespace-nowrap">
                    ${escapeHtml(tsFmtDate(rec.date))}
                    ${rec.date === todayISO ? '<span class="ml-1 text-[10px] font-semibold px-1.5 py-0.5 rounded bg-slate-100 text-slate-500">Today</span>' : ''}
                </td>
                <td class="py-3.5 pr-4 text-slate-700 whitespace-nowrap">${escapeHtml(tsFmtTime(rec.scheduleStart))} – ${escapeHtml(tsFmtTime(rec.scheduleEnd))}</td>
                <td class="py-3.5 pr-4 text-slate-700 whitespace-nowrap">${tsTimeCell(rec, rec.timeIn)}</td>
                <td class="py-3.5 pr-4 text-slate-700 whitespace-nowrap">${tsTimeCell(rec, rec.timeOut)}</td>
                <td class="py-3.5 pr-4 text-slate-700 whitespace-nowrap">${noBreak ? dash : `${rec.breakMin} min`}</td>
                <td class="py-3.5 pr-4 text-slate-700 text-right">${hours(rec.regular)}</td>
                <td class="py-3.5 pr-4 text-right ${rec.overtime > 0 ? 'text-purple-700 font-semibold' : 'text-slate-700'}">${hours(rec.overtime)}</td>
                <td class="py-3.5 pr-4 text-slate-900 font-semibold text-right">${hours(rec.total)}</td>
                <td class="py-3.5 pr-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${timesheetStatusClass(rec.status)}">${escapeHtml(rec.status)}</span>
                    ${tsReviewTag(rec)}
                </td>
                <td class="py-3.5">
                    <button type="button" data-ts-view="${escapeHtml(rec.id)}"
                            class="px-3 py-1.5 text-xs font-mono font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200/60 rounded-lg transition-colors whitespace-nowrap flex items-center gap-1.5">
                        <i class="fa-solid fa-eye text-[11px]"></i> View Details
                    </button>
                </td>
            </tr>`;
    }).join('');
}

// ---- Delete timesheet records (manager table only; attendance and schedules remain intact) ----
let timesheetDeleteSelection = new Set();
let timesheetDeleteModalRows = [];

function closeTimesheetDeleteModal() {
    document.getElementById('timesheet-delete-modal')?.classList.add('hidden');
}

function ensureTimesheetDeleteModal() {
    let modal = document.getElementById('timesheet-delete-modal');
    if (modal) return modal;

    modal = document.createElement('div');
    modal.id = 'timesheet-delete-modal';
    modal.className = 'hidden fixed inset-0 z-[70] flex items-center justify-center bg-primary/40 backdrop-blur-sm p-4';
    modal.setAttribute('role', 'dialog');
    modal.setAttribute('aria-modal', 'true');
    modal.setAttribute('aria-labelledby', 'timesheet-delete-title');
    document.body.appendChild(modal);

    modal.addEventListener('click', event => {
        if (event.target === modal || event.target.closest('[data-ts-delete-close]')) {
            closeTimesheetDeleteModal();
            return;
        }

        if (event.target.closest('[data-ts-delete-back]')) {
            renderTimesheetDeleteListModal();
            return;
        }

        if (event.target.closest('[data-ts-delete-next]')) {
            if (timesheetDeleteSelection.size === 0) {
                showToast('Select at least one timesheet record to delete.', 'info');
                return;
            }
            renderTimesheetDeleteConfirmation();
            return;
        }

        if (event.target.closest('[data-ts-delete-confirm]')) {
            submitTimesheetDelete();
        }
    });

    modal.addEventListener('change', event => {
        const checkbox = event.target.closest('[data-ts-delete-select]');
        if (checkbox) {
            if (checkbox.checked) timesheetDeleteSelection.add(checkbox.value);
            else timesheetDeleteSelection.delete(checkbox.value);
            updateTimesheetDeleteSelectionUI();
            return;
        }

        const selectAll = event.target.closest('[data-ts-delete-select-all]');
        if (selectAll) {
            modal.querySelectorAll('[data-ts-delete-select]').forEach(item => {
                item.checked = selectAll.checked;
                if (selectAll.checked) timesheetDeleteSelection.add(item.value);
                else timesheetDeleteSelection.delete(item.value);
            });
            updateTimesheetDeleteSelectionUI();
        }
    });

    modal.addEventListener('keydown', event => {
        if (event.key === 'Escape') closeTimesheetDeleteModal();
    });
    return modal;
}

function openTimesheetDeleteModal() {
    timesheetDeleteModalRows = Array.isArray(timesheetView) ? [...timesheetView] : [];
    if (!timesheetDeleteModalRows.length) {
        showToast('There are no timesheet records in the current table to delete.', 'info');
        return;
    }
    timesheetDeleteSelection = new Set();
    renderTimesheetDeleteListModal();
}

function renderTimesheetDeleteListModal() {
    const modal = ensureTimesheetDeleteModal();
    const rows = timesheetDeleteModalRows;
    const rowHtml = rows.map(rec => `
        <tr class="border-b border-slate-100 last:border-0">
            <td class="px-3 py-3"><input type="checkbox" data-ts-delete-select value="${escapeHtml(rec.id)}" class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500" ${timesheetDeleteSelection.has(rec.id) ? 'checked' : ''} aria-label="Select ${escapeHtml(rec.employeeName)} ${escapeHtml(rec.date)}"></td>
            <td class="px-3 py-3 font-medium text-slate-800">${escapeHtml(rec.employeeName)}<span class="block text-[11px] text-slate-400">${escapeHtml(rec.employeeId)}</span></td>
            <td class="px-3 py-3 whitespace-nowrap">${escapeHtml(tsFmtDate(rec.date, true))}</td>
            <td class="px-3 py-3 whitespace-nowrap">${escapeHtml(tsFmtTime(rec.scheduleStart))} – ${escapeHtml(tsFmtTime(rec.scheduleEnd))}</td>
            <td class="px-3 py-3 whitespace-nowrap">${escapeHtml(rec.timeIn ? tsFmtTime(rec.timeIn) : '—')}</td>
            <td class="px-3 py-3 whitespace-nowrap">${escapeHtml(rec.timeOut ? tsFmtTime(rec.timeOut) : '—')}</td>
            <td class="px-3 py-3 whitespace-nowrap">${escapeHtml(rec.status || '—')}</td>
        </tr>`).join('');

    modal.innerHTML = `
        <div class="bg-card rounded-2xl shadow-xl border border-border w-full max-w-6xl max-h-[90vh] flex flex-col">
            <div class="flex items-start justify-between gap-4 px-6 py-5 border-b border-slate-100">
                <div><h3 id="timesheet-delete-title" class="text-lg font-bold text-[#0c2340]">Select Timesheet Records</h3><p class="text-xs text-slate-500 mt-1">Select the records to remove from this manager table. Employee attendance and schedules will remain unchanged.</p></div>
                <button type="button" data-ts-delete-close class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="px-5 py-4 overflow-auto">
                <table class="w-full min-w-[800px] text-left text-sm">
                    <thead class="sticky top-0 bg-slate-50 text-slate-600"><tr>
                        <th class="px-3 py-3"><input type="checkbox" data-ts-delete-select-all class="h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500" aria-label="Select all records"></th>
                        <th class="px-3 py-3">Employee</th><th class="px-3 py-3">Date</th><th class="px-3 py-3">Schedule</th><th class="px-3 py-3">Time In</th><th class="px-3 py-3">Time Out</th><th class="px-3 py-3">Status</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100">${rowHtml}</tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                <span id="timesheet-delete-selected-count" class="text-sm text-slate-500">0 selected</span>
                <div class="flex gap-2"><button type="button" data-ts-delete-close class="px-4 py-2 rounded-lg border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50">Cancel</button><button type="button" data-ts-delete-next class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-semibold hover:bg-red-700"><i class="fa-solid fa-trash-can mr-1.5"></i>Delete Selected</button></div>
            </div>
        </div>`;
    modal.classList.remove('hidden');
    updateTimesheetDeleteSelectionUI();
}

function updateTimesheetDeleteSelectionUI() {
    const modal = document.getElementById('timesheet-delete-modal');
    if (!modal) return;
    const count = timesheetDeleteSelection.size;
    const label = modal.querySelector('#timesheet-delete-selected-count');
    if (label) label.textContent = `${count} selected`;
    const allBoxes = [...modal.querySelectorAll('[data-ts-delete-select]')];
    const selectAll = modal.querySelector('[data-ts-delete-select-all]');
    if (selectAll) {
        selectAll.checked = allBoxes.length > 0 && allBoxes.every(box => box.checked);
        selectAll.indeterminate = allBoxes.some(box => box.checked) && !selectAll.checked;
    }
}

function renderTimesheetDeleteConfirmation() {
    const modal = ensureTimesheetDeleteModal();
    const count = timesheetDeleteSelection.size;
    modal.innerHTML = `
        <div class="bg-card rounded-2xl shadow-xl border border-border w-full max-w-md">
            <div class="p-6">
                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-100 text-red-600"><i class="fa-solid fa-triangle-exclamation text-xl"></i></div>
                <h3 id="timesheet-delete-title" class="text-lg font-bold text-[#0c2340] text-center">Are you sure you want to delete?</h3>
                <p class="mt-2 text-sm text-slate-500 text-center">You selected ${count} timesheet record${count === 1 ? '' : 's'}. They will be removed from the Manager Team Timesheet Records table. Attendance records and employee schedules will not be deleted.</p>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" data-ts-delete-back class="px-4 py-2 rounded-lg border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50">Cancel</button>
                <button type="button" data-ts-delete-confirm class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-semibold hover:bg-red-700"><i class="fa-solid fa-trash-can mr-1.5"></i>Yes, Delete</button>
            </div>
        </div>`;
}

async function submitTimesheetDelete() {
    const selected = timesheetDeleteModalRows.filter(rec => timesheetDeleteSelection.has(rec.id));
    if (!selected.length) {
        showToast('Select at least one timesheet record to delete.', 'info');
        renderTimesheetDeleteListModal();
        return;
    }

    const confirmButton = document.querySelector('[data-ts-delete-confirm]');
    if (confirmButton) {
        confirmButton.disabled = true;
        confirmButton.textContent = 'Deleting…';
        confirmButton.classList.add('opacity-60', 'cursor-not-allowed');
    }

    try {
        const response = await fetch('/api/timesheet/manager-delete.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ records: selected.map(rec => ({ employeeId: rec.employeeId, date: rec.date })) })
        });
        const result = await response.json().catch(() => ({}));
        if (!response.ok || !result.success) throw new Error(result.message || 'Unable to delete selected timesheet records.');

        closeTimesheetDeleteModal();
        const refreshed = await loadManagerTimesheetsFromDatabase();
        if (refreshed) renderManagerTimesheets();
        showToast(result.message || 'Selected timesheet records deleted.', 'success');
    } catch (error) {
        console.error('Manager timesheet deletion failed:', error);
        showToast(error.message || 'Unable to delete selected timesheet records.', 'error');
        if (confirmButton) {
            confirmButton.disabled = false;
            confirmButton.textContent = 'Yes, Delete';
            confirmButton.classList.remove('opacity-60', 'cursor-not-allowed');
        }
    }
}

// ---- Details modal ----
function ensureTimesheetModal() {
    let modal = document.getElementById('timesheet-modal');
    if (modal) return modal;

    modal = document.createElement('div');
    modal.id = 'timesheet-modal';
    modal.className = 'hidden fixed inset-0 z-[60] flex items-center justify-center bg-primary/40 backdrop-blur-sm p-4';
    modal.setAttribute('role', 'dialog');
    modal.setAttribute('aria-modal', 'true');
    modal.setAttribute('aria-labelledby', 'timesheet-modal-title');
    document.body.appendChild(modal);

    modal.addEventListener('click', event => {
        if (event.target === modal || event.target.closest('[data-ts-close]')) {
            closeTimesheetModal();
            return;
        }
        const actionBtn = event.target.closest('[data-ts-action]');
        if (actionBtn && !actionBtn.disabled) {
            applyTimesheetAction(modal.dataset.recordId, actionBtn.dataset.tsAction);
        }
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') closeTimesheetModal();
    });
    return modal;
}

function closeTimesheetModal() {
    document.getElementById('timesheet-modal')?.classList.add('hidden');
}

function tsCanApprove(rec) {
    if (rec.flagged) return { ok: false, reason: 'Remove the flag before approving.' };
    if (rec.review === 'Approved') return { ok: false, reason: 'This timesheet is already approved.' };
    if (!TS_COMPLETE_STATUSES.includes(rec.status)) {
        return { ok: false, reason: `A ${rec.status.toLowerCase()} timesheet cannot be approved.` };
    }
    return { ok: true, reason: '' };
}

function openTimesheetDetails(recordId) {
    const rec = getTeamTimesheetRecords().find(r => r.id === recordId);
    if (!rec) return;

    const modal = ensureTimesheetModal();
    modal.dataset.recordId = rec.id;

    const approve = tsCanApprove(rec);
    const canFlag = rec.status !== 'Absent' || rec.flagged;
    const field = (label, value) => `
        <div>
            <p class="text-[11px] font-medium text-slate-400 mb-1">${label}</p>
            <div class="text-sm font-mono font-semibold text-slate-800">${value}</div>
        </div>`;
    const timeValue = value => value ? escapeHtml(tsFmtTime(value)) : '<span class="text-slate-400 font-normal">Not recorded</span>';
    const hoursValue = value => rec.hasHours ? `${tsHrs(value)} hrs` : '<span class="text-slate-400 font-normal">—</span>';
    const disabledCls = 'opacity-50 cursor-not-allowed';

    const lateNote = rec.lateMin > TS_LATE_GRACE_MIN ? `<span class="block mt-1 text-[11px] font-normal text-slate-500">Arrived ${rec.lateMin} min after shift start</span>` : '';
    const flagNote = rec.flagged ? '<span class="block mt-1 text-[11px] font-normal text-red-600">Marked incomplete by manager</span>' : '';

    modal.innerHTML = `
        <div class="bg-card rounded-2xl shadow-xl border border-border w-full max-w-2xl max-h-[90vh] flex flex-col">
            <div class="flex items-start justify-between gap-4 px-6 py-5 border-b border-slate-100">
                <div>
                    <h3 id="timesheet-modal-title" class="text-lg font-bold text-[#0c2340]">Timesheet Details</h3>
                    <p class="text-xs text-slate-500 mt-1">${escapeHtml(tsFmtDate(rec.date, true))}</p>
                </div>
                <button type="button" data-ts-close aria-label="Close" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="px-6 py-5 overflow-y-auto">
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-6 gap-y-5">
                    ${field('Employee Name', escapeHtml(rec.employeeName))}
                    ${field('Employee ID', escapeHtml(rec.employeeId))}
                    ${field('Date', escapeHtml(tsFmtDate(rec.date, true)))}
                    ${field('Scheduled Shift', `${escapeHtml(tsFmtTime(rec.scheduleStart))} – ${escapeHtml(tsFmtTime(rec.scheduleEnd))}`)}
                    ${field('Time In', timeValue(rec.timeIn))}
                    ${field('Time Out', timeValue(rec.timeOut))}
                    ${field('Break Duration', rec.status === 'Absent' ? '<span class="text-slate-400 font-normal">—</span>' : `${rec.breakMin} min`)}
                    ${field('Regular Hours', hoursValue(rec.regular))}
                    ${field('Overtime Hours', hoursValue(rec.overtime))}
                    ${field('Total Hours', hoursValue(rec.total))}
                    ${field('Attendance Status', `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${timesheetStatusClass(rec.status)}">${escapeHtml(rec.status)}</span>${lateNote}${flagNote}`)}
                    ${field('Review Status', escapeHtml(tsReviewLabel(rec)))}
                </div>

                <div class="mt-6">
                    <p class="text-[11px] font-medium text-slate-400 mb-1.5">Remarks</p>
                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 text-xs text-gray-500 italic">"${escapeHtml(rec.remarks)}"</div>
                </div>

                <div class="mt-4">
                    <label for="timesheet-manager-note" class="block text-[11px] font-medium text-slate-400 mb-1.5">Manager Notes (optional)</label>
                    <textarea id="timesheet-manager-note" rows="2" maxlength="300" placeholder="Add a note for this timesheet..."
                              class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-accent/40 focus:border-accent resize-none">${escapeHtml(rec.managerNote)}</textarea>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row gap-2">
                <button type="button" data-ts-action="review"
                        class="flex-1 py-2 bg-[#0c2340] hover:bg-slate-800 text-white font-medium text-sm rounded-xl transition">
                    <i class="fa-solid fa-eye text-xs mr-1.5"></i>Review Timesheet
                </button>
                <button type="button" data-ts-action="approve" ${approve.ok ? '' : 'disabled'} title="${escapeHtml(approve.reason)}"
                        class="flex-1 py-2 bg-emerald-500 hover:bg-emerald-600 text-white font-medium text-sm rounded-xl transition ${approve.ok ? '' : disabledCls}">
                    <i class="fa-solid fa-check text-xs mr-1.5"></i>Approve Timesheet
                </button>
                <button type="button" data-ts-action="flag" ${canFlag ? '' : 'disabled'} title="${canFlag ? '' : 'There is no timesheet to flag for an absence.'}"
                        class="flex-1 py-2 border border-red-500 text-red-500 hover:bg-red-50 font-medium text-sm rounded-xl transition ${canFlag ? '' : disabledCls}">
                    <i class="fa-solid fa-flag text-xs mr-1.5"></i>${rec.flagged ? 'Remove Flag' : 'Flag / Mark as Incomplete'}
                </button>
            </div>
        </div>`;

    modal.classList.remove('hidden');
    modal.querySelector('[data-ts-close]')?.focus();
}

async function applyTimesheetAction(recordId, action) {
    const rec = getTeamTimesheetRecords().find(r => r.id === recordId);
    if (!rec) return;

    const note = (document.getElementById('timesheet-manager-note')?.value || '').trim();

    if (action === 'approve') {
        const check = tsCanApprove(rec);
        if (!check.ok) {
            showToast(check.reason, 'info');
            return;
        }
    }

    try {
        const response = await fetch('/api/timesheet/manager.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                employeeId: rec.employeeId,
                date: rec.date,
                action,
                note
            })
        });

        const result = await response.json().catch(() => ({}));
        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Unable to save the timesheet decision.');
        }

        closeTimesheetModal();

        // Hide approved/flagged rows immediately; the authoritative API then
        // reloads the queue so the decision remains applied after a refresh.
        if (action === 'approve' || action === 'flag') {
            managerTimesheetDbRecords = managerTimesheetDbRecords.filter(item => item.id !== rec.id);
        }
        renderManagerTimesheets();

        const tone = action === 'flag' ? 'info' : 'success';
        showToast(result.message || 'Timesheet decision saved.', tone);

        const refreshed = await loadManagerTimesheetsFromDatabase();
        if (refreshed) {
            renderManagerTimesheets();
        }
    } catch (error) {
        console.error('Manager timesheet action failed:', error);
        showToast(error.message || 'Unable to save the timesheet decision.', 'error');
    }
}

// ---- Export PDF report ----
function pdfEscape(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}


function exportTimesheetsPDF() {

    const filters =
        getTimesheetFilters();

    const team =
        getManagerTeam();

    const allTimesheetRows =
        getTeamTimesheetRecords();

    const filteredTimesheetRows =
        applyTimesheetFilters(
            allTimesheetRows,
            filters
        );


    /*
     * Keep the PDF employee list based on the
     * employees assigned to this manager.
     */
    let selectedTeam =
        team.slice();


    /*
     * Apply the employee filter to the assigned team.
     */
    if (filters.member) {

        selectedTeam =
            selectedTeam.filter(
                emp =>
                    String(emp.id) ===
                    String(filters.member)
            );
    }


    /*
     * Apply the search filter to the assigned team.
     */
    if (filters.search) {

        selectedTeam =
            selectedTeam.filter(emp => {

                const name =
                    String(emp.name || '')
                        .toLowerCase();

                const id =
                    String(emp.id || '')
                        .toLowerCase();

                return (
                    name.includes(
                        filters.search
                    ) ||
                    id.includes(
                        filters.search
                    )
                );
            });
    }


    /*
     * Build PDF rows.
     *
     * Existing timesheet records are kept.
     * Employees with no record in the selected
     * period still appear with blank attendance
     * information.
     */
    const rows = [];

    selectedTeam.forEach(emp => {

        const employeeRows =
            filteredTimesheetRows.filter(
                rec =>
                    String(rec.employeeId) ===
                    String(emp.id)
            );


        if (employeeRows.length) {

            employeeRows.forEach(
                rec => rows.push(rec)
            );

        } else {

            rows.push({

                id:
                    `team_${emp.id}`,

                employeeId:
                    String(emp.id),

                employeeName:
                    String(emp.name || '—'),

                position:
                    String(emp.position || ''),

                date:
                    '',

                scheduleStart:
                    null,

                scheduleEnd:
                    null,

                timeIn:
                    null,

                timeOut:
                    null,

                breakMin:
                    0,

                regular:
                    0,

                overtime:
                    0,

                total:
                    0,

                hasHours:
                    false,

                status:
                    'No Record',

                flagged:
                    false,

                review:
                    'Pending',

                managerNote:
                    '',

                calculatedStatus:
                    'No Record'

            });

        }

    });


    /*
     * If there are no assigned employees at all,
     * then there is nothing to export.
     */
    if (!rows.length) {

        showToast(
            'No employees are assigned to this manager.',
            'info'
        );

        return;
    }


    const session =
        managerSession() || {};
    const memberName = filters.member
        ? (team.find(emp => emp.id === filters.member)?.name || filters.member)
        : 'All Team Members';

    const periodLabel = filters.period === 'date' && filters.date
        ? tsFmtDate(filters.date, true)
        : filters.period === 'month'
            ? 'Current Month'
            : filters.period === 'all'
                ? 'All Records'
                : 'Current Week';

    const totalHours = rows.reduce((sum, rec) => sum + (rec.hasHours ? Number(rec.total || 0) : 0), 0);
    const regularHours = rows.reduce((sum, rec) => sum + (rec.hasHours ? Number(rec.regular || 0) : 0), 0);
    const overtimeHours = rows.reduce((sum, rec) => sum + (rec.hasHours ? Number(rec.overtime || 0) : 0), 0);
    const completed = rows.filter(rec => rec.status === 'Completed').length;
    const flagged = rows.filter(rec => rec.flagged).length;
    const approved = rows.filter(rec => rec.review === 'Approved').length;

    const statusClass = status => ({
        'Completed': 'status-completed',
        'Late': 'status-late',
        'In Progress': 'status-progress',
        'Absent': 'status-absent',
        'Overtime': 'status-overtime',
        'Incomplete': 'status-incomplete',
        'No Record': 'status-default'
    }[status] || 'status-default');

    const tableRows = rows.map(rec => `
        <tr>
            <td><strong>${pdfEscape(rec.employeeName)}</strong><small>${pdfEscape(rec.employeeId)}</small></td>
            <td>
    ${rec.date
            ? pdfEscape(
                tsFmtDate(
                    rec.date,
                    true
                )
            )
            : '<span class="muted">—</span>'
        }
</td>

<td>
    ${rec.scheduleStart && rec.scheduleEnd
            ? pdfEscape(
                `${tsFmtTime(rec.scheduleStart)} - ${tsFmtTime(rec.scheduleEnd)}`
            )
            : '<span class="muted">—</span>'
        }
</td>
            <td>${rec.timeIn ? pdfEscape(tsFmtTime(rec.timeIn)) : '<span class="muted">—</span>'}</td>
            <td>${rec.timeOut ? pdfEscape(tsFmtTime(rec.timeOut)) : '<span class="muted">—</span>'}</td>
            <td class="number">${rec.status === 'Absent' ? '—' : pdfEscape(`${rec.breakMin} min`)}</td>
            <td class="number">${rec.hasHours ? tsHrs(rec.regular) : '—'}</td>
            <td class="number">${rec.hasHours ? tsHrs(rec.overtime) : '—'}</td>
            <td class="number strong">${rec.hasHours ? tsHrs(rec.total) : '—'}</td>
            <td><span class="status ${statusClass(rec.status)}">${pdfEscape(rec.status)}</span></td>
            <td>${pdfEscape(tsReviewLabel(rec))}</td>
        </tr>`).join('');

    const reportWindow = window.open('', '_blank', 'width=1200,height=900');
    if (!reportWindow) {
        showToast('Please allow pop-ups to generate the PDF report.', 'info');
        return;
    }

    reportWindow.document.open();
    reportWindow.document.write(`<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Team Timesheet Report</title>
<style>
@page { size: A4 landscape; margin: 12mm; }
* { box-sizing: border-box; }
body { margin:0; font-family:Arial,Helvetica,sans-serif; color:#0c2340; background:#fff; font-size:9px; }
.header { display:flex; justify-content:space-between; align-items:flex-start; border-bottom:2px solid #0c2340; padding-bottom:10px; margin-bottom:12px; }
.brand { font-size:10px; color:#64748b; font-weight:600; letter-spacing:.04em; text-transform:uppercase; }
h1 { margin:3px 0 4px; font-size:22px; line-height:1.1; }
.subtitle { color:#64748b; font-size:10px; }
.meta { text-align:right; color:#475569; font-size:9px; line-height:1.55; }
.summary { display:grid; grid-template-columns:repeat(5,1fr); gap:7px; margin-bottom:12px; }
.card { border:1px solid #dbe3ec; border-radius:6px; padding:8px 10px; background:#f8fafc; }
.card .label { color:#64748b; font-size:8px; text-transform:uppercase; letter-spacing:.05em; }
.card .value { font-size:15px; font-weight:700; margin-top:2px; }
.filters { display:flex; gap:18px; padding:7px 9px; background:#f1f5f9; border:1px solid #e2e8f0; border-radius:5px; margin-bottom:10px; color:#475569; }
table { width:100%; border-collapse:collapse; table-layout:fixed; }
thead { display:table-header-group; }
th { background:#0c2340; color:#fff; text-align:left; font-size:8px; padding:7px 5px; border:1px solid #0c2340; text-transform:uppercase; }
td { padding:6px 5px; border:1px solid #dbe3ec; vertical-align:middle; color:#334155; }
tbody tr:nth-child(even) { background:#f8fafc; }
td small { display:block; color:#94a3b8; font-size:7px; margin-top:1px; }
.number { text-align:right; } .strong { font-weight:700; color:#0c2340; } .muted { color:#94a3b8; }
.status { display:inline-block; padding:2px 5px; border-radius:9px; font-weight:700; font-size:7px; white-space:nowrap; }
.status-completed { background:#dcfce7; color:#166534; }
.status-late { background:#fef3c7; color:#92400e; }
.status-progress { background:#e0f2fe; color:#075985; }
.status-absent { background:#ffe4e6; color:#9f1239; }
.status-overtime { background:#f3e8ff; color:#6b21a8; }
.status-incomplete { background:#ffedd5; color:#9a3412; }
.status-default { background:#f1f5f9; color:#475569; }
.footer { margin-top:10px; padding-top:7px; border-top:1px solid #cbd5e1; color:#64748b; font-size:7px; display:flex; justify-content:space-between; }
</style>
</head>
<body>
<div class="report">
    <div class="header">
        <div>
            <div class="brand">Holiday Travelers Inc.</div>
            <h1>Team Timesheet Report</h1>
            <div class="subtitle">Manager timesheet summary and attendance record</div>
        </div>
        <div class="meta">
            <div><strong>Prepared by:</strong> ${pdfEscape(session.name || 'Manager')}</div>
            <div><strong>Generated:</strong> ${pdfEscape(new Date().toLocaleString())}</div>
        </div>
    </div>
    <div class="summary">
        <div class="card"><div class="label">Total Hours</div><div class="value">${tsHrs(totalHours)} hrs</div></div>
        <div class="card"><div class="label">Regular Hours</div><div class="value">${tsHrs(regularHours)} hrs</div></div>
        <div class="card"><div class="label">Overtime</div><div class="value">${tsHrs(overtimeHours)} hrs</div></div>
        <div class="card"><div class="label">Completed Shifts</div><div class="value">${completed}</div></div>
        <div class="card"><div class="label">Approved / Flagged</div><div class="value">${approved} / ${flagged}</div></div>
    </div>
    <div class="filters">
        <span><strong>Period:</strong> ${pdfEscape(periodLabel)}</span>
        <span><strong>Team Member:</strong> ${pdfEscape(memberName)}</span>
        <span><strong>Employees:</strong> ${selectedTeam.length}</span>
        <span><strong>Rows:</strong> ${rows.length}</span>
    </div>
    <table>
        <thead><tr>
            <th style="width:13%">Employee</th><th style="width:8%">Date</th><th style="width:11%">Schedule</th>
            <th style="width:7%">Time In</th><th style="width:7%">Time Out</th><th style="width:6%">Break</th>
            <th style="width:7%">Regular</th><th style="width:7%">Overtime</th><th style="width:7%">Total</th>
            <th style="width:10%">Status</th><th style="width:10%">Review</th>
        </tr></thead>
        <tbody>${tableRows}</tbody>
    </table>
    <div class="footer">
        <span>Workforce Management System • Timesheet Report</span>
        <span>Select <strong>Save as PDF</strong> in the browser Print dialog.</span>
    </div>
</div>
<script>
window.onload = function () { setTimeout(function () { window.print(); }, 350); };
<\/script>
</body>
</html>`);
    reportWindow.document.close();

    showToast(`PDF report prepared for ${rows.length} timesheet record${rows.length === 1 ? '' : 's'}`, 'success');
}

// ---- Toast (only defined if the page doesn't already provide one) ----
if (typeof window.showToast !== 'function') {
    window.showToast = function (message, type = 'info') {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'fixed bottom-6 right-6 z-[80] flex flex-col gap-2';
            document.body.appendChild(container);
        }

        const icon = type === 'success'
            ? '<i class="fa-solid fa-circle-check text-emerald-500"></i>'
            : '<i class="fa-solid fa-circle-info text-accent"></i>';

        const toast = document.createElement('div');
        toast.className = 'flex items-center gap-2.5 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-700 shadow-lg transition-opacity duration-300';
        toast.innerHTML = `${icon}<span></span>`;
        toast.lastElementChild.textContent = message;
        container.appendChild(toast);

        setTimeout(() => { toast.style.opacity = '0'; }, 2600);
        setTimeout(() => toast.remove(), 3000);
    };
}

// ---- Wiring ----
document.addEventListener('DOMContentLoaded', () => {
    const rerender = () => renderManagerTimesheets();
    const period = document.getElementById('timesheet-period');
    const date = document.getElementById('timesheet-date');

    document.getElementById('timesheet-search')?.addEventListener('input', rerender);
    document.getElementById('timesheet-status')?.addEventListener('change', rerender);
    document.getElementById('timesheet-member')?.addEventListener('change', rerender);

    period?.addEventListener('change', () => {
        if (period.value === 'date') {
            try { date?.showPicker?.(); } catch (err) { date?.focus(); }
        } else if (date) {
            date.value = '';
        }
        rerender();
    });

    date?.addEventListener('change', () => {
        if (period && date.value) period.value = 'date';
        else if (period && period.value === 'date') period.value = 'week';
        rerender();
    });

    document.getElementById('timesheet-reset')?.addEventListener('click', () => {
        ['timesheet-search', 'timesheet-date', 'timesheet-status', 'timesheet-member'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        if (period) period.value = 'week';
        rerender();
    });

    document.getElementById('manager-timesheet-body')?.addEventListener('click', event => {
        const btn = event.target.closest('[data-ts-view]');
        if (btn) openTimesheetDetails(btn.dataset.tsView);
    });
    document.getElementById('timesheet-delete-open')?.addEventListener('click', openTimesheetDeleteModal);
    document
        .getElementById(
            'manager-file-leave-btn'
        )
        ?.addEventListener(
            'click',
            openManagerLeaveRequestModal
        );

    document
        .getElementById(
            'manager-close-leave-modal'
        )
        ?.addEventListener(
            'click',
            closeManagerLeaveRequestModal
        );

    document
        .getElementById(
            'manager-cancel-leave-btn'
        )
        ?.addEventListener(
            'click',
            closeManagerLeaveRequestModal
        );

    document
        .getElementById(
            'manager-leave-request-form'
        )
        ?.addEventListener(
            'submit',
            submitManagerLeaveRequest
        );
});


window.renderManagerTimesheets = renderManagerTimesheets;
window.openTimesheetDetails = openTimesheetDetails;
window.exportTimesheetsPDF = exportTimesheetsPDF;
