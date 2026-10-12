// ---- Employee Profile: row click -> toast -> modal ----
function openEmployeeProfile(row) {
    const data = row.dataset;

    // Populate modal fields
    document.getElementById('profile-name').textContent = data.name;
    document.getElementById('profile-id').textContent = data.id;
    document.getElementById('profile-position').textContent = data.position;
    document.getElementById('profile-department').textContent = data.department;

    const statusEl = document.getElementById('profile-status');
    statusEl.textContent = data.status;
    if (data.status === 'LEAVE') {
        statusEl.className = 'inline-flex items-center mt-2 bg-red-100 text-red-600 text-xs font-semibold px-3 py-1 rounded-full';
    } else {
        statusEl.className = 'inline-flex items-center mt-2 bg-emerald-100 text-emerald-700 text-xs font-semibold px-3 py-1 rounded-full';
    }

    // Reset all accordion panels closed
    document.querySelectorAll('#modal-employee-profile .profile-accordion-panel').forEach(p => p.classList.add('hidden'));
    document.querySelectorAll('#modal-employee-profile .profile-accordion-btn i').forEach(i => i.classList.remove('rotate-180'));

    // Show brief "View Profile" toast, then open modal
    const toast = document.getElementById('view-profile-toast');
    toast.classList.remove('hidden');
    requestAnimationFrame(() => toast.classList.remove('opacity-0'));

    setTimeout(() => {
        toast.classList.add('opacity-0');
        setTimeout(() => toast.classList.add('hidden'), 300);

        const modal = document.getElementById('modal-employee-profile');
        modal.classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    }, 450);
}

function closeEmployeeProfile() {
    document.getElementById('modal-employee-profile').classList.add('hidden');
}

// ---- Attendance Correction: row click -> review modal ----
let currentCorrectionRow = null;

function openCorrectionModal(row) {
    currentCorrectionRow = row;
    const data = row.dataset;

    document.getElementById('correction-employee').textContent = data.employee;
    document.getElementById('correction-date').textContent = data.date;
    document.getElementById('correction-current-clockin').textContent = data.clockin || '—';
    document.getElementById('correction-current-clockout').textContent = data.clockout || '—';
    document.getElementById('correction-requested').textContent = data.requestedClockout
        ? `Clock Out: ${data.requestedClockout}`
        : '—';
    document.getElementById('correction-reason').textContent = data.reason || '—';
    document.getElementById('correction-attachment').textContent = data.attachment ? data.attachment : 'No attachment';

    const modal = document.getElementById('modal-correction-review');
    modal.classList.remove('hidden');
    if (window.lucide) lucide.createIcons();
}

function closeCorrectionModal() {
    document.getElementById('modal-correction-review').classList.add('hidden');
    currentCorrectionRow = null;
}

function approveCorrection() {
    if (!currentCorrectionRow) return;
    markCorrectionRow(currentCorrectionRow, 'APPROVED');
    closeCorrectionModal();
}

function rejectCorrection() {
    if (!currentCorrectionRow) return;
    markCorrectionRow(currentCorrectionRow, 'REJECTED');
    closeCorrectionModal();
}

function markCorrectionRow(row, newStatus) {
    row.dataset.status = newStatus;

    const statusCell = row.querySelector('td:last-child');
    if (newStatus === 'APPROVED') {
        statusCell.innerHTML = '<span class="inline-flex items-center bg-emerald-100 text-emerald-700 text-xs font-semibold px-3 py-1 rounded-full">APPROVED</span>';
    } else {
        statusCell.innerHTML = '<span class="inline-flex items-center bg-red-100 text-red-600 text-xs font-semibold px-3 py-1 rounded-full">REJECTED</span>';
    }

    // Make the row no longer clickable/reviewable
    row.removeAttribute('onclick');
    row.classList.remove('group', 'cursor-pointer', 'hover:bg-background');
    row.classList.add('cursor-default');
}

// ---- Master Roster: toggle between Week (table) and Month (calendar) view ----
function toggleScheduleView() {
    const weekView = document.getElementById('weekRosterView');
    const monthView = document.getElementById('monthRosterView');
    const label = document.getElementById('scheduleViewToggleLabel');
    const icon = document.getElementById('scheduleViewToggleIcon');
    const title = document.getElementById('scheduleHeaderTitle');
    const isShowingMonth = !monthView.classList.contains('hidden');

    if (isShowingMonth) {
        // Switch back to Week view
        monthView.classList.add('hidden');
        weekView.classList.remove('hidden');
        label.textContent = 'Month';
        icon.classList.remove('fa-table-cells-large');
        icon.classList.add('fa-calendar-days');
        title.textContent = 'July 26 - Aug 1, 2026';
    } else {
        // Switch to Month view
        weekView.classList.add('hidden');
        monthView.classList.remove('hidden');
        label.textContent = 'Week';
        icon.classList.remove('fa-calendar-days');
        icon.classList.add('fa-table-cells-large');
        title.textContent = 'August 2026';
    }
}

// ---- Master Roster: role filter ----
// Maps each employee to the role bucket used by the "All Roles" filter.
const SCHEDULE_EMPLOYEE_ROLES = {
    'Employee 1': 'Tour Guides',
    'Employee 2': 'Office Staff',
    'Employee 3': 'Drivers'
};

function filterScheduleByRole() {
    const role = document.getElementById('scheduleRoleFilter').value;

    // Week view: hide/show whole employee rows
    document.querySelectorAll('#weekRosterView tbody tr[data-emp]').forEach(row => {
        const emp = row.getAttribute('data-emp');
        const matches = role === 'All Roles' || SCHEDULE_EMPLOYEE_ROLES[emp] === role;
        row.classList.toggle('hidden', !matches);
    });

    // Month view: hide/show individual shift entries within each day
    document.querySelectorAll('#monthRosterView .mt-1.space-y-1 > div').forEach(entry => {
        const label = entry.title || entry.textContent || '';
        const emp = label.split('·')[0].trim();
        const matches = role === 'All Roles' || SCHEDULE_EMPLOYEE_ROLES[emp] === role;
        entry.classList.toggle('hidden', !matches);
    });
}

// ---- Master Roster: Create Schedule modal ----
function openCreateScheduleModal() {
    document.getElementById('modal-create-schedule').classList.remove('hidden');
}

function closeCreateScheduleModal() {
    document.getElementById('modal-create-schedule').classList.add('hidden');
}

function updateScheduleDateDisplay(nativeInput) {
    if (!nativeInput.value) return;
    // Parse as local date (avoid UTC offset shifting the day)
    const [year, month, day] = nativeInput.value.split('-').map(Number);
    const date = new Date(year, month - 1, day);
    const formatted = date.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
    document.getElementById('schedule-form-date').value = formatted;
}

