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

    const closeEmployeeLeaveModal = () => {
        requestLeaveModal?.classList.add('hidden');
        requestLeaveForm?.reset();
    };

    if (openRequestLeaveBtn && requestLeaveModal) {
        const openModal = () => {
            requestLeaveModal.classList.remove('hidden');
        };


        openRequestLeaveBtn.addEventListener('click', openModal);
        closeRequestLeaveBtn?.addEventListener(
            'click',
            closeEmployeeLeaveModal
        );

        cancelRequestLeaveBtn?.addEventListener(
            'click',
            closeEmployeeLeaveModal
        );

        requestLeaveBackdrop?.addEventListener(
            'click',
            closeEmployeeLeaveModal
        );

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

    function renderEmployeeAttendanceShared() {
        if (!window.SharedData) return;
        const employee = getCurrentEmployeeRecord();
        if (!employee) return;

        const record = employeeTodayRecord();
        const statusEl = document.getElementById('employee-attendance-status');
        const timeInEl = document.getElementById('employee-time-in');
        const clockOutEl = document.getElementById('employee-clock-out-time');
        const clockOutBtn = document.getElementById('clock-out-btn');
        const clockInBtn = document.getElementById('clock-in-btn');
        const historyBody = document.getElementById('employee-attendance-history-body');

        if (statusEl) {
            const status = record?.status || 'Not Clocked In';
            const dot = ['Present', 'Late'].includes(status) ? 'bg-success' : 'bg-gray-400';
            statusEl.innerHTML = `<span class="h-2 w-2 rounded-full ${dot}"></span>${status}`;
        }
        if (timeInEl) timeInEl.textContent = record?.clockIn || '--:--';
        if (clockOutEl) clockOutEl.textContent = record?.clockOut || '05:00 PM';

        if (clockInBtn) {
            const alreadyClockedIn = !!record?.clockIn && !record?.clockOut;
            clockInBtn.classList.toggle('hidden', alreadyClockedIn);
        }
        if (clockOutBtn) {
            const canClockOut = !!record?.clockIn && !record?.clockOut;
            clockOutBtn.classList.toggle('hidden', !canClockOut);
        }

        if (historyBody) {
            const rows = SharedData.getAttendance()
                .filter(r => r.employeeId === employee.id)
                .sort((a, b) => String(b.date).localeCompare(String(a.date)));

            historyBody.innerHTML = rows.length ? rows.map(r => `
                <tr class="hover:bg-background transition-colors">
                    <td class="px-6 py-3.5 text-primary font-medium">${escapeHtmlEmployee(r.date)}</td>
                    <td class="px-6 py-3.5 text-gray-500">${escapeHtmlEmployee(r.clockIn || '--')}</td>
                    <td class="px-6 py-3.5 text-gray-400">${escapeHtmlEmployee(r.clockOut || '--')}</td>
                    <td class="px-6 py-3.5">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium ${employeeStatusClass(r.status)}">
                            <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                            ${escapeHtmlEmployee(r.status || 'Unknown')}
                        </span>
                    </td>
                </tr>
            `).join('') : `
                <tr><td colspan="4" class="px-6 py-8 text-center text-gray-400">No attendance records yet.</td></tr>`;
        }
    }

    async function loadEmployeeTodayAttendanceFromDatabase() {
        try {

            const response = await fetch(
                'api/attendance/my-clock.php',
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
                    'Unable to load today attendance.'
                );
            }

            const attendance =
                result.data?.attendance || null;

            const schedule =
                result.data?.schedule || null;

            const statusEl =
                document.getElementById(
                    'employee-attendance-status'
                );

            const timeInEl =
                document.getElementById(
                    'employee-time-in'
                );

            const clockOutEl =
                document.getElementById(
                    'employee-clock-out-time'
                );

            const workTimeEl =
                document.getElementById(
                    'employee-current-work-time'
                );

            const clockInBtn =
                document.getElementById(
                    'clock-in-btn'
                );

            const clockOutBtn =
                document.getElementById(
                    'clock-out-btn'
                );

            const status =
                String(
                    attendance?.status || ''
                ).toLowerCase();

            let statusLabel = 'Not Clocked In';
            let statusClass = 'bg-gray-400';

            if (status === 'present') {
                statusLabel = 'Present';
                statusClass = 'bg-success';
            }

            if (status === 'late') {
                statusLabel = 'Late';
                statusClass = 'bg-warning';
            }

            if (status === 'on_leave') {
                statusLabel = 'Leave';
                statusClass = 'bg-purple-500';
            }

            if (statusEl) {
                statusEl.innerHTML =
                    `<span class="h-2 w-2 rounded-full ${statusClass}"></span>` +
                    statusLabel;
            }

            if (timeInEl) {
                timeInEl.textContent =
                    attendance?.time_in
                        ? formatEmployeeTime(
                            attendance.time_in
                        )
                        : '--:--';
            }

            if (clockOutEl) {
                clockOutEl.textContent =
                    schedule?.end_time
                        ? formatEmployeeTime(
                            `2000-01-01 ${schedule.end_time}`
                        )
                        : '--:--';
            }

            if (workTimeEl) {

                if (
                    attendance?.time_in
                ) {

                    const start =
                        new Date(
                            attendance.time_in.replace(
                                ' ',
                                'T'
                            )
                        );

                    const end =
                        attendance.time_out
                            ? new Date(
                                attendance.time_out.replace(
                                    ' ',
                                    'T'
                                )
                            )
                            : new Date();

                    const minutes =
                        Math.max(
                            0,
                            Math.floor(
                                (end - start) / 60000
                            )
                        );

                    const hours =
                        Math.floor(minutes / 60);

                    const mins =
                        minutes % 60;

                    workTimeEl.textContent =
                        `${String(hours).padStart(2, '0')}h ` +
                        `${String(mins).padStart(2, '0')}m`;

                } else {

                    workTimeEl.textContent =
                        '--h --m';
                }
            }

            if (clockInBtn) {
                clockInBtn.disabled =
                    !!attendance?.time_in;

                clockInBtn.classList.toggle(
                    'opacity-50',
                    clockInBtn.disabled
                );

                clockInBtn.classList.toggle(
                    'cursor-not-allowed',
                    clockInBtn.disabled
                );
            }

            if (clockOutBtn) {

                clockOutBtn.disabled =
                    !attendance?.time_in ||
                    !!attendance?.time_out;

                clockOutBtn.classList.toggle(
                    'opacity-50',
                    clockOutBtn.disabled
                );

                clockOutBtn.classList.toggle(
                    'cursor-not-allowed',
                    clockOutBtn.disabled
                );
            }

        } catch (error) {

            console.error(
                'Employee today attendance error:',
                error
            );
        }
    }

    async function loadEmployeeAttendanceFromDatabase() {

        const historyBody =
            document.getElementById('employee-attendance-history-body');

        if (!historyBody) return;

        try {

            const response = await fetch(
                'api/attendance/my-records.php',
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
                    'Unable to load attendance history.'
                );
            }

            const records = result.records || [];

            historyBody.innerHTML = '';

            if (records.length === 0) {

                historyBody.innerHTML = `
                <tr>
                    <td
                        colspan="4"
                        class="px-6 py-8 text-center text-gray-400">
                        No attendance records yet.
                    </td>
                </tr>
            `;

                return;
            }

            records.forEach(record => {

                let status = String(
                    record.status || 'unknown'
                ).toLowerCase();

                let statusLabel = 'Present';
                let statusClass = 'bg-green-100 text-green-700';

                if (status === 'late') {
                    statusLabel = 'Late';
                    statusClass = 'bg-amber-100 text-amber-700';
                }

                if (
                    status === 'absent'
                ) {
                    statusLabel = 'Absent';
                    statusClass = 'bg-red-100 text-red-700';
                }

                if (
                    status === 'on_leave' ||
                    status === 'leave'
                ) {
                    statusLabel = 'Leave';
                    statusClass = 'bg-purple-100 text-purple-700';
                }

                const row =
                    document.createElement('tr');

                row.className =
                    'hover:bg-background transition-colors';

                row.innerHTML = `
                <td class="px-6 py-3.5 text-primary font-medium">
                    ${escapeHtmlEmployee(
                    record.attendance_date
                )}
                </td>

                <td class="px-6 py-3.5 text-gray-500">
                    ${record.time_in
                        ? escapeHtmlEmployee(
                            formatEmployeeTime(record.time_in)
                        )
                        : '--'
                    }
                </td>

                <td class="px-6 py-3.5 text-gray-500">
                    ${record.time_out
                        ? escapeHtmlEmployee(
                            formatEmployeeTime(record.time_out)
                        )
                        : '--'
                    }
                </td>

                <td class="px-6 py-3.5">
                    <span
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium ${statusClass}">

                        <span
                            class="h-1.5 w-1.5 rounded-full bg-current">
                        </span>

                        ${statusLabel}

                    </span>
                </td>
            `;

                historyBody.appendChild(row);
            });

        } catch (error) {

            console.error(
                'Employee attendance loading error:',
                error
            );

            historyBody.innerHTML = `
            <tr>
                <td
                    colspan="4"
                    class="px-6 py-8 text-center text-red-500">
                    Unable to load attendance history.
                </td>
            </tr>
        `;
        }
    }

    function formatEmployeeTime(value) {

        const date = new Date(
            value.replace(' ', 'T')
        );

        if (Number.isNaN(date.getTime())) {
            return value;
        }

        return date.toLocaleTimeString(
            [],
            {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            }
        );
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
        try {

            const response = await fetch(
                'api/attendance/my-clock.php',
                {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'time_in'
                    })
                }
            );

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(
                    result.message ||
                    'Unable to clock in.'
                );
            }

            showToastEmployee(
                result.message,
                result.status === 'late'
                    ? 'info'
                    : 'success'
            );

            await loadEmployeeTodayAttendanceFromDatabase();
            await loadEmployeeAttendanceFromDatabase();

        } catch (error) {

            showToastEmployee(
                error.message ||
                'Unable to clock in.',
                'error'
            );
        }
    }

    async function clockOutEmployee() {
        try {

            const response = await fetch(
                'api/attendance/my-clock.php',
                {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'time_out'
                    })
                }
            );

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(
                    result.message ||
                    'Unable to clock out.'
                );
            }

            showToastEmployee(
                result.message,
                'success'
            );

            await loadEmployeeTodayAttendanceFromDatabase();
            await loadEmployeeAttendanceFromDatabase();

        } catch (error) {

            showToastEmployee(
                error.message ||
                'Unable to clock out.',
                'error'
            );
        }
    }

    async function handleEmployeeLeaveSubmit(event) {
        event.preventDefault();

        const leaveType =
            document.getElementById('leave-type')?.value || '';

        const startDate =
            document.getElementById('leave-start')?.value || '';

        const endDate =
            document.getElementById('leave-end')?.value || '';

        const reason =
            document.getElementById('leave-reason')?.value || '';

        let response;
        let data;

        /*
         * Only the actual server request is inside
         * this try/catch.
         */
        try {
            response = await fetch(
                'api/leave/create.php',
                {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        leave_type: leaveType,
                        start_date: startDate,
                        end_date: endDate,
                        reason: reason
                    })
                }
            );

            data = await response.json();

        } catch (error) {

            console.error(
                'Leave request server error:',
                error
            );

            showToastEmployee(
                'Unable to connect to the server.',
                'error'
            );

            return;
        }

        /*
         * Server responded, but rejected the request.
         */
        if (!response.ok || !data.success) {

            showToastEmployee(
                data.message ||
                'Unable to submit leave request.',
                'error'
            );

            return;
        }

        /*
         * The request was successfully saved.
         */
        showToastEmployee(
            'Leave request submitted successfully.',
            'success'
        );

        /*
         * Close the modal.
         */
        closeEmployeeLeaveModal();

        /*
         * Clear the form.
         */
        const form = event.target;

        if (form) {
            form.reset();
        }

        /*
         * Refresh the leave history separately.
         * An error here must NOT make the successful
         * submission look like a server failure.
         */
        try {

            await loadMyLeaveRequests();

        } catch (error) {

            console.error(
                'Leave history refresh error:',
                error
            );
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

    async function loadMyLeaveRequests() {
        const body = document.getElementById('employee-leave-requests-body');
        if (!body) return;

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

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(
                    data.message || 'Unable to load leave requests.'
                );
            }

            const requests = data.requests || [];

            body.innerHTML = '';

            if (requests.length === 0) {
                body.innerHTML = `
                <tr>
                    <td colspan="4"
                        class="px-6 py-8 text-center text-gray-400">
                        No leave requests yet.
                    </td>
                </tr>
            `;
                return;
            }

            requests.forEach(request => {
                const startDate = String(request.start_date || '');
                const endDate = String(request.end_date || '');

                const dates =
                    startDate === endDate
                        ? startDate
                        : `${startDate} – ${endDate}`;

                const days = calculateLeaveDays(
                    startDate,
                    endDate
                );

                let status = String(
                    request.status || 'pending'
                ).toLowerCase();

                let statusLabel = 'Pending';
                let statusClass = 'bg-amber-100 text-amber-700';

                if (status === 'approved') {
                    statusLabel = 'Approved';
                    statusClass = 'bg-green-100 text-green-700';
                }

                if (
                    status === 'rejected' ||
                    status === 'declined'
                ) {
                    statusLabel = 'Rejected';
                    statusClass = 'bg-red-100 text-red-700';
                }

                if (status === 'cancelled') {
                    statusLabel = 'Cancelled';
                    statusClass = 'bg-gray-100 text-gray-600';
                }

                const row = document.createElement('tr');

                row.className =
                    'hover:bg-background transition-colors';

                row.innerHTML = `
                <td class="px-6 py-3.5 text-primary font-medium">
                    ${escapeHtmlEmployee(request.leave_type)}
                </td>

                <td class="px-6 py-3.5 text-gray-500">
                    ${escapeHtmlEmployee(dates)}
                </td>

                <td class="px-6 py-3.5 text-gray-500">
                    ${days}
                </td>

                <td class="px-6 py-3.5">
                    <span
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium ${statusClass}">
                        <span
                            class="h-1.5 w-1.5 rounded-full bg-current">
                        </span>
                        ${statusLabel}
                    </span>
                </td>
            `;

                body.appendChild(row);
            });

        } catch (error) {
            console.error(
                'Load leave requests error:',
                error
            );

            body.innerHTML = `
            <tr>
                <td colspan="4"
                    class="px-6 py-8 text-center text-red-500">
                    Unable to load leave requests.
                </td>
            </tr>
        `;
        }
    }

    function calculateLeaveDays(startDate, endDate) {
        if (!startDate || !endDate) return 0;

        const start = new Date(`${startDate}T00:00:00`);
        const end = new Date(`${endDate}T00:00:00`);

        if (
            Number.isNaN(start.getTime()) ||
            Number.isNaN(end.getTime())
        ) {
            return 0;
        }

        const difference =
            end.getTime() - start.getTime();

        return Math.floor(
            difference / (1000 * 60 * 60 * 24)
        ) + 1;
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

    async function loadEmployeeScheduleFromDatabase() {
        try {
            const response = await fetch(
                'api/schedules/my.php',
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
                    'Unable to load employee schedule.'
                );
            }

            renderEmployeeScheduleFromDatabase(result);

        } catch (error) {

            console.error(
                'Employee schedule loading error:',
                error
            );

            const dayGrid =
                document.getElementById(
                    'employee-schedule-day-grid'
                );

            const overviewGrid =
                document.getElementById(
                    'employee-schedule-overview-grid'
                );

            if (dayGrid) {
                dayGrid.innerHTML = `
                <div class="col-span-full py-8 text-center text-red-500">
                    Unable to load schedule.
                </div>
            `;
            }

            if (overviewGrid) {
                overviewGrid.innerHTML = `
                <div class="col-span-full py-8 text-center text-red-500">
                    Unable to load schedule.
                </div>
            `;
            }
        }
    }

    function renderEmployeeScheduleFromDatabase(result) {

        const days = Array.isArray(result.days)
            ? result.days
            : [];

        const weekLabel =
            result.weekLabel || '';

        const weekLabelEl =
            document.getElementById(
                'employee-schedule-week-label'
            );

        const overviewLabelEl =
            document.getElementById(
                'employee-schedule-overview-label'
            );

        const dayGrid =
            document.getElementById(
                'employee-schedule-day-grid'
            );

        const overviewGrid =
            document.getElementById(
                'employee-schedule-overview-grid'
            );

        if (weekLabelEl) {
            weekLabelEl.textContent = weekLabel;
        }

        if (overviewLabelEl) {
            overviewLabelEl.textContent = weekLabel;
        }

        if (dayGrid) {

            const weekdays =
                days.filter(day =>
                    day.dayNumber >= 1 &&
                    day.dayNumber <= 5
                );

            dayGrid.innerHTML =
                weekdays.map(day => {

                    const off = day.isOff;

                    const cardClass = off
                        ? 'border border-dashed border-border rounded-xl p-4 bg-background/60'
                        : 'border border-dashed border-border rounded-xl p-4';

                    const nameClass = off
                        ? 'text-sm font-semibold text-gray-400'
                        : 'text-sm font-semibold text-primary';

                    const timeClass = off
                        ? 'text-sm text-gray-400'
                        : 'text-sm text-gray-500';

                    const shiftText =
                        off
                            ? 'Day Off'
                            : day.shiftName;

                    const timeText =
                        off || !day.startTime || !day.endTime
                            ? '&mdash;'
                            : `${escapeHtmlEmployee(
                                employeeScheduleTime(day.startTime)
                            )} &ndash; ${escapeHtmlEmployee(
                                employeeScheduleTime(day.endTime)
                            )}`;

                    const positionText =
                        off
                            ? '&nbsp;'
                            : escapeHtmlEmployee(
                                day.position || 'Office / Operations'
                            );

                    return `
                    <div class="${cardClass}">
                        <p class="text-xs font-semibold text-primary uppercase tracking-wide">
                            ${escapeHtmlEmployee(day.day)}
                        </p>

                        <p class="text-xs text-gray-400 mb-3">
                            ${escapeHtmlEmployee(
                        employeeScheduleDate(day.date)
                    )}
                        </p>

                        <p class="${nameClass}">
                            ${escapeHtmlEmployee(shiftText)}
                        </p>

                        <p class="${timeClass}">
                            ${timeText}
                        </p>

                        <p class="text-xs text-gray-400 mt-1">
                            ${positionText}
                        </p>
                    </div>
                `;
                }).join('');
        }

        if (overviewGrid) {

            overviewGrid.innerHTML =
                days.map(day => {

                    const off = day.isOff;

                    const outerClass = off
                        ? 'rounded-xl border border-border overflow-hidden bg-background/60 hover:shadow-md hover:-translate-y-0.5 transition-all-300'
                        : 'rounded-xl border border-border overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition-all-300';

                    const initials =
                        result.employee?.name
                            ? result.employee.name
                                .trim()
                                .split(/\s+/)
                                .map(part => part.charAt(0))
                                .slice(0, 1)
                                .join('')
                                .toUpperCase()
                            : 'E';

                    const avatarClass = off
                        ? 'h-7 w-7 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center text-[11px] font-bold'
                        : 'h-7 w-7 rounded-full bg-accent/15 text-accent flex items-center justify-center text-[11px] font-bold';

                    const badgeClass = off
                        ? 'inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-400'
                        : 'inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-600';

                    const badgeText =
                        off
                            ? 'OFF'
                            : employeeScheduleShortShift(day);

                    return `
                    <div class="${outerClass}">
                        <div class="bg-background px-2 py-2 text-center border-b border-border">
                            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">
                                ${escapeHtmlEmployee(day.shortDay)}
                            </p>

                            <p class="text-sm font-bold text-primary">
                                ${day.dateNumber}
                            </p>
                        </div>

                        <div class="p-3 flex flex-col items-center gap-2">
                            <div class="${avatarClass}">
                                ${escapeHtmlEmployee(initials)}
                            </div>

                            <span class="${badgeClass}">
                                ${escapeHtmlEmployee(badgeText)}
                            </span>
                        </div>
                    </div>
                `;
                }).join('');
        }
    }

    function employeeScheduleTime(value) {

        const parts =
            String(value || '').split(':');

        if (parts.length < 2) {
            return value;
        }

        let hour = Number(parts[0]);
        const minute = Number(parts[1]);

        if (
            Number.isNaN(hour) ||
            Number.isNaN(minute)
        ) {
            return value;
        }

        const suffix =
            hour >= 12 ? 'PM' : 'AM';

        const displayHour =
            hour % 12 || 12;

        return `${String(displayHour).padStart(2, '0')}:${String(minute).padStart(2, '0')} ${suffix}`;
    }

    function employeeScheduleDate(value) {

        const date =
            new Date(`${value}T00:00:00`);

        if (Number.isNaN(date.getTime())) {
            return value;
        }

        return date.toLocaleDateString(
            'en-US',
            {
                month: 'short',
                day: 'numeric'
            }
        );
    }

    function employeeScheduleShortShift(day) {

        const shift =
            String(day.shiftName || '');

        if (
            day.startTime &&
            day.endTime
        ) {
            const startHour =
                Number(
                    String(day.startTime).split(':')[0]
                );

            const endHour =
                Number(
                    String(day.endTime).split(':')[0]
                );

            if (
                !Number.isNaN(startHour) &&
                !Number.isNaN(endHour)
            ) {
                return `${startHour}-${endHour}`;
            }
        }

        return shift;
    }

    async function loadEmployeeGrowthFromDatabase() {

        try {

            const response = await fetch(
                'api/growth/my.php',
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
                    'Unable to load My Growth.'
                );
            }

            const data = result.data || {};

            initAttendanceTrendChart(
                data.attendance_trend || []
            );

            // Employee Metrics - Attendance Rate
            const metricAttendance =
                document.getElementById(
                    'employee-metric-attendance-rate'
                );

            if (metricAttendance) {
                metricAttendance.textContent =
                    `${Number(
                        data.attendance_rate || 0
                    ).toFixed(1)}%`;
            }

            // Employee Metrics - Total Hours Worked
            const metricWorkHours =
                document.getElementById(
                    'employee-metric-work-hours'
                );

            if (metricWorkHours) {
                metricWorkHours.textContent =
                    `${Number(
                        data.work_hours || 0
                    ).toFixed(2)} hrs`;
            }

            // Employee Metrics - Overtime Hours
            const metricOvertimeHours =
                document.getElementById(
                    'employee-metric-overtime-hours'
                );

            if (metricOvertimeHours) {
                metricOvertimeHours.textContent =
                    `${Number(
                        data.overtime_hours || 0
                    ).toFixed(2)} hrs`;
            }

            // Employee Metrics - Late Occurrences
            const metricLateOccurrences =
                document.getElementById(
                    'employee-metric-late-occurrences'
                );

            if (metricLateOccurrences) {
                metricLateOccurrences.textContent =
                    String(
                        Number(
                            data.late_records || 0
                        )
                    );
            }

            // Employee Metrics - Leave Used
            const metricLeaveUsed =
                document.getElementById(
                    'employee-metric-leave-used'
                );

            if (metricLeaveUsed) {
                metricLeaveUsed.textContent =
                    `${Number(
                        data.leave_used_days || 0
                    )} days`;
            }

            // Employee Metrics - Timesheet Completion
            const metricTimesheetCompletion =
                document.getElementById(
                    'employee-metric-timesheet-completion'
                );

            if (metricTimesheetCompletion) {
                metricTimesheetCompletion.textContent =
                    `${Number(
                        data.timesheet_completion_rate || 0
                    ).toFixed(1)}%`;
            }

            // Period
            const periodEl =
                document.getElementById(
                    'employee-growth-period'
                );

            if (periodEl) {

                periodEl.textContent =
                    data.period_start && data.period_end
                        ? `${data.period_start} to ${data.period_end}`
                        : 'Current Year';
            }

            // Attendance
            const attendanceRate =
                document.getElementById(
                    'employee-growth-attendance-rate'
                );

            if (attendanceRate) {

                attendanceRate.textContent =
                    `${Number(
                        data.attendance_rate || 0
                    ).toFixed(1)}%`;
            }

            // Work Hours
            const workHours =
                document.getElementById(
                    'employee-growth-work-hours'
                );

            if (workHours) {

                workHours.textContent =
                    `${Number(
                        data.work_hours || 0
                    ).toFixed(2)} hrs`;
            }

            // Overtime
            const overtimeHours =
                document.getElementById(
                    'employee-growth-overtime-hours'
                );

            if (overtimeHours) {

                overtimeHours.textContent =
                    `${Number(
                        data.overtime_hours || 0
                    ).toFixed(2)} hrs`;
            }

            // Leave Used
            const leaveUsed =
                document.getElementById(
                    'employee-growth-leave-used'
                );

            if (leaveUsed) {

                leaveUsed.textContent =
                    `${Number(
                        data.leave_used_days || 0
                    )} days`;
            }

        } catch (error) {

            console.error(
                'Employee My Growth error:',
                error
            );
        }
    }

    async function loadEmployeeTimesheetFromDatabase() {
        try {
            const response = await fetch(
                'api/timesheet/my.php',
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
                    'Unable to load employee timesheet.'
                );
            }

            renderEmployeeTimesheetFromDatabase(result);

        } catch (error) {
            console.error(
                'Employee timesheet loading error:',
                error
            );

            const daily =
                document.getElementById(
                    'employee-timesheet-daily'
                );

            if (daily) {
                daily.innerHTML = `
                <div class="py-6 text-center text-red-500">
                    Unable to load timesheet.
                </div>
            `;
            }
        }
    }

    function renderEmployeeTimesheetFromDatabase(result) {

        const days =
            Array.isArray(result.days)
                ? result.days
                : [];

        const summary =
            result.summary || {};

        const review =
            result.review || {};

        const period =
            result.period || {};

        const statusEl =
            document.getElementById(
                'employee-timesheet-status'
            );

        const periodEl =
            document.getElementById(
                'employee-timesheet-period'
            );

        const dailyEl =
            document.getElementById(
                'employee-timesheet-daily'
            );

        const regularEl =
            document.getElementById(
                'employee-timesheet-regular'
            );

        const overtimeEl =
            document.getElementById(
                'employee-timesheet-overtime'
            );

        const totalEl =
            document.getElementById(
                'employee-timesheet-total'
            );

        if (periodEl) {
            periodEl.textContent =
                `Current Period · ${period.label || ''
                }`;
        }

        if (statusEl) {

            const status =
                String(
                    review.label ||
                    'Pending Approval'
                );

            let classes =
                'bg-amber-100 text-amber-700';

            if (
                String(
                    review.status || ''
                ).toLowerCase() === 'approved'
            ) {
                classes =
                    'bg-green-100 text-green-700';
            }

            if (
                String(
                    review.status || ''
                ).toLowerCase() === 'rejected'
            ) {
                classes =
                    'bg-red-100 text-red-700';
            }

            statusEl.className =
                `inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium ${classes}`;

            statusEl.innerHTML = `
            <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
            ${escapeHtmlEmployee(status)}
        `;
        }

        if (dailyEl) {

            const weekdays =
                days.filter(day =>
                    [
                        'Monday',
                        'Tuesday',
                        'Wednesday',
                        'Thursday',
                        'Friday'
                    ].includes(day.day)
                );

            dailyEl.innerHTML =
                weekdays.map(day => {

                    const hours =
                        Number(
                            day.totalHours || 0
                        );

                    const label =
                        hours === 1
                            ? '1h'
                            : `${hours}h`;

                    return `
                    <div class="flex items-center justify-between py-3">
                        <span class="text-sm text-gray-500">
                            ${escapeHtmlEmployee(day.day)}
                        </span>

                        <div class="text-right">
                            <span class="text-sm font-semibold text-primary">
                                ${escapeHtmlEmployee(label)}
                            </span>

                            ${day.status &&
                            day.status !== 'Completed' &&
                            day.status !== 'Day Off'
                            ? `
                                        <span class="block text-[11px] text-gray-400">
                                            ${escapeHtmlEmployee(day.status)}
                                        </span>
                                      `
                            : ''
                        }
                        </div>
                    </div>
                `;
                }).join('');
        }

        if (regularEl) {
            regularEl.textContent =
                `${Number(
                    summary.regularHours || 0
                )}h`;
        }

        if (overtimeEl) {
            overtimeEl.textContent =
                `${Number(
                    summary.overtimeHours || 0
                )}h`;
        }

        if (totalEl) {
            totalEl.textContent =
                `${Number(
                    summary.totalHours || 0
                )}h`;
        }
    }

    function openEmployeeShiftRequestModal() {
        let modal =
            document.getElementById(
                'employee-shift-request-modal'
            );

        if (!modal) {

            modal = document.createElement('div');

            modal.id =
                'employee-shift-request-modal';

            modal.className =
                'hidden fixed inset-0 z-[70] flex items-center justify-center bg-primary/40 backdrop-blur-sm p-4';

            modal.innerHTML = `
            <div class="bg-card rounded-2xl shadow-xl border border-border w-full max-w-lg max-h-[90vh] overflow-hidden">

                <div class="flex items-start justify-between px-6 py-5 border-b border-border">
                    <div>
                        <h3 class="text-lg font-bold text-primary">
                            Request Shift Change
                        </h3>

                        <p class="text-xs text-gray-400 mt-1">
                            Submit a request for your Manager to review.
                        </p>
                    </div>

                    <button
                        type="button"
                        id="employee-shift-request-close"
                        class="w-8 h-8 rounded-lg text-gray-400 hover:bg-background hover:text-gray-600 transition-colors">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="px-6 py-5 overflow-y-auto max-h-[70vh]">

                    <form id="employee-shift-request-form">

                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                                Requested Date
                            </label>

                            <input
                                id="employee-shift-request-date"
                                type="date"
                                required
                                class="w-full rounded-lg border border-border bg-white px-3 py-2.5 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-accent/40 focus:border-accent">
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                                Requested Shift
                            </label>

                            <select
                                id="employee-shift-request-shift"
                                required
                                class="w-full rounded-lg border border-border bg-white px-3 py-2.5 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-accent/40 focus:border-accent">
                                <option value="8-5">8-5</option>
                                <option value="9-6">9-6</option>
                                <option value="10-7">10-7</option>
                                <option value="OFF">OFF</option>
                                <option value="__custom__">Custom shift...</option>
                            </select>
                        </div>

                        <div
                            id="employee-shift-request-custom-wrap"
                            class="hidden mb-4">

                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                                Custom Shift
                            </label>

                            <input
                                id="employee-shift-request-custom"
                                type="text"
                                maxlength="40"
                                placeholder="Example: 7:30-16:30"
                                class="w-full rounded-lg border border-border bg-white px-3 py-2.5 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-accent/40 focus:border-accent">
                        </div>

                        <div class="mb-5">
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                                Reason
                            </label>

                            <textarea
                                id="employee-shift-request-reason"
                                rows="4"
                                maxlength="500"
                                placeholder="Explain why you are requesting this shift change."
                                class="w-full rounded-lg border border-border bg-white px-3 py-2.5 text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-accent/40 focus:border-accent resize-none"></textarea>
                        </div>

                        <button
                            type="submit"
                            class="w-full py-2.5 bg-primary hover:bg-slate-800 text-white font-semibold text-sm rounded-xl transition-colors">
                            Submit Shift Request
                        </button>

                    </form>

                    <div class="mt-6 pt-5 border-t border-border">

                        <h4 class="text-sm font-bold text-primary mb-3">
                            My Shift Requests
                        </h4>

                        <div
                            id="employee-shift-request-history"
                            class="space-y-3">
                            <p class="text-xs text-gray-400">
                                Loading...
                            </p>
                        </div>

                    </div>

                </div>

            </div>
        `;

            document.body.appendChild(modal);

            document
                .getElementById(
                    'employee-shift-request-close'
                )
                ?.addEventListener(
                    'click',
                    closeEmployeeShiftRequestModal
                );

            modal.addEventListener(
                'click',
                event => {
                    if (event.target === modal) {
                        closeEmployeeShiftRequestModal();
                    }
                }
            );

            document
                .getElementById(
                    'employee-shift-request-form'
                )
                ?.addEventListener(
                    'submit',
                    submitEmployeeShiftRequest
                );

            document
                .getElementById(
                    'employee-shift-request-shift'
                )
                ?.addEventListener(
                    'change',
                    event => {

                        const customWrap =
                            document.getElementById(
                                'employee-shift-request-custom-wrap'
                            );

                        if (!customWrap) return;

                        customWrap.classList.toggle(
                            'hidden',
                            event.target.value !== '__custom__'
                        );

                        if (
                            event.target.value === '__custom__'
                        ) {
                            document
                                .getElementById(
                                    'employee-shift-request-custom'
                                )
                                ?.focus();
                        }
                    }
                );
        }

        modal.classList.remove('hidden');

        loadEmployeeShiftRequests();
    }

    function closeEmployeeShiftRequestModal() {

        document
            .getElementById(
                'employee-shift-request-modal'
            )
            ?.classList.add('hidden');
    }

    async function loadEmployeeShiftRequests() {

        const history =
            document.getElementById(
                'employee-shift-request-history'
            );

        if (!history) return;

        try {

            const response =
                await fetch(
                    'api/shift-requests/my.php',
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
                    'Unable to load shift requests.'
                );
            }

            const requests =
                Array.isArray(result.data)
                    ? result.data
                    : [];

            if (!requests.length) {

                history.innerHTML = `
                <p class="text-xs text-gray-400">
                    No shift requests yet.
                </p>
            `;

                return;
            }

            history.innerHTML =
                requests.map(request => {

                    const status =
                        String(
                            request.status ||
                            'pending'
                        ).toLowerCase();

                    let statusClass =
                        'bg-amber-100 text-amber-700';

                    if (
                        status === 'approved'
                    ) {
                        statusClass =
                            'bg-green-100 text-green-700';
                    }

                    if (
                        status === 'rejected'
                    ) {
                        statusClass =
                            'bg-red-100 text-red-700';
                    }

                    return `
                    <div class="border border-border rounded-xl p-3">

                        <div class="flex items-center justify-between gap-3">

                            <div>
                                <p class="text-sm font-semibold text-primary">
                                    ${escapeHtmlEmployee(
                        request.requested_date
                    )}
                                </p>

                                <p class="text-xs text-gray-400 mt-0.5">
                                    Requested shift:
                                    ${escapeHtmlEmployee(
                        request.requested_shift
                    )}
                                </p>
                            </div>

                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-semibold ${statusClass}">
                                ${escapeHtmlEmployee(
                        status.charAt(0).toUpperCase() +
                        status.slice(1)
                    )}
                            </span>

                        </div>

                        ${request.reason
                            ? `
                                    <p class="text-xs text-gray-500 mt-2">
                                        ${escapeHtmlEmployee(
                                request.reason
                            )}
                                    </p>
                                  `
                            : ''
                        }

                    </div>
                `;
                }).join('');

        } catch (error) {

            console.error(
                'Employee shift request history error:',
                error
            );

            history.innerHTML = `
            <p class="text-xs text-red-500">
                Unable to load shift requests.
            </p>
        `;
        }
    }

    async function submitEmployeeShiftRequest(event) {

        event.preventDefault();

        const date =
            document.getElementById(
                'employee-shift-request-date'
            )?.value || '';

        const shiftSelect =
            document.getElementById(
                'employee-shift-request-shift'
            );

        const customShift =
            document.getElementById(
                'employee-shift-request-custom'
            )?.value
                ?.trim() || '';

        const reason =
            document.getElementById(
                'employee-shift-request-reason'
            )?.value
                ?.trim() || '';

        if (!date) {

            showToastEmployee(
                'Please select a requested date.',
                'info'
            );

            return;
        }

        let shift =
            shiftSelect?.value || '';

        if (shift === '__custom__') {
            shift = customShift;
        }

        if (!shift) {

            showToastEmployee(
                'Please select or enter a requested shift.',
                'info'
            );

            return;
        }

        try {

            const response =
                await fetch(
                    'api/shift-requests/my.php',
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
                            requested_date: date,
                            requested_shift: shift,
                            reason: reason
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
                    'Unable to submit shift request.'
                );
            }

            document
                .getElementById(
                    'employee-shift-request-form'
                )
                ?.reset();

            document
                .getElementById(
                    'employee-shift-request-custom-wrap'
                )
                ?.classList.add('hidden');

            await loadEmployeeShiftRequests();

            showToastEmployee(
                'Shift request submitted successfully.',
                'success'
            );

        } catch (error) {

            console.error(
                'Employee shift request submit error:',
                error
            );

            showToastEmployee(
                error.message ||
                'Unable to submit shift request.',
                'info'
            );
        }
    }

    document
        .getElementById(
            'employee-shift-request-btn'
        )
        ?.addEventListener(
            'click',
            openEmployeeShiftRequestModal
        );

    loadEmployeeTodayAttendanceFromDatabase();
    renderEmployeeLeaveRequests();
    loadEmployeeAttendanceFromDatabase();
    loadMyLeaveRequests();
    loadEmployeeScheduleFromDatabase();
    loadEmployeeTimesheetFromDatabase();
    loadEmployeeGrowthFromDatabase();
    //---------------------------- My Attendance Trend Chart ----------------------------
    let attendanceTrendChart = null;

    function initAttendanceTrendChart(trend = []) {
        const canvas = document.getElementById('attendance-trend-chart');
        if (!canvas || typeof Chart === 'undefined' || attendanceTrendChart || !trend.length) return;

        attendanceTrendChart = new Chart(canvas, {
            type: 'line',
            data: {
                labels: trend.map(
                    item => item.label
                ),
                datasets: [{
                    label: 'Attendance Rate',
                    data: trend.map(
                        item => Number(item.rate || 0)
                    ),
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
                        min: 0,
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
        const firstNotification =
            notificationMenu.querySelector(
                '[data-notification-id="new-system-report"]'
            );

        const session =
            window.SharedData?.getSession
                ? window.SharedData.getSession()
                : null;

        const notificationKey =
            `ht_notification_new_system_report_read_${session?.employee_id || session?.id || 'employee'}`;

        function markNotificationAsRead() {
            localStorage.setItem(notificationKey, '1');

            if (unreadBadge) {
                unreadBadge.classList.add('hidden');
            }

            if (firstNotification) {
                firstNotification.classList.remove('bg-blue-50/50');

                const blueDot =
                    firstNotification.querySelector('.bg-blue-600');

                if (blueDot) {
                    blueDot.remove();
                }
            }
        }

        if (localStorage.getItem(notificationKey) === '1') {
            markNotificationAsRead();
        }

        markAllBtn?.addEventListener('click', (event) => {
            event.stopPropagation();
            markNotificationAsRead();
        });
    }

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

        let searchRequest = null;

        searchInput.addEventListener(
            'input',
            async function () {

                const query =
                    this.value.trim();

                suggestionsBox.innerHTML = '';

                if (query.length === 0) {
                    suggestionsBox.classList.add('hidden');
                    return;
                }

                if (searchRequest) {
                    searchRequest.abort();
                }

                searchRequest =
                    new AbortController();

                try {

                    const response =
                        await fetch(
                            'api/employees/search.php?q=' +
                            encodeURIComponent(query),
                            {
                                method: 'GET',
                                credentials: 'same-origin',
                                headers: {
                                    'Accept':
                                        'application/json'
                                },
                                cache: 'no-store',
                                signal:
                                    searchRequest.signal
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
                            'Unable to search employees.'
                        );
                    }

                    const employees =
                        Array.isArray(result.data)
                            ? result.data
                            : [];

                    suggestionsBox.innerHTML = '';

                    if (employees.length === 0) {

                        const emptyItem =
                            document.createElement('li');

                        emptyItem.textContent =
                            'No employees found.';

                        emptyItem.className =
                            'px-4 py-3 text-sm text-gray-400';

                        suggestionsBox.appendChild(
                            emptyItem
                        );

                        suggestionsBox.classList.remove(
                            'hidden'
                        );

                        return;
                    }

                    employees.forEach(employee => {

                        const li =
                            document.createElement('li');

                        li.className =
                            'px-4 py-3 hover:bg-gray-100 cursor-pointer transition-colors';

                        const name =
                            document.createElement('div');

                        name.textContent =
                            employee.name || 'Unknown Employee';

                        name.className =
                            'text-sm font-semibold text-primary';

                        const details =
                            document.createElement('div');

                        details.textContent =
                            [
                                employee.employee_id,
                                employee.position,
                                employee.department
                            ]
                                .filter(Boolean)
                                .join(' • ');

                        details.className =
                            'text-xs text-gray-400 mt-0.5';

                        li.appendChild(name);
                        li.appendChild(details);

                        li.addEventListener(
                            'click',
                            () => {

                                searchInput.value =
                                    employee.name || '';

                                suggestionsBox.classList.add(
                                    'hidden'
                                );
                            }
                        );

                        suggestionsBox.appendChild(li);
                    });

                    suggestionsBox.classList.remove(
                        'hidden'
                    );

                } catch (error) {

                    if (
                        error.name ===
                        'AbortError'
                    ) {
                        return;
                    }

                    console.error(
                        'Employee search failed:',
                        error
                    );

                    suggestionsBox.innerHTML = '';

                    suggestionsBox.classList.add(
                        'hidden'
                    );
                }
            }
        );

        document.addEventListener(
            'click',
            event => {

                if (
                    !searchContainer.contains(
                        event.target
                    )
                ) {
                    suggestionsBox.classList.add(
                        'hidden'
                    );
                }
            }
        );

        searchInput.addEventListener(
            'focus',
            () => {

                if (
                    suggestionsBox.children.length > 0 &&
                    searchInput.value.trim().length > 0
                ) {
                    suggestionsBox.classList.remove(
                        'hidden'
                    );
                }
            }
        );
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

    fetch('/api/auth/logout.php', {
        method: 'POST',
        credentials: 'same-origin'
    }).catch(() => { });

    setTimeout(() => {
        window.location.replace('/login');
    }, 700);
}
window.handleEmployeeLogout = handleEmployeeLogout;
