// =============================================================================
// shared-data.js — Holiday Travelers Workforce Management Subsystem
// -----------------------------------------------------------------------------
// ONE shared data layer for desktop + mobile.
//
// Data flow:
//   Browser -> api/shared-data.php -> MySQL/phpMyAdmin database
//
// localStorage is kept only as an offline fallback/cache. This means the same
// database records can be viewed and edited from a laptop and cellphone when
// both are connected to the same hosted application.
//
// IMPORTANT: Serve the project through Apache/PHP (for example XAMPP). Do not
// open the HTML files directly with file:// if you want cross-device syncing.
//
// Existing pages can keep using the synchronous SharedData.getX()/setX() API,
// so manager.js / employee.js / admin.js do not need to be rewritten.
// =============================================================================

(function (global) {
    'use strict';

    const STORAGE_PREFIX = 'ht_';
    const STORAGE_VERSION = 'v2';
    const API_URL = '/api/shared-data.php';
    const REQUEST_TIMEOUT_MS = 5000;

    function todayISO() {
        return new Date().toISOString().slice(0, 10);
    }

    // ---- Seed data ----------------------------------------------------------
    // The first device that reaches an empty server collection initializes it.
    // After that, the server copy is authoritative for connected devices.
    const SEED = {
        employees: [
            { id: 'ADM-001', name: 'Admin Account', email: 'admin@holidaytravels.com', role: 'admin', position: 'System Administrator', department: 'Management', managerId: null, initials: 'AA', color: '#0C2340', status: 'ACTIVE' },
            { id: 'MGR-001', name: 'Manager Account', email: 'manager@holidaytravels.com', role: 'manager', position: 'Operations Manager', department: 'Operations', managerId: null, initials: 'MA', color: '#163B6D', status: 'ACTIVE' },
            { id: 'EMP-000', name: 'Employee Account', email: 'employee@holidaytravels.com', role: 'employee', position: 'Tour Guide', department: 'Operations', managerId: 'MGR-001', initials: 'EA', color: '#F59B45', status: 'ACTIVE' },
            { id: 'EMP-001', name: 'Elena Rostova', email: 'elena.rostova@holidaytravels.com', role: 'employee', position: 'Tour Guide', department: 'Operations', managerId: 'MGR-001', initials: 'ER', color: '#EF4444', status: 'ACTIVE' },
            { id: 'EMP-002', name: 'David Kim', email: 'david.kim@holidaytravels.com', role: 'employee', position: 'Tour Guide', department: 'Operations', managerId: 'MGR-001', initials: 'DK', color: '#6FA9E6', status: 'ACTIVE' },
            { id: 'EMP-003', name: 'Mateo Rossi', email: 'mateo.rossi@holidaytravels.com', role: 'employee', position: 'Front Desk', department: 'Guest Services', managerId: 'MGR-001', initials: 'MR', color: '#F59B45', status: 'ACTIVE' },
            { id: 'EMP-004', name: 'Sofia Alvarez', email: 'sofia.alvarez@holidaytravels.com', role: 'employee', position: 'Front Desk', department: 'Guest Services', managerId: 'MGR-001', initials: 'SA', color: '#8B5CF6', status: 'ACTIVE' },
            { id: 'EMP-005', name: 'James Chen', email: 'james.chen@holidaytravels.com', role: 'employee', position: 'Tour Guide', department: 'Operations', managerId: 'MGR-001', initials: 'JC', color: '#10B981', status: 'ACTIVE' }
        ],

        attendanceRecords: [
            { id: 1, employeeId: 'EMP-001', date: todayISO(), schedule: '08:00-17:00', clockIn: '07:58', clockOut: '17:02', status: 'Present' },
            { id: 2, employeeId: 'EMP-002', date: todayISO(), schedule: '08:00-17:00', clockIn: '08:21', clockOut: null, status: 'Late' },
            { id: 3, employeeId: 'EMP-003', date: todayISO(), schedule: '09:00-18:00', clockIn: null, clockOut: null, status: 'Absent' },
            { id: 4, employeeId: 'EMP-004', date: todayISO(), schedule: '08:00-17:00', clockIn: null, clockOut: null, status: 'On Leave' },
            { id: 5, employeeId: 'EMP-000', date: todayISO(), schedule: '08:00-17:00', clockIn: '07:55', clockOut: '17:05', status: 'Present' }
        ],

        leaveRequests: [
            { id: 1, employeeId: 'EMP-001', name: 'Elena Rostova', initials: 'ER', color: '#EF4444', type: 'Annual Leave', status: 'Approved', dates: 'Oct 15 - Oct 25', reason: 'Family Vacation' },
            { id: 2, employeeId: 'EMP-002', name: 'David Kim', initials: 'DK', color: '#6FA9E6', type: 'Sick Leave', status: 'Approved', dates: 'Oct 12 - Oct 13', reason: 'Flu Case' },
            { id: 3, employeeId: 'EMP-003', name: 'Mateo Rossi', initials: 'MR', color: '#F59B45', type: 'Comp Off', status: 'Pending', dates: 'Oct 20', reason: 'Worked on Weekend tours' }
        ],

        shiftRequests: [],

        schedules: [
            { employeeId: 'EMP-001', week: 'Aug 10-16, 2026', mon: '8-5', tue: '8-5', wed: 'OFF', thu: '8-5', fri: '8-5' },
            { employeeId: 'EMP-002', week: 'Aug 10-16, 2026', mon: '9-6', tue: '9-6', wed: '9-6', thu: 'OFF', fri: '9-6' },
            { employeeId: 'EMP-003', week: 'Aug 10-16, 2026', mon: '8-5', tue: 'ABSENT', wed: '8-5', thu: '8-5', fri: '8-5' },
            { employeeId: 'EMP-004', week: 'Aug 10-16, 2026', mon: 'OFF', tue: '8-5', wed: 'LEAVE', thu: 'LEAVE', fri: 'LEAVE' }
        ]
    };

    // ---- Low-level storage helpers ------------------------------------------
    function storageKey(collection) {
        return `${STORAGE_PREFIX}${STORAGE_VERSION}_${collection}`;
    }

    function readLocalCollection(collection) {
        try {
            const raw = localStorage.getItem(storageKey(collection));
            return raw === null ? null : JSON.parse(raw);
        } catch (err) {
            console.error(`shared-data: failed to read local cache "${collection}"`, err);
            return null;
        }
    }

    function writeLocalCollection(collection, value) {
        try {
            localStorage.setItem(storageKey(collection), JSON.stringify(value));
            return true;
        } catch (err) {
            console.error(`shared-data: failed to write local cache "${collection}"`, err);
            return false;
        }
    }

    function apiRequest(method, collection, value) {
        try {
            const xhr = new XMLHttpRequest();
            const query = `${API_URL}?collection=${encodeURIComponent(collection)}`;
            xhr.open(method, query, false); // Keep existing SharedData API synchronous.
            xhr.setRequestHeader('Accept', 'application/json');
            if (method !== 'GET') {
                xhr.setRequestHeader('Content-Type', 'application/json');
                xhr.send(JSON.stringify({ collection, data: value }));
            } else {
                xhr.send(null);
            }

            if (xhr.status < 200 || xhr.status >= 300) return { ok: false, data: null };
            const payload = JSON.parse(xhr.responseText || '{}');
            if (!payload.success) return { ok: false, data: null };
            return { ok: true, data: payload.data };
        } catch (err) {
            // This is expected when the project is opened without Apache/PHP or
            // the device is temporarily offline; local cache remains available.
            console.warn(`shared-data: server ${method} failed for "${collection}"; using local cache`, err);
            return { ok: false, data: null };
        }
    }

    function readCollection(collection) {
        const remote = apiRequest('GET', collection);
        if (remote.ok && remote.data !== null && remote.data !== undefined) {
            writeLocalCollection(collection, remote.data);
            return remote.data;
        }
        return readLocalCollection(collection);
    }

    function writeCollection(collection, value) {
        const remote = apiRequest('PUT', collection, value);
        if (remote.ok) {
            return writeLocalCollection(collection, remote.data ?? value);
        }
        return writeLocalCollection(collection, value);
    }

    function readLegacyLocalCollection(collection) {
        try {
            const raw = localStorage.getItem(`${STORAGE_PREFIX}v1_${collection}`);
            return raw === null ? null : JSON.parse(raw);
        } catch (err) {
            console.warn(`shared-data: failed to read legacy cache \"${collection}\"`, err);
            return null;
        }
    }

    function ensureSeeded() {
        Object.keys(SEED).forEach(collection => {
            const remote = apiRequest('GET', collection);
            if (remote.ok) {
                if (remote.data === null || remote.data === undefined) {
                    // Migrate an existing v1 browser copy to the shared server
                    // on first connection; otherwise initialize the seed data.
                    const legacy = readLegacyLocalCollection(collection);
                    writeCollection(collection, legacy === null ? SEED[collection] : legacy);
                } else {
                    writeLocalCollection(collection, remote.data);
                }
                return;
            }

            if (readLocalCollection(collection) === null) {
                const legacy = readLegacyLocalCollection(collection);
                writeLocalCollection(collection, legacy === null ? SEED[collection] : legacy);
            }
        });
    }

    ensureSeeded();

    // ---- Public API -----------------------------------------------------------
    const SharedData = {
        get(collection) {
            const data = readCollection(collection);
            return data === null || data === undefined ? [] : data;
        },

        set(collection, value) {
            return writeCollection(collection, value);
        },

        getEmployees() { return this.get('employees'); },
        setEmployees(v) { return this.set('employees', v); },

        getAttendance() { return this.get('attendanceRecords'); },
        setAttendance(v) { return this.set('attendanceRecords', v); },

        getLeaveRequests() { return this.get('leaveRequests'); },
        setLeaveRequests(v) { return this.set('leaveRequests', v); },

        getSchedules() { return this.get('schedules'); },
        setSchedules(v) { return this.set('schedules', v); },

        getShiftRequests() { return this.get('shiftRequests'); },
        setShiftRequests(v) { return this.set('shiftRequests', v); },

        findEmployeeById(id) {
            return this.getEmployees().find(e => e.id === id) || null;
        },

        findEmployeeByEmail(email) {
            const normalized = (email || '').trim().toLowerCase();
            return this.getEmployees().find(e => (e.email || '').toLowerCase() === normalized) || null;
        },

        getTeamForManager(managerId) {
            return this.getEmployees().filter(e => e.managerId === managerId);
        },

        // Session stays device/browser-local on purpose; it is login state, not
        // shared application data. Users can sign in separately on each device.
        setSession(user) {
            try {
                sessionStorage.setItem(`${STORAGE_PREFIX}session`, JSON.stringify(user));
                return true;
            } catch (err) {
                console.error('shared-data: failed to save session', err);
                return false;
            }
        },

        getSession() {
            try {
                const raw = sessionStorage.getItem(`${STORAGE_PREFIX}session`);
                return raw ? JSON.parse(raw) : null;
            } catch (err) {
                console.error('shared-data: failed to read session', err);
                return null;
            }
        },

        clearSession() {
            sessionStorage.removeItem(`${STORAGE_PREFIX}session`);
        },

        resetAll() {
            Object.keys(SEED).forEach(collection => {
                this.set(collection, SEED[collection]);
            });
            this.clearSession();
        },

        requireRole(allowedRoles, redirectTo) {
            const session = this.getSession();
            const destination = redirectTo || 'login.html';
            if (!session || !allowedRoles.includes(session.role)) {
                window.location.href = destination;
                return null;
            }
            return session;
        }
    };

    const ROLE_LABELS = { admin: 'Administrator', manager: 'Manager', employee: 'Employee', user: 'Employee' };

    function applySessionToDOM() {
        const session = SharedData.getSession();
        if (!session) return;

        document.querySelectorAll('[data-session="name"]').forEach(el => {
            el.textContent = session.name || el.textContent;
        });
        document.querySelectorAll('[data-session="role"]').forEach(el => {
            el.textContent = ROLE_LABELS[session.role] || session.role || el.textContent;
        });
        document.querySelectorAll('[data-session="email"]').forEach(el => {
            el.textContent = session.email || el.textContent;
        });
    }

    document.addEventListener('DOMContentLoaded', applySessionToDOM);

    global.SharedData = SharedData;
})(window);