function handleCreateScheduleSubmit(event) {
    event.preventDefault();

    const employee = document.getElementById('schedule-form-employee').value;
    const dateText = document.getElementById('schedule-form-date').value;
    const shift = document.getElementById('schedule-form-shift').value;
    const startRaw = document.getElementById('schedule-form-start').value;
    const endRaw = document.getElementById('schedule-form-end').value;
    const workType = document.getElementById('schedule-form-worktype').value;
    const remarks = document.getElementById('schedule-form-remarks').value.trim();

    const isoDate = getScheduleIsoDate(dateText);
    const startParsed = parseScheduleTime(startRaw);
    const endParsed = parseScheduleTime(endRaw);
    const colors = scheduleColorForWorkType(workType);

    let addedToWeek = false;
    let addedToMonth = false;

    if (isoDate) {
        const start24 = formatScheduleTime24(startParsed) || startRaw;
        const end24 = formatScheduleTime24(endParsed) || endRaw;
        const start12 = formatScheduleTime12Short(startParsed) || startRaw;
        const end12 = formatScheduleTime12Short(endParsed) || endRaw;

        addedToWeek = insertScheduleIntoWeekRoster(employee, isoDate, start24, end24, workType, remarks, colors);
        addedToMonth = insertScheduleIntoMonthRoster(employee, isoDate, start12, end12, colors);
    }

    closeCreateScheduleModal();
    event.target.reset();
    document.getElementById('schedule-form-date').value = 'August 20, 2026';
    filterScheduleByRole();

    if (addedToWeek || addedToMonth) {
        showScheduleToast(`Schedule created for ${employee} on ${dateText}.`, false);
    } else {
        showScheduleToast(`Schedule saved for ${employee} on ${dateText}. Switch to the week/month that contains that date to see it.`, true);
    }
}

// ---- Master Roster: Create Schedule helpers ----
function getScheduleIsoDate(dateText) {
    const nativeVal = document.getElementById('schedule-form-date-native').value;
    if (nativeVal) return nativeVal;
    const parsed = new Date(dateText);
    if (isNaN(parsed)) return null;
    const y = parsed.getFullYear();
    const m = String(parsed.getMonth() + 1).padStart(2, '0');
    const d = String(parsed.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

function parseScheduleTime(timeStr) {
    const t = (timeStr || '').trim();
    let d = new Date(`2000-01-01 ${t}`);
    if (isNaN(d)) d = new Date(`2000-01-01T${t}`);
    if (isNaN(d)) return null;
    return { h24: d.getHours(), m: d.getMinutes() };
}

function formatScheduleTime24(parsed) {
    if (!parsed) return null;
    return `${String(parsed.h24).padStart(2, '0')}:${String(parsed.m).padStart(2, '0')}`;
}

function formatScheduleTime12Short(parsed) {
    if (!parsed) return null;
    let h = parsed.h24 % 12;
    if (h === 0) h = 12;
    return `${h}`;
}

function scheduleColorForWorkType(workType) {
    const palette = {
        'Field Duty': { bg: 'bg-blue-50', text: 'text-blue-700', border: 'border-blue-100', hover: 'hover:bg-blue-100' },
        'Office Work': { bg: 'bg-purple-50', text: 'text-purple-700', border: 'border-purple-100', hover: 'hover:bg-purple-100' },
        'Driving': { bg: 'bg-amber-50', text: 'text-amber-700', border: 'border-amber-100', hover: 'hover:bg-amber-100' },
        'Monitoring': { bg: 'bg-emerald-50', text: 'text-emerald-700', border: 'border-emerald-100', hover: 'hover:bg-emerald-100' }
    };
    return palette[workType] || palette['Field Duty'];
}

function insertScheduleIntoWeekRoster(employee, isoDate, start24, end24, workType, remarks, colors) {
    const row = document.querySelector(`#weekRosterView tbody tr[data-emp="${employee}"]`);
    if (!row) return false;
    if (row.querySelector('td[colspan]')) return false; // e.g. employee is on a full-week leave block

    const headerRow = document.querySelector('#weekRosterView thead tr');
    const header = headerRow ? headerRow.querySelector(`th[data-date="${isoDate}"]`) : null;
    if (!header) return false;

    const colIndex = Array.from(headerRow.children).indexOf(header);
    const cells = row.querySelectorAll('td');
    if (colIndex < 0 || colIndex >= cells.length) return false;

    const cell = cells[colIndex];
    const label = remarks ? `${workType} / ${remarks}` : workType;
    cell.innerHTML = `<div class="${colors.bg} ${colors.text} text-xs p-1.5 rounded border ${colors.border} text-center cursor-pointer ${colors.hover}">
                <div class="font-bold">${start24} - ${end24}</div>
                <div class="truncate text-[10px]">${label}</div>
            </div>`;
    return true;
}

function insertScheduleIntoMonthRoster(employee, isoDate, start12, end12, colors) {
    const dayCell = document.querySelector(`#monthRosterView div[data-date="${isoDate}"]`);
    if (!dayCell) return false;
    const container = dayCell.querySelector('.mt-1.space-y-1');
    if (!container) return false;

    const label = `${employee} · ${start12}-${end12}`;
    const entry = document.createElement('div');
    entry.className = `${colors.bg} ${colors.text} text-[10px] px-1.5 py-1 rounded truncate`;
    entry.title = label;
    entry.textContent = label;
    container.appendChild(entry);
    return true;
}

function showScheduleToast(message, isWarning) {
    const toast = document.createElement('div');
    toast.className = `fixed bottom-6 right-6 z-[100] max-w-sm px-4 py-3 rounded-xl shadow-lg text-sm font-medium text-white ${isWarning ? 'bg-amber-500' : 'bg-success'}`;
    toast.style.transition = 'opacity 0.3s ease';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; }, 2500);
    setTimeout(() => { toast.remove(); }, 2900);
}

// ---- Live Attendance nav: toggle Overtime submenu ----
function toggleAttendanceSubmenu(event) {
    const submenu = document.getElementById('attendance-submenu');
    const chevron = document.getElementById('attendance-submenu-chevron');
    submenu.classList.toggle('hidden');
    chevron.classList.toggle('rotate-180');
}

// ---- Employee table: search + filter ----
function filterEmployeeTable() {
    const searchTerm = document.getElementById('employee-search').value.trim().toLowerCase();
    const department = document.getElementById('employee-filter-department').value;
    const status = document.getElementById('employee-filter-status').value;

    const rows = document.querySelectorAll('#employees-table-body tr[data-id]');
    let visibleCount = 0;

    rows.forEach(row => {
        const name = (row.dataset.name || '').toLowerCase();
        const id = (row.dataset.id || '').toLowerCase();
        const rowDept = row.dataset.department || '';
        const rowStatus = row.dataset.status || '';

        const matchesSearch = !searchTerm || name.includes(searchTerm) || id.includes(searchTerm);
        const matchesDept = !department || rowDept === department;
        const matchesStatus = !status || rowStatus === status;

        const isVisible = matchesSearch && matchesDept && matchesStatus;
        row.classList.toggle('hidden', !isVisible);
        if (isVisible) visibleCount++;
    });

    // Show/hide "no results" row
    let emptyRow = document.getElementById('employee-no-results-row');
    if (visibleCount === 0) {
        if (!emptyRow) {
            emptyRow = document.createElement('tr');
            emptyRow.id = 'employee-no-results-row';
            emptyRow.innerHTML = '<td colspan="5" class="px-5 py-8 text-center text-gray-400">No employees match your search/filters.</td>';
            document.getElementById('employees-table-body').appendChild(emptyRow);
        }
    } else if (emptyRow) {
        emptyRow.remove();
    }
}

// ---- Live Attendance table: search + filter ----
function filterAttendanceTable() {
    const searchTerm = document.getElementById('attendance-search').value.trim().toLowerCase();
    const department = document.getElementById('attendance-filter-department').value;
    const status = document.getElementById('attendance-filter-status').value;

    const rows = document.querySelectorAll('#attendance-table-body tr[data-id]');
    let visibleCount = 0;

    rows.forEach(row => {
        const name = (row.dataset.name || '').toLowerCase();
        const id = (row.dataset.id || '').toLowerCase();
        const rowDept = row.dataset.department || '';
        const rowStatus = row.dataset.status || '';

        const matchesSearch = !searchTerm || name.includes(searchTerm) || id.includes(searchTerm);
        const matchesDept = !department || rowDept === department;
        const matchesStatus = !status || rowStatus === status;

        const isVisible = matchesSearch && matchesDept && matchesStatus;
        row.classList.toggle('hidden', !isVisible);
        if (isVisible) visibleCount++;
    });

    // Show/hide "no results" row
    let emptyRow = document.getElementById('attendance-no-results-row');
    if (visibleCount === 0) {
        if (!emptyRow) {
            emptyRow = document.createElement('tr');
            emptyRow.id = 'attendance-no-results-row';
            emptyRow.innerHTML = '<td colspan="6" class="px-5 py-8 text-center text-gray-400">No attendance records match your search/filters.</td>';
            document.getElementById('attendance-table-body').appendChild(emptyRow);
        }
    } else if (emptyRow) {
        emptyRow.remove();
    }
}

// ---- Attendance Records table: date range + filters ----
function filterAttendanceRecords() {
    const dateRange = document.getElementById('records-date-range').value.trim().toLowerCase();
    const department = document.getElementById('records-filter-department').value;
    const status = document.getElementById('records-filter-status').value;
    const employee = document.getElementById('records-filter-employee').value;

    const rows = document.querySelectorAll('#attendance-records-table-body tr[data-date]');
    let visibleCount = 0;

    rows.forEach(row => {
        const rowDate = (row.dataset.date || '').toLowerCase();
        const rowEmployee = row.dataset.employee || '';
        const rowDept = row.dataset.department || '';
        const rowStatus = row.dataset.status || '';

        const matchesDate = !dateRange || rowDate.includes(dateRange);
        const matchesDept = !department || rowDept === department;
        const matchesStatus = !status || rowStatus === status;
        const matchesEmployee = !employee || rowEmployee === employee;

        const isVisible = matchesDate && matchesDept && matchesStatus && matchesEmployee;
        row.classList.toggle('hidden', !isVisible);
        if (isVisible) visibleCount++;
    });

    // Show/hide "no results" row
    let recordsEmptyRow = document.getElementById('records-no-results-row');
    if (visibleCount === 0) {
        if (!recordsEmptyRow) {
            recordsEmptyRow = document.createElement('tr');
            recordsEmptyRow.id = 'records-no-results-row';
            recordsEmptyRow.innerHTML = '<td colspan="7" class="px-5 py-8 text-center text-gray-400">No records match your filters.</td>';
            document.getElementById('attendance-records-table-body').appendChild(recordsEmptyRow);
        }
    } else if (recordsEmptyRow) {
        recordsEmptyRow.remove();
    }
}

// ---- DTR Archive: compute date range from the period dropdown + date picker ----
function getAttendancePeriodRange() {
    const periodEl = document.getElementById('attendancePeriodSelect');
    const dateEl = document.getElementById('attendancePeriodDate');
    const period = periodEl ? periodEl.value : 'Today';
    const anchorStr = dateEl && dateEl.value ? dateEl.value : new Date().toISOString().slice(0, 10);
    const anchor = new Date(anchorStr + 'T00:00:00');

    let start, end;
    if (period === 'This Week') {
        const day = anchor.getDay(); // 0=Sun..6=Sat
        const diffToMonday = (day === 0 ? -6 : 1 - day);
        start = new Date(anchor); start.setDate(anchor.getDate() + diffToMonday);
        end = new Date(start); end.setDate(start.getDate() + 6);
    } else if (period === 'This Month') {
        start = new Date(anchor.getFullYear(), anchor.getMonth(), 1);
        end = new Date(anchor.getFullYear(), anchor.getMonth() + 1, 0);
    } else { // Today
        start = new Date(anchor);
        end = new Date(anchor);
    }

    const toISO = d => d.toISOString().slice(0, 10);
    return { period, start: toISO(start), end: toISO(end) };
}

function formatDateLabel(isoStr) {
    const d = new Date(isoStr + 'T00:00:00');
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

// Live preview so it's clear the calendar date + period dropdown drive the Export DTR button
function updateAttendancePeriodPreview() {
    const preview = document.getElementById('attendancePeriodPreview');
    if (!preview) return;
    const { period, start, end } = getAttendancePeriodRange();
    const anchorDateStr = document.getElementById('attendancePeriodDate')?.value || '';
    if (period === 'Today' && isActualToday(anchorDateStr)) {
        preview.textContent = `Will export: Today's Attendance (live)`;
        return;
    }
    const rangeLabel = start === end ? formatDateLabel(start) : `${formatDateLabel(start)} - ${formatDateLabel(end)}`;
    preview.textContent = `Will export: ${period} (${rangeLabel})`;
}
document.addEventListener('DOMContentLoaded', updateAttendancePeriodPreview);

// ---- DTR Archive: open print dialog (defaults to "Save as PDF" in most browsers) ----
function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function openDTRPrintWindow(title, headers, tableData) {
    const printWindow = window.open('', '_blank', 'width=900,height=700');
    if (!printWindow) {
        showScheduleToast('Please allow pop-ups to export the DTR.', true);
        return;
    }

    const headHtml = headers.map(h => `<th>${escapeHtml(h)}</th>`).join('');
    const rowsHtml = tableData.map(r => `<tr>${r.map(c => `<td>${escapeHtml(c)}</td>`).join('')}</tr>`).join('');

    printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>${escapeHtml(title)}</title>
                    <meta charset="UTF-8">
                    <style>
                        @page { size: A4; margin: 16mm; }
                        body { font-family: Arial, Helvetica, sans-serif; color: #1F2937; padding: 0; margin: 0; }
                        .header { border-bottom: 2px solid #163B6D; padding-bottom: 10px; margin-bottom: 16px; }
                        .header h1 { font-size: 18px; color: #163B6D; margin: 0 0 4px 0; }
                        .header p { font-size: 11px; color: #6B7280; margin: 2px 0; }
                        table { width: 100%; border-collapse: collapse; font-size: 11px; }
                        th, td { border: 1px solid #E5E7EB; padding: 6px 8px; text-align: left; }
                        th { background: #F59B45; color: #fff; text-transform: uppercase; font-size: 10px; }
                        tr:nth-child(even) { background: #F8FAFC; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>Holiday Travelers &mdash; Daily Time Record (DTR)</h1>
                        <p>${escapeHtml(title)}</p>
                        <p>Generated: ${new Date().toLocaleString()}</p>
                    </div>
                    <table>
                        <thead>
                            <tr>${headHtml}</tr>
                        </thead>
                        <tbody>${rowsHtml}</tbody>
                    </table>
                </body>
                </html>
            `);
    printWindow.document.close();

    printWindow.onload = () => {
        printWindow.focus();
        printWindow.print();
    };
    // Fallback in case onload doesn't fire (some browsers with document.write)
    setTimeout(() => {
        printWindow.focus();
        printWindow.print();
    }, 400);
}

function isActualToday(dateStr) {
    const todayISO = new Date().toISOString().slice(0, 10);
    return dateStr === todayISO;
}

// Print the live "Today's Attendance" table (used when period = Today AND the
// calendar date is the real current date — i.e. there's no historical record yet).
function exportTodaysAttendancePrint() {
    const rows = Array.from(document.querySelectorAll('#attendance-table-body tr[data-id]:not(.hidden)'));
    if (rows.length === 0) {
        showScheduleToast("No employees to export in Today's Attendance.", true);
        return;
    }
    const tableData = rows.map(row => {
        const cells = row.querySelectorAll('td');
        return [
            cells[0].textContent.trim(), // ID
            cells[1].textContent.trim(), // Employee
            cells[2].textContent.trim(), // Schedule
            cells[3].textContent.trim(), // Clock In
            cells[4].textContent.trim(), // Clock Out
            cells[5].textContent.trim()  // Status
        ];
    });
    const todayLabel = formatDateLabel(new Date().toISOString().slice(0, 10));
    openDTRPrintWindow(
        `Today's Attendance — ${todayLabel} (${rows.length} employee(s))`,
        ['ID', 'Employee', 'Schedule', 'Clock In', 'Clock Out', 'Status'],
        tableData
    );
}

function exportDTRArchive() {
    const { period, start, end } = getAttendancePeriodRange();
    const anchorDateStr = document.getElementById('attendancePeriodDate')?.value || '';

    // Today + the real current date selected -> pull from the live Today's Attendance table
    if (period === 'Today' && isActualToday(anchorDateStr)) {
        exportTodaysAttendancePrint();
        return;
    }

    const allRows = document.querySelectorAll('#attendance-records-table-body tr[data-date]');
    const matchingRows = Array.from(allRows).filter(row => {
        const rowDate = row.dataset.date;
        return rowDate >= start && rowDate <= end;
    });

    const rangeLabel = start === end ? formatDateLabel(start) : `${formatDateLabel(start)} - ${formatDateLabel(end)}`;

    if (matchingRows.length === 0) {
        showScheduleToast(`No DTR records found for ${period} (${rangeLabel}).`, true);
        return;
    }

    const tableData = matchingRows.map(row => {
        const cells = row.querySelectorAll('td');
        return [
            cells[0].textContent.trim(),
            cells[1].textContent.trim(),
            cells[2].textContent.trim(),
            cells[3].textContent.trim(),
            cells[4].textContent.trim(),
            cells[5].textContent.trim()
        ];
    });

    openDTRPrintWindow(
        `${period} — ${rangeLabel} (${matchingRows.length} record(s))`,
        ['Date', 'Employee', 'Schedule', 'Clock In', 'Clock Out', 'Status'],
        tableData
    );
}

function exportSingleDTR(btn) {
    const row = btn.closest('tr');
    const cells = row.querySelectorAll('td');
    const employeeName = cells[1].textContent.trim();
    const tableData = [[
        cells[0].textContent.trim(),
        cells[1].textContent.trim(),
        cells[2].textContent.trim(),
        cells[3].textContent.trim(),
        cells[4].textContent.trim(),
        cells[5].textContent.trim()
    ]];
    openDTRPrintWindow(
        `DTR for ${employeeName}`,
        ['Date', 'Employee', 'Schedule', 'Clock In', 'Clock Out', 'Status'],
        tableData
    );
}

function toggleAccordion(btn) {
    const panel = btn.nextElementSibling;
    const icon = btn.querySelector('i');
    panel.classList.toggle('hidden');
    icon.classList.toggle('rotate-180');
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeEmployeeProfile();
});

(function () {
    let mobileNavReady = false;

    function getDesktopNavigation() {
        return document.querySelector('aside:not(#mobile-menu) nav');
    }

    function getDesktopLink(target) {
        const nav = getDesktopNavigation();
        return nav ? nav.querySelector(`.nav-btn[data-target="${target}"]`) : null;
    }

    function syncMobileMenuState(target) {
        document.querySelectorAll('#mobile-sidebar-nav .mobile-nav-item').forEach(item => {
            const active = item.dataset.target === target;
            item.classList.toggle('bg-secondary', active);
            item.classList.toggle('text-white', active);
            item.classList.toggle('text-gray-300', !active);
            item.classList.toggle('font-semibold', active);
            item.classList.toggle('font-medium', !active);
        });

        const activeDesktop = getDesktopLink(target);
        const pageTitle = document.getElementById('page-title');
        if (pageTitle && activeDesktop) {
            pageTitle.textContent = activeDesktop.textContent.replace(/\s+/g, ' ').trim();
        }
    }

    function toggleMobileAttendanceSubmenu() {
        const submenu = document.getElementById('mobile-attendance-submenu');
        const chevron = document.getElementById('mobile-attendance-submenu-chevron');
        if (!submenu) return;

        const willOpen = submenu.classList.contains('hidden');
        submenu.classList.toggle('hidden', !willOpen);
        if (chevron) chevron.classList.toggle('rotate-180', willOpen);
    }

    function activateDesktopTarget(target) {
        const desktopLink = getDesktopLink(target);
        if (!desktopLink) return false;

        // Trigger the original navigation element so scripts/script.js remains
        // responsible for changing tabs and running the existing admin logic.
        desktopLink.click();
        syncMobileMenuState(target);
        return true;
    }

    function wireMobileNavigation(navRoot) {
        navRoot.querySelectorAll('.nav-btn[data-target]').forEach(item => {
            const target = item.dataset.target;
            item.classList.remove('active');
            item.classList.add('mobile-nav-item', 'w-full', 'transition-colors');
            item.removeAttribute('onclick');
            item.setAttribute('href', '#');

            if (target === 'time-attendance') {
                const chevron = item.querySelector('i[id*="chevron"]');
                if (chevron) chevron.id = 'mobile-attendance-submenu-chevron';

                item.addEventListener('click', function (event) {
                    event.preventDefault();
                    activateDesktopTarget(target);
                    toggleMobileAttendanceSubmenu();
                });
            } else {
                item.addEventListener('click', function (event) {
                    event.preventDefault();
                    if (activateDesktopTarget(target)) closeMobileMenu();
                });
            }
        });

        const submenu = navRoot.querySelector('#mobile-attendance-submenu');
        if (submenu) {
            submenu.querySelectorAll('.nav-btn[data-target]').forEach(item => {
                const target = item.dataset.target;
                item.classList.add('mobile-nav-item');
                item.removeAttribute('onclick');
                item.setAttribute('href', '#');
                item.addEventListener('click', function (event) {
                    event.preventDefault();
                    if (activateDesktopTarget(target)) closeMobileMenu();
                });
            });
        }
    }

    function buildMobileNavigation() {
        if (mobileNavReady) return;

        const sidebarNav = document.getElementById('mobile-sidebar-nav');
        const desktopAside = document.querySelector('aside:not(#mobile-menu)');
        if (!sidebarNav || !desktopAside) return;

        const desktopUser = desktopAside.querySelector(':scope > div:nth-of-type(2)');
        const mobileUser = document.getElementById('mobile-sidebar-user');
        if (desktopUser && mobileUser) mobileUser.innerHTML = desktopUser.innerHTML;

        const desktopNav = getDesktopNavigation();
        if (!desktopNav) return;

        // Copy the complete desktop navigation so the mobile sidebar contains
        // the same Overview, Employees, Modules and Overtime structure.
        const clone = desktopNav.cloneNode(true);
        clone.id = 'mobile-admin-nav';
        clone.className = 'flex flex-col space-y-1';

        const mobileSubmenu = clone.querySelector('#attendance-submenu');
        if (mobileSubmenu) {
            mobileSubmenu.id = 'mobile-attendance-submenu';
            mobileSubmenu.classList.add('mobile-sidebar-submenu');
        }

        clone.querySelectorAll('.nav-btn').forEach(item => {
            item.classList.remove('active');
            item.classList.add('mobile-nav-item');
        });

        sidebarNav.innerHTML = '';
        sidebarNav.appendChild(clone);
        wireMobileNavigation(clone);

        mobileNavReady = true;
        syncMobileMenuState('dashboard');

        if (window.lucide) lucide.createIcons();
    }

    function openMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        const backdrop = document.getElementById('mobile-menu-backdrop');
        const btn = document.getElementById('mobile-menu-btn');
        if (!menu || !btn) return;

        buildMobileNavigation();
        menu.classList.remove('hidden');
        if (backdrop) backdrop.classList.remove('hidden');

        btn.setAttribute('aria-expanded', 'true');
        btn.innerHTML = '<i class="fa-solid fa-xmark text-xl"></i>';
        document.body.classList.add('mobile-nav-open');
    }

    function closeMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        const backdrop = document.getElementById('mobile-menu-backdrop');
        const btn = document.getElementById('mobile-menu-btn');
        if (!menu || !btn) return;

        menu.classList.add('hidden');
        if (backdrop) backdrop.classList.add('hidden');

        btn.setAttribute('aria-expanded', 'false');
        btn.innerHTML = '<i class="fa-solid fa-bars text-xl"></i>';
        document.body.classList.remove('mobile-nav-open');
    }

    function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        if (!menu) return;
        if (menu.classList.contains('hidden')) openMobileMenu();
        else closeMobileMenu();
    }

    function applyMobileTableLabels() {
        document.querySelectorAll('.mobile-stack-table').forEach(table => {
            const headings = Array.from(table.querySelectorAll('thead th'))
                .map(th => th.textContent.replace(/\s+/g, ' ').trim());

            table.querySelectorAll('tbody tr').forEach(row => {
                Array.from(row.children).forEach((cell, index) => {
                    if (cell.tagName === 'TD' && !cell.hasAttribute('colspan') && headings[index]) {
                        cell.dataset.label = headings[index];
                    }
                });
            });
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('mobile-menu-btn');
        const closeBtn = document.getElementById('mobile-sidebar-close');
        const backdrop = document.getElementById('mobile-menu-backdrop');

        if (btn) {
            btn.setAttribute('aria-expanded', 'false');
            btn.setAttribute('aria-controls', 'mobile-menu');
            btn.addEventListener('click', toggleMobileMenu);
        }

        if (closeBtn) closeBtn.addEventListener('click', closeMobileMenu);
        if (backdrop) backdrop.addEventListener('click', closeMobileMenu);

        buildMobileNavigation();
        applyMobileTableLabels();

        document.querySelectorAll('.mobile-stack-table tbody').forEach(tbody => {
            const observer = new MutationObserver(applyMobileTableLabels);
            observer.observe(tbody, { childList: true, subtree: true });
        });

        document.addEventListener('keydown', event => {
            if (event.key === 'Escape') closeMobileMenu();
        });
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 768) closeMobileMenu();
    });
})();

(function () {
    function updateAdminOverview() {
        if (!window.SharedData) return;

        const employees = SharedData.getEmployees().filter(e => e.role === 'user');
        const employeeIds = new Set(employees.map(e => e.id));
        const attendance = SharedData.getAttendance().filter(r => employeeIds.has(r.employeeId));
        const leaves = SharedData.getLeaveRequests().filter(r => employeeIds.has(r.employeeId));
        const today = new Date().toISOString().slice(0, 10);

        const todayAttendance = attendance.filter(r => r.date === today);
        const clockedIn = todayAttendance.filter(r => r.clockIn && !r.clockOut && String(r.status).toLowerCase() !== 'on leave').length;
        const onLeaveToday = todayAttendance.filter(r => ['on leave', 'leave'].includes(String(r.status).toLowerCase())).length;

        const now = new Date();
        const weekStart = new Date(now);
        weekStart.setDate(now.getDate() - 6);
        weekStart.setHours(0, 0, 0, 0);
        const lateThisWeek = attendance.filter(r => {
            if (String(r.status).toLowerCase() !== 'late' || !r.date) return false;
            const d = new Date(r.date + 'T00:00:00');
            return d >= weekStart && d <= now;
        }).length;

        const pendingLeaves = leaves.filter(r => String(r.status).toLowerCase() === 'pending').length;

        const setText = (id, value) => {
            const el = document.getElementById(id);
            if (el) el.textContent = String(value);
        };

        const clockedEl = document.getElementById('overview-clocked-in');
        if (clockedEl) clockedEl.innerHTML = `${clockedIn} <span class="text-lg text-gray-400 font-normal">/ ${employees.length}</span>`;

        setText('overview-active-tours', 0); // No shared tour/operations collection exists yet.
        setText('overview-late-week', lateThisWeek);
        setText('overview-on-leave', onLeaveToday);
        setText('overview-pending-timesheets', 0); // No shared timesheet collection exists yet.
        setText('overview-pending-leaves', pendingLeaves);

        // Keep Live Attendance summary cards in sync when they exist.
        const liveCards = document.querySelectorAll('#time-attendance .font-heading.text-3xl');
        if (liveCards.length >= 4) {
            const present = todayAttendance.filter(r => String(r.status).toLowerCase() === 'present').length;
            const late = todayAttendance.filter(r => String(r.status).toLowerCase() === 'late').length;
            const absent = todayAttendance.filter(r => String(r.status).toLowerCase() === 'absent').length;
            const leave = onLeaveToday;
            liveCards[0].textContent = present;
            liveCards[1].textContent = late;
            liveCards[2].textContent = absent;
            liveCards[3].textContent = leave;
        }
    }

    window.updateAdminOverview = updateAdminOverview;

    document.addEventListener('DOMContentLoaded', updateAdminOverview);
    window.addEventListener('storage', function (event) {
        if (event.key && event.key.indexOf('ht_v1_') === 0) updateAdminOverview();
    });
    setInterval(updateAdminOverview, 1000);
})();

function escapeScheduleDayHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, character => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    })[character]);
}

function getMobileScheduleDayEntries(isoDate, source) {
    if (source === 'month') {
        const cell = document.querySelector(`#monthRosterView [data-date="${isoDate}"]`);
        return Array.from(cell?.querySelectorAll('.mt-1.space-y-1 > div') || []).map(entry => ({
            employee: (entry.title || entry.textContent || '').split('·')[0].trim() || 'Employee',
            details: (entry.title || entry.textContent || '').split('·').slice(1).join('·').trim() || 'Scheduled'
        }));
    }

    const table = document.querySelector('#weekRosterView table');
    const header = Array.from(table?.querySelectorAll('thead th[data-date]') || [])
        .find(cell => cell.getAttribute('data-date') === isoDate);
    if (!header) return [];

    const columnIndex = Array.from(header.parentElement.children).indexOf(header);
    return Array.from(table.querySelectorAll('tbody tr[data-emp]'))
        .map(row => ({
            employee: row.dataset.emp || 'Employee',
            details: row.children[columnIndex]?.textContent.replace(/\s+/g, ' ').trim() || ''
        }))
        .filter(entry => entry.details);
}

function openScheduleDayModal(isoDate, source = 'month') {
    const modal = document.getElementById('modal-schedule-day');
    const title = document.getElementById('schedule-day-modal-title');
    const summary = document.getElementById('schedule-day-modal-summary');
    const content = document.getElementById('schedule-day-modal-content');
    if (!modal || !title || !summary || !content) return;

    const date = new Date(`${isoDate}T12:00:00`);
    if (isNaN(date)) return;

    const entries = getMobileScheduleDayEntries(isoDate, source);
    title.textContent = date.toLocaleDateString('en-US', {
        weekday: 'long',
        month: 'long',
        day: 'numeric',
        year: 'numeric'
    });
    summary.textContent = entries.length
        ? `${entries.length} schedule${entries.length === 1 ? '' : 's'} on this day`
        : 'No schedule or leave recorded for this day.';

    content.innerHTML = entries.length
        ? entries.map(entry => `
            <div class="border border-border rounded-xl p-4 bg-white shadow-sm">
                <div class="flex items-start gap-3 min-w-0">
                    <span class="w-9 h-9 rounded-xl bg-blue-50 text-primary flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-calendar-check"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="font-semibold text-primary text-sm">${escapeScheduleDayHtml(entry.employee)}</p>
                        <p class="text-xs text-gray-500 mt-0.5 break-words">${escapeScheduleDayHtml(entry.details)}</p>
                    </div>
                </div>
            </div>`).join('')
        : `<div class="border border-dashed border-gray-300 rounded-xl p-6 text-center">
                <i class="fa-regular fa-calendar-xmark text-3xl text-gray-300"></i>
                <p class="text-sm font-semibold text-gray-500 mt-3">No schedule available</p>
                <p class="text-xs text-gray-400 mt-1">No roster entry exists for this date.</p>
            </div>`;

    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeScheduleDayModal() {
    document.getElementById('modal-schedule-day')?.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('monthRosterView')?.addEventListener('click', event => {
        const dayCell = event.target.closest('[data-date]');
        if (!dayCell || !document.getElementById('monthRosterView').contains(dayCell)) return;
        const isoDate = dayCell.getAttribute('data-date');
        if (isoDate) openScheduleDayModal(isoDate, 'month');
    });

    document.getElementById('weekRosterView')?.addEventListener('click', event => {
        const roster = document.getElementById('weekRosterView');
        const cell = event.target.closest('td');
        if (!cell || !roster.contains(cell)) return;
        const row = cell.parentElement;
        const columnIndex = Array.from(row.children).indexOf(cell);
        const header = roster.querySelector(`thead th:nth-child(${columnIndex + 1})`);
        const isoDate = header?.getAttribute('data-date');
        if (isoDate) openScheduleDayModal(isoDate, 'week');
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') closeScheduleDayModal();
    });
});

function openAddEmployeeModal() {
    const modal = document.getElementById('modal-add-employee');
    const message = document.getElementById('add-employee-message');
    if (!modal) return;
    modal.classList.remove('hidden');
    message?.classList.add('hidden');
    if (window.lucide) lucide.createIcons();
}

function closeAddEmployeeModal() {
    document.getElementById('modal-add-employee')?.classList.add('hidden');
}

function appendMobileEmployeeRow(employee) {
    const tbody = document.getElementById('employees-table-body');
    if (!tbody) return;

    const row = document.createElement('tr');
    row.className = 'hover:bg-background transition-all-300';
    row.dataset.id = employee.employee_no;
    row.dataset.name = employee.display_name.toUpperCase();
    row.dataset.position = employee.position_name.toUpperCase();
    row.dataset.department = employee.department_name.toUpperCase();
    row.dataset.status = employee.employment_status;
    row.onclick = () => openEmployeeProfile(row);
    row.innerHTML = `
        <td class="px-5 py-4 font-medium text-primary">${escapeScheduleDayHtml(employee.employee_no)}</td>
        <td class="px-5 py-4 font-semibold text-primary">${escapeScheduleDayHtml(employee.display_name)}</td>
        <td class="px-5 py-4 text-gray-600">${escapeScheduleDayHtml(employee.position_name)}</td>
        <td class="px-5 py-4 text-gray-600">${escapeScheduleDayHtml(employee.department_name)}</td>
        <td class="px-5 py-4"><span class="inline-flex items-center bg-emerald-100 text-emerald-700 text-xs font-semibold px-3 py-1 rounded-full">${escapeScheduleDayHtml(employee.employment_status)}</span></td>`;
    tbody.appendChild(row);
    applyMobileTableLabels();
}

async function handleAddEmployeeSubmit(event) {
    event.preventDefault();

    const button = document.getElementById('add-employee-submit');
    const message = document.getElementById('add-employee-message');
    const getValue = id => document.getElementById(id)?.value.trim() || '';
    const employee = {
        employee_no: getValue('add-employee-no'),
        first_name: getValue('add-first-name'),
        middle_name: getValue('add-middle-name'),
        last_name: getValue('add-last-name'),
        phone: getValue('add-phone'),
        department_name: getValue('add-department'),
        position_name: getValue('add-position'),
        hire_date: document.getElementById('add-hire-date')?.value || null,
        address: getValue('add-address'),
        employment_type: document.getElementById('add-employment-type')?.value || 'REGULAR',
        employment_status: document.getElementById('add-employment-status')?.value || 'ACTIVE'
    };
    employee.display_name = `${employee.first_name} ${employee.last_name}`.trim();

    if (!button || !message) return;
    button.disabled = true;
    button.textContent = 'Saving...';
    message.className = 'hidden text-sm rounded-xl px-4 py-3';

    try {
        const response = await fetch('api/employees/create.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify(employee)
        });
        const result = await response.json();
        if (!response.ok || !result.success) {
            throw new Error(result.message || result.error || 'Failed to create employee.');
        }

        message.textContent = 'Employee saved successfully.';
        message.className = 'text-sm rounded-xl px-4 py-3 bg-emerald-50 text-emerald-700 border border-emerald-200';
        appendMobileEmployeeRow(employee);
        document.getElementById('add-employee-form').reset();
        setTimeout(closeAddEmployeeModal, 500);
    } catch (error) {
        message.textContent = error.message || 'Unable to save employee.';
        message.className = 'text-sm rounded-xl px-4 py-3 bg-red-50 text-red-700 border border-red-200';
    } finally {
        button.disabled = false;
        button.textContent = 'Save Employee';
    }
}

// Desktop header interactions shared with the main admin page.
(function () {
    const NOTIFICATION_PREF_KEY = 'workforce_admin_notifications_enabled';

    function escapeAdminHeaderHtml(value) {
        const element = document.createElement('div');
        element.textContent = value == null ? '' : String(value);
        return element.innerHTML;
    }

    function setSearchExpanded(expanded) {
        document.getElementById('admin-global-search')?.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    }

    function getEmployeeRows() {
        return Array.from(document.querySelectorAll('#employees-table-body tr[data-id]'));
    }

    window.handleAdminGlobalSearch = function () {
        const input = document.getElementById('admin-global-search');
        const results = document.getElementById('admin-global-search-results');
        if (!input || !results) return;

        const query = input.value.trim().toLowerCase();
        if (!query) {
            results.innerHTML = '';
            results.classList.add('hidden');
            setSearchExpanded(false);
            return;
        }

        const matches = getEmployeeRows().filter(row => {
            const data = row.dataset;
            return [data.name, data.id, data.position, data.department, data.status]
                .filter(Boolean).join(' ').toLowerCase().includes(query);
        }).slice(0, 8);

        if (!matches.length) {
            results.innerHTML = '<div class="px-4 py-5 text-center"><i class="fa-solid fa-user-slash text-gray-300 text-lg"></i><p class="text-sm font-semibold text-gray-500 mt-2">No employees found</p><p class="text-xs text-gray-400 mt-1">Try an employee name or ID.</p></div>';
            results.classList.remove('hidden');
            setSearchExpanded(true);
            return;
        }

        results.innerHTML = matches.map((row, index) => {
            const data = row.dataset;
            const status = String(data.status || 'ACTIVE').toUpperCase();
            const statusClass = status === 'LEAVE' ? 'bg-red-100 text-red-600' : 'bg-emerald-100 text-emerald-700';
            return `<button type="button" class="admin-search-result w-full flex items-center gap-3 p-3 rounded-xl text-left" data-result-index="${index}"><span class="w-10 h-10 rounded-xl bg-background flex items-center justify-center text-primary shrink-0"><i class="fa-solid fa-user"></i></span><span class="min-w-0 flex-1"><span class="block text-sm font-semibold text-primary truncate">${escapeAdminHeaderHtml(data.name)}</span><span class="block text-xs text-gray-500 truncate">${escapeAdminHeaderHtml(data.id)} - ${escapeAdminHeaderHtml(data.position || 'Employee')}</span></span><span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-bold ${statusClass}">${escapeAdminHeaderHtml(status)}</span></button>`;
        }).join('');

        results.querySelectorAll('.admin-search-result').forEach((button, index) => {
            button.addEventListener('click', () => {
                const row = matches[index];
                if (!row) return;
                const employeeName = row.dataset.name || row.dataset.id || '';
                window.closeAdminHeaderMenus();
                if (typeof switchTab === 'function') switchTab('employees');
                const employeeSearch = document.getElementById('employee-search');
                if (employeeSearch) {
                    employeeSearch.value = employeeName;
                    if (typeof filterEmployeeTable === 'function') filterEmployeeTable();
                }
                setTimeout(() => {
                    if (typeof openEmployeeProfile === 'function') openEmployeeProfile(row);
                }, 80);
            });
        });

        results.classList.remove('hidden');
        setSearchExpanded(true);
    };

    window.closeAdminHeaderMenus = function () {
        document.getElementById('admin-global-search-results')?.classList.add('hidden');
        document.getElementById('admin-notification-panel')?.classList.add('hidden');
        document.getElementById('admin-settings-panel')?.classList.add('hidden');
        setSearchExpanded(false);
        document.getElementById('admin-notification-button')?.setAttribute('aria-expanded', 'false');
        document.getElementById('admin-settings-button')?.setAttribute('aria-expanded', 'false');
    };

    window.refreshAdminNotifications = function () {
        const list = document.getElementById('admin-notification-list');
        const summary = document.getElementById('admin-notification-summary');
        const dot = document.getElementById('admin-notification-dot');
        if (!list || !summary || !dot) return;

        if (localStorage.getItem(NOTIFICATION_PREF_KEY) === 'false') {
            dot.style.display = 'none';
            summary.textContent = 'Notification alerts are disabled.';
            list.innerHTML = '<div class="px-4 py-8 text-center"><i class="fa-solid fa-bell-slash text-gray-300 text-2xl"></i><p class="text-sm font-semibold text-gray-500 mt-2">Alerts are off</p><p class="text-xs text-gray-400 mt-1">Turn them back on from Settings.</p></div>';
            return;
        }

        const pendingTimesheets = Number.parseInt(document.getElementById('ts-summary-pending')?.textContent || '0', 10) || 0;
        const pendingCorrections = document.querySelectorAll('#attendance-correction-table-body tr[data-status="PENDING"]').length;
        const items = [];
        if (pendingTimesheets) items.push({ title: `${pendingTimesheets} pending timesheet${pendingTimesheets === 1 ? '' : 's'}`, detail: 'Timesheet approvals need review.', action: () => switchTab('timesheet'), icon: 'fa-file-signature', iconClass: 'bg-amber-50 text-amber-600' });
        if (pendingCorrections) items.push({ title: `${pendingCorrections} attendance correction${pendingCorrections === 1 ? '' : 's'}`, detail: 'Employee correction requests are waiting.', action: () => switchTab('time-attendance'), icon: 'fa-user-pen', iconClass: 'bg-blue-50 text-primary' });

        dot.style.display = items.length ? '' : 'none';
        summary.textContent = items.length ? `${items.length} item${items.length === 1 ? '' : 's'} need your attention.` : 'You are all caught up.';
        list.innerHTML = items.length ? items.map((item, index) => `<button type="button" class="admin-notification-item w-full flex items-start gap-3 p-3 rounded-xl text-left hover:bg-background transition-colors" data-notification-index="${index}"><span class="w-9 h-9 rounded-xl ${item.iconClass} flex items-center justify-center shrink-0"><i class="fa-solid ${item.icon}"></i></span><span class="min-w-0 flex-1"><span class="block text-sm font-semibold text-primary">${escapeAdminHeaderHtml(item.title)}</span><span class="block text-xs text-gray-500 mt-0.5">${escapeAdminHeaderHtml(item.detail)}</span></span><i class="fa-solid fa-chevron-right text-[10px] text-gray-300 mt-2"></i></button>`).join('') : '<div class="px-4 py-8 text-center"><i class="fa-solid fa-circle-check text-emerald-500 text-2xl"></i><p class="text-sm font-semibold text-primary mt-2">No pending notifications</p><p class="text-xs text-gray-400 mt-1">There is nothing waiting for your review.</p></div>';
        list.querySelectorAll('.admin-notification-item').forEach((button, index) => button.addEventListener('click', () => { window.closeAdminHeaderMenus(); items[index]?.action(); }));
    };

    function syncNotificationSettingUI() {
        const enabled = localStorage.getItem(NOTIFICATION_PREF_KEY) !== 'false';
        const text = document.getElementById('admin-notification-toggle-text');
        const pill = document.getElementById('admin-notification-toggle-pill');
        const knob = pill?.querySelector('span');
        if (text) text.textContent = enabled ? 'Enabled' : 'Disabled';
        if (pill) pill.className = `inline-flex w-10 h-6 rounded-full relative transition-colors ${enabled ? 'bg-primary' : 'bg-gray-300'}`;
        if (knob) knob.className = `absolute top-1 w-4 h-4 rounded-full bg-white transition-all ${enabled ? 'left-5' : 'left-1'}`;
    }

    window.toggleAdminNotifications = function (event) {
        event?.stopPropagation();
        const panel = document.getElementById('admin-notification-panel');
        if (!panel) return;
        const open = panel.classList.contains('hidden');
        document.getElementById('admin-settings-panel')?.classList.add('hidden');
        document.getElementById('admin-global-search-results')?.classList.add('hidden');
        panel.classList.toggle('hidden', !open);
        document.getElementById('admin-notification-button')?.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) refreshAdminNotifications();
    };

    window.toggleAdminSettings = function (event) {
        event?.stopPropagation();
        const panel = document.getElementById('admin-settings-panel');
        if (!panel) return;
        const open = panel.classList.contains('hidden');
        document.getElementById('admin-notification-panel')?.classList.add('hidden');
        document.getElementById('admin-global-search-results')?.classList.add('hidden');
        panel.classList.toggle('hidden', !open);
        document.getElementById('admin-settings-button')?.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) syncNotificationSettingUI();
    };

    window.openSettingsEmployees = function () {
        window.closeAdminHeaderMenus();
        if (typeof switchTab === 'function') switchTab('employees');
        document.getElementById('employee-search')?.focus();
    };

    window.toggleAdminNotificationAlerts = function (event) {
        event?.stopPropagation();
        const enabled = localStorage.getItem(NOTIFICATION_PREF_KEY) !== 'false';
        localStorage.setItem(NOTIFICATION_PREF_KEY, enabled ? 'false' : 'true');
        syncNotificationSettingUI();
        refreshAdminNotifications();
    };

    document.addEventListener('click', event => {
        const ids = ['admin-global-search-wrap', 'admin-notification-panel', 'admin-notification-button', 'admin-settings-panel', 'admin-settings-button'];
        if (!ids.some(id => document.getElementById(id)?.contains(event.target))) window.closeAdminHeaderMenus();
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') window.closeAdminHeaderMenus();
    });

    document.addEventListener('DOMContentLoaded', () => {
        syncNotificationSettingUI();
        setTimeout(window.refreshAdminNotifications, 0);
    });
})();