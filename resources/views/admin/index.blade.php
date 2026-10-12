<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Holiday Travelers - Admin Portal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@500;700&display=swap"
        rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <style>
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #F8FAFC;
        }

        ::-webkit-scrollbar-thumb {
            background: #E5E7EB;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #6FA9E6;
        }

        .transition-all-300 {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(5px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ================= ADMIN HEADER INTERACTIONS ================= */
        #admin-global-search-results .admin-search-result:hover {
            background: #F8FAFC;
        }

        #admin-global-search-results .admin-search-result:focus-visible,
        #admin-notification-panel button:focus-visible,
        #admin-settings-panel button:focus-visible {
            outline: 2px solid #6FA9E6;
            outline-offset: 2px;
        }

        #admin-notification-panel .admin-notification-item+.admin-notification-item {
            border-top: 1px solid #E5E7EB;
        }

        #admin-notification-dot.admin-alerts-disabled {
            display: none;
        }

        /* ================= TIMESHEET MODULE ENHANCEMENTS ================= */
        #timesheet .ts-summary-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        #timesheet .ts-summary-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
        }

        #timesheet .ts-info-box {
            background: #F8FAFC;
            border: 1px solid #E5E7EB;
            border-radius: 16px;
            padding: 12px 14px;
        }

        #timesheet .ts-info-box span {
            display: block;
            color: #6B7280;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-weight: 700;
            margin-bottom: 3px;
        }

        #timesheet .ts-info-box strong {
            display: block;
            color: #163B6D;
            font-size: 13px;
            line-height: 1.35;
        }

        #timesheet .ts-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 11px;
            line-height: 1;
            font-weight: 700;
            border: 1px solid transparent;
            white-space: nowrap;
        }

        #timesheet .ts-status-badge::before {
            content: "";
            width: 6px;
            height: 6px;
            border-radius: 999px;
            background: currentColor;
        }

        #timesheet .status-pending {
            color: #B45309;
            background: #FFFBEB;
            border-color: #FDE68A;
        }

        #timesheet .status-approved {
            color: #047857;
            background: #ECFDF5;
            border-color: #A7F3D0;
        }

        #timesheet .status-rejected {
            color: #B91C1C;
            background: #FEF2F2;
            border-color: #FECACA;
        }

        #timesheet .status-correction {
            color: #92400E;
            background: #FFFBEB;
            border-color: #FCD34D;
        }

        #timesheet .ts-record-card {
            position: relative;
            overflow: hidden;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }

        #timesheet .ts-record-card:hover {
            border-color: #CBD5E1;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        }

        #timesheet .ts-record-card.ts-high-overtime {
            border-color: #FECACA;
        }

        #timesheet .ts-high-ot-bar {
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: #EF4444;
        }

        #timesheet .ts-high-ot-value {
            color: #DC2626;
            background: #FEF2F2;
            border: 1px solid #FECACA;
            border-radius: 10px;
            padding: 3px 8px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-weight: 800;
        }

        #timesheet .ts-high-ot-label {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-top: 6px;
            color: #B91C1C;
            background: #FEF2F2;
            border: 1px solid #FECACA;
            border-radius: 999px;
            padding: 4px 9px;
            font-size: 10px;
            line-height: 1;
            font-weight: 800;
        }

        #timesheet .ts-action-btn {
            transition: background 0.2s ease, color 0.2s ease, opacity 0.2s ease;
        }

        #timesheet .ts-action-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        @media (max-width: 640px) {
            #timesheet .ts-summary-card {
                padding: 13px;
            }

            #timesheet .ts-record-card .ts-stat-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                width: 100%;
            }

            #timesheet .ts-record-card .ts-actions {
                width: 100%;
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>
</head>

<body class="bg-background text-primary font-body antialiased flex h-screen overflow-hidden">


    <div id="app-workspace" class="h-screen w-full flex">
        <!-- Sidebar Navigation (Admin Dark Theme) -->
        <aside class="w-64 bg-primary text-gray-300 flex-shrink-0 hidden md:flex flex-col shadow-xl z-20 pb-6">
            <!-- Brand -->
            <div class="h-16 flex items-center px-6 border-b border-white/10 bg-primary">
                <img src="assets/images/Company_logo.png" alt="Company Logo" class="mr-3 object-contain"
                    style="height: 72px; width: auto;">
                <span class="font-bold text-lg text-white tracking-tight">Workforce Admin</span>
            </div>

            <!-- User Info -->
            <div class="p-6 border-b border-white/10 bg-primary/50">
                <div class="flex items-center space-x-3">
                    <img src="assets/images/profile.png" alt="Admin Avatar"
                        class="h-12 w-12 rounded-full object-cover border-2 border-accent">
                    <div>
                        <h3 class="text-sm font-semibold text-white" data-session="name">Admin Name</h3>
                        <p class="text-xs text-gray-400" data-session="role">Admin Role</p>
                    </div>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">

                <a href="#"
                    class="nav-btn active flex items-center px-3 py-2.5 text-sm font-medium rounded-2xl bg-secondary text-white"
                    data-target="dashboard">
                    <i class="fa-solid fa-chart-line w-6 text-center mr-2"></i> Overview
                </a>
                <a href="#"
                    class="nav-btn flex items-center px-3 py-2.5 text-sm font-medium rounded-2xl text-gray-400 hover:bg-white/10 hover:text-white transition-colors"
                    data-target="employees">
                    <i class="fa-solid fa-users w-6 text-center mr-2"></i> Employees
                </a>

                <hr class="border-t border-white/10 my-3">

                <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 mt-2">Modules</p>

                <a href="#"
                    class="nav-btn flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-2xl text-gray-400 hover:bg-white/10 hover:text-white transition-colors"
                    data-target="time-attendance" onclick="toggleAttendanceSubmenu(event)">
                    <span class="flex items-center">
                        <i class="fa-solid fa-users-viewfinder w-6 text-center mr-2"></i> Live Attendance
                    </span>
                    <i id="attendance-submenu-chevron"
                        class="fa-solid fa-chevron-down text-xs transition-transform"></i>
                </a>
                <div id="attendance-submenu" class="hidden ml-8 space-y-1 overflow-hidden">

                    <a href="#"
                        class="nav-btn flex items-center px-3 py-2 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/10 hover:text-white transition-colors"
                        data-target="overtime">
                        <i class="fa-solid fa-business-time w-5 text-center mr-2 text-xs"></i>
                        Overtime
                    </a>

                    <a href="#"
                        class="nav-btn flex items-center px-3 py-2 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/10 hover:text-white transition-colors"
                        data-target="kiosk-log">
                        <i class="fa-solid fa-id-card w-5 text-center mr-2 text-xs"></i>
                        Log Entry
                    </a>

                </div>
                <a href="#"
                    class="nav-btn flex items-center px-3 py-2.5 text-sm font-medium rounded-2xl text-gray-400 hover:bg-white/10 hover:text-white transition-colors"
                    data-target="schedule">
                    <i class="fa-solid fa-calendar-check w-6 text-center mr-2"></i> Master Roster
                </a>
                <a href="#"
                    class="nav-btn flex items-center px-3 py-2.5 text-sm font-medium rounded-2xl text-gray-400 hover:bg-white/10 hover:text-white transition-colors"
                    data-target="timesheet">
                    <i class="fa-solid fa-file-signature w-6 text-center mr-2"></i> Timesheet Approvals
                </a>
                <a href="#"
                    class="nav-btn flex items-center px-3 py-2.5 text-sm font-medium rounded-2xl text-gray-400 hover:bg-white/10 hover:text-white transition-colors"
                    data-target="leave">
                    <i class="fa-solid fa-plane-slash w-6 text-center mr-2"></i> Leave Requests
                </a>
                <a href="#"
                    class="nav-btn flex items-center px-3 py-2.5 text-sm font-medium rounded-2xl text-gray-400 hover:bg-white/10 hover:text-white transition-colors"
                    data-target="analytics">
                    <i class="fa-solid fa-chart-pie w-6 text-center mr-2"></i> Workforce Analytics
                </a>
            </nav>

            <!-- Footer / Logout -->
            <button onclick="logout()"
                class="w-full py-2 bg-error/10 hover:bg-error/20 text-error font-button text-sm rounded-xl flex items-center justify-center gap-2 transition-all-300 mb-4">
                <i data-lucide="log-out" class="w-4 h-5"></i>
                Logout System
            </button>

        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            <!-- Mobile Header -->
            <header class="bg-primary flex items-center justify-between px-4 h-16 md:hidden z-30 shadow-md">
                <div class="flex items-center">
                    <i class="fa-solid fa-earth-americas text-secondary mr-2"></i>
                    <span class="font-bold text-white">Workforce Admin</span>
                </div>
                <button id="mobile-menu-btn" class="text-gray-300 hover:text-white focus:outline-none p-2">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
            </header>

            <!-- Mobile Menu Dropdown -->
            <div id="mobile-menu"
                class="hidden md:hidden bg-primary absolute w-full top-16 z-20 shadow-xl border-t border-white/10">
                <nav class="flex flex-col p-2 space-y-1">
                    <!-- Populated by JS -->
                </nav>
            </div>

            <!------------ Top Bar (Desktop) ----------------------------->
            <header
                class="bg-card border-b border-border items-center justify-between px-8 h-16 hidden md:flex z-10 shadow-sm">
                <h1 class="font-heading text-xl font-bold text-primary flex items-center" id="page-title">
                    Overview
                </h1>
                <div class="flex items-center space-x-6">
                    <!-- Global Employee Search -->
                    <div class="relative" id="admin-global-search-wrap">
                        <i
                            class="fa-solid fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                        <input id="admin-global-search" type="text" autocomplete="off"
                            placeholder="Search staff, tours..." aria-label="Search employees"
                            aria-controls="admin-global-search-results" aria-expanded="false"
                            oninput="handleAdminGlobalSearch()" onfocus="handleAdminGlobalSearch()"
                            class="pl-10 pr-4 py-2 border border-border rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-accent bg-background w-64">
                        <div id="admin-global-search-results"
                            class="hidden absolute right-0 top-12 w-80 max-h-80 overflow-y-auto bg-card border border-border rounded-2xl shadow-xl z-[80] p-2"
                            role="listbox" aria-label="Employee search results"></div>
                    </div>

                    <!-- Notifications -->
                    <div class="relative">
                        <button id="admin-notification-button" type="button" onclick="toggleAdminNotifications(event)"
                            class="font-button relative p-2 text-gray-400 hover:text-accent transition-colors rounded-xl hover:bg-background focus:outline-none focus:ring-2 focus:ring-accent/40"
                            aria-label="Notifications" aria-haspopup="true" aria-expanded="false">
                            <i class="fa-solid fa-bell text-xl"></i>
                            <span id="admin-notification-dot"
                                class="absolute top-1.5 right-1.5 block h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-white"></span>
                        </button>
                        <div id="admin-notification-panel"
                            class="hidden absolute right-0 top-12 w-96 max-w-[calc(100vw-2rem)] bg-card border border-border rounded-2xl shadow-xl z-[80] overflow-hidden">
                            <div class="px-4 py-3 border-b border-border flex items-center justify-between">
                                <div>
                                    <p class="font-heading font-bold text-primary text-sm">Notifications</p>
                                    <p id="admin-notification-summary" class="text-xs text-gray-500 mt-0.5">Checking
                                        pending items...</p>
                                </div>
                                <button type="button" onclick="refreshAdminNotifications(); event.stopPropagation();"
                                    class="text-xs font-semibold text-accent hover:text-primary px-2 py-1 rounded-lg hover:bg-background">Refresh</button>
                            </div>
                            <div id="admin-notification-list" class="max-h-80 overflow-y-auto p-2"></div>
                        </div>
                    </div>

                    <!-- Settings -->
                    <div class="relative">
                        <button id="admin-settings-button" type="button" onclick="toggleAdminSettings(event)"
                            class="font-button relative p-2 text-gray-400 hover:text-accent transition-colors rounded-xl hover:bg-background focus:outline-none focus:ring-2 focus:ring-accent/40"
                            aria-label="Settings" aria-haspopup="true" aria-expanded="false">
                            <i class="fa-solid fa-gear text-xl"></i>
                        </button>
                        <div id="admin-settings-panel"
                            class="hidden absolute right-0 top-12 w-80 max-w-[calc(100vw-2rem)] bg-card border border-border rounded-2xl shadow-xl z-[80] overflow-hidden">
                            <div class="px-4 py-3 border-b border-border">
                                <p class="font-heading font-bold text-primary text-sm">Admin Settings</p>
                                <p class="text-xs text-gray-500 mt-0.5">Quick controls for the admin header.</p>
                            </div>
                            <div class="p-3 space-y-2">
                                <button type="button" onclick="openSettingsEmployees()"
                                    class="w-full flex items-center justify-between gap-3 p-3 rounded-xl hover:bg-background transition-colors text-left">
                                    <span class="flex items-center gap-3">
                                        <span
                                            class="w-9 h-9 rounded-xl bg-blue-50 text-primary flex items-center justify-center"><i
                                                class="fa-solid fa-users"></i></span>
                                        <span>
                                            <span class="block text-sm font-semibold text-primary">Employee
                                                Management</span>
                                            <span class="block text-xs text-gray-500">Open the employee directory</span>
                                        </span>
                                    </span>
                                    <i class="fa-solid fa-chevron-right text-xs text-gray-400"></i>
                                </button>
                                <button id="admin-notification-toggle" type="button"
                                    onclick="toggleAdminNotificationAlerts(event)"
                                    class="w-full flex items-center justify-between gap-3 p-3 rounded-xl hover:bg-background transition-colors text-left">
                                    <span class="flex items-center gap-3">
                                        <span
                                            class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center"><i
                                                class="fa-solid fa-bell"></i></span>
                                        <span>
                                            <span class="block text-sm font-semibold text-primary">Notification
                                                Alerts</span>
                                            <span id="admin-notification-toggle-text"
                                                class="block text-xs text-gray-500">Enabled</span>
                                        </span>
                                    </span>
                                    <span id="admin-notification-toggle-pill"
                                        class="inline-flex w-10 h-6 rounded-full bg-primary relative transition-colors">
                                        <span
                                            class="absolute top-1 left-5 w-4 h-4 rounded-full bg-white transition-all"></span>
                                    </span>
                                </button>
                                <div class="border-t border-border pt-2 mt-2 flex justify-end">
                                    <button type="button" onclick="closeAdminHeaderMenus()"
                                        class="text-xs font-semibold text-gray-500 hover:text-primary px-3 py-2 rounded-lg hover:bg-background">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Scrollable Content -->
            <main class="flex-1 overflow-y-auto p-4 md:p-8 bg-background relative">

                <!-- ================= OVERVIEW TAB ================= -->
                <div id="dashboard" class="tab-content active max-w-7xl mx-auto space-y-6">

                    <!-- Actionable Alerts -->
                    <div
                        class="bg-accent/10 border-l-4 border-accent p-4 rounded-r-2xl flex justify-between items-start md:items-center flex-col md:flex-row gap-4">
                        <div class="flex items-center">
                            <div class="p-2 bg-accent/20 rounded-full text-primary mr-4">
                                <i class="fa-solid fa-circle-exclamation text-lg"></i>
                            </div>
                            <div>
                                <h3 class="font-heading font-semibold text-primary">Action Required</h3>
                                <p class="font-body text-sm text-primary/80">
                                    You have <strong id="overview-pending-timesheets">0</strong> timesheets and
                                    <strong id="overview-pending-leaves">0</strong> leave requests awaiting your
                                    approval.
                                </p>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="switchTab('timesheet')"
                                class="font-button px-4 py-2 bg-card text-primary text-sm font-medium rounded-xl shadow-sm border border-border hover:bg-background">View
                                Timesheets</button>
                        </div>
                    </div>

                    <!----------------------------------------------- CARDS -------------------------------------------------------------------------->
                    <!-- Admin Metrics Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="bg-card p-5 rounded-2xl shadow-sm border border-border flex flex-col">
                            <div class="flex justify-between items-start mb-4">
                                <div class="p-3 bg-emerald-100 text-emerald-600 rounded-2xl"><i
                                        class="fa-solid fa-users text-xl"></i></div>
                                <span
                                    class="text-emerald-500 text-sm font-bold bg-emerald-50 px-2 py-1 rounded">Live</span>
                            </div>
                            <h3 id="overview-clocked-in" class="font-heading text-3xl font-bold text-primary">0 <span
                                    class="text-lg text-gray-400 font-normal">/ 0</span></h3>
                            <p class="text-sm font-medium text-gray-500 mt-1">Staff Currently Clocked In</p>
                        </div>

                        <div class="bg-card p-5 rounded-2xl shadow-sm border border-border flex flex-col">
                            <div class="flex justify-between items-start mb-4">
                                <div class="p-3 bg-amber-100 text-amber-600 rounded-2xl"><i
                                        class="fa-solid fa-map-location-dot text-xl"></i></div>
                            </div>
                            <h3 id="overview-active-tours" class="font-heading text-3xl font-bold text-primary">0</h3>
                            <p class="text-sm font-medium text-gray-500 mt-1">Active Tours Today</p>
                        </div>

                        <div class="bg-card p-5 rounded-2xl shadow-sm border border-border flex flex-col">
                            <div class="flex justify-between items-start mb-4">
                                <div class="p-3 bg-purple-100 text-purple-600 rounded-2xl"><i
                                        class="fa-solid fa-user-clock text-xl"></i></div>
                                <span class="text-red-500 text-xs font-bold"><i class="fa-solid fa-arrow-trend-up"></i>
                                    2%</span>
                            </div>
                            <h3 id="overview-late-week" class="font-heading text-3xl font-bold text-primary">0</h3>
                            <p class="text-sm font-medium text-gray-500 mt-1">Late Arrivals (This Week)</p>
                        </div>

                        <div class="bg-card p-5 rounded-2xl shadow-sm border border-border flex flex-col">
                            <div class="flex justify-between items-start mb-4">
                                <div class="p-3 bg-red-100 text-red-600 rounded-2xl"><i
                                        class="fa-solid fa-user-minus text-xl"></i></div>
                            </div>
                            <h3 id="overview-on-leave" class="font-heading text-3xl font-bold text-primary">0</h3>
                            <p class="text-sm font-medium text-gray-500 mt-1">Staff on Leave Today</p>
                        </div>
                    </div>

                    <!--------------------------------------------- Live Operations & Upcoming Shifts ------------------------------------------->

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                        <!-- Live Tours -->
                        <div
                            class="lg:col-span-2 bg-card rounded-2xl shadow-sm border border-border flex flex-col h-full">
                            <div
                                class="p-5 border-b border-border flex justify-between items-center bg-background rounded-t-2xl">
                                <h3 class="font-semibold text-primary">Live Tour Operations</h3>
                                <span class="relative flex h-3 w-3">
                                    <span
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                                </span>
                            </div>
                            <div class="p-0 overflow-x-auto">
                                <table class="w-full text-sm text-left">
                                    <thead class="text-xs text-gray-500 uppercase bg-card border-b border-border">
                                        <tr>
                                            <th class="px-5 py-3">Tour / Location</th>
                                            <th class="px-5 py-3">Assigned Guide</th>
                                            <th class="px-5 py-3">Time</th>
                                            <th class="px-5 py-3">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-border">
                                        <tr class="hover:bg-background">
                                            <td class="px-5 py-4">
                                                <p class="font-semibold text-primary">Name of Tour</p>
                                                <p class="text-xs text-gray-500"><i
                                                        class="fa-solid fa-location-dot mr-1"></i> Address</p>
                                            </td>
                                            <td class="px-5 py-4 flex items-center space-x-2">
                                                <img src="assets/images/Employee.png" class="w-8 h-8 rounded-full">
                                                <span class="font-medium">Employee 1</span>
                                            </td>
                                            <td class="px-5 py-4 text-gray-600">--</td>
                                            <td class="px-5 py-4"><span
                                                    class="bg-emerald-100 text-emerald-800 text-xs px-2 py-1 rounded font-semibold">In
                                                    Progress</span></td>
                                        </tr>
                                        <tr class="hover:bg-background">
                                            <td class="px-5 py-4">
                                                <p class="font-semibold text-primary">Name of Tour</p>
                                                <p class="text-xs text-gray-500"><i
                                                        class="fa-solid fa-location-dot mr-1"></i> Address</p>
                                            </td>
                                            <td class="px-5 py-4 flex items-center space-x-2">
                                                <img src="assets/images/Employee.png" class="w-8 h-8 rounded-full">
                                                <span class="font-medium">Employee 2</span>
                                            </td>
                                            <td class="px-5 py-4 text-gray-600">--</td>
                                            <td class="px-5 py-4"><span
                                                    class="bg-emerald-100 text-emerald-800 text-xs px-2 py-1 rounded font-semibold">In
                                                    Progress</span></td>
                                        </tr>
                                        <tr class="hover:bg-background">
                                            <td class="px-5 py-4">
                                                <p class="font-semibold text-primary">Name of Tour</p>
                                                <p class="text-xs text-gray-500"><i
                                                        class="fa-solid fa-location-dot mr-1"></i> Address</p>
                                            </td>
                                            <td class="px-5 py-4 flex items-center space-x-2">
                                                <img src="assets/images/Employee.png" class="w-8 h-8 rounded-full">
                                                <span class="font-medium">Employee 3</span>
                                            </td>
                                            <td class="px-5 py-4 text-gray-600">--</td>
                                            <td class="px-5 py-4"><span
                                                    class="bg-amber-100 text-amber-800 text-xs px-2 py-1 rounded font-semibold">Staging
                                                    (Soon)</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="p-3 border-t border-border bg-background text-center rounded-b-2xl">
                                <a href="active-operations.html" target="_blank" rel="noopener noreferrer"
                                    class="text-sm font-medium text-accent hover:text-primary">
                                    View All Active Operations &rarr;
                                </a>
                            </div>
                        </div>

                        <!--------------------------------------------- Callouts / Unassigned------------------------------------ -->

                        <div class="bg-card rounded-2xl shadow-sm border border-border">
                            <div class="p-5 border-b border-border flex justify-between items-center">
                                <h3 class="font-semibold text-primary">Critical Alerts</h3>
                            </div>
                            <div class="p-4 space-y-4">
                                <!-- DATABASE-DRIVEN SICK CALL-OUT ALERTS -->
                                <div id="critical-alerts-container" class="space-y-4">
                                </div>
                                <!-- Alert 2 -->
                                <div
                                    class="flex items-start space-x-3 p-3 bg-amber-50 rounded-2xl border border-amber-100">
                                    <i class="fa-solid fa-clock-rotate-left text-amber-500 mt-1"></i>
                                    <div>
                                        <p class="text-sm font-bold text-amber-900">Missing Clock-in</p>
                                        <p class="text-xs text-amber-700 mt-1">Employee Name is 15 mins late for HQ
                                            Briefing.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= EMPLOYEES TAB ================= -->
                <div id="employees" class="tab-content max-w-7xl mx-auto space-y-6 hidden">
                    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-2">

                        <!-- Search + Filters -->
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full lg:w-auto">
                            <div class="relative">
                                <i
                                    class="fa-solid fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                                <input id="employee-search" type="text" placeholder="Search Employee"
                                    oninput="filterEmployeeTable()"
                                    class="pl-9 pr-4 py-2.5 border border-border rounded-xl text-sm text-primary bg-card focus:outline-none focus:ring-2 focus:ring-accent w-full sm:w-64">
                            </div>

                            <div class="relative">
                                <select id="employee-filter-department" onchange="filterEmployeeTable()"
                                    class="pl-4 pr-9 py-2.5 border border-border rounded-xl text-sm text-primary bg-card focus:outline-none focus:ring-2 focus:ring-accent appearance-none w-full sm:w-auto">
                                    <option value="">Department</option>
                                    <option value="OPERATIONS">Operations</option>
                                    <option value="MONITORING">Monitoring</option>
                                </select>
                                <i
                                    class="fa-solid fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                            </div>

                            <div class="relative">
                                <select id="employee-filter-status" onchange="filterEmployeeTable()"
                                    class="pl-4 pr-9 py-2.5 border border-border rounded-xl text-sm text-primary bg-card focus:outline-none focus:ring-2 focus:ring-accent appearance-none w-full sm:w-auto">
                                    <option value="">Status</option>
                                    <option value="ACTIVE">Active</option>
                                    <option value="LEAVE">Leave</option>
                                </select>
                                <i
                                    class="fa-solid fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 flex-shrink-0">

                            <button type="button" onclick="openAssignManagerModal()"
                                class="font-button px-5 py-2.5 bg-primary hover:opacity-90 text-white font-medium text-sm rounded-xl shadow-lg flex items-center gap-2 transition-all-300">
                                <i class="fa-solid fa-user-tie"></i>
                                Assign Employee to Manager
                            </button>

                            <button type="button" onclick="openAddEmployeeModal()"
                                class="font-button px-5 py-2.5 bg-secondary hover:bg-[#E08A3B] text-white font-medium text-sm rounded-xl shadow-lg shadow-secondary/30 flex items-center gap-2 transition-all-300">
                                <i class="fa-solid fa-user-plus"></i>
                                Add Employee
                            </button>

                        </div>
                    </div>

                    <div class="bg-card rounded-2xl shadow-sm border border-border overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs font-semibold text-white uppercase bg-secondary">
                                <tr>
                                    <th class="px-5 py-3.5">ID</th>
                                    <th class="px-5 py-3.5">Employee</th>
                                    <th class="px-5 py-3.5">Position</th>
                                    <th class="px-5 py-3.5">Department</th>
                                    <th class="px-5 py-3.5">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border" id="employees-table-body">
                                <tr class="group cursor-pointer hover:bg-background transition-all-300"
                                    onclick="openEmployeeProfile(this)" data-id="EMP1" data-name="EMPLOYEE 1"
                                    data-position="TOUR GUIDE" data-department="OPERATIONS" data-status="ACTIVE">
                                    <td class="px-5 py-4 font-medium text-primary">EMP1</td>
                                    <td class="px-5 py-4 font-semibold text-primary">EMPLOYEE 1</td>
                                    <td class="px-5 py-4 text-gray-600">TOUR GUIDE</td>
                                    <td class="px-5 py-4 text-gray-600">OPERATIONS</td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center justify-between gap-2">
                                            <span
                                                class="inline-flex items-center bg-emerald-100 text-emerald-700 text-xs font-semibold px-3 py-1 rounded-full">ACTIVE</span>
                                            <button
                                                class="hidden md:inline-flex items-center gap-1 text-xs font-medium text-red-600 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                                <i class="fa-solid fa-trash"></i> Remove
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="group cursor-pointer hover:bg-background transition-all-300"
                                    onclick="openEmployeeProfile(this)" data-id="EMP2" data-name="EMPLOYEE 2"
                                    data-position="STAFF" data-department="OPERATIONS" data-status="LEAVE">
                                    <td class="px-5 py-4 font-medium text-primary">EMP2</td>
                                    <td class="px-5 py-4 font-semibold text-primary">EMPLOYEE 2</td>
                                    <td class="px-5 py-4 text-gray-600">STAFF</td>
                                    <td class="px-5 py-4 text-gray-600">OPERATIONS</td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center justify-between gap-2">
                                            <span
                                                class="inline-flex items-center bg-red-100 text-red-600 text-xs font-semibold px-3 py-1 rounded-full">LEAVE</span>
                                            <button
                                                class="hidden md:inline-flex items-center gap-1 text-xs font-medium text-red-600 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                                <i class="fa-solid fa-trash"></i> Remove
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="group cursor-pointer hover:bg-background transition-all-300"
                                    onclick="openEmployeeProfile(this)" data-id="EMP3" data-name="EMPLOYEE 3"
                                    data-position="MANAGER" data-department="MONITORING" data-status="ACTIVE">
                                    <td class="px-5 py-4 font-medium text-primary">EMP3</td>
                                    <td class="px-5 py-4 font-semibold text-primary">EMPLOYEE 3</td>
                                    <td class="px-5 py-4 text-gray-600">MANAGER</td>
                                    <td class="px-5 py-4 text-gray-600">MONITORING</td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center justify-between gap-2">
                                            <span
                                                class="inline-flex items-center bg-emerald-100 text-emerald-700 text-xs font-semibold px-3 py-1 rounded-full">ACTIVE</span>
                                            <button
                                                class="hidden md:inline-flex items-center gap-1 text-xs font-medium text-red-600 opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                                <i class="fa-solid fa-trash"></i> Remove
                                            </button>

                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- ================= KIOSK RFID ASSIGNMENT ================= -->

                    <div class="bg-card rounded-2xl shadow-sm border border-border overflow-hidden">

                        <div
                            class="p-5 border-b border-border flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

                            <div>
                                <h3 class="font-heading font-bold text-primary">
                                    Kiosk RFID Assignment
                                </h3>


                            </div>

                            <button type="button" onclick="openAssignRfidModal()"
                                class="font-button px-5 py-2.5 bg-primary hover:opacity-90 text-white font-medium text-sm rounded-xl shadow-lg flex items-center gap-2 transition-all-300">

                                <i class="fa-solid fa-id-card"></i>

                                Assign Employee ID to RFID (UID)

                            </button>

                        </div>

                        <div class="overflow-x-auto">

                            <table class="w-full text-sm text-left">

                                <thead class="text-xs font-semibold text-white uppercase bg-secondary">

                                    <tr>

                                        <th class="px-5 py-3.5">
                                            Employee ID
                                        </th>

                                        <th class="px-5 py-3.5">
                                            Employee
                                        </th>

                                        <th class="px-5 py-3.5">
                                            RFID UID
                                        </th>

                                        <th class="px-5 py-3.5">
                                            Status
                                        </th>

                                        <th class="px-5 py-3.5">
                                            Assigned At
                                        </th>

                                    </tr>

                                </thead>

                                <tbody id="rfid-assignments-table-body" class="divide-y divide-border">

                                    <tr>

                                        <td colspan="5" class="px-5 py-8 text-center text-gray-400">

                                            Loading RFID assignments...

                                        </td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>

                    </div>
                    <!-- ================= MANAGER TEAM ================= -->
                    <div id="admin-manager-team"
                        class="bg-card rounded-2xl shadow-sm border border-border overflow-hidden">
                        <div
                            class="p-5 border-b border-border flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <h3 class="font-heading font-bold text-primary">Manager Team</h3>
                            </div>

                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                                <div class="relative">
                                    <select id="manager-team-department" onchange="loadManagerTeam()"
                                        class="pl-4 pr-9 py-2.5 border border-border rounded-xl text-sm text-primary bg-card focus:outline-none focus:ring-2 focus:ring-accent appearance-none w-full sm:w-auto">
                                        <option value="">Department</option>
                                        <option value="HR">HR</option>
                                        <option value="Tour Operation">Tour Operation</option>
                                        <option value="Booking Department">Booking Department</option>
                                        <option value="Visa Department">Visa Department</option>
                                    </select>
                                    <i
                                        class="fa-solid fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                                </div>

                                <button type="button" onclick="openPromoteEmployeeModal()"
                                    class="font-button px-5 py-2.5 bg-primary hover:opacity-90 text-white font-medium text-sm rounded-xl shadow-lg flex items-center justify-center gap-2 transition-all-300">
                                    PROMOTE EMPLOYEE
                                </button>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs font-semibold text-white uppercase bg-secondary">
                                    <tr>
                                        <th class="px-5 py-3.5">Manager</th>
                                        <th class="px-5 py-3.5">Employee Name</th>
                                        <th class="px-5 py-3.5">Employee ID</th>
                                        <th class="px-5 py-3.5">Position</th>
                                        <th class="px-5 py-3.5">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border" id="manager-team-table-body">
                                    <tr>
                                        <td colspan="5" class="px-5 py-8 text-center text-gray-400">
                                            Loading manager teams...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <!-------------------------------- ================= LIVE ATTENDANCE TAB =================-------------------------------->

                <div id="time-attendance" class="tab-content max-w-7xl mx-auto space-y-6 hidden">

                    <!-- Attendance Period Selector + Date/Export -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div class="relative inline-block">
                            <select id="attendancePeriodSelect" onchange="updateAttendancePeriodPreview()"
                                class="pl-4 pr-9 py-2.5 border border-border rounded-2xl text-sm font-medium text-primary bg-card focus:outline-none focus:ring-2 focus:ring-accent appearance-none">
                                <option>Today</option>
                                <option>This Week</option>
                                <option>This Month</option>
                            </select>
                            <i
                                class="fa-solid fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                        </div>

                        <div class="flex flex-col items-end gap-1">
                            <div class="flex space-x-3">
                                <input id="attendancePeriodDate" type="date" onchange="updateAttendancePeriodPreview()"
                                    class="border border-border rounded-2xl px-3 py-2 text-sm text-gray-600"
                                    value="2026-07-26">
                                <button onclick="exportDTRArchive()"
                                    class="font-button bg-card border border-border text-primary px-4 py-2 rounded-2xl text-sm font-medium hover:bg-background"><i
                                        class="fa-solid fa-file-pdf mr-2"></i> Export DTR</button>
                            </div>
                            <p id="attendancePeriodPreview" class="text-xs text-gray-400 pr-1"></p>
                        </div>
                    </div>

                    <!-- Attendance Summary Cards -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="bg-card p-5 rounded-2xl shadow-sm border border-border flex flex-col">
                            <div class="flex justify-between items-start mb-4">
                                <div class="p-3 bg-emerald-100 text-emerald-600 rounded-2xl"><i
                                        class="fa-solid fa-user-check text-xl"></i></div>
                            </div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Present</p>
                            <h3 class="font-heading text-3xl font-bold text-primary mt-2">2</h3>
                            <p class="text-sm font-medium text-gray-500 mt-1">Total Percentage</p>
                        </div>

                        <div class="bg-card p-5 rounded-2xl shadow-sm border border-border flex flex-col">
                            <div class="flex justify-between items-start mb-4">
                                <div class="p-3 bg-purple-100 text-purple-600 rounded-2xl"><i
                                        class="fa-solid fa-clock text-xl"></i></div>
                            </div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Late</p>
                            <h3 class="font-heading text-3xl font-bold text-primary mt-2">1</h3>
                            <p class="text-sm font-medium text-gray-500 mt-1">Today</p>
                        </div>

                        <div class="bg-card p-5 rounded-2xl shadow-sm border border-border flex flex-col">
                            <div class="flex justify-between items-start mb-4">
                                <div class="p-3 bg-amber-100 text-amber-600 rounded-2xl"><i
                                        class="fa-solid fa-user-xmark text-xl"></i></div>
                            </div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Absent</p>
                            <h3 class="font-heading text-3xl font-bold text-primary mt-2">0</h3>
                            <p class="text-sm font-medium text-gray-500 mt-1">Today</p>
                        </div>

                        <div class="bg-card p-5 rounded-2xl shadow-sm border border-border flex flex-col">
                            <div class="flex justify-between items-start mb-4">
                                <div class="p-3 bg-red-100 text-red-600 rounded-2xl"><i
                                        class="fa-solid fa-plane-slash text-xl"></i></div>
                            </div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">On Leave</p>
                            <h3 class="font-heading text-3xl font-bold text-primary mt-2">1</h3>
                            <p class="text-sm font-medium text-gray-500 mt-1">Today</p>
                        </div>
                    </div>

                    <!=============================== TODAYS ATTENDANCE TABLE=========================>
                        <div class="bg-card rounded-2xl shadow-sm border border-border overflow-hidden">
                            <div class="px-4 pt-4">
                                <h3 class="font-heading text-base font-bold text-primary">Today's Attendance</h3>
                            </div>
                            <div class="p-4 bg-secondary border-b border-border flex justify-between items-center">
                                <div class="relative">
                                    <i
                                        class="fa-solid fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                                    <input id="attendance-search" type="text" placeholder="Search Employee"
                                        oninput="filterAttendanceTable()"
                                        class="pl-9 pr-4 py-2.5 border border-border rounded-xl text-sm text-primary bg-card focus:outline-none focus:ring-2 focus:ring-accent w-full sm:w-64">
                                </div>
                                <div class="flex space-x-2">
                                    <div class="relative">
                                        <select id="attendance-filter-department" onchange="filterAttendanceTable()"
                                            class="pl-4 pr-9 py-2.5 border border-border rounded-xl text-sm text-primary bg-card focus:outline-none focus:ring-2 focus:ring-accent appearance-none w-full sm:w-auto">
                                            <option value="">Department</option>
                                            <option value="OPERATIONS">Operations</option>
                                            <option value="MONITORING">Monitoring</option>
                                        </select>
                                        <i
                                            class="fa-solid fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                                    </div>
                                    <div class="relative">
                                        <select id="attendance-filter-status" onchange="filterAttendanceTable()"
                                            class="pl-4 pr-9 py-2.5 border border-border rounded-xl text-sm text-primary bg-card focus:outline-none focus:ring-2 focus:ring-accent appearance-none w-full sm:w-auto">
                                            <option value="">Status</option>
                                            <option value="ACTIVE">Active</option>
                                            <option value="LEAVE">Leave</option>
                                        </select>
                                        <i
                                            class="fa-solid fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                                    </div>
                                </div>
                            </div>
                            <! ----------------- Inside the table ------------->
                                <div class="bg-card rounded-2xl shadow-sm border border-border overflow-x-auto">
                                    <table class="w-full text-sm text-left">
                                        <thead class="text-xs font-semibold text-primary uppercase bg-background">
                                            <tr>
                                                <th class="px-5 py-3.5">ID</th>
                                                <th class="px-5 py-3.5">Employee</th>
                                                <th class="px-5 py-3.5">Schedule</th>
                                                <th class="px-5 py-3.5">Clock In</th>
                                                <th class="px-5 py-3.5">Clock Out</th>
                                                <th class="px-5 py-3.5">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-border" id="attendance-table-body">
                                            <tr class="group cursor-pointer hover:bg-background transition-all-300"
                                                onclick="openEmployeeProfile(this)" data-id="EMP1"
                                                data-name="EMPLOYEE 1" data-position="TOUR GUIDE"
                                                data-department="OPERATIONS" data-status="ACTIVE">
                                                <td class="px-5 py-4 font-medium text-primary">EMP1</td>
                                                <td class="px-5 py-4 font-semibold text-primary">EMPLOYEE 1</td>
                                                <td class="px-5 py-4 text-gray-600">8 - 5 PM</td>
                                                <td class="px-5 py-4 text-gray-600">8:00 AM</td>
                                                <td class="px-5 py-4 text-gray-600">5:00 PM</td>
                                                <td class="px-5 py-4">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <span
                                                            class="inline-flex items-center bg-emerald-100 text-emerald-700 text-xs font-semibold px-3 py-1 rounded-full">ACTIVE</span>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr class="group cursor-pointer hover:bg-background transition-all-300"
                                                onclick="openEmployeeProfile(this)" data-id="EMP2"
                                                data-name="EMPLOYEE 2" data-position="STAFF"
                                                data-department="OPERATIONS" data-status="LEAVE">
                                                <td class="px-5 py-4 font-medium text-primary">EMP2</td>
                                                <td class="px-5 py-4 font-semibold text-primary">EMPLOYEE 2</td>
                                                <td class="px-5 py-4 text-gray-600">N/A</td>
                                                <td class="px-5 py-4 text-gray-600"> -- </td>
                                                <td class="px-5 py-4 text-gray-600"> -- </td>
                                                <td class="px-5 py-4">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <span
                                                            class="inline-flex items-center bg-red-100 text-red-600 text-xs font-semibold px-3 py-1 rounded-full">LEAVE</span>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr class="group cursor-pointer hover:bg-background transition-all-300"
                                                onclick="openEmployeeProfile(this)" data-id="EMP3"
                                                data-name="EMPLOYEE 3" data-position="MANAGER"
                                                data-department="MONITORING" data-status="ACTIVE">
                                                <td class="px-5 py-4 font-medium text-primary">EMP3</td>
                                                <td class="px-5 py-4 font-semibold text-primary">EMPLOYEE 3</td>
                                                <td class="px-5 py-4 text-gray-600">8 - 5 PM</td>
                                                <td class="px-5 py-4 text-gray-600"> 9:00 AM </td>
                                                <td class="px-5 py-4 text-gray-600"> 5:00 PM </td>
                                                <td class="px-5 py-4">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <span
                                                            class="inline-flex items-center bg-emerald-100 text-emerald-700 text-xs font-semibold px-3 py-1 rounded-full">ACTIVE</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                        </div>

                        <!=============================== ATTENDANCE RECORDS / DTR ARCHIVE
                            SECTION=========================>
                            <div class="space-y-6" id="time-attendance-records-wrapper">
                                <div>
                                    <h3 class="font-heading text-lg font-bold text-primary">Attendance Records</h3>
                                    <p class="text-xs text-gray-500 mt-0.5">Daily Time Record (DTR) archive â€” filter
                                        and
                                        export historical logs.</p>
                                </div>

                                <!-- Filters -->
                                <div class="flex flex-wrap gap-3">
                                    <div class="relative">
                                        <input id="records-date-range" type="text" placeholder="Date Range"
                                            onchange="filterAttendanceRecords()"
                                            class="pl-4 pr-4 py-2.5 border border-border rounded-xl text-sm text-primary bg-card focus:outline-none focus:ring-2 focus:ring-accent w-44">
                                    </div>
                                    <div class="relative">
                                        <select id="records-filter-department" onchange="filterAttendanceRecords()"
                                            class="pl-4 pr-9 py-2.5 border border-border rounded-xl text-sm text-primary bg-card focus:outline-none focus:ring-2 focus:ring-accent appearance-none w-44">
                                            <option value="">Department</option>
                                            <option value="OPERATIONS">Operations</option>
                                            <option value="MONITORING">Monitoring</option>
                                        </select>
                                        <i
                                            class="fa-solid fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                                    </div>
                                    <div class="relative">
                                        <select id="records-filter-status" onchange="filterAttendanceRecords()"
                                            class="pl-4 pr-9 py-2.5 border border-border rounded-xl text-sm text-primary bg-card focus:outline-none focus:ring-2 focus:ring-accent appearance-none w-44">
                                            <option value="">Status</option>
                                            <option value="PRESENT">Present</option>
                                            <option value="LATE">Late</option>
                                            <option value="ABSENT">Absent</option>
                                            <option value="LEAVE">Leave</option>
                                        </select>
                                        <i
                                            class="fa-solid fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                                    </div>
                                    <div class="relative">
                                        <select id="records-filter-employee" onchange="filterAttendanceRecords()"
                                            class="pl-4 pr-9 py-2.5 border border-border rounded-xl text-sm text-primary bg-card focus:outline-none focus:ring-2 focus:ring-accent appearance-none w-44">
                                            <option value="">Employee</option>
                                            <option value="EMPLOYEE 1">Employee 1</option>
                                            <option value="EMPLOYEE 2">Employee 2</option>
                                            <option value="EMPLOYEE 3">Employee 3</option>
                                        </select>
                                        <i
                                            class="fa-solid fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                                    </div>
                                </div>

                                <!-- Records Table -->
                                <div class="bg-card rounded-2xl shadow-sm border border-border overflow-x-auto">
                                    <table class="w-full text-sm text-left">
                                        <thead class="text-xs font-semibold text-white uppercase bg-secondary">
                                            <tr>
                                                <th class="px-5 py-3.5">Date</th>
                                                <th class="px-5 py-3.5">Employee</th>
                                                <th class="px-5 py-3.5">Schedule</th>
                                                <th class="px-5 py-3.5">Clock In</th>
                                                <th class="px-5 py-3.5">Clock Out</th>
                                                <th class="px-5 py-3.5">Status</th>
                                                <th class="px-5 py-3.5 text-right">Archive</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-border" id="attendance-records-table-body">
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!=============================== ATTENDANCE CORRECTION SECTION=========================>
                                <div class="space-y-6" id="attendance-correction-wrapper">
                                    <h3 class="font-heading text-lg font-bold text-primary">Attendance Correction</h3>

                                    <div class="bg-card rounded-2xl shadow-sm border border-border overflow-x-auto">
                                        <table class="w-full text-sm text-left">
                                            <thead class="text-xs font-semibold text-white uppercase bg-secondary">
                                                <tr>
                                                    <th class="px-5 py-3.5">Request</th>
                                                    <th class="px-5 py-3.5">Employee</th>
                                                    <th class="px-5 py-3.5">Date</th>
                                                    <th class="px-5 py-3.5">Reason</th>
                                                    <th class="px-5 py-3.5">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-border" id="attendance-correction-table-body">

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                </div>
                <!-- ================= KIOSK LOG ENTRY ================= -->
                <div id="kiosk-log" class="tab-content max-w-7xl mx-auto space-y-6 hidden">

                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">

                        <div>
                            <h2 class="font-heading text-xl font-bold text-primary">
                                RFID Log Entry
                            </h2>

                            <p class="text-sm text-gray-500 mt-1">
                                Employees who used their RFID card at a kiosk.
                            </p>
                        </div>

                        <button type="button" id="refresh-kiosk-log-btn"
                            class="font-button px-4 py-2.5 bg-card border border-border text-primary rounded-xl text-sm font-medium hover:bg-background">
                            <i class="fa-solid fa-rotate mr-2"></i>
                            Refresh
                        </button>

                    </div>

                    <div class="bg-card rounded-2xl shadow-sm border border-border overflow-x-auto">

                        <table class="w-full text-sm text-left">

                            <thead class="text-xs font-semibold text-white uppercase bg-secondary">
                                <tr>
                                    <th class="px-5 py-3.5">Log ID</th>
                                    <th class="px-5 py-3.5">Employee</th>
                                    <th class="px-5 py-3.5">Employee ID</th>
                                    <th class="px-5 py-3.5">RFID UID</th>
                                    <th class="px-5 py-3.5">Kiosk</th>
                                    <th class="px-5 py-3.5">Scan Status</th>
                                    <th class="px-5 py-3.5">Scanned At</th>
                                </tr>
                            </thead>

                            <tbody id="kiosk-log-table-body" class="divide-y divide-border">
                            </tbody>

                        </table>

                    </div>

                </div>
                <!-- ================= SHIFT & SCHEDULE TAB (Master Roster) ================= -->
                <div id="schedule" class="tab-content max-w-7xl mx-auto space-y-6 hidden">
                    <div
                        class="flex flex-col sm:flex-row justify-between items-center gap-4 bg-card p-4 rounded-2xl shadow-sm border border-border mb-4">
                        <div class="flex items-center space-x-4">
                            <button type="button" id="schedulePrevBtn" onclick="navigateSchedulePeriod(-1)"
                                class="font-button p-2 rounded hover:bg-background text-gray-600"
                                aria-label="Previous week or month" title="Previous week / month">
                                <i class="fa-solid fa-chevron-left"></i>
                            </button>
                            <h2 class="font-heading text-lg font-bold text-primary" id="scheduleHeaderTitle">July 26 -
                                Aug 1, 2026</h2>
                            <button type="button" id="scheduleNextBtn" onclick="navigateSchedulePeriod(1)"
                                class="font-button p-2 rounded hover:bg-background text-gray-600"
                                aria-label="Next week or month" title="Next week / month">
                                <i class="fa-solid fa-chevron-right"></i>
                            </button>
                            <button class="font-button p-2 rounded hover:bg-background text-gray-600"
                                id="scheduleViewToggleBtn" onclick="toggleScheduleView()"><i
                                    class="fa-solid fa-calendar-days" id="scheduleViewToggleIcon"></i> <span
                                    id="scheduleViewToggleLabel">Month</span></button>
                        </div>
                        <div class="flex space-x-3">
                            <div class="relative">
                                <i
                                    class="fa-solid fa-filter absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs"></i>
                                <select id="scheduleRoleFilter" onchange="filterScheduleByRole()"
                                    class="pl-8 pr-4 py-2 border border-border rounded-2xl text-sm text-primary bg-card focus:outline-none focus:ring-2 focus:ring-accent appearance-none">
                                    <option>All Roles</option>
                                    <option>Tour Guides</option>
                                    <option>Drivers</option>
                                    <option>Office Staff</option>
                                </select>
                            </div>
                            <button type="button" onclick="openReassignScheduleModal()"
                                class="font-button bg-primary text-white px-4 py-2 rounded-2xl text-sm font-medium shadow-sm hover:bg-[#102F57]">
                                <i class="fa-solid fa-user-arrow-right mr-1"></i> Re-assign Schedule
                            </button>

                            <button type="button" onclick="openCreateScheduleModal()"
                                class="font-button bg-secondary text-white px-4 py-2 rounded-2xl text-sm font-medium shadow-sm hover:bg-[#E08A3B]">
                                <i class="fa-solid fa-plus mr-1"></i> Create Schedule
                            </button>
                        </div>
                    </div>

                    <!-- Horizontal Calendar Table / Roster View -->
                    <div id="weekRosterView">
                        <div class="bg-card rounded-2xl shadow-sm border border-border overflow-x-auto">
                            <table class="w-full text-sm text-left border-collapse min-w-[900px]">
                                <thead>
                                    <tr class="bg-background border-b border-border">
                                        <th class="p-3 w-48 font-semibold text-gray-600 border-r border-border">Employee
                                        </th>
                                        <th class="p-3 text-center text-gray-500 font-medium border-r border-border"
                                            data-date="2026-07-26">Mon 26</th>
                                        <th class="p-3 text-center text-gray-500 font-medium border-r border-border"
                                            data-date="2026-07-27">Tue 27</th>
                                        <th class="p-3 text-center text-gray-500 font-medium border-r border-border"
                                            data-date="2026-07-28">Wed 28</th>
                                        <th class="p-3 text-center text-gray-500 font-medium border-r border-border"
                                            data-date="2026-07-29">Thu 29</th>
                                        <th class="p-3 text-center text-gray-500 font-medium border-r border-border"
                                            data-date="2026-07-30">Fri 30</th>
                                        <th class="p-3 text-center text-gray-500 font-medium bg-background"
                                            data-date="2026-07-31">Sat 31</th>
                                        <th class="p-3 text-center text-gray-500 font-medium bg-background"
                                            data-date="2026-08-01">Sun 1</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border" id="master-roster-body">
                                </tbody>

                            </table>
                        </div>
                    </div>
                    <!-- Monthly Calendar Roster View -->
                    <div id="monthRosterView" class="hidden">
                        <div class="bg-card rounded-2xl shadow-sm border border-border overflow-hidden">
                            <div class="grid grid-cols-7 bg-secondary text-white text-xs font-semibold uppercase">
                                <div class="p-3 text-center border-r border-white/10">Mon</div>
                                <div class="p-3 text-center border-r border-white/10">Tue</div>
                                <div class="p-3 text-center border-r border-white/10">Wed</div>
                                <div class="p-3 text-center border-r border-white/10">Thu</div>
                                <div class="p-3 text-center border-r border-white/10">Fri</div>
                                <div class="p-3 text-center border-r border-white/10">Sat</div>
                                <div class="p-3 text-center">Sun</div>
                            </div>
                            <div class="grid grid-cols-7">
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40"
                                    data-date="2026-07-27">
                                    <span class="text-xs font-medium text-gray-300">27</span>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40"
                                    data-date="2026-07-28">
                                    <span class="text-xs font-medium text-gray-300">28</span>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40"
                                    data-date="2026-07-29">
                                    <span class="text-xs font-medium text-gray-300">29</span>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40"
                                    data-date="2026-07-30">
                                    <span class="text-xs font-medium text-gray-300">30</span>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40"
                                    data-date="2026-07-31">
                                    <span class="text-xs font-medium text-gray-300">31</span>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40 hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-01">
                                    <span class="text-xs font-semibold text-secondary">1</span>
                                    <div class="mt-1 space-y-1">
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40 hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-02">
                                    <span class="text-xs font-semibold text-primary">2</span>
                                    <div class="mt-1 space-y-1">
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-03">
                                    <span class="text-xs font-semibold text-primary">3</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-blue-50 text-blue-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 1 Â· 9-5">Employee 1 Â· 9-5</div>
                                        <div class="bg-amber-50 text-amber-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 3 Â· 8-4">Employee 3 Â· 8-4</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-04">
                                    <span class="text-xs font-semibold text-primary">4</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-emerald-50 text-emerald-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 1 Â· 10-4">Employee 1 Â· 10-4</div>
                                        <div class="bg-amber-50 text-amber-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 3 Â· 8-4">Employee 3 Â· 8-4</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-05">
                                    <span class="text-xs font-semibold text-primary">5</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-amber-50 text-amber-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 3 Â· 8-4">Employee 3 Â· 8-4</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-06">
                                    <span class="text-xs font-semibold text-primary">6</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-emerald-50 text-emerald-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 1 Â· 10-4">Employee 1 Â· 10-4</div>
                                        <div class="bg-amber-50 text-amber-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 3 Â· 8-4">Employee 3 Â· 8-4</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-07">
                                    <span class="text-xs font-semibold text-primary">7</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-purple-50 text-purple-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 1 Â· 2-8">Employee 1 Â· 2-8</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40 hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-08">
                                    <span class="text-xs font-semibold text-primary">8</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-amber-50 text-amber-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 3 Â· 9-5">Employee 3 Â· 9-5</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40 hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-09">
                                    <span class="text-xs font-semibold text-primary">9</span>
                                    <div class="mt-1 space-y-1">
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-10">
                                    <span class="text-xs font-semibold text-primary">10</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-red-50 text-red-600 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 2 Â· Vacation Leave">Employee 2 Â· Vacation Leave</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-11">
                                    <span class="text-xs font-semibold text-primary">11</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-red-50 text-red-600 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 2 Â· Vacation Leave">Employee 2 Â· Vacation Leave</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-12">
                                    <span class="text-xs font-semibold text-primary">12</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-red-50 text-red-600 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 2 Â· Vacation Leave">Employee 2 Â· Vacation Leave</div>
                                        <div class="bg-blue-50 text-blue-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 1 Â· 9-5">Employee 1 Â· 9-5</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-13">
                                    <span class="text-xs font-semibold text-primary">13</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-red-50 text-red-600 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 2 Â· Vacation Leave">Employee 2 Â· Vacation Leave</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-14">
                                    <span class="text-xs font-semibold text-primary">14</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-red-50 text-red-600 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 2 Â· Vacation Leave">Employee 2 Â· Vacation Leave</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40 hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-15">
                                    <span class="text-xs font-semibold text-primary">15</span>
                                    <div class="mt-1 space-y-1">
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40 hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-16">
                                    <span class="text-xs font-semibold text-primary">16</span>
                                    <div class="mt-1 space-y-1">
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-17">
                                    <span class="text-xs font-semibold text-primary">17</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-blue-50 text-blue-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 1 Â· 9-5">Employee 1 Â· 9-5</div>
                                        <div class="bg-amber-50 text-amber-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 3 Â· 8-4">Employee 3 Â· 8-4</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-18">
                                    <span class="text-xs font-semibold text-primary">18</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-emerald-50 text-emerald-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 1 Â· 10-4">Employee 1 Â· 10-4</div>
                                        <div class="bg-amber-50 text-amber-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 3 Â· 8-4">Employee 3 Â· 8-4</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-19">
                                    <span class="text-xs font-semibold text-primary">19</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-amber-50 text-amber-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 3 Â· 8-4">Employee 3 Â· 8-4</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-20">
                                    <span class="text-xs font-semibold text-primary">20</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-emerald-50 text-emerald-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 1 Â· 10-4">Employee 1 Â· 10-4</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-21">
                                    <span class="text-xs font-semibold text-primary">21</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-purple-50 text-purple-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 1 Â· 2-8">Employee 1 Â· 2-8</div>
                                        <div class="bg-amber-50 text-amber-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 3 Â· 8-4">Employee 3 Â· 8-4</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40 hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-22">
                                    <span class="text-xs font-semibold text-primary">22</span>
                                    <div class="mt-1 space-y-1">
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40 hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-23">
                                    <span class="text-xs font-semibold text-primary">23</span>
                                    <div class="mt-1 space-y-1">
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-24">
                                    <span class="text-xs font-semibold text-primary">24</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-blue-50 text-blue-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 1 Â· 9-5">Employee 1 Â· 9-5</div>
                                        <div class="bg-amber-50 text-amber-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 3 Â· 8-4">Employee 3 Â· 8-4</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-25">
                                    <span class="text-xs font-semibold text-primary">25</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-emerald-50 text-emerald-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 1 Â· 10-4">Employee 1 Â· 10-4</div>
                                        <div class="bg-amber-50 text-amber-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 3 Â· 8-4">Employee 3 Â· 8-4</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-26">
                                    <span class="text-xs font-semibold text-primary">26</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-amber-50 text-amber-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 3 Â· 8-4">Employee 3 Â· 8-4</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-27">
                                    <span class="text-xs font-semibold text-primary">27</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-emerald-50 text-emerald-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 1 Â· 10-4">Employee 1 Â· 10-4</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-28">
                                    <span class="text-xs font-semibold text-primary">28</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-purple-50 text-purple-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 1 Â· 2-8">Employee 1 Â· 2-8</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40 hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-29">
                                    <span class="text-xs font-semibold text-primary">29</span>
                                    <div class="mt-1 space-y-1">
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40 hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-30">
                                    <span class="text-xs font-semibold text-primary">30</span>
                                    <div class="mt-1 space-y-1">
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] hover:bg-background/60 transition-all-300 cursor-pointer"
                                    data-date="2026-08-31">
                                    <span class="text-xs font-semibold text-primary">31</span>
                                    <div class="mt-1 space-y-1">
                                        <div class="bg-blue-50 text-blue-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 1 Â· 9-5">Employee 1 Â· 9-5</div>
                                        <div class="bg-amber-50 text-amber-700 text-[10px] px-1.5 py-1 rounded truncate"
                                            title="Employee 3 Â· 8-4">Employee 3 Â· 8-4</div>
                                    </div>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40"
                                    data-date="2026-09-01">
                                    <span class="text-xs font-medium text-gray-300">1</span>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40"
                                    data-date="2026-09-02">
                                    <span class="text-xs font-medium text-gray-300">2</span>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40"
                                    data-date="2026-09-03">
                                    <span class="text-xs font-medium text-gray-300">3</span>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40"
                                    data-date="2026-09-04">
                                    <span class="text-xs font-medium text-gray-300">4</span>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40"
                                    data-date="2026-09-05">
                                    <span class="text-xs font-medium text-gray-300">5</span>
                                </div>
                                <div class="border-r border-b border-border p-2 min-h-[110px] bg-background/40"
                                    data-date="2026-09-06">
                                    <span class="text-xs font-medium text-gray-300">6</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-4 mt-4 text-xs text-gray-500">
                            <span class="inline-flex items-center gap-1.5"><span
                                    class="w-2.5 h-2.5 rounded-full bg-blue-400"></span> Tour / HQ Shift</span>
                            <span class="inline-flex items-center gap-1.5"><span
                                    class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span> City Walk</span>
                            <span class="inline-flex items-center gap-1.5"><span
                                    class="w-2.5 h-2.5 rounded-full bg-purple-400"></span> Evening Tour</span>
                            <span class="inline-flex items-center gap-1.5"><span
                                    class="w-2.5 h-2.5 rounded-full bg-amber-400"></span> Driver / Route</span>
                            <span class="inline-flex items-center gap-1.5"><span
                                    class="w-2.5 h-2.5 rounded-full bg-red-400"></span> Leave / Vacation</span>
                        </div>
                    </div>
                </div>

                <!-- ================= TIMESHEET APPROVALS ================= -->
                <div id="timesheet" class="tab-content max-w-7xl mx-auto space-y-6 hidden">
                    <!-- Timesheet Header / Actions -->
                    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                        <div>
                            <h2 class="font-heading text-2xl font-bold text-primary">Timesheet Approvals</h2>
                        </div>
                        <div class="flex flex-wrap gap-2 w-full lg:w-auto">
                            <button id="timesheet-history-btn" type="button" onclick="openTimesheetHistory()"
                                class="font-button bg-card border border-border text-primary px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-background shadow-sm transition-all-300 flex items-center gap-2">
                                <i class="fa-solid fa-clock-rotate-left"></i> View History
                            </button>
                            <button id="timesheet-request-correction" type="button"
                                onclick="openTimesheetCorrectionRequests()"
                                class="font-button bg-amber-600 text-white px-4 py-2.5 rounded-xl text-sm font-medium shadow-sm hover:bg-amber-700 transition-all-300 flex items-center gap-2">
                                <i class="fa-solid fa-pen-to-square"></i> Request Correction
                            </button>
                        </div>
                    </div>

                    <!-- Timesheet Summary -->
                    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4" id="timesheet-summary">
                        <div class="ts-summary-card bg-card p-4 rounded-2xl shadow-sm border border-border">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total
                                        Employees</p>
                                    <p id="ts-summary-employees"
                                        class="font-heading text-2xl font-bold text-primary mt-1">0</p>
                                </div>
                                <div class="p-2.5 bg-blue-50 text-primary rounded-xl"><i class="fa-solid fa-users"></i>
                                </div>
                            </div>
                        </div>
                        <div class="ts-summary-card bg-card p-4 rounded-2xl shadow-sm border border-border">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Pending
                                        Approval</p>
                                    <p id="ts-summary-pending"
                                        class="font-heading text-2xl font-bold text-amber-600 mt-1">0</p>
                                </div>
                                <div class="p-2.5 bg-amber-50 text-amber-600 rounded-xl"><i
                                        class="fa-solid fa-hourglass-half"></i></div>
                            </div>
                        </div>
                        <div class="ts-summary-card bg-card p-4 rounded-2xl shadow-sm border border-border">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Approved</p>
                                    <p id="ts-summary-approved"
                                        class="font-heading text-2xl font-bold text-emerald-600 mt-1">0</p>
                                </div>
                                <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-xl"><i
                                        class="fa-solid fa-circle-check"></i></div>
                            </div>
                        </div>
                        <div class="ts-summary-card bg-card p-4 rounded-2xl shadow-sm border border-border">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Rejected</p>
                                    <p id="ts-summary-rejected"
                                        class="font-heading text-2xl font-bold text-red-600 mt-1">0</p>
                                </div>
                                <div class="p-2.5 bg-red-50 text-red-600 rounded-xl"><i
                                        class="fa-solid fa-circle-xmark"></i></div>
                            </div>
                        </div>
                        <div class="ts-summary-card bg-card p-4 rounded-2xl shadow-sm border border-border">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Regular
                                        Hours</p>
                                    <p id="ts-summary-regular"
                                        class="font-heading text-2xl font-bold text-primary mt-1">0 hrs</p>
                                </div>
                                <div class="p-2.5 bg-slate-100 text-primary rounded-xl"><i
                                        class="fa-solid fa-business-time"></i></div>
                            </div>
                        </div>
                        <div class="ts-summary-card bg-card p-4 rounded-2xl shadow-sm border border-border">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Overtime
                                        Hours</p>
                                    <p id="ts-summary-overtime"
                                        class="font-heading text-2xl font-bold text-red-600 mt-1">0 hrs</p>
                                </div>
                                <div class="p-2.5 bg-red-50 text-red-600 rounded-xl"><i
                                        class="fa-solid fa-stopwatch"></i></div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs text-gray-400">
                        <p id="timesheet-summary-note">Summary reflects the current timesheet filters.</p>
                        <button type="button" onclick="clearTimesheetFilters()"
                            class="text-primary hover:text-secondary font-semibold">Clear Filters</button>
                    </div>

                    <!-- Search and Filters -->
                    <div class="bg-card p-4 md:p-5 rounded-2xl shadow-sm border border-border">
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-3">
                            <div class="relative sm:col-span-2 xl:col-span-1">
                                <i
                                    class="fa-solid fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                                <input id="timesheet-search" type="text" placeholder="Search Employee"
                                    oninput="filterTimesheets()"
                                    class="pl-9 pr-4 py-2.5 border border-border rounded-xl text-sm text-primary bg-background focus:outline-none focus:ring-2 focus:ring-accent w-full">
                            </div>

                            <div class="relative">
                                <select id="timesheet-filter-department" onchange="filterTimesheets()"
                                    class="pl-4 pr-9 py-2.5 border border-border rounded-xl text-sm text-primary bg-background focus:outline-none focus:ring-2 focus:ring-accent appearance-none w-full">
                                    <option value="">All Departments</option>
                                </select>
                                <i
                                    class="fa-solid fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                            </div>

                            <div class="relative">
                                <select id="timesheet-filter-period" onchange="filterTimesheets()"
                                    class="pl-4 pr-9 py-2.5 border border-border rounded-xl text-sm text-primary bg-background focus:outline-none focus:ring-2 focus:ring-accent appearance-none w-full">
                                    <option value="">All Pay Periods</option>
                                </select>
                                <i
                                    class="fa-solid fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                            </div>

                            <div class="relative">
                                <select id="timesheet-filter-status" onchange="filterTimesheets()"
                                    class="pl-4 pr-9 py-2.5 border border-border rounded-xl text-sm text-primary bg-background focus:outline-none focus:ring-2 focus:ring-accent appearance-none w-full">
                                    <option value="">All Statuses</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Approved">Approved</option>
                                    <option value="Rejected">Rejected</option>
                                    <option value="Correction Requested">Correction Requested</option>
                                </select>
                                <i
                                    class="fa-solid fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                            </div>

                            <div class="relative">
                                <select id="timesheet-filter-overtime" onchange="filterTimesheets()"
                                    class="pl-4 pr-9 py-2.5 border border-border rounded-xl text-sm text-primary bg-background focus:outline-none focus:ring-2 focus:ring-accent appearance-none w-full">
                                    <option value="">All Overtime</option>
                                    <option value="with">With Overtime</option>
                                    <option value="high">High Overtime (&gt; 10 hrs)</option>
                                    <option value="none">No Overtime</option>
                                </select>
                                <i
                                    class="fa-solid fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Employee Timesheet Cards -->
                    <div id="timesheet-records-container" class="space-y-4"></div>

                    <!-- REVIEW MODAL -->
                    <div id="modal-timesheet-review"
                        class="hidden fixed inset-0 bg-primary/80 backdrop-blur-sm flex items-center justify-center z-[70] p-4"
                        onclick="if(event.target===this) closeTimesheetReview()">
                        <div
                            class="bg-card rounded-3xl shadow-2xl border border-border max-w-6xl w-full max-h-[92vh] overflow-hidden flex flex-col">
                            <div
                                class="px-5 md:px-6 py-5 border-b border-border flex items-start justify-between gap-4 flex-shrink-0">
                                <div>
                                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Timesheet
                                        Review</p>
                                    <h3 id="timesheet-review-name"
                                        class="font-heading text-xl font-bold text-primary mt-1">â€”</h3>
                                    <div class="flex flex-wrap items-center gap-2 mt-2">
                                        <span id="timesheet-review-status"
                                            class="ts-status-badge status-pending">Pending</span>
                                        <span id="timesheet-review-ot-warning"
                                            class="hidden text-xs font-bold text-red-600 bg-red-50 border border-red-100 px-2.5 py-1 rounded-full">
                                            <i class="fa-solid fa-triangle-exclamation mr-1"></i> High Overtime
                                        </span>
                                    </div>
                                </div>
                                <button type="button" onclick="closeTimesheetReview()"
                                    class="p-2 rounded-xl hover:bg-background text-gray-500 transition-colors"
                                    aria-label="Close review">
                                    <i class="fa-solid fa-xmark text-xl"></i>
                                </button>
                            </div>

                            <div class="overflow-y-auto p-5 md:p-6 space-y-5">
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                    <div class="ts-info-box">
                                        <span>Employee ID</span>
                                        <strong id="timesheet-review-id">â€”</strong>
                                    </div>
                                    <div class="ts-info-box">
                                        <span>Department</span>
                                        <strong id="timesheet-review-department">â€”</strong>
                                    </div>
                                    <div class="ts-info-box">
                                        <span>Pay Period</span>
                                        <strong id="timesheet-review-period">â€”</strong>
                                    </div>
                                    <div class="ts-info-box">
                                        <span>Submitted Date</span>
                                        <strong id="timesheet-review-submitted">â€”</strong>
                                    </div>
                                </div>

                                <div id="timesheet-review-reason-wrap"
                                    class="hidden p-4 rounded-2xl border border-amber-200 bg-amber-50">
                                    <p class="text-xs font-bold text-amber-800 uppercase tracking-wider">Recorded Reason
                                    </p>
                                    <p id="timesheet-review-reason" class="text-sm text-amber-900 mt-1"></p>
                                </div>

                                <div class="bg-card rounded-2xl border border-border overflow-hidden">
                                    <div class="px-4 py-4 border-b border-border bg-background">
                                        <div class="flex items-center justify-between gap-3">
                                            <div>
                                                <h4 class="font-heading font-bold text-primary">Daily Timesheet</h4>
                                                <p class="text-xs text-gray-500 mt-0.5">Review individual attendance and
                                                    worked hours for the selected pay period.</p>
                                            </div>
                                            <div class="text-right text-xs">
                                                <p class="text-gray-500">Submitted Totals</p>
                                                <p class="font-bold text-primary"><span
                                                        id="timesheet-review-regular">0</span> regular + <span
                                                        id="timesheet-review-overtime">0</span> OT</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-sm text-left min-w-[900px]">
                                            <thead
                                                class="text-xs font-semibold text-gray-500 uppercase bg-background border-b border-border">
                                                <tr>
                                                    <th class="px-4 py-3">Date</th>
                                                    <th class="px-4 py-3">Day</th>
                                                    <th class="px-4 py-3">Time In</th>
                                                    <th class="px-4 py-3">Time Out</th>
                                                    <th class="px-4 py-3">Break</th>
                                                    <th class="px-4 py-3 text-right">Regular Hours</th>
                                                    <th class="px-4 py-3 text-right">Overtime</th>
                                                    <th class="px-4 py-3">Attendance Status</th>
                                                </tr>
                                            </thead>
                                            <tbody id="timesheet-review-entries" class="divide-y divide-border"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="px-5 md:px-6 py-4 border-t border-border bg-background flex flex-col sm:flex-row justify-end gap-2 flex-shrink-0">
                                <button type="button" onclick="closeTimesheetReview()"
                                    class="font-button px-4 py-2.5 border border-border text-gray-600 rounded-xl text-sm font-medium hover:bg-card">Close</button>
                                <button id="timesheet-review-reject" type="button" onclick="rejectFromReview()"
                                    class="font-button px-4 py-2.5 bg-red-100 text-red-700 rounded-xl text-sm font-semibold hover:bg-red-200">
                                    <i class="fa-solid fa-xmark mr-1"></i> Reject Timesheet
                                </button>
                                <button id="timesheet-review-approve" type="button" onclick="approveFromReview()"
                                    class="font-button px-4 py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-semibold hover:bg-emerald-700">
                                    <i class="fa-solid fa-check mr-1"></i> Approve Timesheet
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- REASON MODAL -->
                    <div id="modal-timesheet-reason"
                        class="hidden fixed inset-0 bg-primary/80 backdrop-blur-sm flex items-center justify-center z-[80] p-4"
                        onclick="if(event.target===this) closeTimesheetReasonModal()">
                        <div
                            class="bg-card rounded-3xl shadow-2xl border border-border max-w-lg w-full overflow-hidden">
                            <div class="px-6 py-5 border-b border-border flex items-center justify-between">
                                <div>
                                    <p id="timesheet-reason-kicker"
                                        class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Timesheet
                                        Action</p>
                                    <h3 id="timesheet-reason-title"
                                        class="font-heading text-lg font-bold text-primary mt-1">Reason</h3>
                                </div>
                                <button type="button" onclick="closeTimesheetReasonModal()"
                                    class="p-2 rounded-xl hover:bg-background text-gray-500">
                                    <i class="fa-solid fa-xmark text-lg"></i>
                                </button>
                            </div>
                            <form id="timesheet-reason-form" onsubmit="submitTimesheetReason(event)"
                                class="p-6 space-y-5">
                                <p id="timesheet-reason-prompt" class="text-sm text-gray-600">Please provide a reason.
                                </p>

                                <div id="timesheet-rejection-options" class="space-y-2">
                                    <label
                                        class="block text-xs font-semibold text-gray-500 uppercase tracking-wider">Rejection
                                        Reason</label>
                                    <select id="timesheet-rejection-select" onchange="toggleTimesheetOtherReason()"
                                        class="w-full px-4 py-3 border border-border rounded-xl text-sm text-primary bg-background focus:outline-none focus:ring-2 focus:ring-accent">
                                        <option value="">Select a reason</option>
                                        <option value="Incorrect Time Entry">Incorrect Time Entry</option>
                                        <option value="Excessive Overtime">Excessive Overtime</option>
                                        <option value="Missing Attendance Record">Missing Attendance Record</option>
                                        <option value="Incomplete Timesheet">Incomplete Timesheet</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>

                                <div id="timesheet-other-reason-wrap" class="hidden">
                                    <label for="timesheet-other-reason"
                                        class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Other
                                        Reason</label>
                                    <textarea id="timesheet-other-reason" rows="3" placeholder="Enter the reason..."
                                        class="w-full px-4 py-3 border border-border rounded-xl text-sm text-primary bg-background focus:outline-none focus:ring-2 focus:ring-accent resize-none"></textarea>
                                </div>

                                <div id="timesheet-correction-reason-wrap" class="hidden">
                                    <label for="timesheet-correction-reason-input"
                                        class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Correction
                                        Reason</label>
                                    <textarea id="timesheet-correction-reason-input" rows="4"
                                        placeholder="Example: Missing time-out on July 18."
                                        class="w-full px-4 py-3 border border-border rounded-xl text-sm text-primary bg-background focus:outline-none focus:ring-2 focus:ring-accent resize-none"></textarea>
                                </div>

                                <div class="flex flex-col-reverse sm:flex-row justify-end gap-2">
                                    <button type="button" onclick="closeTimesheetReasonModal()"
                                        class="font-button px-4 py-2.5 border border-border text-gray-600 rounded-xl text-sm font-medium hover:bg-background">Cancel</button>
                                    <button id="timesheet-reason-submit" type="submit"
                                        class="font-button px-4 py-2.5 bg-red-600 text-white rounded-xl text-sm font-semibold hover:bg-red-700">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- HISTORY MODAL -->
                    <div id="modal-timesheet-history"
                        class="hidden fixed inset-0 bg-primary/80 backdrop-blur-sm flex items-center justify-center z-[70] p-4"
                        onclick="if(event.target===this) closeTimesheetHistory()">
                        <div
                            class="bg-card rounded-3xl shadow-2xl border border-border max-w-6xl w-full max-h-[92vh] overflow-hidden flex flex-col">
                            <div class="px-5 md:px-6 py-5 border-b border-border flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Audit Trail
                                    </p>
                                    <h3 class="font-heading text-xl font-bold text-primary mt-1">Timesheet History</h3>
                                </div>
                                <button type="button" onclick="closeTimesheetHistory()"
                                    class="p-2 rounded-xl hover:bg-background text-gray-500">
                                    <i class="fa-solid fa-xmark text-xl"></i>
                                </button>
                            </div>
                            <div class="overflow-auto">
                                <table class="w-full text-sm text-left min-w-[900px]">
                                    <thead class="text-xs font-semibold text-white uppercase bg-secondary sticky top-0">
                                        <tr>
                                            <th class="px-5 py-3.5">Employee</th>
                                            <th class="px-5 py-3.5">Action</th>
                                            <th class="px-5 py-3.5">Status Change</th>
                                            <th class="px-5 py-3.5">Admin</th>
                                            <th class="px-5 py-3.5">Date / Time</th>
                                            <th class="px-5 py-3.5">Reason</th>
                                        </tr>
                                    </thead>
                                    <tbody id="timesheet-history-body" class="divide-y divide-border"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- CORRECTION REQUESTS MODAL -->
                    <div id="modal-timesheet-correction-requests"
                        class="hidden fixed inset-0 bg-primary/80 backdrop-blur-sm flex items-center justify-center z-[75] p-4"
                        onclick="if(event.target===this) closeTimesheetCorrectionRequests()">
                        <div
                            class="bg-card rounded-3xl shadow-2xl border border-border max-w-3xl w-full max-h-[88vh] overflow-hidden flex flex-col">
                            <div
                                class="px-5 md:px-6 py-5 border-b border-border flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Employee
                                        Timesheets</p>
                                    <h3 class="font-heading text-xl font-bold text-primary mt-1">Request Correction</h3>
                                    <p class="text-sm text-gray-500 mt-1">View an employee timesheet before sending a
                                        correction request.</p>
                                </div>
                                <button type="button" onclick="closeTimesheetCorrectionRequests()"
                                    class="p-2 rounded-xl hover:bg-background text-gray-500">
                                    <i class="fa-solid fa-xmark text-xl"></i>
                                </button>
                            </div>
                            <div id="timesheet-correction-request-list" class="overflow-y-auto p-5 md:p-6 space-y-3">
                            </div>
                        </div>
                    </div>
                </div>

                <!---------------------------- MODULE 5: LEAVE REGISTRY ---------------------------------->
                <div id="leave" class="tab-content max-w-6xl mx-auto space-y-6 hidden">
                    <div class="flex justify-between items-center flex-wrap gap-4">

                        <button onclick="openRequestLeaveModal()"
                            class="px-5 py-2.5 bg-background border border-border hover:bg-gray-50 text-primary font-button font-medium text-sm rounded-xl shadow-sm flex items-center gap-2 transition-all-300">
                            <i data-lucide="plus-circle" class="w-4 h-4 text-accent"></i>
                            File Leave Request
                        </button>
                    </div>

                    <!-- Leaves interactive cards grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="leaves-grid-container">
                        <!-- Injected dynamically -->
                    </div>
                </div>

                <!-- ================= OVERTIME (submenu of Live Attendance) ================= -->
                <div id="overtime" class="tab-content max-w-6xl mx-auto space-y-6 hidden">
                    <h3 class="font-heading text-xl font-bold text-primary text-center">Overtime Management</h3>

                    <!-- Summary Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <div class="bg-card p-5 rounded-2xl shadow-sm border border-border text-center">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Pending</p>
                            <h3 id="overtime-pending-count" class="font-heading text-3xl font-bold text-primary mt-2">0
                            </h3>
                        </div>
                        <div class="bg-card p-5 rounded-2xl shadow-sm border border-border text-center">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Approved</p>
                            <h3 id="overtime-approved-count" class="font-heading text-3xl font-bold text-primary mt-2">0
                            </h3>
                        </div>
                        <div class="bg-card p-5 rounded-2xl shadow-sm border border-border text-center">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Hours</p>
                            <h3 id="overtime-total-hours" class="font-heading text-3xl font-bold text-primary mt-2">0hrs
                            </h3>
                        </div>
                    </div>

                    <!-- Overtime Table -->
                    <div class="bg-card rounded-2xl shadow-sm border border-border overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs font-semibold text-white uppercase bg-secondary">
                                <tr>
                                    <th class="px-5 py-3.5">Employee</th>
                                    <th class="px-5 py-3.5">Date</th>
                                    <th class="px-5 py-3.5">Hours</th>
                                    <th class="px-5 py-3.5">Reason</th>
                                    <th class="px-5 py-3.5">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border" id="overtime-table-body">

                            </tbody>
                        </table>
                    </div>
                </div>


                <!-- ================= WORKFORCE ANALYTICS ================= -->
                <div id="analytics" class="tab-content max-w-6xl mx-auto space-y-6 hidden">
                    <div class="mb-6 flex justify-between items-center">

                        <select class="bg-card border border-border text-primary text-sm rounded-2xl p-2">
                            <option>Q3 2026 (Current)</option>
                            <option>Q2 2026</option>
                            <option>Year to Date</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Labor Costs Chart -->
                        <div class="bg-card p-5 rounded-2xl shadow-sm border border-border">
                            <h3 class="font-semibold text-primary mb-4 text-sm uppercase tracking-wider">ATTENDANCE
                                RECORDS (LAST 7 DAYS)</h3>
                            <div class="h-72 relative w-full">
                                <canvas id="costChart"></canvas>
                            </div>
                        </div>

                        <!-- Utilization Chart -->
                        <div class="bg-card p-5 rounded-2xl shadow-sm border border-border">
                            <h3 class="font-semibold text-primary mb-4 text-sm uppercase tracking-wider">ACTIVE
                                EMPLOYEES BY DEPARTMENT</h3>
                            <div class="h-72 relative w-full flex justify-center">
                                <canvas id="utilizationChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div class="bg-card p-5 rounded-2xl shadow-sm border border-border">
                            <p class="text-sm text-gray-500 font-medium">Total Overtime Hours</p>
                            <h3 id="admin-analytics-overtime"
                                class="font-heading text-2xl font-bold text-amber-600 mt-1">0 hrs</h3>
                            <p class="text-xs text-gray-400 mt-1">
                                From PostgreSQL attendance records
                            </p>
                        </div>
                        <div class="bg-card p-5 rounded-2xl shadow-sm border border-border">
                            <p class="text-sm text-gray-500 font-medium">Avg. Absenteeism Rate</p>
                            <h3 id="admin-analytics-absence"
                                class="font-heading text-2xl font-bold text-emerald-600 mt-1">0%</h3>
                            <p class="text-xs text-gray-400 mt-1">
                                Based on the current 7-day period
                            </p>
                        </div>
                        <div class="bg-card p-5 rounded-2xl shadow-sm border border-border">
                            <p class="text-sm text-gray-500 font-medium">Shift Fulfillment Rate</p>
                            <h3 id="admin-analytics-fulfillment"
                                class="font-heading text-2xl font-bold text-blue-600 mt-1">0%</h3>
                            <p class="text-xs text-gray-400 mt-1">
                                Scheduled workdays vs attendance
                            </p>
                        </div>
                    </div>
                    <div class="bg-card p-5 rounded-2xl shadow-sm border border-border mt-6">

                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="font-semibold text-primary text-sm uppercase tracking-wider">
                                    Workforce by Department
                                </h3>

                                <p class="text-xs text-gray-400 mt-1">
                                    Active employees currently stored in PostgreSQL
                                </p>
                            </div>
                        </div>

                        <div id="admin-analytics-departments"
                            class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                        </div>

                    </div>
                </div>

            </main>
        </div>

        <!-- MODAL: ASSIGN EMPLOYEE ID TO RFID -->
        <div id="modal-assign-rfid"
            class="hidden fixed inset-0 bg-primary/80 backdrop-blur-sm flex items-center justify-center z-[80] p-4"
            onclick="if(event.target===this) closeAssignRfidModal()">

            <div class="bg-card rounded-2xl shadow-2xl border border-border max-w-md w-full overflow-hidden">

                <div class="px-6 py-5 border-b border-border flex items-center justify-between bg-background">

                    <div>

                        <h3 class="font-heading font-bold text-primary text-lg">
                            Assign Employee ID to RFID (UID)
                        </h3>

                        <p class="text-xs text-gray-500 mt-1">
                            Scan or enter the RFID UID to assign.
                        </p>

                    </div>

                    <button type="button" onclick="closeAssignRfidModal()"
                        class="p-1.5 rounded-lg hover:bg-gray-200 text-gray-500">

                        <i data-lucide="x" class="w-5 h-5"></i>

                    </button>

                </div>

                <form id="assign-rfid-form" onsubmit="handleAssignRfidSubmit(event)" class="p-6 space-y-4">

                    <div>

                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">

                            Employee

                        </label>

                        <select id="assign-rfid-employee" required
                            class="w-full border border-border rounded-xl px-3 py-2.5 text-sm bg-background text-primary focus:outline-none focus:ring-2 focus:ring-accent">

                            <option value="">
                                Select employee
                            </option>

                        </select>

                    </div>

                    <div>

                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">

                            RFID UID

                        </label>

                        <div class="relative">

                            <i class="fa-solid fa-id-card absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                            </i>

                            <input id="assign-rfid-uid" type="text" required maxlength="100" autocomplete="off"
                                placeholder="Scan RFID card..."
                                class="w-full border border-border rounded-xl pl-10 pr-3 py-2.5 text-sm text-primary focus:outline-none focus:ring-2 focus:ring-accent">

                        </div>

                        <p class="text-[11px] text-gray-400 mt-1">
                            Your RFID reader can type the UID directly into this field.
                        </p>

                    </div>

                    <div id="assign-rfid-message" class="hidden text-sm rounded-xl px-4 py-3">
                    </div>

                    <div class="flex gap-3 pt-2">

                        <button type="button" onclick="closeAssignRfidModal()"
                            class="flex-1 py-3 bg-error hover:bg-red-600 text-white font-button font-semibold rounded-xl">

                            Cancel

                        </button>

                        <button id="assign-rfid-submit" type="submit"
                            class="flex-1 py-3 bg-secondary hover:bg-[#E08A3B] text-white font-button font-semibold rounded-xl">

                            Assign RFID

                        </button>

                    </div>

                </form>

            </div>

        </div>

        <!-- MODAL: ADD EMPLOYEE -->
        <div id="modal-add-employee"
            class="hidden fixed inset-0 bg-primary/80 backdrop-blur-sm flex items-center justify-center z-[70] p-4"
            onclick="if(event.target===this) closeAddEmployeeModal()">
            <div
                class="bg-card rounded-2xl shadow-2xl border border-border max-w-2xl w-full max-h-[92vh] overflow-y-auto">
                <div class="px-6 py-5 border-b border-border flex items-center justify-between bg-background">
                    <div>
                        <h3 class="font-heading font-bold text-primary text-lg">Add Employee</h3>
                        <p class="text-xs text-gray-500 mt-1">Create a new employee record in MySQL.</p>
                    </div>
                    <button type="button" onclick="closeAddEmployeeModal()"
                        class="p-1.5 rounded-lg hover:bg-gray-200 text-gray-500">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form id="add-employee-form" onsubmit="handleAddEmployeeSubmit(event)" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Employee ID *</label>
                            <input id="add-employee-no" required maxlength="30"
                                class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Hire Date</label>
                            <input id="add-hire-date" type="date"
                                class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">First Name *</label>
                            <input id="add-first-name" required maxlength="80"
                                class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Middle Name</label>
                            <input id="add-middle-name" maxlength="80"
                                class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Last Name *</label>
                            <input id="add-last-name" required maxlength="80"
                                class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Phone</label>
                            <input id="add-phone" maxlength="30"
                                class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Gmail *</label>
                            <input id="add-email" type="email" required maxlength="255" autocomplete="email"
                                placeholder="employee@gmail.com"
                                class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Password *</label>
                            <input id="add-password" type="password" required minlength="8" maxlength="255"
                                autocomplete="new-password" placeholder="Minimum 8 characters"
                                class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Department *</label>
                            <input id="add-department" required maxlength="100" placeholder="e.g. OPERATIONS"
                                class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Position *</label>
                            <input id="add-position" required maxlength="100" placeholder="e.g. TOUR GUIDE"
                                class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Employment Type</label>
                            <select id="add-employment-type" onchange="handleEmploymentTypeChange()"
                                class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent">
                                <option value="REGULAR">Regular</option>
                                <option value="FULL_TIME">Full-Time</option>
                                <option value="INTERNSHIP_OJT">Internship (OJT)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Status</label>
                            <input id="add-employment-status" type="text" value="" readonly
                                class="w-full border border-border rounded-xl px-3 py-2.5 text-sm bg-gray-100 text-gray-700 cursor-not-allowed">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Address</label>
                        <textarea id="add-address" rows="2" maxlength="255"
                            class="w-full border border-border rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent"></textarea>
                    </div>

                    <div id="add-employee-message" class="hidden text-sm rounded-xl px-4 py-3"></div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" onclick="closeAddEmployeeModal()"
                            class="flex-1 py-3 bg-error hover:bg-red-600 text-white font-button font-semibold rounded-xl">
                            Cancel
                        </button>
                        <button id="add-employee-submit" type="submit"
                            class="flex-1 py-3 bg-secondary hover:bg-[#E08A3B] text-white font-button font-semibold rounded-xl">
                            Save Employee
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: REQUEST LEAVE -->
        <div id="modal-request-leave"
            class="hidden fixed inset-0 bg-primary/80 backdrop-blur-sm flex items-center justify-center z-50 p-4 transition-opacity">
            <div
                class="bg-card rounded-2xl shadow-2xl border border-border max-w-md w-full overflow-hidden transform transition-all">
                <div class="px-6 py-5 border-b border-border flex justify-between items-center bg-background">
                    <h3 class="font-heading font-bold text-primary text-lg">File Leave Request</h3>
                    <button onclick="closeRequestLeaveModal()"
                        class="p-1.5 rounded-lg hover:bg-gray-200 text-gray-500 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <form onsubmit="handleRequestLeaveSubmit(event)" class="p-6 space-y-5">
                    <div>
                        <label
                            class="block font-button text-xs font-medium text-gray-500 mb-2 uppercase tracking-wider">Applicant
                            Employee</label>
                        <select id="leave-form-emp" required
                            class="w-full bg-background border border-border rounded-xl p-3 text-sm focus:outline-none focus:ring-2 focus:ring-accent text-primary">
                            <!-- Populated dynamically -->
                        </select>
                    </div>
                    <div>
                        <label
                            class="block font-button text-xs font-medium text-gray-500 mb-2 uppercase tracking-wider">Leave
                            Category</label>
                        <select id="leave-form-type" required
                            class="w-full bg-background border border-border rounded-xl p-3 text-sm focus:outline-none focus:ring-2 focus:ring-accent text-primary">
                            <option value="Service Incentive Leave (SIL)">Service Incentive Leave (SIL)</option>
                            <option value="Vacation Leave">Vacation Leave</option>
                            <option value="Sick Leave">Sick Leave</option>
                            <option value="Maternity Leave">Maternity Leave</option>
                            <option value="Paternity Leave">Paternity Leave</option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="block font-button text-xs font-medium text-gray-500 mb-2 uppercase tracking-wider">
                            Requested Dates
                        </label>

                        <div class="grid grid-cols-2 gap-4">

                            <!-- FROM -->
                            <div>
                                <label class="block font-button text-xs font-semibold text-gray-600 mb-1.5">
                                    From
                                </label>

                                <input type="date" id="leave-form-start-date" required
                                    class="w-full bg-background border border-border rounded-xl p-3 text-sm focus:outline-none focus:ring-2 focus:ring-accent text-primary" />
                            </div>

                            <!-- TO -->
                            <div>
                                <label class="block font-button text-xs font-semibold text-gray-600 mb-1.5">
                                    To
                                </label>

                                <input type="date" id="leave-form-end-date" required
                                    class="w-full bg-background border border-border rounded-xl p-3 text-sm focus:outline-none focus:ring-2 focus:ring-accent text-primary" />
                            </div>

                        </div>
                    </div>
                    <div id="leave-form-reason-wrap">

                        <label for="leave-form-reason"
                            class="block font-button text-xs font-medium text-gray-500 mb-2 uppercase tracking-wider">
                            Reason / Notes
                        </label>

                        <textarea id="leave-form-reason" required rows="3" placeholder="Explain reasoning..."
                            class="w-full bg-background border border-border rounded-xl p-3 text-sm focus:outline-none focus:ring-2 focus:ring-accent text-primary"></textarea>

                    </div>
                    <div class="pt-2">
                        <button type="submit"
                            class="w-full py-3 bg-secondary hover:bg-[#E08A3B] text-white font-button font-medium rounded-xl shadow-lg shadow-secondary/30 text-sm transition-all-300">
                            File Request
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: CREATE SCHEDULE -->
        <!-- DAY INFORMATION MODAL -->
        <div id="modal-schedule-day"
            class="hidden fixed inset-0 bg-primary/80 backdrop-blur-sm flex items-center justify-center z-[70] p-4"
            onclick="if(event.target===this) closeScheduleDayModal()">
            <div
                class="bg-card rounded-2xl border-4 border-secondary shadow-2xl max-w-lg w-full max-h-[85vh] overflow-hidden">
                <div class="px-6 py-4 border-b border-border flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Selected Day</p>
                        <h3 id="schedule-day-modal-title" class="font-heading font-bold text-primary text-xl mt-0.5">
                            Schedule Details</h3>
                    </div>
                    <button type="button" onclick="closeScheduleDayModal()"
                        class="w-9 h-9 rounded-full hover:bg-background text-gray-500 flex items-center justify-center transition-colors"
                        aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="p-6">
                    <div id="schedule-day-modal-summary" class="text-sm text-gray-500 mb-4"></div>
                    <div id="schedule-day-modal-content" class="space-y-3 max-h-[55vh] overflow-y-auto pr-1"></div>
                </div>
            </div>
        </div>

        <div id="modal-create-schedule"
            class="hidden fixed inset-0 bg-primary/80 backdrop-blur-sm flex items-center justify-center z-50 p-4 transition-opacity"
            onclick="if(event.target===this) closeCreateScheduleModal()">
            <div
                class="bg-card rounded-[28px] border-4 border-secondary shadow-2xl max-w-sm w-full overflow-hidden transform transition-all">
                <form onsubmit="handleCreateScheduleSubmit(event)" class="p-6 space-y-4">
                    <h3 class="font-heading font-bold text-primary text-lg text-center mb-1">Create Schedule</h3>

                    <div>
                        <label class="block font-button text-xs font-semibold text-gray-600 mb-1.5">Employee</label>
                        <select id="schedule-form-employee" required
                            class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent text-primary">
                            <option value="">Loading employees...</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-button text-xs font-semibold text-gray-600 mb-1.5">Date</label>
                        <div class="flex items-center gap-2">
                            <input type="text" id="schedule-form-date" required value="August 20, 2026"
                                class="flex-1 bg-white border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent text-primary" />
                            <div class="relative">
                                <button type="button"
                                    onclick="document.getElementById('schedule-form-date-native').showPicker && document.getElementById('schedule-form-date-native').showPicker()"
                                    class="w-[42px] h-[42px] flex items-center justify-center bg-white border border-gray-300 rounded-lg text-gray-500 hover:bg-background transition-colors">
                                    <i class="fa-regular fa-calendar"></i>
                                </button>
                                <input type="date" id="schedule-form-date-native"
                                    onchange="updateScheduleDateDisplay(this)"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block font-button text-xs font-semibold text-gray-600 mb-1.5">Shift</label>
                        <select id="schedule-form-shift" required onchange="updateScheduleShiftTimes()"
                            class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent text-primary">
                            <option>Regular</option>
                            <option>Morning</option>
                            <option>Afternoon</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-button text-xs font-semibold text-gray-600 mb-1.5">Start
                                Time</label>
                            <input type="text" id="schedule-form-start" required value="8:00 AM" readonly tabindex="-1"
                                aria-readonly="true"
                                class="w-full bg-gray-100 border border-gray-300 rounded-lg px-3 py-2.5 text-sm text-center text-primary cursor-not-allowed select-none" />
                        </div>
                        <div>
                            <label class="block font-button text-xs font-semibold text-gray-600 mb-1.5">End Time</label>
                            <input type="text" id="schedule-form-end" required value="5:00 PM" readonly tabindex="-1"
                                aria-readonly="true"
                                class="w-full bg-gray-100 border border-gray-300 rounded-lg px-3 py-2.5 text-sm text-center text-primary cursor-not-allowed select-none" />
                        </div>
                    </div>

                    <div>
                        <label class="block font-button text-xs font-semibold text-gray-600 mb-1.5">Work Type</label>
                        <select id="schedule-form-worktype" required
                            class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent text-primary">
                            <option value="Field Duty">Field Duty</option>
                            <option value="Office Work">Office Work</option>
                            <option value="Driving">Driving</option>
                            <option value="Monitoring">Monitoring</option>
                            <option value="Meeting">Meeting</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-button text-xs font-semibold text-gray-600 mb-1.5">Remarks</label>
                        <textarea id="schedule-form-remarks" rows="2"
                            class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent text-primary resize-none"></textarea>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" onclick="closeCreateScheduleModal()"
                            class="flex-1 py-3 bg-error hover:bg-red-600 text-white font-button font-semibold rounded-full shadow-md text-sm transition-all-300">
                            Cancel
                        </button>
                        <button type="submit"
                            class="flex-1 py-3 bg-success hover:bg-emerald-600 text-white font-button font-semibold rounded-full shadow-md text-sm transition-all-300">
                            Save Schedule
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <!-- MODAL: RE-ASSIGN SCHEDULE -->
    <div id="modal-reassign-schedule"
        class="hidden fixed inset-0 bg-primary/80 backdrop-blur-sm flex items-center justify-center z-[60] p-4"
        onclick="if(event.target===this) closeReassignScheduleModal()">

        <div class="bg-card rounded-[28px] border-4 border-primary shadow-2xl max-w-md w-full overflow-hidden">

            <form onsubmit="handleReassignScheduleSubmit(event)" class="p-6 space-y-4">

                <h3 class="font-heading font-bold text-primary text-lg text-center">
                    Re-assign Schedule
                </h3>

                <!-- CURRENT SCHEDULE -->
                <div>
                    <label class="block font-button text-xs font-semibold text-gray-600 mb-1.5">
                        Current Schedule
                    </label>

                    <select id="reassign-schedule-select" required onchange="loadReassignReplacementEmployees()"
                        class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent text-primary">

                        <option value="">Loading schedules...</option>

                    </select>
                </div>

                <!-- REPLACEMENT EMPLOYEE -->
                <div>
                    <label class="block font-button text-xs font-semibold text-gray-600 mb-1.5">
                        Replacement Employee
                    </label>

                    <select id="reassign-employee-select" required disabled
                        class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent text-primary">

                        <option value="">Select a schedule first</option>

                    </select>
                </div>

                <div class="flex gap-3 pt-2">

                    <button type="button" onclick="closeReassignScheduleModal()"
                        class="flex-1 py-3 bg-error hover:bg-red-600 text-white font-button font-semibold rounded-full shadow-md text-sm transition-all-300">
                        Cancel
                    </button>

                    <button type="submit"
                        class="flex-1 py-3 bg-primary hover:bg-[#102F57] text-white font-button font-semibold rounded-full shadow-md text-sm transition-all-300">
                        Re-assign
                    </button>

                </div>

            </form>
        </div>
        <!-- MODAL: CONFIRM SCHEDULE -->
        <div id="modal-confirm-schedule"
            class="hidden fixed inset-0 bg-primary/80 backdrop-blur-sm flex items-center justify-center z-[80] p-4"
            onclick="if(event.target===this) closeConfirmScheduleModal()">

            <div class="bg-card rounded-[28px] border-4 border-secondary shadow-2xl max-w-sm w-full overflow-hidden">

                <div class="p-6 text-center">

                    <div
                        class="mx-auto mb-4 w-14 h-14 rounded-full bg-secondary/10 text-secondary flex items-center justify-center">
                        <i class="fa-solid fa-calendar-check text-2xl"></i>
                    </div>

                    <h3 class="font-heading font-bold text-primary text-xl">
                        Confirm Schedule?
                    </h3>

                    <p id="confirm-schedule-summary" class="text-sm text-gray-500 mt-2 leading-relaxed">
                        Please confirm the schedule details.
                    </p>

                    <div class="flex gap-3 mt-6">

                        <button type="button" onclick="closeConfirmScheduleModal()"
                            class="flex-1 py-3 bg-error hover:bg-red-600 text-white font-button font-semibold rounded-full shadow-md text-sm transition-all-300">
                            No
                        </button>

                        <button type="button" onclick="confirmCreateSchedule()"
                            class="flex-1 py-3 bg-success hover:bg-emerald-600 text-white font-button font-semibold rounded-full shadow-md text-sm transition-all-300">
                            Yes
                        </button>

                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- TOAST: View Profile -->
    <div id="view-profile-toast"
        class="hidden fixed z-[60] top-6 left-1/2 -translate-x-1/2 bg-primary text-white text-sm font-medium px-4 py-2.5 rounded-xl shadow-lg flex items-center gap-2 transition-all-300 opacity-0">
        <i class="fa-solid fa-eye text-accent"></i> Viewing Profile
    </div>

    <!-- MODAL: ADMIN RFID VERIFICATION FOR EMPLOYEE EDIT -->
    <div id="modal-admin-rfid-edit"
        class="hidden fixed inset-0 bg-primary/80 backdrop-blur-sm flex items-center justify-center z-[70] p-4">

        <style>
            #admin-rfid-edit-scanner .admin-scan-line {
                background: linear-gradient(to bottom,
                        transparent,
                        #F59B45,
                        transparent);
                animation: admin-rfid-scan 2s cubic-bezier(0.4, 0, 0.2, 1) infinite;
            }

            @keyframes admin-rfid-scan {
                0% {
                    top: -10%;
                    opacity: 0;
                }

                10% {
                    opacity: 1;
                }

                90% {
                    opacity: 1;
                }

                100% {
                    top: 110%;
                    opacity: 0;
                }
            }

            #admin-rfid-edit-scanner .admin-ripple {
                position: absolute;
                border-radius: 50%;
                border: 2px solid #6FA9E6;
                animation: admin-rfid-ripple 2s linear infinite;
                opacity: 0;
            }

            @keyframes admin-rfid-ripple {
                0% {
                    transform: scale(0.8);
                    opacity: 0.5;
                }

                100% {
                    transform: scale(1.5);
                    opacity: 0;
                }
            }

            #admin-rfid-edit-scanner.admin-scanning .admin-id-card-icon {
                transform: scale(0.95);
            }

            #admin-rfid-edit-scanner.admin-success .admin-id-card-icon {
                color: #22C55E;
            }

            #admin-rfid-edit-scanner.admin-error .admin-id-card-icon {
                color: #EF4444;
            }
        </style>

        <div class="bg-card rounded-[2rem] shadow-2xl border border-border w-full max-w-lg overflow-hidden">

            <!-- Header -->
            <div class="px-6 py-5 border-b border-border flex justify-between items-center">

                <div>
                    <h3 class="font-heading font-bold text-primary text-lg">
                        Admin RFID Verification
                    </h3>

                    <p class="text-xs text-gray-500 mt-1">
                        Scan your Admin RFID card to continue.
                    </p>
                </div>

                <button type="button" onclick="closeAdminRfidEditModal()"
                    class="p-2 rounded-lg hover:bg-gray-200 text-gray-500">

                    <i data-lucide="x" class="w-5 h-5"></i>

                </button>
            </div>

            <!-- Scanner -->
            <div class="px-6 py-8">

                <div id="admin-rfid-edit-status"
                    class="text-center font-heading text-xl font-semibold text-primary mb-6">
                    Please Tap Here
                </div>

                <div id="admin-rfid-edit-scanner" class="relative flex items-center justify-center mx-auto w-64 h-64">

                    <!-- Ripple -->
                    <div id="admin-rfid-edit-ripple" class="absolute inset-0 m-auto w-44 h-44">

                        <div class="admin-ripple" style="animation-delay:0s; width:100%; height:100%;">
                        </div>

                        <div class="admin-ripple" style="animation-delay:1s; width:100%; height:100%;">
                        </div>

                    </div>

                    <!-- Corner brackets -->
                    <svg class="absolute top-0 left-0 w-16 h-16 text-primary admin-scanner-border" viewBox="0 0 64 64"
                        fill="none">

                        <path d="M0 64V0H64" stroke="currentColor" stroke-width="4" />

                        <path d="M12 52V12H52" stroke="currentColor" stroke-width="4" />

                    </svg>

                    <svg class="absolute top-0 right-0 w-16 h-16 text-primary admin-scanner-border" viewBox="0 0 64 64"
                        fill="none" transform="scale(-1,1)">

                        <path d="M0 64V0H64" stroke="currentColor" stroke-width="4" />

                        <path d="M12 52V12H52" stroke="currentColor" stroke-width="4" />

                    </svg>

                    <svg class="absolute bottom-0 left-0 w-16 h-16 text-primary admin-scanner-border"
                        viewBox="0 0 64 64" fill="none" transform="scale(1,-1)">

                        <path d="M0 64V0H64" stroke="currentColor" stroke-width="4" />

                        <path d="M12 52V12H52" stroke="currentColor" stroke-width="4" />

                    </svg>

                    <svg class="absolute bottom-0 right-0 w-16 h-16 text-primary admin-scanner-border"
                        viewBox="0 0 64 64" fill="none" transform="scale(-1,-1)">

                        <path d="M0 64V0H64" stroke="currentColor" stroke-width="4" />

                        <path d="M12 52V12H52" stroke="currentColor" stroke-width="4" />

                    </svg>

                    <!-- Main scanner area -->
                    <div
                        class="relative w-48 h-48 rounded-xl overflow-hidden flex items-center justify-center bg-card shadow-inner border border-border/50">

                        <!-- RFID card icon -->
                        <svg id="admin-rfid-edit-card-icon"
                            class="admin-id-card-icon w-32 h-auto text-primary transition-all duration-300"
                            viewBox="0 0 100 70" fill="none" stroke="currentColor" stroke-width="3"
                            stroke-linecap="round" stroke-linejoin="round">

                            <rect x="5" y="5" width="90" height="60" rx="4" />

                            <rect x="15" y="15" width="25" height="30" rx="2" />

                            <circle cx="27.5" cy="24" r="5" />

                            <path d="M18 41c0-4 4-7 9.5-7s9.5 3 9.5 7" />

                            <line x1="50" y1="20" x2="80" y2="20" />

                            <line x1="50" y1="30" x2="70" y2="30" />

                            <line x1="50" y1="40" x2="85" y2="40" />

                            <rect x="70" y="50" width="15" height="8" rx="1" />

                            <circle cx="20" cy="54" r="1" fill="currentColor" stroke="none" />

                            <circle cx="35" cy="54" r="1" fill="currentColor" stroke="none" />

                        </svg>

                        <!-- Laser -->
                        <div id="admin-rfid-edit-laser"
                            class="absolute left-0 w-full h-2 admin-scan-line hidden opacity-0 pointer-events-none">
                        </div>

                        <!-- Success -->
                        <div id="admin-rfid-edit-success"
                            class="absolute inset-0 bg-green-100/70 backdrop-blur-[2px] flex items-center justify-center hidden">

                            <svg class="w-24 h-24 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M5 13l4 4L19 7">
                                </path>

                            </svg>
                        </div>

                        <!-- Denied -->
                        <div id="admin-rfid-edit-denied"
                            class="absolute inset-0 bg-red-100/70 backdrop-blur-[2px] flex items-center justify-center hidden">

                            <svg class="w-24 h-24 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M6 6l12 12M18 6L6 18">
                                </path>

                            </svg>
                        </div>

                    </div>
                </div>

                <!-- Hidden RFID input -->
                <input id="admin-rfid-edit-input" type="text" autocomplete="off" tabindex="0"
                    class="absolute opacity-0 pointer-events-none w-px h-px" aria-label="Admin RFID scanner input">

                <p class="mt-6 text-primary/50 text-xs font-medium tracking-wider uppercase text-center">
                    Align RFID Card within the frame
                </p>

            </div>

        </div>
    </div>

    <!-- MODAL: EMPLOYEE PROFILE -->
    <div id="modal-employee-profile"
        class="hidden fixed inset-0 bg-primary/80 backdrop-blur-sm flex items-center justify-center z-50 p-4 transition-opacity"
        onclick="if(event.target===this) closeEmployeeProfile()">

        <div
            class="bg-card rounded-2xl shadow-2xl border border-border max-w-4xl w-full overflow-hidden max-h-[92vh] flex flex-col">

            <!-- Header -->
            <div class="px-6 py-5 border-b border-border flex justify-between items-center bg-background flex-shrink-0">

                <div>
                    <h3 class="font-heading font-bold text-primary text-lg">
                        Employee Profile
                    </h3>

                    <p id="profile-id" class="text-xs text-gray-500 mt-1">
                        â€”
                    </p>
                </div>

                <div class="flex items-center gap-2">

                    <button id="profile-edit-info-btn" type="button" onclick="startEmployeeEdit()"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-secondary text-white text-sm font-semibold hover:opacity-90 transition">
                        <i class="fa-solid fa-pen-to-square"></i>
                        Edit Info
                    </button>

                    <button onclick="closeEmployeeProfile()"
                        class="p-2 rounded-lg hover:bg-gray-200 text-gray-500 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>

                </div>
            </div>

            <!-- Scrollable content -->
            <div class="overflow-y-auto">

                <!-- Identity -->
                <div class="p-6 border-b border-border flex items-center gap-4">

                    <div
                        class="w-20 h-20 rounded-full bg-accent/20 text-accent flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-user text-3xl"></i>
                    </div>

                    <div>

                        <h4 id="profile-name" class="font-heading font-bold text-primary text-xl leading-tight">
                            â€”
                        </h4>

                        <p id="profile-position" class="text-sm text-gray-600 mt-1">
                            â€”
                        </p>

                        <p id="profile-department" class="text-sm text-gray-500 mt-0.5">
                            â€”
                        </p>

                        <span id="profile-status"
                            class="inline-flex items-center mt-3 bg-gray-100 text-gray-600 text-xs font-semibold px-3 py-1 rounded-full">
                            â€”
                        </span>

                    </div>
                </div>

                <!-- Information -->
                <div class="border-b border-border">

                    <!-- Personal Information -->
                    <button
                        class="profile-accordion-btn w-full flex items-center justify-between px-6 py-4 text-sm font-semibold text-primary hover:bg-background transition-colors"
                        onclick="toggleAccordion(this)">

                        Personal Information

                        <i class="fa-solid fa-chevron-down text-xs text-gray-400 transition-transform"></i>
                    </button>

                    <div class="profile-accordion-panel hidden px-6 pb-5 text-sm text-gray-600 space-y-2">

                        <p>
                            <span class="text-gray-400">Date of Birth:</span>
                            <span id="profile-date-of-birth">â€”</span>
                        </p>

                        <p>
                            <span class="text-gray-400">Gender:</span>
                            <span id="profile-gender">â€”</span>
                        </p>

                        <p>
                            <span class="text-gray-400">Address:</span>
                            <span id="profile-address">â€”</span>
                        </p>

                    </div>

                    <!-- Contact Information -->
                    <button
                        class="profile-accordion-btn w-full flex items-center justify-between px-6 py-4 text-sm font-semibold text-primary hover:bg-background transition-colors"
                        onclick="toggleAccordion(this)">

                        Contact Information

                        <i class="fa-solid fa-chevron-down text-xs text-gray-400 transition-transform"></i>
                    </button>

                    <div class="profile-accordion-panel hidden px-6 pb-5 text-sm text-gray-600 space-y-2">

                        <p>
                            <span class="text-gray-400">Phone:</span>
                            <span id="profile-phone">â€”</span>
                        </p>

                        <p>
                            <span class="text-gray-400">Email:</span>
                            <span id="profile-email">â€”</span>
                        </p>

                    </div>

                    <!-- Employment Information -->
                    <button
                        class="profile-accordion-btn w-full flex items-center justify-between px-6 py-4 text-sm font-semibold text-primary hover:bg-background transition-colors"
                        onclick="toggleAccordion(this)">

                        Employment Information

                        <i class="fa-solid fa-chevron-down text-xs text-gray-400 transition-transform"></i>
                    </button>

                    <div class="profile-accordion-panel hidden px-6 pb-5 text-sm text-gray-600 space-y-2">

                        <p>
                            <span class="text-gray-400">Department:</span>
                            <span id="profile-employment-department">â€”</span>
                        </p>

                        <p>
                            <span class="text-gray-400">Position:</span>
                            <span id="profile-employment-position">â€”</span>
                        </p>

                        <p>
                            <span class="text-gray-400">Date Hired:</span>
                            <span id="profile-hire-date">â€”</span>
                        </p>

                        <p>
                            <span class="text-gray-400">Employment Type:</span>
                            <span id="profile-employment-type">â€”</span>
                        </p>

                        <p>
                            <span class="text-gray-400">Employment Status:</span>
                            <span id="profile-employment-status">â€”</span>
                        </p>

                    </div>

                    <!-- Emergency Contact -->
                    <button
                        class="profile-accordion-btn w-full flex items-center justify-between px-6 py-4 text-sm font-semibold text-primary hover:bg-background transition-colors"
                        onclick="toggleAccordion(this)">

                        Emergency Contact

                        <i class="fa-solid fa-chevron-down text-xs text-gray-400 transition-transform"></i>
                    </button>

                    <div class="profile-accordion-panel hidden px-6 pb-5 text-sm text-gray-600 space-y-2">

                        <p>
                            <span class="text-gray-400">Name:</span>
                            <span id="profile-emergency-name">â€”</span>
                        </p>

                        <p>
                            <span class="text-gray-400">Relationship:</span>
                            <span id="profile-emergency-relationship">â€”</span>
                        </p>

                        <p>
                            <span class="text-gray-400">Phone:</span>
                            <span id="profile-emergency-phone">â€”</span>
                        </p>

                    </div>

                </div>

                <!-- Database Records -->
                <div>

                    <!-- Attendance Summary -->
                    <button
                        class="profile-accordion-btn w-full flex items-center justify-between px-6 py-4 text-sm font-semibold text-primary hover:bg-background transition-colors"
                        onclick="toggleAccordion(this)">

                        Attendance Summary

                        <i class="fa-solid fa-chevron-down text-xs text-gray-400 transition-transform"></i>
                    </button>

                    <div class="profile-accordion-panel hidden px-6 pb-5 text-sm text-gray-600 space-y-2">

                        <p>
                            <span class="text-gray-400">Period:</span>
                            <span id="profile-attendance-period">â€”</span>
                        </p>

                        <p>
                            <span class="text-gray-400">Days Present:</span>
                            <span id="profile-days-present">0</span>
                        </p>

                        <p>
                            <span class="text-gray-400">Late Arrivals:</span>
                            <span id="profile-late-arrivals">0</span>
                        </p>

                    </div>

                    <!-- Schedule -->
                    <button
                        class="profile-accordion-btn w-full flex items-center justify-between px-6 py-4 text-sm font-semibold text-primary hover:bg-background transition-colors"
                        onclick="toggleAccordion(this)">

                        Schedule

                        <i class="fa-solid fa-chevron-down text-xs text-gray-400 transition-transform"></i>
                    </button>

                    <div id="profile-schedule"
                        class="profile-accordion-panel hidden px-6 pb-5 text-sm text-gray-600 space-y-3">
                        <p class="text-gray-400">
                            Loading...
                        </p>
                    </div>

                    <!-- Leave Balance -->
                    <button
                        class="profile-accordion-btn w-full flex items-center justify-between px-6 py-4 text-sm font-semibold text-primary hover:bg-background transition-colors"
                        onclick="toggleAccordion(this)">

                        Leave Balance

                        <i class="fa-solid fa-chevron-down text-xs text-gray-400 transition-transform"></i>
                    </button>

                    <div class="profile-accordion-panel hidden px-6 pb-5 text-sm text-gray-600 space-y-2">

                        <p>
                            <span class="text-gray-400">Vacation Leave Balance:</span>
                            <span id="profile-vacation-balance">0</span>
                        </p>

                        <p>
                            <span class="text-gray-400">Sick Leave Balance:</span>
                            <span id="profile-sick-balance">0</span>
                        </p>

                        <p>
                            <span class="text-gray-400">Emergency Leave Balance:</span>
                            <span id="profile-emergency-balance">0</span>
                        </p>

                    </div>

                    <!-- Timesheet History -->
                    <button
                        class="profile-accordion-btn w-full flex items-center justify-between px-6 py-4 text-sm font-semibold text-primary hover:bg-background transition-colors"
                        onclick="toggleAccordion(this)">

                        Timesheet History

                        <i class="fa-solid fa-chevron-down text-xs text-gray-400 transition-transform"></i>
                    </button>

                    <div id="profile-timesheet"
                        class="profile-accordion-panel hidden px-6 pb-5 text-sm text-gray-600 space-y-3">
                        <p class="text-gray-400">
                            Loading...
                        </p>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- MODAL: ATTENDANCE CORRECTION REVIEW -->
    <div id="modal-correction-review"
        class="hidden fixed inset-0 bg-primary/80 backdrop-blur-sm flex items-center justify-center z-50 p-4 transition-opacity"
        onclick="if(event.target===this) closeCorrectionModal()">
        <div
            class="bg-card rounded-3xl shadow-2xl border border-border max-w-md w-full overflow-hidden transform transition-all max-h-[90vh] flex flex-col">

            <!-- Header -->
            <div class="px-6 py-5 flex justify-between items-center flex-shrink-0">
                <h3 class="font-heading font-bold text-primary text-lg mx-auto">Attendance Correction</h3>
                <button onclick="closeCorrectionModal()"
                    class="absolute right-6 p-1.5 rounded-lg hover:bg-gray-200 text-gray-500 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="overflow-y-auto px-6 pb-6 space-y-5">
                <div class="text-sm text-gray-600 space-y-1">
                    <p><span class="font-semibold text-primary">Employee:</span> <span
                            id="correction-employee">â€”</span>
                    </p>
                    <p><span class="font-semibold text-primary">Date:</span> <span id="correction-date">â€”</span></p>
                </div>

                <div>
                    <h4 class="font-heading font-bold text-primary text-sm mb-2">Current Record</h4>
                    <div class="text-sm text-gray-600 space-y-1">
                        <p>Clock In: <span id="correction-current-clockin">â€”</span></p>
                        <p>Clock Out: <span id="correction-current-clockout">â€”</span></p>
                    </div>
                </div>

                <div>
                    <h4 class="font-heading font-bold text-primary text-sm mb-2">Requested Correction</h4>
                    <div class="text-sm text-gray-600">
                        <p id="correction-requested">â€”</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-heading font-bold text-primary text-sm mb-2">Reason:</h4>
                        <div class="border border-border rounded-xl p-3 text-sm text-gray-600 min-h-[70px]"
                            id="correction-reason">â€”</div>
                    </div>
                    <div>
                        <h4 class="font-heading font-bold text-primary text-sm mb-2">Attachment:</h4>
                        <div class="border border-border rounded-xl p-3 text-sm text-gray-400 min-h-[70px] flex items-center justify-center text-center"
                            id="correction-attachment">No attachment</div>
                    </div>
                </div>

                <div class="flex justify-center gap-10 pt-2">
                    <button onclick="rejectCorrection()"
                        class="flex items-center gap-2 text-red-600 font-semibold text-sm hover:opacity-80 transition-opacity">
                        <i class="fa-solid fa-xmark text-lg"></i> Reject
                    </button>
                    <button onclick="approveCorrection()"
                        class="flex items-center gap-2 text-emerald-600 font-semibold text-sm hover:opacity-80 transition-opacity">
                        <i class="fa-solid fa-check text-lg"></i> Approve
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="scripts/shared-data.js"></script>
    <script src="scripts/script.js"></script>

    <script>

        async function loadEmployeesFromDatabase() {
            const tableBody = document.getElementById('employees-table-body');

            if (!tableBody) return;

            try {
                const response = await fetch('api/employees/list.php', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    },
                    cache: 'no-store'
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Failed to load employees.');
                }

                tableBody.innerHTML = '';

                result.data.forEach(employee => {
                    const row = document.createElement('tr');

                    row.className =
                        'group cursor-pointer hover:bg-background transition-all-300';

                    row.dataset.id = employee.id;
                    row.dataset.name = employee.name.toUpperCase();
                    row.dataset.position = (employee.position || '').toUpperCase();
                    row.dataset.department = (employee.department || '').toUpperCase();
                    row.dataset.status = employee.status;

                    row.onclick = function () {
                        openEmployeeProfile(this);
                    };

                    const statusClass =
                        employee.status === 'LEAVE'
                            ? 'bg-red-100 text-red-600'
                            : 'bg-emerald-100 text-emerald-700';

                    row.innerHTML = `
                <td class="px-5 py-4 font-medium text-primary">
                    ${employee.id}
                </td>

                <td class="px-5 py-4 font-semibold text-primary">
                    ${employee.name.toUpperCase()}
                </td>

                <td class="px-5 py-4 text-gray-600">
                    ${(employee.position || '').toUpperCase()}
                </td>

                <td class="px-5 py-4 text-gray-600">
                    ${(employee.department || '').toUpperCase()}
                </td>

                <td class="px-5 py-4">
                    <span class="inline-flex items-center ${statusClass} text-xs font-semibold px-3 py-1 rounded-full">
                        ${employee.status}
                    </span>
                </td>
            `;

                    tableBody.appendChild(row);
                });

                filterEmployeeTable();

            } catch (error) {
                console.error('Employee loading error:', error);

                tableBody.innerHTML = `
            <tr>
                <td colspan="5" class="px-5 py-8 text-center text-red-500">
                    Unable to load employee records.
                </td>
            </tr>
        `;
            }
        }

        // ---- Kiosk RFID Assignment ----

        async function loadRfidAssignments() {

            const tableBody =
                document.getElementById(
                    'rfid-assignments-table-body'
                );

            if (!tableBody) {
                return;
            }

            try {

                const response =
                    await fetch(
                        'api/kiosk/rfid-assignments.php',
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
                        'Unable to load RFID assignments.'
                    );
                }

                tableBody.innerHTML = '';

                const assignments =
                    Array.isArray(result.data)
                        ? result.data
                        : [];

                if (assignments.length === 0) {

                    tableBody.innerHTML = `
                <tr>
                    <td
                        colspan="5"
                        class="px-5 py-8 text-center text-gray-400">
                        No RFID assignments yet.
                    </td>
                </tr>
            `;

                    return;
                }

                assignments.forEach(assignment => {

                    const row =
                        document.createElement('tr');

                    row.className =
                        'hover:bg-background transition-colors';

                    const employeeId =
                        document.createElement('td');

                    employeeId.className =
                        'px-5 py-4 font-medium text-primary';

                    employeeId.textContent =
                        assignment.employee_id || 'â€”';

                    const employeeName =
                        document.createElement('td');

                    employeeName.className =
                        'px-5 py-4 font-semibold text-primary';

                    employeeName.textContent =
                        assignment.employee_name || 'â€”';

                    const rfidUid =
                        document.createElement('td');

                    rfidUid.className =
                        'px-5 py-4 font-mono text-sm text-gray-600';

                    rfidUid.textContent =
                        assignment.rfid_uid || 'â€”';

                    const status =
                        document.createElement('td');

                    status.className =
                        'px-5 py-4';

                    const statusBadge =
                        document.createElement('span');

                    statusBadge.className =
                        assignment.status === 'active'
                            ? 'inline-flex items-center bg-emerald-100 text-emerald-700 text-xs font-semibold px-3 py-1 rounded-full'
                            : 'inline-flex items-center bg-gray-100 text-gray-600 text-xs font-semibold px-3 py-1 rounded-full';

                    statusBadge.textContent =
                        String(
                            assignment.status || ''
                        ).toUpperCase();

                    status.appendChild(
                        statusBadge
                    );

                    const createdAt =
                        document.createElement('td');

                    createdAt.className =
                        'px-5 py-4 text-gray-500';

                    createdAt.textContent =
                        assignment.created_at || 'â€”';

                    row.appendChild(employeeId);
                    row.appendChild(employeeName);
                    row.appendChild(rfidUid);
                    row.appendChild(status);
                    row.appendChild(createdAt);

                    tableBody.appendChild(row);
                });

            } catch (error) {

                console.error(
                    'RFID assignment loading error:',
                    error
                );

                tableBody.innerHTML = `
            <tr>
                <td
                    colspan="5"
                    class="px-5 py-8 text-center text-red-500">
                    Unable to load RFID assignments.
                </td>
            </tr>
        `;
            }
        }


        async function openAssignRfidModal(
            employeeIdToSelect = ''
        ) {

            const modal =
                document.getElementById(
                    'modal-assign-rfid'
                );

            const employeeSelect =
                document.getElementById(
                    'assign-rfid-employee'
                );

            const rfidInput =
                document.getElementById(
                    'assign-rfid-uid'
                );

            const message =
                document.getElementById(
                    'assign-rfid-message'
                );

            message.className =
                'hidden';

            message.textContent =
                '';

            rfidInput.value =
                '';

            employeeSelect.innerHTML =
                '<option value="">Loading employees...</option>';

            modal.classList.remove('hidden');

            try {

                const response =
                    await fetch(
                        'api/employees/list.php',
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
                        'Unable to load employees.'
                    );
                }

                employeeSelect.innerHTML =
                    '<option value="">Select employee</option>';

                const employees =
                    Array.isArray(result.data)
                        ? result.data
                        : [];

                employees.forEach(employee => {

                    const option =
                        document.createElement(
                            'option'
                        );

                    option.value =
                        employee.id;

                    option.textContent =
                        `${employee.id} â€” ${employee.name}`;

                    employeeSelect.appendChild(
                        option
                    );
                });

                if (employeeIdToSelect) {

                    employeeSelect.value =
                        employeeIdToSelect;
                }

                setTimeout(() => {
                    rfidInput.focus();
                }, 100);

            } catch (error) {

                employeeSelect.innerHTML =
                    '<option value="">Unable to load employees</option>';

                message.textContent =
                    error.message ||
                    'Unable to load employees.';

                message.className =
                    'text-sm rounded-xl px-4 py-3 bg-red-50 text-red-600';
            }

            if (window.lucide) {
                lucide.createIcons();
            }
        }


        function closeAssignRfidModal() {

            document
                .getElementById(
                    'modal-assign-rfid'
                )
                .classList.add('hidden');
        }


        async function handleAssignRfidSubmit(
            event
        ) {

            event.preventDefault();

            const employeeId =
                document.getElementById(
                    'assign-rfid-employee'
                ).value.trim();

            const rfidUid =
                document.getElementById(
                    'assign-rfid-uid'
                ).value.trim();

            const message =
                document.getElementById(
                    'assign-rfid-message'
                );

            const submitButton =
                document.getElementById(
                    'assign-rfid-submit'
                );

            if (!employeeId) {

                message.textContent =
                    'Please select an employee.';

                message.className =
                    'text-sm rounded-xl px-4 py-3 bg-red-50 text-red-600';

                return;
            }

            if (!rfidUid) {

                message.textContent =
                    'Please scan or enter the RFID UID.';

                message.className =
                    'text-sm rounded-xl px-4 py-3 bg-red-50 text-red-600';

                return;
            }

            submitButton.disabled =
                true;

            submitButton.textContent =
                'Assigning...';

            message.className =
                'hidden';

            try {

                const response =
                    await fetch(
                        'api/kiosk/rfid-assignments.php',
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
                                rfid_uid:
                                    rfidUid
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
                        'Unable to assign RFID.'
                    );
                }

                message.textContent =
                    result.message;

                message.className =
                    'text-sm rounded-xl px-4 py-3 bg-emerald-50 text-emerald-700 border border-emerald-200';

                await loadRfidAssignments();
                await loadEmployeesFromDatabase();

                setTimeout(() => {

                    closeAssignRfidModal();

                }, 700);

            } catch (error) {

                message.textContent =
                    error.message ||
                    'Unable to assign RFID.';

                message.className =
                    'text-sm rounded-xl px-4 py-3 bg-red-50 text-red-600 border border-red-200';

            } finally {

                submitButton.disabled =
                    false;

                submitButton.textContent =
                    'Assign RFID';
            }
        }

        // ---- Add Employee ----
        function openAddEmployeeModal() {
            const employmentType = document.getElementById('add-employment-type');
            const employmentStatus = document.getElementById('add-employment-status');

            employmentType.selectedIndex = -1;
            employmentStatus.value = '';

            document.getElementById('modal-add-employee').classList.remove('hidden');
            document.getElementById('add-employee-message').classList.add('hidden');
            if (window.lucide) lucide.createIcons();
        }

        function handleEmploymentTypeChange() {
            const employmentStatus = document.getElementById('add-employment-status');
            const employmentType = document.getElementById('add-employment-type');

            if (employmentType.value) {
                employmentStatus.value = 'Active';
            } else {
                employmentStatus.value = '';
            }
        }

        function closeAddEmployeeModal() {
            document.getElementById('modal-add-employee').classList.add('hidden');
        }

        async function handleAddEmployeeSubmit(event) {
            event.preventDefault();

            const button = document.getElementById('add-employee-submit');
            const message = document.getElementById('add-employee-message');
            button.disabled = true;
            button.textContent = 'Saving...';
            message.className = 'hidden';

            const payload = {
                employee_no: document.getElementById('add-employee-no').value.trim(),
                first_name: document.getElementById('add-first-name').value.trim(),
                middle_name: document.getElementById('add-middle-name').value.trim(),
                last_name: document.getElementById('add-last-name').value.trim(),
                phone: document.getElementById('add-phone').value.trim(),
                email: document.getElementById('add-email').value.trim(),
                password: document.getElementById('add-password').value,
                department_name: document.getElementById('add-department').value.trim(),
                position_name: document.getElementById('add-position').value.trim(),
                hire_date: document.getElementById('add-hire-date').value || null,
                address: document.getElementById('add-address').value.trim(),
                employment_type: document.getElementById('add-employment-type').value,
                employment_status: document.getElementById('add-employment-status').value
            };

            payload.display_name = `${payload.first_name} ${payload.last_name}`.trim();

            try {
                const response = await fetch('api/employees/create.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.message || result.error || 'Failed to create employee.');
                }

                message.textContent =
                    'Employee saved successfully.';

                const newEmployeeId =
                    result.employee?.employee_no ||
                    payload.employee_no;

                document
                    .getElementById('add-employee-form')
                    .reset();

                document.getElementById('add-employment-type').selectedIndex = -1;
                document.getElementById('add-employment-status').value = '';

                await loadEmployeesFromDatabase();

                await loadRfidAssignments();

                closeAddEmployeeModal();

                openAssignRfidModal(
                    newEmployeeId
                );
            } catch (error) {
                message.textContent = error.message;
                message.className = 'text-sm rounded-xl px-4 py-3 bg-red-50 text-red-700 border border-red-200';
            } finally {
                button.disabled = false;
                button.textContent = 'Save Employee';
            }
        }

        loadRfidAssignments();

        // ---- Employee Profile: row click -> toast -> modal ----
        let currentProfileEmployeeId = null;

        async function openEmployeeProfile(row) {
            const employeeId = String(row.dataset.id || '').trim();

            if (!employeeId) {
                alert('Employee ID is missing.');
                return;
            }

            currentProfileEmployeeId = employeeId;

            // Basic information from the selected row
            document.getElementById('profile-name').textContent =
                row.dataset.name || 'â€”';

            document.getElementById('profile-id').textContent =
                employeeId;

            document.getElementById('profile-position').textContent =
                row.dataset.position || 'â€”';

            document.getElementById('profile-department').textContent =
                row.dataset.department || 'â€”';

            const statusEl = document.getElementById('profile-status');

            statusEl.textContent =
                row.dataset.status || 'â€”';

            if (row.dataset.status === 'LEAVE') {
                statusEl.className =
                    'inline-flex items-center mt-2 bg-red-100 text-red-600 text-xs font-semibold px-3 py-1 rounded-full';
            } else {
                statusEl.className =
                    'inline-flex items-center mt-2 bg-emerald-100 text-emerald-700 text-xs font-semibold px-3 py-1 rounded-full';
            }

            // Reset database fields
            const fields = [
                'profile-date-of-birth',
                'profile-gender',
                'profile-address',
                'profile-phone',
                'profile-email',
                'profile-employment-department',
                'profile-employment-position',
                'profile-hire-date',
                'profile-employment-type',
                'profile-employment-status',
                'profile-emergency-name',
                'profile-emergency-relationship',
                'profile-emergency-phone',
                'profile-attendance-period',
                'profile-days-present',
                'profile-late-arrivals',
                'profile-vacation-balance',
                'profile-sick-balance',
                'profile-emergency-balance',
            ];

            fields.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.textContent = 'â€”';
            });

            document.getElementById('profile-days-present').textContent = '0';
            document.getElementById('profile-late-arrivals').textContent = '0';
            document.getElementById('profile-vacation-balance').textContent = '0';
            document.getElementById('profile-sick-balance').textContent = '0';
            document.getElementById('profile-emergency-balance').textContent = '0';

            document.getElementById('profile-schedule').innerHTML =
                '<p class="text-gray-400">Loading...</p>';

            document.getElementById('profile-timesheet').innerHTML =
                '<p class="text-gray-400">Loading...</p>';

            // Close all accordion panels
            document
                .querySelectorAll('#modal-employee-profile .profile-accordion-panel')
                .forEach(panel => panel.classList.add('hidden'));

            document
                .querySelectorAll('#modal-employee-profile .profile-accordion-btn i')
                .forEach(icon => icon.classList.remove('rotate-180'));

            // Open modal
            const modal = document.getElementById('modal-employee-profile');

            modal.classList.remove('hidden');

            if (window.lucide) {
                lucide.createIcons();
            }

            try {
                const response = await fetch(
                    'api/employees/profile.php?employee_id=' +
                    encodeURIComponent(employeeId),
                    {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json'
                        }
                    }
                );

                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(
                        result.message || 'Unable to load employee profile.'
                    );
                }

                const data = result.data || {};
                const employee = data.employee || {};
                const attendance = data.attendance_summary || {};
                const leaveBalance =
                    data.leave_balance || {};

                // Identity
                document.getElementById('profile-name').textContent =
                    employee.full_name || 'â€”';

                document.getElementById('profile-id').textContent =
                    employee.employee_id || employeeId;

                document.getElementById('profile-position').textContent =
                    employee.position || 'â€”';

                document.getElementById('profile-department').textContent =
                    employee.department || 'â€”';

                // Personal Information
                document.getElementById('profile-date-of-birth').textContent =
                    employee.date_of_birth || 'â€”';

                document.getElementById('profile-gender').textContent =
                    employee.gender || 'â€”';

                document.getElementById('profile-address').textContent =
                    employee.address || 'â€”';

                // Contact Information
                document.getElementById('profile-phone').textContent =
                    employee.phone || 'â€”';

                document.getElementById('profile-email').textContent =
                    employee.email || 'â€”';

                // Employment Information
                document.getElementById('profile-employment-department').textContent =
                    employee.department || 'â€”';

                document.getElementById('profile-employment-position').textContent =
                    employee.position || 'â€”';

                document.getElementById('profile-hire-date').textContent =
                    employee.hire_date || 'â€”';

                document.getElementById('profile-employment-type').textContent =
                    employee.employment_type || 'â€”';

                document.getElementById('profile-employment-status').textContent =
                    employee.employment_status || 'â€”';

                // Emergency Contact
                document.getElementById('profile-emergency-name').textContent =
                    employee.emergency_name || 'â€”';

                document.getElementById('profile-emergency-relationship').textContent =
                    employee.emergency_relationship || 'â€”';

                document.getElementById('profile-emergency-phone').textContent =
                    employee.emergency_phone || 'â€”';

                // Attendance
                document.getElementById('profile-attendance-period').textContent =
                    attendance.period_start && attendance.period_end
                        ? attendance.period_start + ' to ' + attendance.period_end
                        : 'â€”';

                document.getElementById('profile-days-present').textContent =
                    String(attendance.days_present ?? 0);

                document.getElementById('profile-late-arrivals').textContent =
                    String(attendance.late_arrivals ?? 0);

                // Leave
                document.getElementById('profile-vacation-balance').textContent =
                    String(leaveBalance.vacation_remaining ?? 0);

                document.getElementById('profile-sick-balance').textContent =
                    String(leaveBalance.sick_remaining ?? 0);

                document.getElementById('profile-emergency-balance').textContent =
                    String(leaveBalance.emergency_remaining ?? 0);

                // Schedule
                const scheduleBox =
                    document.getElementById('profile-schedule');

                const schedules =
                    Array.isArray(data.schedule)
                        ? data.schedule
                        : [];

                if (schedules.length === 0) {
                    scheduleBox.innerHTML =
                        '<p class="text-gray-400">No upcoming shifts to display.</p>';
                } else {
                    scheduleBox.innerHTML = schedules.map(item => `
                <div class="border border-border rounded-xl p-3 bg-background">
                    <div class="font-semibold text-primary">
                        ${escapeProfileHtml(item.shift_name || 'Shift')}
                    </div>

                    <div class="text-xs text-gray-500 mt-1">
                        ${escapeProfileHtml(item.schedule_date || 'â€”')}
                    </div>

                    <div class="text-xs text-gray-500 mt-1">
                        ${escapeProfileHtml(item.start_time || 'â€”')}
                        -
                        ${escapeProfileHtml(item.end_time || 'â€”')}
                    </div>
                </div>
            `).join('');
                }

                // Timesheet History
                const timesheetBox =
                    document.getElementById('profile-timesheet');

                const timesheets =
                    Array.isArray(data.timesheet_history)
                        ? data.timesheet_history
                        : [];

                if (timesheets.length === 0) {
                    timesheetBox.innerHTML =
                        '<p class="text-gray-400">No timesheet records to display.</p>';
                } else {
                    timesheetBox.innerHTML = timesheets.map(item => `
                <div class="border border-border rounded-xl p-3 bg-background">

                    <div class="flex justify-between gap-3">
                        <span class="font-semibold text-primary">
                            ${escapeProfileHtml(item.date || 'â€”')}
                        </span>

                        <span class="text-xs text-gray-500">
                            ${escapeProfileHtml(item.status || 'â€”')}
                        </span>
                    </div>

                    <div class="grid grid-cols-3 gap-3 mt-2 text-xs text-gray-500">

                        <div>
                            <div>Time In</div>
                            <div class="font-medium text-primary">
                                ${escapeProfileHtml(item.time_in || 'â€”')}
                            </div>
                        </div>

                        <div>
                            <div>Time Out</div>
                            <div class="font-medium text-primary">
                                ${escapeProfileHtml(item.time_out || 'â€”')}
                            </div>
                        </div>

                        <div>
                            <div>Worked Hours</div>
                            <div class="font-medium text-primary">
                                ${escapeProfileHtml(String(item.worked_hours ?? 0))}
                            </div>
                        </div>

                    </div>
                </div>
            `).join('');
                }

                if (window.lucide) {
                    lucide.createIcons();
                }

            } catch (error) {
                console.error('Employee profile load failed:', error);
                alert(error.message || 'Unable to load employee profile.');
            }
        }

        function escapeProfileHtml(value) {
            const div = document.createElement('div');
            div.textContent = value == null ? '' : String(value);
            return div.innerHTML;
        }

        function startEmployeeEdit() {

            if (!currentProfileEmployeeId) {
                alert('No employee is selected.');
                return;
            }

            const modal =
                document.getElementById('modal-admin-rfid-edit');

            const input =
                document.getElementById('admin-rfid-edit-input');

            const status =
                document.getElementById('admin-rfid-edit-status');

            const scanner =
                document.getElementById('admin-rfid-edit-scanner');

            const laser =
                document.getElementById('admin-rfid-edit-laser');

            const success =
                document.getElementById('admin-rfid-edit-success');

            const denied =
                document.getElementById('admin-rfid-edit-denied');

            modal.classList.remove('hidden');

            status.textContent =
                'Please Tap Here';

            status.className =
                'text-center font-heading text-xl font-semibold text-primary mb-6';

            scanner.classList.remove(
                'admin-scanning',
                'admin-success',
                'admin-error'
            );

            laser.classList.add('hidden', 'opacity-0');

            success.classList.add('hidden');
            denied.classList.add('hidden');

            input.value = '';

            setTimeout(() => {
                input.focus();
            }, 150);

            if (window.lucide) {
                lucide.createIcons();
            }
        }


        function closeAdminRfidEditModal() {

            const modal =
                document.getElementById('modal-admin-rfid-edit');

            const input =
                document.getElementById('admin-rfid-edit-input');

            const laser =
                document.getElementById('admin-rfid-edit-laser');

            const success =
                document.getElementById('admin-rfid-edit-success');

            const denied =
                document.getElementById('admin-rfid-edit-denied');

            modal.classList.add('hidden');

            input.value = '';

            laser.classList.add('hidden', 'opacity-0');

            success.classList.add('hidden');
            denied.classList.add('hidden');
        }


        const adminRfidEditInput =
            document.getElementById('admin-rfid-edit-input');

        if (adminRfidEditInput) {

            adminRfidEditInput.addEventListener(
                'keydown',
                async function (event) {

                    if (event.key !== 'Enter') {
                        return;
                    }

                    event.preventDefault();

                    const rfidUid =
                        adminRfidEditInput.value.trim();

                    if (!rfidUid) {
                        return;
                    }

                    await verifyAdminRfidForEdit(rfidUid);
                }
            );
        }


        async function verifyAdminRfidForEdit(rfidUid) {

            const status =
                document.getElementById('admin-rfid-edit-status');

            const scanner =
                document.getElementById('admin-rfid-edit-scanner');

            const laser =
                document.getElementById('admin-rfid-edit-laser');

            const success =
                document.getElementById('admin-rfid-edit-success');

            const denied =
                document.getElementById('admin-rfid-edit-denied');

            const input =
                document.getElementById('admin-rfid-edit-input');

            status.textContent =
                'Processing...';

            status.className =
                'text-center font-heading text-xl font-semibold text-[#F59B45] mb-6';

            scanner.classList.add('admin-scanning');

            scanner.classList.remove(
                'admin-success',
                'admin-error'
            );

            success.classList.add('hidden');
            denied.classList.add('hidden');

            laser.classList.remove('hidden', 'opacity-0');

            try {

                const body =
                    new URLSearchParams();

                body.append(
                    'employee_id',
                    currentProfileEmployeeId
                );

                body.append(
                    'rfid_uid',
                    rfidUid
                );

                const response =
                    await fetch(
                        'api/employees/verify-admin-rfid.php',
                        {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type':
                                    'application/x-www-form-urlencoded',
                                'Accept':
                                    'application/json'
                            },
                            body: body.toString()
                        }
                    );

                const result =
                    await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(
                        result.message ||
                        'Admin RFID verification failed.'
                    );
                }

                laser.classList.add('hidden', 'opacity-0');

                scanner.classList.remove('admin-scanning');
                scanner.classList.add('admin-success');

                success.classList.remove('hidden');

                status.textContent =
                    'RFID Verified';

                status.className =
                    'text-center font-heading text-xl font-semibold text-green-500 mb-6';

                setTimeout(() => {

                    closeAdminRfidEditModal();

                    window.open(
                        'employee-edit.html?id=' +
                        encodeURIComponent(
                            currentProfileEmployeeId
                        ),
                        '_blank'
                    );

                }, 700);

            } catch (error) {

                console.error(
                    'Admin RFID verification failed:',
                    error
                );

                laser.classList.add('hidden', 'opacity-0');

                scanner.classList.remove('admin-scanning');
                scanner.classList.add('admin-error');

                success.classList.add('hidden');
                denied.classList.remove('hidden');

                status.textContent =
                    'RFID Denied';

                status.className =
                    'text-center font-heading text-xl font-semibold text-red-500 mb-6';

                input.value = '';

                setTimeout(() => {

                    denied.classList.add('hidden');

                    scanner.classList.remove('admin-error');

                    status.textContent =
                        'Please Tap Here';

                    status.className =
                        'text-center font-heading text-xl font-semibold text-primary mb-6';

                    input.focus();

                }, 1400);
            }
        }

        function closeEmployeeProfile() {
            document.getElementById('modal-employee-profile').classList.add('hidden');
        }

        // ---- Attendance Correction: row click -> review modal ----
        let currentCorrectionRow = null;

        let currentCorrection = null;

        async function loadAttendanceCorrectionsFromDatabase() {

            const tableBody =
                document.getElementById(
                    'attendance-correction-table-body'
                );

            if (!tableBody) {
                return;
            }

            try {

                const response = await fetch(
                    'api/attendance/corrections.php',
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
                        'Unable to load attendance corrections.'
                    );
                }

                tableBody.innerHTML = '';

                const records =
                    Array.isArray(result.data)
                        ? result.data
                        : [];

                if (!records.length) {

                    tableBody.innerHTML = `
                <tr>
                    <td colspan="5"
                        class="px-5 py-8 text-center text-gray-400">
                        No attendance correction requests found.
                    </td>
                </tr>
            `;

                    return;
                }

                records.forEach(record => {

                    const row =
                        document.createElement('tr');

                    row.className =
                        record.status === 'PENDING'
                            ? 'group cursor-pointer hover:bg-background transition-all-300'
                            : 'cursor-default';

                    row.dataset.id = record.id;
                    row.dataset.employeeId =
                        record.employeeId;
                    row.dataset.employee =
                        record.employee;
                    row.dataset.date =
                        record.date;
                    row.dataset.clockin =
                        record.clockin || '';
                    row.dataset.clockout =
                        record.clockout || '';
                    row.dataset.requestedClockin =
                        record.requestedClockin || '';
                    row.dataset.requestedClockout =
                        record.requestedClockout || '';
                    row.dataset.reason =
                        record.reason || '';
                    row.dataset.attachment =
                        record.attachment || '';
                    row.dataset.status =
                        record.status;

                    if (
                        record.status === 'PENDING'
                    ) {
                        row.onclick = function () {
                            openCorrectionModal(this);
                        };
                    }

                    const statusClass =
                        record.status === 'APPROVED'
                            ? 'bg-emerald-100 text-emerald-700'
                            : record.status === 'REJECTED'
                                ? 'bg-red-100 text-red-600'
                                : 'bg-amber-100 text-amber-700';

                    const dateText =
                        new Date(
                            record.date + 'T00:00:00'
                        ).toLocaleDateString(
                            'en-US',
                            {
                                month: 'short',
                                day: 'numeric',
                                year: 'numeric'
                            }
                        );

                    const reason =
                        record.reason || 'â€”';

                    row.innerHTML = `
                <td class="px-5 py-4 font-medium text-primary">
                    AT-${String(record.id).padStart(3, '0')}
                </td>

                <td class="px-5 py-4 font-semibold text-primary">
                    ${escapeHtml(record.employee)}
                </td>

                <td class="px-5 py-4 text-gray-600">
                    ${escapeHtml(dateText)}
                </td>

                <td class="px-5 py-4 text-gray-600">
                    ${escapeHtml(reason)}
                </td>

                <td class="px-5 py-4">
                    <div class="flex items-center justify-between gap-2">
                        <span class="inline-flex items-center ${statusClass} text-xs font-semibold px-3 py-1 rounded-full">
                            ${escapeHtml(record.status)}
                        </span>

                        ${record.status === 'PENDING'
                            ? `
                                    <span class="hidden md:inline-flex items-center gap-1 text-xs font-medium text-accent opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                        <i class="fa-solid fa-eye"></i>
                                        Review
                                    </span>
                                `
                            : ''
                        }
                    </div>
                </td>
            `;

                    tableBody.appendChild(row);
                });

            } catch (error) {

                console.error(
                    'Attendance correction loading error:',
                    error
                );

                tableBody.innerHTML = `
            <tr>
                <td colspan="5"
                    class="px-5 py-8 text-center text-red-500">
                    Unable to load attendance corrections.
                </td>
            </tr>
        `;
            }
        }

        function openCorrectionModal(row) {

            currentCorrection = {
                id: Number(row.dataset.id),
                employee: row.dataset.employee || '',
                date: row.dataset.date || '',
                clockin: row.dataset.clockin || '',
                clockout: row.dataset.clockout || '',
                requestedClockin:
                    row.dataset.requestedClockin || '',
                requestedClockout:
                    row.dataset.requestedClockout || '',
                reason: row.dataset.reason || '',
                attachment:
                    row.dataset.attachment || '',
                status: row.dataset.status || ''
            };

            document.getElementById(
                'correction-employee'
            ).textContent =
                currentCorrection.employee;

            document.getElementById(
                'correction-date'
            ).textContent =
                new Date(
                    currentCorrection.date + 'T00:00:00'
                ).toLocaleDateString(
                    'en-US',
                    {
                        month: 'short',
                        day: 'numeric',
                        year: 'numeric'
                    }
                );

            document.getElementById(
                'correction-current-clockin'
            ).textContent =
                currentCorrection.clockin
                    ? formatCorrectionDateTime(
                        currentCorrection.clockin
                    )
                    : 'â€”';

            document.getElementById(
                'correction-current-clockout'
            ).textContent =
                currentCorrection.clockout
                    ? formatCorrectionDateTime(
                        currentCorrection.clockout
                    )
                    : 'â€”';

            let requestedText = 'â€”';

            if (
                currentCorrection.requestedClockin
            ) {
                requestedText =
                    `Clock In: ${formatCorrectionDateTime(
                        currentCorrection.requestedClockin
                    )}`;
            }

            if (
                currentCorrection.requestedClockout
            ) {
                requestedText =
                    requestedText === 'â€”'
                        ? ''
                        : requestedText + '<br>';

                requestedText +=
                    `Clock Out: ${formatCorrectionDateTime(
                        currentCorrection.requestedClockout
                    )}`;
            }

            document.getElementById(
                'correction-requested'
            ).innerHTML =
                requestedText;

            document.getElementById(
                'correction-reason'
            ).textContent =
                currentCorrection.reason || 'â€”';

            document.getElementById(
                'correction-attachment'
            ).textContent =
                currentCorrection.attachment ||
                'No attachment';

            const modal =
                document.getElementById(
                    'modal-correction-review'
                );

            modal.classList.remove('hidden');

            if (window.lucide) {
                lucide.createIcons();
            }
        }

        function formatCorrectionDateTime(value) {

            const date =
                new Date(
                    String(value).replace(
                        ' ',
                        'T'
                    )
                );

            if (Number.isNaN(date.getTime())) {
                return value;
            }

            return date.toLocaleString(
                'en-US',
                {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric',
                    hour: 'numeric',
                    minute: '2-digit'
                }
            );
        }

        function closeCorrectionModal() {

            document.getElementById(
                'modal-correction-review'
            ).classList.add('hidden');

            currentCorrection = null;
        }

        async function approveCorrection() {
            await processAttendanceCorrection(
                'approve'
            );
        }

        async function rejectCorrection() {
            await processAttendanceCorrection(
                'reject'
            );
        }

        async function processAttendanceCorrection(
            action
        ) {

            if (
                !currentCorrection ||
                !currentCorrection.id
            ) {
                return;
            }

            try {

                const response = await fetch(
                    'api/attendance/corrections.php',
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
                            action,
                            correction_id:
                                currentCorrection.id
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
                        'Unable to process attendance correction.'
                    );
                }

                closeCorrectionModal();

                await loadAttendanceCorrectionsFromDatabase();

                if (
                    typeof showToast ===
                    'function'
                ) {
                    showToast(
                        result.message,
                        'success'
                    );
                }

            } catch (error) {

                if (
                    typeof showToast ===
                    'function'
                ) {
                    showToast(
                        error.message ||
                        'Unable to process attendance correction.',
                        'error'
                    );
                } else {
                    alert(
                        error.message ||
                        'Unable to process attendance correction.'
                    );
                }
            }
        }

        // ---- Master Roster: Week / Month view + period navigation ----
        // The same < / > buttons navigate by WEEK while Week view is active,
        // and by MONTH while Month view is active. Navigation also works across years.
        let scheduleMonthDate = new Date(2026, 7, 1); // August 2026
        let scheduleWeekStart = new Date(2026, 6, 26); // July 26, 2026 (matches the existing roster)
        const scheduleCalendarData = {};
        const scheduleWeekData = {};
        let scheduleWeekCaptured = false;

        function normalizeScheduleDate(dateValue) {
            if (dateValue instanceof Date) {
                return new Date(dateValue.getFullYear(), dateValue.getMonth(), dateValue.getDate());
            }
            const raw = String(dateValue || '').replace(/\//g, '-');
            const d = new Date(raw + (raw.length <= 10 ? 'T12:00:00' : ''));
            return isNaN(d) ? null : new Date(d.getFullYear(), d.getMonth(), d.getDate());
        }

        function scheduleIso(dateValue) {
            const d = normalizeScheduleDate(dateValue);
            if (!d) return null;
            return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
        }

        function formatScheduleRange(startDate, numberOfDays = 7) {
            const start = normalizeScheduleDate(startDate);
            const end = new Date(start);
            end.setDate(start.getDate() + numberOfDays - 1);
            const sameYear = start.getFullYear() === end.getFullYear();
            const startText = start.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            const endText = end.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            return `${startText} - ${endText}`;
        }

        function setScheduleMonthTitle(dateValue) {
            const title = document.getElementById('scheduleHeaderTitle');
            const d = normalizeScheduleDate(dateValue);
            if (!d) return;
            scheduleMonthDate = new Date(d.getFullYear(), d.getMonth(), 1);
            if (title) {
                title.textContent = scheduleMonthDate.toLocaleDateString('en-US', {
                    month: 'long',
                    year: 'numeric'
                });
            }
        }

        function setScheduleWeekTitle(startDate) {
            const title = document.getElementById('scheduleHeaderTitle');
            scheduleWeekStart = normalizeScheduleDate(startDate) || scheduleWeekStart;
            if (title) title.textContent = formatScheduleRange(scheduleWeekStart, 7);
        }

        function captureExistingMonthSchedules() {
            // Month View is now database-driven.
            // Clear any old hard-coded/sample schedule data.
            Object.keys(scheduleCalendarData).forEach(key => {
                delete scheduleCalendarData[key];
            });
        }

        function captureExistingWeekSchedules() {
            if (scheduleWeekCaptured) return;
            const table = document.querySelector('#weekRosterView table');
            const headerRow = table?.querySelector('thead tr');
            if (!table || !headerRow) return;

            const headers = Array.from(headerRow.querySelectorAll('th[data-date]'));
            const dates = headers.map(h => h.getAttribute('data-date')).filter(Boolean);
            if (dates.length === 7) scheduleWeekStart = normalizeScheduleDate(dates[0]);

            table.querySelectorAll('tbody tr[data-emp]').forEach(row => {
                const emp = row.getAttribute('data-emp');
                if (!emp) return;
                if (!scheduleWeekData[emp]) scheduleWeekData[emp] = {};

                const cells = Array.from(row.children).slice(1);
                let dateIndex = 0;
                cells.forEach(cell => {
                    const colspan = Number(cell.getAttribute('colspan') || 1);
                    const html = cell.innerHTML.trim();
                    for (let i = 0; i < colspan && dateIndex < dates.length; i++, dateIndex++) {
                        scheduleWeekData[emp][dates[dateIndex]] = {
                            html: html,
                            className: cell.className
                        };
                    }
                });
            });
            scheduleWeekCaptured = true;
        }

        function renderScheduleWeek(startDate) {
            const weekView = document.getElementById('weekRosterView');
            if (!weekView) return;
            captureExistingWeekSchedules();

            const table = weekView.querySelector('table');
            const headerRow = table?.querySelector('thead tr');
            const body = table?.querySelector('tbody');
            if (!headerRow || !body) return;

            scheduleWeekStart = normalizeScheduleDate(startDate) || scheduleWeekStart;
            const dates = [];
            headerRow.querySelectorAll('th[data-date]').forEach((header, index) => {
                const d = new Date(scheduleWeekStart);
                d.setDate(scheduleWeekStart.getDate() + index);
                const iso = scheduleIso(d);
                dates.push(iso);
                header.setAttribute('data-date', iso);
                header.setAttribute('title', 'Click to view day details');
                header.classList.add('cursor-pointer', 'hover:bg-background/60', 'transition-all-300');
                header.textContent = d.toLocaleDateString('en-US', {
                    weekday: 'short',
                    day: 'numeric'
                });
                if (index >= 5) header.classList.add('bg-background');
                else header.classList.remove('bg-background');
            });

            body.querySelectorAll('tr[data-emp]').forEach(row => {
                const emp = row.getAttribute('data-emp');
                const cells = Array.from(row.children);
                const employeeCell = cells[0];
                const existingEmployeeHTML = employeeCell ? employeeCell.outerHTML : '';
                const data = scheduleWeekData[emp] || {};
                row.innerHTML = existingEmployeeHTML;

                dates.forEach((iso, index) => {
                    const td = document.createElement('td');
                    td.className = index >= 5
                        ? 'p-2 bg-background border-r border-border cursor-pointer hover:bg-background/60 transition-all-300'
                        : 'p-2 border-r border-border cursor-pointer hover:bg-background/60 transition-all-300';
                    td.setAttribute('data-date', iso);
                    td.setAttribute('data-employee', emp);
                    td.setAttribute('title', 'Click to view day details');

                    const item = data[iso];
                    if (item) {
                        td.innerHTML = item.html;
                        // Preserve the original special styling for Off / Leave cells.
                        if (item.className && item.className.includes('bg-background/50')) {
                            td.className = item.className;
                            td.classList.add('cursor-pointer', 'hover:bg-background/60', 'transition-all-300');
                        }
                    }
                    row.appendChild(td);
                });
            });

            setScheduleWeekTitle(scheduleWeekStart);
            filterScheduleByRole();
        }

        function renderScheduleMonth(dateValue) {
            const monthView = document.getElementById('monthRosterView');
            if (!monthView) return;

            const firstDay = new Date(dateValue.getFullYear(), dateValue.getMonth(), 1);
            const mondayOffset = (firstDay.getDay() + 6) % 7;
            const gridStart = new Date(firstDay);
            gridStart.setDate(firstDay.getDate() - mondayOffset);

            const grid = monthView.querySelector('.grid.grid-cols-7:last-child');
            if (!grid) return;
            grid.innerHTML = '';

            for (let i = 0; i < 42; i++) {
                const d = new Date(gridStart);
                d.setDate(gridStart.getDate() + i);
                const iso = scheduleIso(d);
                const inCurrentMonth = d.getMonth() === dateValue.getMonth();
                const dayEntries = scheduleCalendarData[iso] || [];
                const weekend = d.getDay() === 0 || d.getDay() === 6;
                const cell = document.createElement('div');

                cell.className = `border-r border-b border-border p-2 min-h-[110px] ${!inCurrentMonth || weekend ? 'bg-background/40 ' : ''}hover:bg-background/60 transition-all-300 cursor-pointer`;
                cell.setAttribute('data-date', iso);

                const number = document.createElement('span');
                number.className = `text-xs font-semibold ${!inCurrentMonth ? 'text-gray-300' : 'text-primary'}`;
                number.textContent = d.getDate();
                cell.appendChild(number);

                const container = document.createElement('div');
                container.className = 'mt-1 space-y-1';
                dayEntries.forEach(item => {
                    const entry = document.createElement('div');
                    entry.className = item.className || 'text-[10px] px-1.5 py-1 rounded truncate';
                    entry.title = item.title || '';
                    entry.textContent = item.text || '';
                    container.appendChild(entry);
                });
                cell.appendChild(container);
                grid.appendChild(cell);
            }

            filterScheduleByRole();
        }

        function getScheduleWeekEntries(isoDate) {
            const entries = [];

            Object.entries(scheduleWeekData).forEach(([employee, dateMap]) => {
                const item = dateMap?.[isoDate];
                if (!item || !item.html) return;

                const helper = document.createElement('div');
                helper.innerHTML = item.html;

                const rawText = (helper.textContent || '').replace(/\s+/g, ' ').trim();
                if (!rawText) return;

                const timeText = (helper.querySelector('.font-bold')?.textContent || '').replace(/\s+/g, ' ').trim();
                const detailText = (helper.querySelector('.truncate')?.textContent || '').replace(/\s+/g, ' ').trim();
                const isOff = /^off$/i.test(rawText);

                let details = '';
                if (isOff) {
                    details = 'Off';
                } else {
                    details = [timeText, detailText].filter(Boolean).join(' Â· ') || rawText;
                }

                entries.push({
                    employee,
                    details,
                    title: `${employee} Â· ${details}`,
                    text: `${employee} Â· ${details}`,
                    isOff
                });
            });

            return entries;
        }

        function openScheduleDayModal(isoDate, source = 'month') {
            const modal = document.getElementById('modal-schedule-day');
            const title = document.getElementById('schedule-day-modal-title');
            const summary = document.getElementById('schedule-day-modal-summary');
            const content = document.getElementById('schedule-day-modal-content');
            if (!modal || !title || !summary || !content) return;

            const date = normalizeScheduleDate(isoDate);
            if (!date) return;

            const entries = source === 'week'
                ? getScheduleWeekEntries(isoDate)
                : (scheduleCalendarData[isoDate] || []);
            const formattedDate = date.toLocaleDateString('en-US', {
                weekday: 'long',
                month: 'long',
                day: 'numeric',
                year: 'numeric'
            });

            title.textContent = formattedDate;
            summary.textContent = entries.length
                ? `${entries.length} schedule${entries.length === 1 ? '' : 's'} on this day`
                : 'No schedule or leave recorded for this day.';

            if (!entries.length) {
                content.innerHTML = `
                    <div class="border border-dashed border-gray-300 rounded-xl p-6 text-center">
                        <i class="fa-regular fa-calendar-xmark text-3xl text-gray-300"></i>
                        <p class="text-sm font-semibold text-gray-500 mt-3">No schedule available</p>
                        <p class="text-xs text-gray-400 mt-1">You can create a schedule for this date using Create Schedule.</p>
                    </div>`;
            } else {
                content.innerHTML = entries.map((item, index) => {
                    const raw = String(item.title || item.text || '').trim();
                    const parts = raw.split(' Â· ');
                    const employee = item.employee || parts[0] || 'Employee';
                    const details = item.details || parts.slice(1).join(' Â· ') || raw;
                    const isOff = Boolean(item.isOff) || /^off$/i.test(details.trim());
                    const isLeave = !isOff && /leave/i.test(details);
                    const icon = isOff ? 'fa-mug-hot' : (isLeave ? 'fa-plane-departure' : 'fa-calendar-check');
                    const badge = isOff ? 'Off' : (isLeave ? 'Leave' : 'Scheduled');
                    const badgeClass = isOff
                        ? 'bg-gray-100 text-gray-600'
                        : (isLeave ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-700');

                    return `
                        <div class="border border-border rounded-xl p-4 bg-white shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-start gap-3 min-w-0">
                                    <span class="w-9 h-9 rounded-xl ${isLeave ? 'bg-red-50 text-red-600' : 'bg-blue-50 text-primary'} flex items-center justify-center shrink-0">
                                        <i class="fa-solid ${icon}"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-primary text-sm truncate">${escapeScheduleDayHtml(employee)}</p>
                                        <p class="text-xs text-gray-500 mt-0.5 break-words">${escapeScheduleDayHtml(details)}</p>
                                    </div>
                                </div>
                                <span class="text-[10px] font-semibold px-2 py-1 rounded-full shrink-0 ${badgeClass}">${badge}</span>
                            </div>
                        </div>`;
                }).join('');
            }

            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeScheduleDayModal() {
            document.getElementById('modal-schedule-day')?.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        function escapeScheduleDayHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, char => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
            })[char]);
        }

        async function loadScheduleMonthFromDatabase(dateValue) {

            const monthDate =
                normalizeScheduleDate(dateValue) || scheduleMonthDate;

            if (!monthDate) {
                return false;
            }

            const startDate =
                new Date(
                    monthDate.getFullYear(),
                    monthDate.getMonth(),
                    1
                );

            const endDate =
                new Date(
                    monthDate.getFullYear(),
                    monthDate.getMonth() + 1,
                    0
                );

            const startIso =
                scheduleIso(startDate);

            const endIso =
                scheduleIso(endDate);


            try {

                const response = await fetch(
                    `api/schedules/admin.php?start_date=${encodeURIComponent(startIso)}&end_date=${encodeURIComponent(endIso)}`,
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
                        'Unable to load schedules.'
                    );
                }


                /*
                 * Remove the old Month View data.
                 */
                Object.keys(scheduleCalendarData).forEach(key => {
                    delete scheduleCalendarData[key];
                });


                /*
                 * Rebuild Month View data from the database.
                 */
                const schedules =
                    Array.isArray(result.data)
                        ? result.data
                        : [];


                schedules.forEach(schedule => {

                    const isoDate =
                        String(
                            schedule.schedule_date || ''
                        ).trim();


                    if (!isoDate) {
                        return;
                    }


                    const employeeName =
                        String(
                            schedule.employee_name || ''
                        ).trim();


                    const position =
                        String(
                            schedule.position_name ||
                            'Employee'
                        ).trim();


                    /*
                     * Convert database TIME values:
                     * 08:00:00 -> 8:00 AM
                     * 12:00:00 -> 12:00 PM
                     */
                    const startTime =
                        formatDatabaseScheduleTime(
                            schedule.start_time
                        );

                    const endTime =
                        formatDatabaseScheduleTime(
                            schedule.end_time
                        );


                    let label =
                        employeeName;


                    if (startTime && endTime) {

                        label +=
                            ` Â· ${startTime} - ${endTime}`;

                    }


                    const details = [
                        position,
                        schedule.work_type || '',
                        schedule.remarks || ''
                    ]
                        .filter(Boolean)
                        .join(' Â· ');


                    const colors =
                        scheduleColorForWorkType(
                            schedule.work_type || ''
                        );


                    const item = {

                        className:
                            `${colors.bg} ${colors.text} text-[10px] px-1.5 py-1 rounded truncate`,

                        title:
                            details
                                ? `${label} Â· ${details}`
                                : label,

                        text:
                            label,

                        employee:
                            employeeName,

                        details:
                            details,

                        shift:
                            schedule.shift_name || '',

                        workType:
                            schedule.work_type || '',

                        remarks:
                            schedule.remarks || '',

                        employeeId:
                            schedule.employee_id || '',

                        scheduleId:
                            schedule.id || ''

                    };


                    if (
                        !scheduleCalendarData[isoDate]
                    ) {

                        scheduleCalendarData[isoDate] = [];

                    }


                    scheduleCalendarData[isoDate].push(
                        item
                    );


                    /*
                     * Keep the role filter synchronized
                     * with database employees.
                     */
                    let filterRole = 'Office Staff';

                    const lowerPosition =
                        position.toLowerCase();


                    if (
                        lowerPosition.includes('tour') ||
                        lowerPosition.includes('guide')
                    ) {

                        filterRole = 'Tour Guides';

                    } else if (
                        lowerPosition.includes('driver')
                    ) {

                        filterRole = 'Drivers';

                    }


                    SCHEDULE_EMPLOYEE_ROLES[
                        employeeName
                    ] = filterRole;

                });


                return true;


            } catch (error) {

                console.error(
                    'Month View database loading error:',
                    error
                );


                Object.keys(scheduleCalendarData).forEach(key => {
                    delete scheduleCalendarData[key];
                });


                return false;
            }
        }


        /*
         * Convert a database TIME value into
         * a readable 12-hour format.
         *
         * Example:
         * 08:00:00 -> 8:00 AM
         * 13:00:00 -> 1:00 PM
         */
        function formatDatabaseScheduleTime(timeValue) {

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

                return '';
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

        async function showScheduleMonthView(dateValue) {

            const weekView =
                document.getElementById(
                    'weekRosterView'
                );

            const monthView =
                document.getElementById(
                    'monthRosterView'
                );

            const label =
                document.getElementById(
                    'scheduleViewToggleLabel'
                );

            const icon =
                document.getElementById(
                    'scheduleViewToggleIcon'
                );


            if (
                !weekView ||
                !monthView
            ) {

                return false;

            }


            const d =
                normalizeScheduleDate(
                    dateValue
                ) || scheduleMonthDate;


            setScheduleMonthTitle(d);


            /*
             * Load this month's schedules directly
             * from the database before rendering.
             */
            await loadScheduleMonthFromDatabase(
                scheduleMonthDate
            );


            renderScheduleMonth(
                scheduleMonthDate
            );


            monthView.classList.remove('hidden');

            weekView.classList.add('hidden');


            if (label) {
                label.textContent = 'Week';
            }


            if (icon) {

                icon.classList.remove(
                    'fa-calendar-days'
                );

                icon.classList.add(
                    'fa-table-cells-large'
                );

            }


            return true;
        }

        function showScheduleWeekView(startDate) {
            const weekView = document.getElementById('weekRosterView');
            const monthView = document.getElementById('monthRosterView');
            const label = document.getElementById('scheduleViewToggleLabel');
            const icon = document.getElementById('scheduleViewToggleIcon');
            if (!weekView || !monthView) return false;

            const d = normalizeScheduleDate(startDate) || scheduleWeekStart;
            renderScheduleWeek(d);
            weekView.classList.remove('hidden');
            monthView.classList.add('hidden');
            if (label) label.textContent = 'Month';
            if (icon) {
                icon.classList.remove('fa-table-cells-large');
                icon.classList.add('fa-calendar-days');
            }
            return true;
        }

        function toggleScheduleView() {
            const weekView = document.getElementById('weekRosterView');
            const monthView = document.getElementById('monthRosterView');
            const isShowingMonth = !monthView.classList.contains('hidden');

            if (isShowingMonth) {
                showScheduleWeekView(scheduleWeekStart);
            } else {
                showScheduleMonthView(scheduleMonthDate);
            }
        }

        function navigateSchedulePeriod(direction) {
            const amount = Number(direction) || 0;
            const weekView = document.getElementById('weekRosterView');
            const monthView = document.getElementById('monthRosterView');
            const isShowingMonth = monthView && !monthView.classList.contains('hidden');

            if (isShowingMonth) {
                const nextMonth = new Date(scheduleMonthDate.getFullYear(), scheduleMonthDate.getMonth(), 1);
                nextMonth.setMonth(nextMonth.getMonth() + amount);
                scheduleMonthDate = nextMonth;
                showScheduleMonthView(scheduleMonthDate);
            } else if (weekView && !weekView.classList.contains('hidden')) {
                const nextWeek = new Date(scheduleWeekStart);
                nextWeek.setDate(nextWeek.getDate() + (amount * 7));
                scheduleWeekStart = nextWeek;
                showScheduleWeekView(scheduleWeekStart);
            }
        }

        // Backward-compatible function name for any existing calls.
        function navigateScheduleMonth(direction) {
            navigateSchedulePeriod(direction);
        }

        // ---- Master Roster: role filter ----
        // Maps each employee to the role bucket used by the "All Roles" filter.
        const SCHEDULE_EMPLOYEE_ROLES = {};

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
                const emp = label.split('Â·')[0].trim();
                const matches = role === 'All Roles' || SCHEDULE_EMPLOYEE_ROLES[emp] === role;
                entry.classList.toggle('hidden', !matches);
            });
        }
        // ============================================================
        // MASTER ROSTER: RE-ASSIGN SCHEDULE
        // ============================================================

        let reassignSchedules = [];
        let reassignSelectedScheduleId = null;


        // Open modal
        async function openReassignScheduleModal(scheduleId = null) {

            const modal = document.getElementById('modal-reassign-schedule');

            if (!modal) {
                return;
            }

            modal.classList.remove('hidden');

            const scheduleSelect =
                document.getElementById('reassign-schedule-select');

            const employeeSelect =
                document.getElementById('reassign-employee-select');

            scheduleSelect.innerHTML =
                '<option value="">Loading schedules...</option>';

            employeeSelect.innerHTML =
                '<option value="">Select a schedule first</option>';

            employeeSelect.disabled = true;

            reassignSelectedScheduleId = scheduleId;

            try {

                /*
                 * Load schedules for the currently visible
                 * Master Roster period.
                 */

                let startDate;
                let endDate;

                const monthView =
                    document.getElementById('monthRosterView');

                const weekView =
                    document.getElementById('weekRosterView');

                const showingMonth =
                    monthView &&
                    !monthView.classList.contains('hidden');


                if (showingMonth) {

                    const firstDay =
                        new Date(
                            scheduleMonthDate.getFullYear(),
                            scheduleMonthDate.getMonth(),
                            1
                        );

                    const lastDay =
                        new Date(
                            scheduleMonthDate.getFullYear(),
                            scheduleMonthDate.getMonth() + 1,
                            0
                        );

                    startDate = scheduleIso(firstDay);
                    endDate = scheduleIso(lastDay);

                } else {

                    const weekStart =
                        normalizeScheduleDate(scheduleWeekStart);

                    const weekEnd =
                        new Date(weekStart);

                    weekEnd.setDate(
                        weekStart.getDate() + 6
                    );

                    startDate = scheduleIso(weekStart);
                    endDate = scheduleIso(weekEnd);
                }


                const response = await fetch(
                    `api/schedules/admin.php?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`,
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
                        'Unable to load schedules.'
                    );
                }


                reassignSchedules =
                    Array.isArray(result.data)
                        ? result.data
                        : [];


                scheduleSelect.innerHTML =
                    '<option value="">Select schedule</option>';


                if (!reassignSchedules.length) {

                    scheduleSelect.innerHTML =
                        '<option value="">No schedules found</option>';

                    return;
                }


                reassignSchedules.forEach(schedule => {

                    const option =
                        document.createElement('option');

                    option.value = schedule.id;

                    const date =
                        new Date(
                            `${schedule.schedule_date}T00:00:00`
                        );

                    const formattedDate =
                        date.toLocaleDateString(
                            'en-US',
                            {
                                month: 'short',
                                day: 'numeric',
                                year: 'numeric'
                            }
                        );


                    option.textContent =
                        `${schedule.employee_name} â€” ${formattedDate} â€” ${schedule.shift_name}`;

                    scheduleSelect.appendChild(option);

                });


                /*
                 * When Critical Alerts later passes a schedule ID,
                 * automatically select that schedule.
                 */

                if (scheduleId) {

                    const matchingOption =
                        Array.from(
                            scheduleSelect.options
                        ).find(
                            option =>
                                String(option.value) ===
                                String(scheduleId)
                        );

                    if (matchingOption) {

                        scheduleSelect.value =
                            matchingOption.value;

                        await loadReassignReplacementEmployees();
                    }
                }


            } catch (error) {

                console.error(
                    'Re-assign schedule loading error:',
                    error
                );

                scheduleSelect.innerHTML =
                    '<option value="">Unable to load schedules</option>';

            }

        }


        // Close modal
        function closeReassignScheduleModal() {

            const modal =
                document.getElementById(
                    'modal-reassign-schedule'
                );

            if (modal) {
                modal.classList.add('hidden');
            }

        }


        // Load replacement employees
        async function loadReassignReplacementEmployees() {

            const scheduleSelect =
                document.getElementById(
                    'reassign-schedule-select'
                );

            const employeeSelect =
                document.getElementById(
                    'reassign-employee-select'
                );


            const scheduleId =
                scheduleSelect.value;


            employeeSelect.innerHTML =
                '<option value="">Loading employees...</option>';

            employeeSelect.disabled = true;


            if (!scheduleId) {

                employeeSelect.innerHTML =
                    '<option value="">Select a schedule first</option>';

                return;
            }


            const schedule =
                reassignSchedules.find(
                    item =>
                        String(item.id) ===
                        String(scheduleId)
                );


            if (!schedule) {

                employeeSelect.innerHTML =
                    '<option value="">Schedule not found</option>';

                return;
            }


            reassignSelectedScheduleId =
                schedule.id;


            try {

                const response = await fetch(
                    `api/schedules/admin.php?action=employees&date=${encodeURIComponent(schedule.schedule_date)}`,
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


                if (!response.ok || !result.success) {

                    throw new Error(
                        result.message ||
                        'Unable to load employees.'
                    );
                }


                const employees =
                    Array.isArray(result.data)
                        ? result.data
                        : [];


                employeeSelect.innerHTML =
                    '<option value="">Select replacement employee</option>';


                employees.forEach(employee => {

                    /*
                     * Do not show the person who is already
                     * assigned to this schedule.
                     */

                    if (
                        String(employee.id) ===
                        String(schedule.employee_id)
                    ) {
                        return;
                    }


                    const option =
                        document.createElement('option');

                    option.value =
                        employee.id;

                    option.textContent =
                        `${employee.position || 'Employee'} â€” ${employee.name} (${employee.status})`;

                    employeeSelect.appendChild(option);

                });


                employeeSelect.disabled = false;


                if (
                    employeeSelect.options.length === 1
                ) {

                    employeeSelect.innerHTML =
                        '<option value="">No replacement employee available</option>';

                    employeeSelect.disabled = true;
                }


            } catch (error) {

                console.error(
                    'Replacement employee loading error:',
                    error
                );

                employeeSelect.innerHTML =
                    '<option value="">Unable to load employees</option>';

                employeeSelect.disabled = true;

            }

        }


        // Submit re-assignment
        async function handleReassignScheduleSubmit(event) {

            event.preventDefault();


            const scheduleSelect =
                document.getElementById(
                    'reassign-schedule-select'
                );

            const employeeSelect =
                document.getElementById(
                    'reassign-employee-select'
                );


            const scheduleId =
                scheduleSelect.value;

            const replacementEmployeeId =
                employeeSelect.value;


            if (
                !scheduleId ||
                !replacementEmployeeId
            ) {

                showScheduleToast(
                    'Please select a schedule and replacement employee.',
                    true
                );

                return;
            }


            /*
             * Send the request to the existing
             * reassign API.
             */

            try {

                const response =
                    await fetch(
                        'api/schedules/admin.php',
                        {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                action: 'reassign',
                                schedule_id: Number(scheduleId),
                                replacement_employee_id:
                                    replacementEmployeeId
                            })
                        }
                    );


                const result =
                    await response.json();


                if (!response.ok || !result.success) {

                    throw new Error(
                        result.message ||
                        'Unable to re-assign schedule.'
                    );
                }


                closeReassignScheduleModal();


                showScheduleToast(
                    result.message ||
                    'Schedule re-assigned successfully.',
                    false
                );


                /*
                 * Refresh the visible schedule.
                 */

                if (
                    document.getElementById('monthRosterView') &&
                    !document
                        .getElementById('monthRosterView')
                        .classList.contains('hidden')
                ) {

                    showScheduleMonthView(
                        scheduleMonthDate
                    );

                } else {

                    showScheduleWeekView(
                        scheduleWeekStart
                    );
                }


            } catch (error) {

                console.error(
                    'Re-assign schedule error:',
                    error
                );

                showScheduleToast(
                    error.message ||
                    'Unable to re-assign schedule.',
                    true
                );

            }

        }

        // ---- Master Roster: Create Schedule modal ----
        async function openCreateScheduleModal() {

            const modal =
                document.getElementById(
                    'modal-create-schedule'
                );

            modal.classList.remove('hidden');

            updateScheduleShiftTimes();

            await loadCreateScheduleEmployees();
        }

        async function loadCreateScheduleEmployees() {

            const employeeSelect =
                document.getElementById(
                    'schedule-form-employee'
                );

            const dateInput =
                document.getElementById(
                    'schedule-form-date-native'
                );


            if (!employeeSelect) {
                return;
            }


            employeeSelect.innerHTML =
                '<option value="">Loading employees...</option>';

            employeeSelect.disabled = true;


            /*
             * Use the selected schedule date so employees
             * with approved leave covering that date are excluded.
             */
            let scheduleDate =
                dateInput?.value || '';


            if (!scheduleDate) {

                const dateText =
                    document.getElementById(
                        'schedule-form-date'
                    )?.value || '';

                const parsed =
                    new Date(dateText);

                if (!isNaN(parsed.getTime())) {

                    scheduleDate =
                        `${parsed.getFullYear()}-${String(
                            parsed.getMonth() + 1
                        ).padStart(2, '0')}-${String(
                            parsed.getDate()
                        ).padStart(2, '0')}`;

                }

            }


            try {

                const response =
                    await fetch(
                        `api/schedules/admin.php?action=employees&date=${encodeURIComponent(
                            scheduleDate || ''
                        )}`,
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
                        ? result.data
                        : [];


                employeeSelect.innerHTML =
                    '<option value="">Select Employee</option>';


                employees.forEach(employee => {

                    const option =
                        document.createElement('option');


                    option.value =
                        employee.id;


                    option.textContent =
                        `(${employee.position})--${employee.name}-(${employee.status})`;


                    /*
                     * Color the dropdown option itself.
                     * Note that browser support for option
                     * background/text styling varies.
                     */
                    option.style.color =
                        employee.status === 'Active'
                            ? '#059669'
                            : '#dc2626';


                    employeeSelect.appendChild(
                        option
                    );

                });


                employeeSelect.disabled =
                    false;


                if (!employees.length) {

                    employeeSelect.innerHTML =
                        '<option value="">No employees available</option>';

                    employeeSelect.disabled =
                        true;
                }


            } catch (error) {

                console.error(
                    'Create Schedule employee loading error:',
                    error
                );


                employeeSelect.innerHTML =
                    '<option value="">Unable to load employees</option>';

                employeeSelect.disabled =
                    true;

            }
        }

        function closeCreateScheduleModal() {
            document.getElementById('modal-create-schedule').classList.add('hidden');
        }

        // Set the fixed start/end times based on the selected shift.
        // Morning: 8:00 AM - 12:00 PM
        // Afternoon: 1:00 PM - 5:00 PM
        function updateScheduleShiftTimes() {

            const shift =
                document.getElementById(
                    'schedule-form-shift'
                ).value;

            const start =
                document.getElementById(
                    'schedule-form-start'
                );

            const end =
                document.getElementById(
                    'schedule-form-end'
                );

            if (!start || !end) {
                return;
            }


            if (shift === 'Morning') {

                start.value = '8:00 AM';
                end.value = '12:00 PM';

            } else if (shift === 'Afternoon') {

                start.value = '1:00 PM';
                end.value = '5:00 PM';

            } else {

                start.value = '8:00 AM';
                end.value = '5:00 PM';

            }
        }

        function updateScheduleDateDisplay(nativeInput) {

            if (!nativeInput.value) {
                return;
            }


            const [year, month, day] =
                nativeInput.value
                    .split('-')
                    .map(Number);


            const date =
                new Date(
                    year,
                    month - 1,
                    day
                );


            const formatted =
                date.toLocaleDateString(
                    'en-US',
                    {
                        month: 'long',
                        day: 'numeric',
                        year: 'numeric'
                    }
                );


            document.getElementById(
                'schedule-form-date'
            ).value = formatted;


            /*
             * Reload employees because someone may be
             * on approved leave for this particular date.
             */
            loadCreateScheduleEmployees();
        }

        let pendingCreateSchedule = null;

        function handleCreateScheduleSubmit(event) {

            event.preventDefault();


            const employeeSelect =
                document.getElementById(
                    'schedule-form-employee'
                );

            const dateText =
                document.getElementById(
                    'schedule-form-date'
                ).value;

            const shift =
                document.getElementById(
                    'schedule-form-shift'
                ).value;

            const startRaw =
                document.getElementById(
                    'schedule-form-start'
                ).value;

            const endRaw =
                document.getElementById(
                    'schedule-form-end'
                ).value;

            const workType =
                document.getElementById(
                    'schedule-form-worktype'
                ).value;

            const remarks =
                document.getElementById(
                    'schedule-form-remarks'
                ).value.trim();


            const employeeId =
                employeeSelect.value;


            if (!employeeId) {

                showScheduleToast(
                    'Please select an employee.',
                    true
                );

                return;
            }


            const employeeName =
                employeeSelect.options[
                    employeeSelect.selectedIndex
                ]?.textContent || 'Selected employee';


            const isoDate =
                getScheduleIsoDate(dateText);


            if (!isoDate) {

                showScheduleToast(
                    'Please select a valid schedule date.',
                    true
                );

                return;
            }


            /*
             * Build confirmation message.
             */
            const summary =
                document.getElementById(
                    'confirm-schedule-summary'
                );


            if (summary) {

                summary.innerHTML = `
            <strong>${escapeScheduleDayHtml(employeeName)}</strong><br>
            ${escapeScheduleDayHtml(dateText)}<br>
            ${escapeScheduleDayHtml(shift)} Shift â€”
            ${escapeScheduleDayHtml(startRaw)}
            to
            ${escapeScheduleDayHtml(endRaw)}<br>
            ${escapeScheduleDayHtml(workType)}
        `;

            }


            /*
             * Remember the form values temporarily.
             * The actual database save happens only
             * after Yes is clicked.
             */
            pendingCreateSchedule = {

                employeeId,

                employeeName,

                isoDate,

                dateText,

                shift,

                startRaw,

                endRaw,

                workType,

                remarks

            };


            document
                .getElementById(
                    'modal-confirm-schedule'
                )
                .classList.remove('hidden');
        }

        function closeConfirmScheduleModal() {

            const modal =
                document.getElementById(
                    'modal-confirm-schedule'
                );

            if (modal) {
                modal.classList.add('hidden');
            }

        }


        async function confirmCreateSchedule() {

            if (!pendingCreateSchedule) {
                return;
            }


            const schedule =
                pendingCreateSchedule;


            try {

                const response =
                    await fetch(
                        'api/schedules/admin.php',
                        {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({

                                action: 'create',

                                employee_id:
                                    schedule.employeeId,

                                schedule_date:
                                    schedule.isoDate,

                                shift:
                                    schedule.shift,

                                work_type:
                                    schedule.workType,

                                remarks:
                                    schedule.remarks

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
                        'Unable to save schedule.'
                    );
                }


                closeConfirmScheduleModal();
                closeCreateScheduleModal();


                /*
                 * Reset form after a successful database save.
                 */
                const form =
                    document.querySelector(
                        '#modal-create-schedule form'
                    );


                if (form) {
                    form.reset();
                }


                document.getElementById(
                    'schedule-form-date'
                ).value = 'August 20, 2026';


                document.getElementById(
                    'schedule-form-start'
                ).value = '8:00 AM';


                document.getElementById(
                    'schedule-form-end'
                ).value = '5:00 PM';


                pendingCreateSchedule = null;


                showScheduleToast(
                    result.message ||
                    'Schedule saved successfully.',
                    false
                );


                /*
                 * Refresh the database-driven Month View.
                 */
                await showScheduleMonthView(
                    schedule.isoDate
                );


            } catch (error) {

                console.error(
                    'Create schedule error:',
                    error
                );


                showScheduleToast(
                    error.message ||
                    'Unable to save schedule.',
                    true
                );

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
            captureExistingWeekSchedules();
            if (!scheduleWeekData[employee]) scheduleWeekData[employee] = {};

            const label = remarks ? `${workType} / ${remarks}` : workType;
            const html = `<div class="${colors.bg} ${colors.text} text-xs p-1.5 rounded border ${colors.border} text-center cursor-pointer ${colors.hover}">
                <div class="font-bold">${start24} - ${end24}</div>
                <div class="truncate text-[10px]">${label}</div>
            </div>`;
            scheduleWeekData[employee][isoDate] = {
                html,
                className: 'p-2 border-r border-border'
            };

            // Re-render the current week if the new schedule belongs to it.
            const visibleStart = normalizeScheduleDate(scheduleWeekStart);
            const visibleEnd = new Date(visibleStart);
            visibleEnd.setDate(visibleStart.getDate() + 6);
            const target = normalizeScheduleDate(isoDate);
            if (target && target >= visibleStart && target <= visibleEnd) {
                renderScheduleWeek(scheduleWeekStart);
                return true;
            }
            return true;
        }

        function insertScheduleIntoMonthRoster(employee, isoDate, start12, end12, colors, shift = '', workType = '', remarks = '') {
            const label = `${employee} Â· ${start12}-${end12}`;
            const detailsParts = [`${start12}-${end12}`];
            if (shift) detailsParts.push(`${shift} Shift`);
            if (workType) detailsParts.push(workType);
            if (remarks) detailsParts.push(remarks);
            const item = {
                className: `${colors.bg} ${colors.text} text-[10px] px-1.5 py-1 rounded truncate`,
                title: label,
                text: label,
                employee,
                details: detailsParts.join(' Â· '),
                shift,
                workType,
                remarks
            };

            // Keep the schedule in memory so it remains visible after moving
            // to another month and coming back.
            if (!scheduleCalendarData[isoDate]) scheduleCalendarData[isoDate] = [];
            scheduleCalendarData[isoDate].push(item);

            const dayCell = document.querySelector(`#monthRosterView div[data-date="${isoDate}"]`);
            if (!dayCell) return false;
            const container = dayCell.querySelector('.mt-1.space-y-1');
            if (!container) return false;

            const entry = document.createElement('div');
            entry.className = item.className;
            entry.title = item.title;
            entry.textContent = item.text;
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
        window.toggleAttendanceSubmenu = function (event) {
            const submenu = document.getElementById('attendance-submenu');
            const chevron = document.getElementById('attendance-submenu-chevron');

            if (submenu) {
                submenu.classList.toggle('hidden');
            }

            if (chevron) {
                chevron.classList.toggle('rotate-180');
            }
        };

        // ---- Manager Team ----
        async function loadManagerTeam() {
            const tableBody = document.getElementById('manager-team-table-body');
            const departmentSelect = document.getElementById('manager-team-department');

            if (!tableBody) return;

            const selectedDepartment = departmentSelect
                ? departmentSelect.value.trim().toLowerCase()
                : '';

            tableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-5 py-8 text-center text-gray-400">
                        Loading manager teams...
                    </td>
                </tr>
            `;

            try {
                const [managerResponse, employeeResponse, defaultManagerResponse] = await Promise.all([
                    fetch('api/employees/assign-manager.php', {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: { 'Accept': 'application/json' },
                        cache: 'no-store'
                    }),
                    fetch('api/employees/list.php', {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: { 'Accept': 'application/json' },
                        cache: 'no-store'
                    }),
                    fetch('api/employees/profile.php?employee_id=26011002', {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: { 'Accept': 'application/json' },
                        cache: 'no-store'
                    })
                ]);

                const managerResult = await managerResponse.json();
                const employeeResult = await employeeResponse.json();
                const defaultManagerResult = await defaultManagerResponse.json();

                if (!managerResponse.ok || !managerResult.success) {
                    throw new Error(managerResult.message || 'Unable to load manager teams.');
                }

                if (!employeeResponse.ok || !employeeResult.success) {
                    throw new Error(employeeResult.message || 'Unable to load employees.');
                }

                if (!defaultManagerResponse.ok || !defaultManagerResult.success) {
                    throw new Error(defaultManagerResult.message || 'Unable to load the default manager account.');
                }

                const managers = Array.isArray(managerResult.managers) ? managerResult.managers : [];
                const defaultManagerRecord = defaultManagerResult.data?.employee || null;
                const assignedEmployees = Array.isArray(managerResult.employees) ? managerResult.employees : [];
                const employees = Array.isArray(employeeResult.data) ? employeeResult.data : [];

                const normalize = value => String(value || '')
                    .trim()
                    .toLowerCase()
                    .replace(/[_-]+/g, ' ')
                    .replace(/\s+/g, ' ');

                const employeeMap = new Map(
                    employees.map(employee => [String(employee.id || ''), employee])
                );

                const filteredManagers = managers.filter(manager => {
                    if (!selectedDepartment) return true;
                    return normalize(manager.department_name) === normalize(selectedDepartment);
                });

                tableBody.innerHTML = '';

                if (!filteredManagers.length) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-gray-400">
                                No managers found for the selected department.
                            </td>
                        </tr>
                    `;
                    return;
                }

                filteredManagers.forEach(manager => {
                    const teamMembers = assignedEmployees.filter(employee =>
                        Number(employee.manager_id) === Number(manager.manager_id)
                    );
                    const isDefaultManager = String(manager.employee_id || '').trim() === '26011002';
                    const defaultManagerDisplayName = defaultManagerRecord
                        ? [defaultManagerRecord.first_name, defaultManagerRecord.middle_name, defaultManagerRecord.last_name]
                            .filter(Boolean)
                            .join(' ')
                        : '';
                    const managerDisplayName = isDefaultManager
                        ? (defaultManagerRecord?.full_name || defaultManagerDisplayName || manager.manager_name)
                        : manager.manager_name;

                    const managerAvatar =
                        (
                            isDefaultManager
                                ? (
                                    defaultManagerRecord?.avatar_url ||
                                    manager.avatar_url
                                )
                                : manager.avatar_url
                        ) ||
                        'assets/images/Employee.png';

                    const managerCell = document.createElement('td');
                    managerCell.rowSpan = Math.max(teamMembers.length, 1);
                    managerCell.className = 'px-5 py-5 align-middle';
                    managerCell.innerHTML = `
                        <div class="flex flex-col items-center justify-center text-center min-w-[190px]">
                            <div class="w-20 h-20 rounded-full border border-border overflow-hidden flex items-center justify-center bg-accent/15">
                               <img src="${escapeHtml(managerAvatar)}"alt="Manager profile photo"class="w-full h-full object-cover" onerror="this.src='assets/images/Employee.png'">
                            </div>
                            <div class="mt-4 text-sm font-semibold text-primary uppercase">
                                ${escapeHtml(managerDisplayName || 'Manager Account')}
                            </div>
                            <div class="mt-6 text-sm text-gray-500">
                                Members
                            </div>
                            <div class="text-sm text-gray-500">
                                ${teamMembers.length}/${teamMembers.length}
                            </div>
                        </div>
                    `;

                    if (!teamMembers.length) {
                        const row = document.createElement('tr');
                        row.className = 'hover:bg-background transition-all-300';
                        row.appendChild(managerCell);

                        const emptyCell = document.createElement('td');
                        emptyCell.colSpan = 4;
                        emptyCell.className = 'px-5 py-8 text-center text-gray-400';
                        emptyCell.textContent = 'No employees assigned to this manager.';
                        row.appendChild(emptyCell);
                        tableBody.appendChild(row);
                        return;
                    }

                    teamMembers.forEach((assignedEmployee, index) => {
                        const employee = employeeMap.get(String(assignedEmployee.employee_id || '')) || {};
                        const row = document.createElement('tr');
                        row.className = 'hover:bg-background transition-all-300';

                        if (index === 0) {
                            row.appendChild(managerCell);
                        }

                        const status = String(employee.status || assignedEmployee.employment_status || '').toUpperCase();
                        let statusClasses = 'bg-gray-100 text-gray-600';
                        if (status === 'ACTIVE') statusClasses = 'bg-emerald-100 text-emerald-700';
                        else if (status === 'LEAVE') statusClasses = 'bg-red-100 text-red-600';

                        row.innerHTML += `
                            <td class="px-5 py-4 font-semibold text-primary">${escapeHtml(employee.name || assignedEmployee.employee_name || '')}</td>
                            <td class="px-5 py-4 font-medium text-primary">${escapeHtml(assignedEmployee.employee_id || employee.id || '')}</td>
                            <td class="px-5 py-4 text-gray-600">${escapeHtml(employee.position || '')}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center ${statusClasses} text-xs font-semibold px-3 py-1 rounded-full">
                                    ${escapeHtml(status || 'â€”')}
                                </span>
                            </td>
                        `;

                        tableBody.appendChild(row);
                    });
                });
            } catch (error) {
                console.error('Manager team loading error:', error);

                tableBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-red-500">
                            Unable to load manager teams.
                        </td>
                    </tr>
                `;
            }
        }

        function openPromoteEmployeeModal() {
            const modal = document.getElementById('modal-promote-employee');
            if (modal) modal.classList.remove('hidden');
            if (window.lucide) lucide.createIcons();
        }

        function closePromoteEmployeeModal() {
            const modal = document.getElementById('modal-promote-employee');
            if (modal) modal.classList.add('hidden');
        }

        document.addEventListener('DOMContentLoaded', function () {
            loadManagerTeam();
        });

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

        async function loadTodayAttendanceFromDatabase() {

            const tableBody =
                document.getElementById('attendance-table-body');

            if (!tableBody) return;

            try {

                const response = await fetch(
                    'api/attendance/today.php',
                    {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin',
                        cache: 'no-store'
                    }
                );

                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(
                        result.message || 'Unable to load attendance.'
                    );
                }

                tableBody.innerHTML = '';

                result.data.forEach(record => {

                    let statusClass =
                        'bg-emerald-100 text-emerald-700';

                    if (record.status === 'LEAVE') {
                        statusClass =
                            'bg-red-100 text-red-600';
                    }

                    if (record.status === 'ABSENT') {
                        statusClass =
                            'bg-amber-100 text-amber-700';
                    }

                    if (record.status === 'LATE') {
                        statusClass =
                            'bg-purple-100 text-purple-700';
                    }

                    const row =
                        document.createElement('tr');

                    row.className =
                        'group cursor-pointer hover:bg-background transition-all-300';

                    row.dataset.id = record.id;
                    row.dataset.name = record.name;
                    row.dataset.department = record.department;
                    row.dataset.status = record.status;

                    row.setAttribute(
                        'onclick',
                        'openEmployeeProfile(this)'
                    );

                    row.innerHTML = `
                        <td class="px-5 py-4 font-medium text-primary">
                            ${record.id}
                        </td>

                        <td class="px-5 py-4 font-semibold text-primary">
                            ${record.name}
                        </td>

                        <td class="px-5 py-4 text-gray-600">
                            ${record.schedule}
                        </td>

                        <td class="px-5 py-4 text-gray-600">
                            ${record.clockIn}
                        </td>

                        <td class="px-5 py-4 text-gray-600">
                            ${record.clockOut}
                        </td>

                        <td class="px-5 py-4">
                            <div class="flex items-center justify-between gap-2">
                                <span class="inline-flex items-center ${statusClass} text-xs font-semibold px-3 py-1 rounded-full">
                                    ${record.status}
                                </span>
                            </div>
                        </td>
                    `;

                    tableBody.appendChild(row);
                });

                filterAttendanceTable();

            } catch (error) {

                console.error(
                    'Attendance loading error:',
                    error
                );

                tableBody.innerHTML = `
                    <tr>
                        <td colspan="6"
                            class="px-5 py-8 text-center text-red-500">
                            Unable to load attendance records.
                        </td>
                    </tr>
                `;
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

        async function loadAttendanceRecordsFromDatabase() {

            const tableBody =
                document.getElementById('attendance-records-table-body');

            if (!tableBody) return;

            const periodSelect =
                document.getElementById('attendancePeriodSelect');

            const dateInput =
                document.getElementById('attendancePeriodDate');

            const period =
                periodSelect
                    ? periodSelect.value
                    : 'Today';

            const anchor =
                dateInput && dateInput.value
                    ? dateInput.value
                    : new Date().toISOString().slice(0, 10);

            const anchorDate =
                new Date(anchor + 'T00:00:00');

            let start;
            let end;

            if (period === 'This Week') {

                const day = anchorDate.getDay();

                const diff =
                    day === 0
                        ? -6
                        : 1 - day;

                start =
                    new Date(anchorDate);

                start.setDate(
                    anchorDate.getDate() + diff
                );

                end =
                    new Date(start);

                end.setDate(
                    start.getDate() + 6
                );

            } else if (period === 'This Month') {

                start =
                    new Date(
                        anchorDate.getFullYear(),
                        anchorDate.getMonth(),
                        1
                    );

                end =
                    new Date(
                        anchorDate.getFullYear(),
                        anchorDate.getMonth() + 1,
                        0
                    );

            } else {

                start =
                    new Date(anchorDate);

                end =
                    new Date(anchorDate);
            }

            const toISO = date =>
                date.toISOString().slice(0, 10);

            const startDate = toISO(start);
            const endDate = toISO(end);

            try {

                const response = await fetch(
                    `api/attendance/records.php?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`,
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

                if (!response.ok || !result.success) {
                    throw new Error(
                        result.message ||
                        'Unable to load attendance records.'
                    );
                }

                tableBody.innerHTML = '';

                result.data.forEach(record => {

                    let statusClass =
                        'bg-emerald-100 text-emerald-700';

                    if (record.status === 'LATE') {
                        statusClass =
                            'bg-purple-100 text-purple-600';
                    }

                    if (record.status === 'ABSENT') {
                        statusClass =
                            'bg-amber-100 text-amber-700';
                    }

                    if (record.status === 'ON_LEAVE') {
                        statusClass =
                            'bg-red-100 text-red-600';
                    }

                    const row =
                        document.createElement('tr');

                    row.dataset.date = record.date;
                    row.dataset.employee = record.employee;
                    row.dataset.department = record.department;
                    row.dataset.status = record.status;

                    row.innerHTML = `
                        <td
                            class="px-5 py-4 text-gray-600"
                            data-label="Date">
                            ${record.date}
                        </td>

                        <td
                            class="px-5 py-4 font-semibold text-primary"
                            data-label="Employee">
                            ${record.employee}
                        </td>

                        <td
                            class="px-5 py-4 text-gray-600"
                            data-label="Schedule">
                            ${record.schedule}
                        </td>

                        <td
                            class="px-5 py-4 text-gray-600"
                            data-label="Clock In">
                            ${record.clockIn}
                        </td>

                        <td
                            class="px-5 py-4 text-gray-600"
                            data-label="Clock Out">
                            ${record.clockOut}
                        </td>

                        <td
                            class="px-5 py-4"
                            data-label="Status">
                            <span
                                class="inline-flex items-center ${statusClass} text-xs font-semibold px-3 py-1 rounded-full">
                                ${record.status}
                            </span>
                        </td>

                        <td
                            class="px-5 py-4 text-right"
                            data-label="Archive">

                            <button
                                class="text-gray-400 hover:text-primary"
                                onclick="exportSingleDTR(this)"
                                title="Export this DTR entry">

                                <i class="fa-solid fa-download"></i>

                            </button>

                        </td>
                    `;

                    tableBody.appendChild(row);
                });

                filterAttendanceRecords();

            } catch (error) {

                console.error(
                    'DTR loading error:',
                    error
                );

                tableBody.innerHTML = `
                    <tr>
                        <td
                            colspan="7"
                            class="px-5 py-8 text-center text-red-500">
                            Unable to load attendance records.
                        </td>
                    </tr>
                `;
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
        // calendar date is the real current date â€” i.e. there's no historical record yet).
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
                `Today's Attendance â€” ${todayLabel} (${rows.length} employee(s))`,
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
                `${period} â€” ${rangeLabel} (${matchingRows.length} record(s))`,
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

        // Expose functions used by other dashboard initialization scripts.
        window.captureExistingMonthSchedules = captureExistingMonthSchedules;
        window.loadEmployeesFromDatabase = loadEmployeesFromDatabase;
        window.loadAttendanceCorrectionsFromDatabase = loadAttendanceCorrectionsFromDatabase;
        window.loadTodayAttendanceFromDatabase = loadTodayAttendanceFromDatabase;
        window.loadAttendanceRecordsFromDatabase = loadAttendanceRecordsFromDatabase;
    </script>


    <script>
        /* ================= TIMESHEET APPROVALS MODULE ================= */
        (function () {
            'use strict';

            const ADMIN_NAME = 'Admin Account';
            const HIGH_OVERTIME_THRESHOLD = 10;
            let currentReviewId = null;
            let currentReasonId = null;
            let currentReasonAction = null;

            const makeDailyEntry = (date, timeIn, timeOut, breakTime, regularHours, overtime, attendanceStatus) => ({
                date,
                day: new Date(`${date}T12:00:00`).toLocaleDateString('en-US', { weekday: 'short' }),
                timeIn,
                timeOut,
                break: breakTime,
                regularHours,
                overtime,
                attendanceStatus
            });

            const createTimesheet = (data) => ({
                id: data.id,
                avatar: data.avatar || '',
                employee: data.employee,
                employeeId: data.employeeId,
                department: data.department,
                period: data.period,
                regularHours: Number(data.regularHours),
                overtimeHours: Number(data.overtimeHours),
                totalHours: Number((Number(data.regularHours) + Number(data.overtimeHours)).toFixed(2)),
                status: data.status,
                submittedDate: data.submittedDate,
                rejectionReason: data.rejectionReason || '',
                correctionReason: data.correctionReason || '',
                dailyEntries: data.dailyEntries || []
            });

            let TIMESHEET_DATA = [
                createTimesheet({
                    id: 'TS-2026-0716-01',
                    avatar: 'https://i.pravatar.cc/150?img=47',
                    employee: 'Sarah Jenkins',
                    employeeId: 'EMP-1001',
                    department: 'Operations',
                    period: 'Jul 16 - Jul 31, 2026',
                    regularHours: 76.5,
                    overtimeHours: 2,
                    status: 'Pending',
                    submittedDate: 'September 19, 2026',
                    rejectionReason: '',
                    correctionReason: '',
                    dailyEntries: [
                        makeDailyEntry('2026-07-16', '8:02 AM', '5:02 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-17', '8:00 AM', '5:00 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-20', '8:03 AM', '5:03 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-21', '8:05 AM', '4:35 PM', '1 hr', 7.5, 0, 'Present'),
                        makeDailyEntry('2026-07-22', '8:08 AM', '4:38 PM', '1 hr', 7.5, 0, 'Late'),
                        makeDailyEntry('2026-07-23', '8:01 AM', '5:01 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-24', '8:00 AM', '5:00 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-27', '8:12 AM', '4:57 PM', '1 hr', 7.75, 0, 'Late'),
                        makeDailyEntry('2026-07-28', '8:00 AM', '5:00 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-29', '9:10 AM', '6:10 PM', '1 hr', 3.75, 2, 'Late')
                    ]
                }),
                createTimesheet({
                    id: 'TS-2026-0716-02',
                    avatar: 'https://i.pravatar.cc/150?img=15',
                    employee: 'Michael Chang',
                    employeeId: 'EMP-1002',
                    department: 'Monitoring',
                    period: 'Jul 16 - Jul 31, 2026',
                    regularHours: 80,
                    overtimeHours: 12.5,
                    status: 'Pending',
                    submittedDate: 'September 19, 2026',
                    rejectionReason: '',
                    correctionReason: '',
                    dailyEntries: [
                        makeDailyEntry('2026-07-16', '8:00 AM', '5:00 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-17', '8:00 AM', '7:30 PM', '1 hr', 8, 2.5, 'Present'),
                        makeDailyEntry('2026-07-20', '8:00 AM', '5:00 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-21', '8:00 AM', '8:00 PM', '1 hr', 8, 3, 'Present'),
                        makeDailyEntry('2026-07-22', '8:00 AM', '6:30 PM', '1 hr', 8, 1.5, 'Present'),
                        makeDailyEntry('2026-07-23', '8:00 AM', '7:00 PM', '1 hr', 8, 2, 'Present'),
                        makeDailyEntry('2026-07-24', '8:05 AM', '5:05 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-27', '8:00 AM', '6:30 PM', '1 hr', 8, 1.5, 'Present'),
                        makeDailyEntry('2026-07-28', '8:00 AM', '7:00 PM', '1 hr', 8, 2, 'Present'),
                        makeDailyEntry('2026-07-29', '8:00 AM', '5:00 PM', '1 hr', 8, 0, 'Present')
                    ]
                }),
                createTimesheet({
                    id: 'TS-2026-0716-03',
                    avatar: 'https://i.pravatar.cc/150?img=12',
                    employee: 'John Smith',
                    employeeId: 'EMP-1003',
                    department: 'Operations',
                    period: 'Jul 16 - Jul 31, 2026',
                    regularHours: 74.25,
                    overtimeHours: 4.5,
                    status: 'Approved',
                    submittedDate: 'September 18, 2026',
                    rejectionReason: '',
                    correctionReason: '',
                    dailyEntries: [
                        makeDailyEntry('2026-07-16', '8:00 AM', '5:00 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-17', '8:05 AM', '5:05 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-20', '8:10 AM', '4:55 PM', '1 hr', 7.75, 0, 'Late'),
                        makeDailyEntry('2026-07-21', '8:00 AM', '5:00 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-22', '8:00 AM', '4:30 PM', '1 hr', 7.5, 0, 'Present'),
                        makeDailyEntry('2026-07-23', '8:00 AM', '6:30 PM', '1 hr', 8, 1.5, 'Present'),
                        makeDailyEntry('2026-07-24', '8:00 AM', '6:00 PM', '1 hr', 8, 1, 'Present'),
                        makeDailyEntry('2026-07-27', '8:00 AM', '5:00 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-28', '8:00 AM', '6:00 PM', '1 hr', 7.5, 2, 'Present'),
                        makeDailyEntry('2026-07-29', '8:00 AM', '11:30 AM', '0.5 hr', 3.5, 0, 'Present')
                    ]
                }),
                createTimesheet({
                    id: 'TS-2026-0716-04',
                    avatar: 'https://i.pravatar.cc/150?img=44',
                    employee: 'Maria Santos',
                    employeeId: 'EMP-1004',
                    department: 'Guest Services',
                    period: 'Jul 16 - Jul 31, 2026',
                    regularHours: 77,
                    overtimeHours: 0,
                    status: 'Rejected',
                    submittedDate: 'September 18, 2026',
                    rejectionReason: 'Incorrect Time Entry',
                    correctionReason: '',
                    dailyEntries: [
                        makeDailyEntry('2026-07-16', '8:00 AM', '5:00 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-17', '8:15 AM', '4:45 PM', '1 hr', 7.5, 0, 'Late'),
                        makeDailyEntry('2026-07-20', '8:00 AM', '5:00 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-21', '8:00 AM', '5:00 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-22', '8:30 AM', '5:00 PM', '1 hr', 7.5, 0, 'Late'),
                        makeDailyEntry('2026-07-23', '8:00 AM', '5:00 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-24', '8:00 AM', '4:30 PM', '1 hr', 7.5, 0, 'Present'),
                        makeDailyEntry('2026-07-27', '8:00 AM', '5:00 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-28', '8:10 AM', '4:40 PM', '1 hr', 7.5, 0, 'Late'),
                        makeDailyEntry('2026-07-29', '8:00 AM', '4:00 PM', '1 hr', 7, 0, 'Present')
                    ]
                }),
                createTimesheet({
                    id: 'TS-2026-0716-05',
                    avatar: 'https://i.pravatar.cc/150?img=33',
                    employee: 'David Wilson',
                    employeeId: 'EMP-1005',
                    department: 'Finance',
                    period: 'Jul 16 - Jul 31, 2026',
                    regularHours: 72.5,
                    overtimeHours: 8,
                    status: 'Correction Requested',
                    submittedDate: 'September 19, 2026',
                    rejectionReason: '',
                    correctionReason: 'Missing attendance record for July 24.',
                    dailyEntries: [
                        makeDailyEntry('2026-07-16', '8:00 AM', '5:00 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-17', '8:00 AM', '5:30 PM', '1 hr', 7.5, 0.5, 'Present'),
                        makeDailyEntry('2026-07-20', '8:00 AM', '5:00 PM', '1 hr', 8, 0, 'Present'),
                        makeDailyEntry('2026-07-21', '8:10 AM', '4:40 PM', '1 hr', 7, 0, 'Late'),
                        makeDailyEntry('2026-07-22', '8:00 AM', '6:30 PM', '1 hr', 7.5, 1.5, 'Present'),
                        makeDailyEntry('2026-07-23', '8:00 AM', '7:00 PM', '1 hr', 8, 2, 'Present'),
                        makeDailyEntry('2026-07-24', 'â€”', 'â€”', 'â€”', 0, 0, 'Missing Record'),
                        makeDailyEntry('2026-07-27', '8:00 AM', '6:00 PM', '1 hr', 7, 2, 'Present'),
                        makeDailyEntry('2026-07-28', '8:00 AM', '5:00 PM', '1 hr', 7.5, 0, 'Present'),
                        makeDailyEntry('2026-07-29', '8:00 AM', '5:00 PM', '1 hr', 7, 2, 'Present')
                    ]
                })
            ];

            let TIMESHEET_HISTORY = [
                {
                    employee: 'David Wilson',
                    action: 'Correction Requested',
                    previousStatus: 'Pending',
                    newStatus: 'Correction Requested',
                    admin: ADMIN_NAME,
                    timestamp: 'September 20, 2026 - 5:25 PM',
                    reason: 'Missing attendance record for July 24.'
                },
                {
                    employee: 'Maria Santos',
                    action: 'Timesheet Rejected',
                    previousStatus: 'Pending',
                    newStatus: 'Rejected',
                    admin: ADMIN_NAME,
                    timestamp: 'September 20, 2026 - 5:20 PM',
                    reason: 'Incorrect Time Entry'
                },
                {
                    employee: 'John Smith',
                    action: 'Timesheet Approved',
                    previousStatus: 'Pending',
                    newStatus: 'Approved',
                    admin: ADMIN_NAME,
                    timestamp: 'September 20, 2026 - 5:15 PM',
                    reason: 'Verified and complete.'
                }
            ];

            const escapeHtml = (value) => {
                const div = document.createElement('div');
                div.textContent = value == null ? '' : String(value);
                return div.innerHTML;
            };

            const formatHours = (value) => Number(value).toFixed(2).replace(/\.00$/, '').replace(/(\.\d)0$/, '$1');

            const formatSubmittedDate = (value) => value || 'â€”';

            const statusClass = (status) => {
                if (status === 'Approved') return 'status-approved';
                if (status === 'Rejected') return 'status-rejected';
                if (status === 'Correction Requested') return 'status-correction';
                return 'status-pending';
            };

            const statusIcon = (status) => {
                if (status === 'Approved') return 'fa-check';
                if (status === 'Rejected') return 'fa-xmark';
                if (status === 'Correction Requested') return 'fa-pen';
                return 'fa-clock';
            };

            const getRecordById = (id) => TIMESHEET_DATA.find(record => record.id === id);

            function populateTimesheetFilters() {
                const departmentEl = document.getElementById('timesheet-filter-department');
                const periodEl = document.getElementById('timesheet-filter-period');
                if (!departmentEl || !periodEl) return;

                const departments = [...new Set(TIMESHEET_DATA.map(record => record.department))].sort();
                const periods = [...new Set(TIMESHEET_DATA.map(record => record.period))];

                departmentEl.innerHTML = '<option value="">All Departments</option>' +
                    departments.map(department => `<option value="${escapeHtml(department)}">${escapeHtml(department)}</option>`).join('');

                periodEl.innerHTML = '<option value="">All Pay Periods</option>' +
                    periods.map(period => `<option value="${escapeHtml(period)}">${escapeHtml(period)}</option>`).join('');
            }

            function getFilteredTimesheets() {
                const search = (document.getElementById('timesheet-search')?.value || '').trim().toLowerCase();
                const department = document.getElementById('timesheet-filter-department')?.value || '';
                const period = document.getElementById('timesheet-filter-period')?.value || '';
                const status = document.getElementById('timesheet-filter-status')?.value || '';
                const overtime = document.getElementById('timesheet-filter-overtime')?.value || '';

                return TIMESHEET_DATA.filter(record => {
                    const matchesSearch =
                        !search ||
                        record.employee.toLowerCase().includes(search) ||
                        record.employeeId.toLowerCase().includes(search);

                    const matchesDepartment = !department || record.department === department;
                    const matchesPeriod = !period || record.period === period;
                    const matchesStatus = !status || record.status === status;

                    let matchesOvertime = true;
                    if (overtime === 'with') matchesOvertime = record.overtimeHours > 0;
                    if (overtime === 'high') matchesOvertime = record.overtimeHours > HIGH_OVERTIME_THRESHOLD;
                    if (overtime === 'none') matchesOvertime = record.overtimeHours === 0;

                    return matchesSearch && matchesDepartment && matchesPeriod && matchesStatus && matchesOvertime;
                });
            }

            function updateTimesheetSummary(records) {
                const setText = (id, value) => {
                    const element = document.getElementById(id);
                    if (element) element.textContent = value;
                };

                const pending = records.filter(record => record.status === 'Pending').length;
                const approved = records.filter(record => record.status === 'Approved').length;
                const rejected = records.filter(record => record.status === 'Rejected').length;
                const regular = records.reduce((sum, record) => sum + record.regularHours, 0);
                const overtime = records.reduce((sum, record) => sum + record.overtimeHours, 0);

                setText('ts-summary-employees', records.length);
                setText('ts-summary-pending', pending);
                setText('ts-summary-approved', approved);
                setText('ts-summary-rejected', rejected);
                setText('ts-summary-regular', `${formatHours(regular)} hrs`);
                setText('ts-summary-overtime', `${formatHours(overtime)} hrs`);

                const note = document.getElementById('timesheet-summary-note');
                if (note) {
                    note.textContent = records.length === TIMESHEET_DATA.length
                        ? 'Summary reflects all timesheet records.'
                        : `Summary reflects ${records.length} filtered timesheet record${records.length === 1 ? '' : 's'}.`;
                }
            }

            function renderTimesheetRecords(records = getFilteredTimesheets()) {
                const container = document.getElementById('timesheet-records-container');
                if (!container) return;

                updateTimesheetSummary(records);

                if (!records.length) {
                    container.innerHTML = `
                <div class="bg-card p-10 rounded-2xl shadow-sm border border-border text-center">
                    <div class="mx-auto w-14 h-14 rounded-2xl bg-background text-gray-400 flex items-center justify-center">
                        <i class="fa-solid fa-file-circle-xmark text-2xl"></i>
                    </div>
                    <h3 class="font-heading font-bold text-primary mt-4">No timesheets found</h3>
                    <p class="text-sm text-gray-500 mt-1">Try changing the search or filters.</p>
                </div>`;
                    updateTimesheetCorrectionButton(records);
                    return;
                }

                container.innerHTML = records.map(record => {
                    const highOvertime = record.overtimeHours > HIGH_OVERTIME_THRESHOLD;
                    const canApprove = record.status !== 'Approved' && record.status !== 'Rejected';
                    const canReject = record.status !== 'Rejected' && record.status !== 'Approved';
                    const canCorrection = record.status !== 'Rejected' && record.status !== 'Approved';

                    const reason = record.status === 'Rejected'
                        ? record.rejectionReason
                        : record.status === 'Correction Requested'
                            ? record.correctionReason
                            : '';

                    return `
                <article class="ts-record-card bg-card p-5 rounded-2xl shadow-sm border border-border ${highOvertime ? 'ts-high-overtime' : ''}">
                    ${highOvertime ? '<div class="ts-high-ot-bar"></div>' : ''}
                    <div class="flex flex-col gap-5">
                        <div class="grid grid-cols-1 xl:grid-cols-[minmax(240px,1.4fr)_minmax(410px,1fr)_auto] items-center gap-5">
                            <div class="flex items-start gap-4 min-w-0">
                                <img src="${escapeHtml(record.avatar || 'https://i.pravatar.cc/150?img=32')}" alt="${escapeHtml(record.employee)}" class="w-12 h-12 rounded-full object-cover border border-border flex-shrink-0">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h4 class="font-bold text-primary text-lg">${escapeHtml(record.employee)}</h4>
                                        <span class="ts-status-badge ${statusClass(record.status)}"><i class="fa-solid ${statusIcon(record.status)} text-[9px]"></i>${escapeHtml(record.status)}</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">${escapeHtml(record.employeeId)} â€¢ ${escapeHtml(record.department)}</p>
                                    <p class="text-xs text-gray-500 mt-1">Pay Period: <span class="font-medium text-gray-600">${escapeHtml(record.period)}</span></p>
                                </div>
                            </div>

                            <div class="ts-stat-grid grid grid-cols-3 gap-3 text-center">
                                <div class="px-3 py-2 rounded-xl bg-background">
                                    <p class="text-[11px] text-gray-500 uppercase font-semibold">Regular</p>
                                    <p class="font-bold text-primary mt-1">${formatHours(record.regularHours)} hrs</p>
                                </div>
                                <div class="px-3 py-2 rounded-xl ${highOvertime ? 'bg-red-50' : 'bg-amber-50'}">
                                    <p class="text-[11px] ${highOvertime ? 'text-red-600' : 'text-amber-600'} uppercase font-semibold">Overtime</p>
                                    <p class="mt-1 ${highOvertime ? 'ts-high-ot-value' : 'font-bold text-amber-600'}">
                                        ${formatHours(record.overtimeHours)} hrs
                                        ${highOvertime ? '<i class="fa-solid fa-triangle-exclamation" title="Exceeds overtime threshold"></i>' : ''}
                                    </p>
                                    ${highOvertime ? '<span class="ts-high-ot-label"><i class="fa-solid fa-triangle-exclamation"></i> High Overtime</span>' : ''}
                                </div>
                                <div class="px-3 py-2 rounded-xl bg-background">
                                    <p class="text-[11px] text-gray-500 uppercase font-semibold">Total</p>
                                    <p class="font-heading font-bold text-primary text-lg mt-0.5">${formatHours(record.totalHours)} hrs</p>
                                </div>
                            </div>

                            <div class="ts-actions flex flex-wrap xl:flex-col gap-2 xl:min-w-[150px]">
                                <button type="button" onclick="openTimesheetReview('${record.id}')" class="ts-action-btn flex-1 xl:w-full px-3 py-2 border border-border text-gray-600 rounded-xl hover:bg-background text-sm font-medium">
                                    <i class="fa-solid fa-eye mr-1"></i> Review
                                </button>
                                <div class="flex flex-1 gap-2">
                                    <button type="button" onclick="approveTimesheet('${record.id}')" ${canApprove ? '' : 'disabled'} class="ts-action-btn flex-1 px-3 py-2 bg-emerald-100 text-emerald-700 rounded-xl font-semibold hover:bg-emerald-200 text-sm">
                                        <i class="fa-solid fa-check mr-1"></i> Approve
                                    </button>
                                    <button type="button" onclick="openTimesheetRejectModal('${record.id}')" ${canReject ? '' : 'disabled'} class="ts-action-btn flex-1 px-3 py-2 bg-red-100 text-red-700 rounded-xl font-semibold hover:bg-red-200 text-sm">
                                        <i class="fa-solid fa-xmark mr-1"></i> Reject
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 pt-3 border-t border-border text-xs">
                            <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-gray-500">
                                <span><i class="fa-solid fa-calendar-day mr-1 text-gray-400"></i> Submitted: <strong class="text-gray-600">${escapeHtml(formatSubmittedDate(record.submittedDate))}</strong></span>
                                ${reason ? `<span class="${record.status === 'Rejected' ? 'text-red-600' : 'text-amber-700'}"><i class="fa-solid fa-comment-dots mr-1"></i> ${escapeHtml(reason)}</span>` : ''}
                            </div>
                            <span class="text-gray-400">Record ID: ${escapeHtml(record.id)}</span>
                        </div>
                    </div>
                </article>
            `;
                }).join('');

                updateTimesheetCorrectionButton(records);
            }

            function updateTimesheetCorrectionButton(records = getFilteredTimesheets()) {
                const button = document.getElementById('timesheet-request-correction');
                if (!button) return;

                const visiblePending = records.filter(record => record.status === 'Pending').length;
                button.disabled = visiblePending === 0;
                button.innerHTML = `<i class="fa-solid fa-pen-to-square"></i> Request Correction${visiblePending ? ` (${visiblePending})` : ''}`;
                button.classList.toggle('opacity-50', visiblePending === 0);
                button.classList.toggle('cursor-not-allowed', visiblePending === 0);
            }

            function filterTimesheets() {
                renderTimesheetRecords(getFilteredTimesheets());
            }

            function openTimesheetCorrectionRequests() {
                const modal = document.getElementById('modal-timesheet-correction-requests');
                const list = document.getElementById('timesheet-correction-request-list');
                if (!modal || !list) return;

                const records = getFilteredTimesheets();
                const pending = records.filter(record => record.status === 'Pending');
                const requested = records.filter(record => record.status === 'Correction Requested');

                if (!pending.length && !requested.length) {
                    list.innerHTML = `
                <div class="bg-background border border-border rounded-2xl p-8 text-center">
                    <div class="mx-auto w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <i class="fa-solid fa-clipboard-check text-2xl"></i>
                    </div>
                    <h4 class="font-heading font-bold text-primary mt-4">No employee timesheets available</h4>
                    <p class="text-sm text-gray-500 mt-1">There are no pending or correction-requested timesheets in the current view.</p>
                </div>`;
                } else {
                    const section = (title, items, isPending) => {
                        if (!items.length) return '';
                        return `
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h4 class="font-heading font-bold text-primary">${title}</h4>
                            <span class="text-xs font-semibold text-gray-400">${items.length} record${items.length === 1 ? '' : 's'}</span>
                        </div>
                        ${items.map(record => {
                            const reason = record.status === 'Correction Requested' ? record.correctionReason : 'No correction request has been sent yet.';
                            return `
                                <div class="bg-background border border-border rounded-2xl p-4 md:p-5">
                                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <h5 class="font-bold text-primary">${escapeHtml(record.employee)}</h5>
                                                <span class="ts-status-badge ${statusClass(record.status)}">${escapeHtml(record.status)}</span>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-1">${escapeHtml(record.employeeId)} â€¢ ${escapeHtml(record.department)}</p>
                                            <p class="text-xs text-gray-500 mt-1">Pay Period: <span class="font-medium text-gray-600">${escapeHtml(record.period)}</span></p>
                                            <div class="mt-3 p-3 rounded-xl border ${isPending ? 'border-amber-200 bg-amber-50' : 'border-emerald-200 bg-emerald-50'}">
                                                <p class="text-xs font-bold ${isPending ? 'text-amber-800' : 'text-emerald-800'} uppercase tracking-wider">${isPending ? 'Employee Timesheet' : 'Correction Request'}</p>
                                                <p class="text-sm ${isPending ? 'text-amber-900' : 'text-emerald-900'} mt-1">${escapeHtml(reason)}</p>
                                            </div>
                                        </div>
                                        <button type="button" onclick="viewTimesheetFromCorrectionRequests('${record.id}')" class="font-button shrink-0 px-4 py-2.5 ${isPending ? 'bg-amber-600 hover:bg-amber-700' : 'bg-primary hover:bg-primary/90'} text-white rounded-xl text-sm font-semibold">
                                            <i class="fa-solid ${isPending ? 'fa-eye' : 'fa-file-lines'} mr-1"></i> View Employee Request
                                        </button>
                                    </div>
                                </div>`;
                        }).join('')}
                    </div>`;
                    };

                    list.innerHTML = section('Pending Timesheets', pending, true) + section('Correction Requested', requested, false);
                }

                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
                if (window.lucide) lucide.createIcons();
            }

            function viewTimesheetFromCorrectionRequests(id) {
                closeTimesheetCorrectionRequests();
                openTimesheetReview(id);
            }

            function closeTimesheetCorrectionRequests() {
                document.getElementById('modal-timesheet-correction-requests')?.classList.add('hidden');
                if (document.getElementById('modal-timesheet-review')?.classList.contains('hidden') &&
                    document.getElementById('modal-timesheet-history')?.classList.contains('hidden') &&
                    document.getElementById('modal-timesheet-reason')?.classList.contains('hidden')) {
                    document.body.classList.remove('overflow-hidden');
                }
            }

            function clearTimesheetFilters() {
                const ids = [
                    'timesheet-search',
                    'timesheet-filter-department',
                    'timesheet-filter-period',
                    'timesheet-filter-status',
                    'timesheet-filter-overtime'
                ];

                ids.forEach(id => {
                    const element = document.getElementById(id);
                    if (element) element.value = '';
                });

                filterTimesheets();
            }

            function addHistory(record, previousStatus, newStatus, action, reason = '') {
                TIMESHEET_HISTORY.unshift({
                    employee: record.employee,
                    action,
                    previousStatus,
                    newStatus,
                    admin: ADMIN_NAME,
                    timestamp: new Date().toLocaleString('en-US', {
                        month: 'long',
                        day: 'numeric',
                        year: 'numeric',
                        hour: 'numeric',
                        minute: '2-digit'
                    }),
                    reason: reason || ''
                });
            }

            async function changeTimesheetStatus(id, newStatus, reason = '') {
                const record = getRecordById(id);
                if (!record) return false;

                const previousStatus = record.status;
                if (previousStatus === newStatus) return false;

                try {
                    const response = await fetch('api/timesheet-approvals.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'update', id, status: newStatus, reason })
                    });
                    const result = await response.json();
                    if (!response.ok || !result.ok) throw new Error(result.message || 'Unable to save timesheet approval.');

                    record.status = newStatus;
                    if (newStatus === 'Rejected') {
                        record.rejectionReason = reason;
                        record.correctionReason = '';
                    } else if (newStatus === 'Correction Requested') {
                        record.correctionReason = reason;
                        record.rejectionReason = '';
                    } else if (newStatus === 'Approved') {
                        record.rejectionReason = '';
                        record.correctionReason = '';
                    }

                    const actionLabel = newStatus === 'Approved'
                        ? 'Timesheet Approved'
                        : newStatus === 'Rejected'
                            ? 'Timesheet Rejected'
                            : 'Correction Requested';

                    addHistory(record, previousStatus, newStatus, actionLabel, reason);
                    return true;
                } catch (error) {
                    showTimesheetToast(error.message || 'Unable to save timesheet approval.', 'error');
                    return false;
                }
            }

            async function approveTimesheet(id) {
                const record = getRecordById(id);
                if (!record || record.status === 'Approved' || record.status === 'Rejected') return;

                if (await changeTimesheetStatus(id, 'Approved', 'Verified and complete.')) {
                    renderTimesheetRecords(getFilteredTimesheets());
                    showTimesheetToast('Timesheet approved successfully.', 'success');
                }
            }

            async function approveAllTimesheets() {
                const visiblePending = getFilteredTimesheets().filter(record => record.status === 'Pending');

                if (!visiblePending.length) {
                    showTimesheetToast('No visible pending timesheets to approve.', 'info');
                    return;
                }

                try {
                    const response = await fetch('api/timesheet-approvals.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'bulk_approve', ids: visiblePending.map(r => r.id) })
                    });
                    const result = await response.json();
                    if (!response.ok || !result.ok) throw new Error(result.message || 'Unable to approve timesheets.');

                    visiblePending.forEach(record => {
                        record.status = 'Approved';
                        record.rejectionReason = '';
                        record.correctionReason = '';
                        addHistory(record, 'Pending', 'Approved', 'Timesheet Approved', 'Bulk approval by admin.');
                    });

                    renderTimesheetRecords(getFilteredTimesheets());
                    showTimesheetToast(`${result.changed || visiblePending.length} timesheet${(result.changed || visiblePending.length) === 1 ? '' : 's'} approved.`, 'success');
                } catch (error) {
                    showTimesheetToast(error.message || 'Unable to approve timesheets.', 'error');
                }
            }

            function openTimesheetReview(id) {
                const record = getRecordById(id);
                if (!record) return;

                currentReviewId = id;

                const setText = (elementId, value) => {
                    const element = document.getElementById(elementId);
                    if (element) element.textContent = value;
                };

                setText('timesheet-review-name', record.employee);
                setText('timesheet-review-id', record.employeeId);
                setText('timesheet-review-department', record.department);
                setText('timesheet-review-period', record.period);
                setText('timesheet-review-submitted', record.submittedDate);
                setText('timesheet-review-regular', `${formatHours(record.regularHours)} hrs`);
                setText('timesheet-review-overtime', `${formatHours(record.overtimeHours)} hrs`);

                const statusEl = document.getElementById('timesheet-review-status');
                statusEl.textContent = record.status;
                statusEl.className = `ts-status-badge ${statusClass(record.status)}`;

                const warning = document.getElementById('timesheet-review-ot-warning');
                warning.classList.toggle('hidden', record.overtimeHours <= HIGH_OVERTIME_THRESHOLD);

                const reasonWrap = document.getElementById('timesheet-review-reason-wrap');
                const reasonEl = document.getElementById('timesheet-review-reason');
                const reason = record.status === 'Rejected' ? record.rejectionReason : record.correctionReason;
                if (reason) {
                    reasonWrap.classList.remove('hidden');
                    reasonEl.textContent = reason;
                } else {
                    reasonWrap.classList.add('hidden');
                    reasonEl.textContent = '';
                }

                const tbody = document.getElementById('timesheet-review-entries');
                tbody.innerHTML = record.dailyEntries.map(entry => `
            <tr class="hover:bg-background">
                <td class="px-4 py-3 font-medium text-primary">${escapeHtml(formatDailyDate(entry.date))}</td>
                <td class="px-4 py-3 text-gray-600">${escapeHtml(entry.day)}</td>
                <td class="px-4 py-3 text-gray-600">${escapeHtml(entry.timeIn)}</td>
                <td class="px-4 py-3 text-gray-600">${escapeHtml(entry.timeOut)}</td>
                <td class="px-4 py-3 text-gray-600">${escapeHtml(entry.break)}</td>
                <td class="px-4 py-3 text-right font-semibold text-primary">${formatHours(entry.regularHours)} hrs</td>
                <td class="px-4 py-3 text-right font-semibold ${entry.overtime > HIGH_OVERTIME_THRESHOLD ? 'text-red-600' : 'text-amber-600'}">
                    ${formatHours(entry.overtime)} hrs
                    ${entry.overtime > 0 ? '<i class="fa-solid fa-clock-rotate-left ml-1"></i>' : ''}
                </td>
                <td class="px-4 py-3">${attendanceBadge(entry.attendanceStatus)}</td>
            </tr>
        `).join('');

                const approveBtn = document.getElementById('timesheet-review-approve');
                const rejectBtn = document.getElementById('timesheet-review-reject');

                const locked = record.status === 'Approved' || record.status === 'Rejected';
                approveBtn.disabled = locked;
                rejectBtn.disabled = locked;
                [approveBtn, rejectBtn].forEach(button => {
                    button.classList.toggle('opacity-50', button.disabled);
                    button.classList.toggle('cursor-not-allowed', button.disabled);
                });

                document.getElementById('modal-timesheet-review').classList.remove('hidden');
                document.body.classList.add('overflow-hidden');

                if (window.lucide) lucide.createIcons();
            }

            function formatDailyDate(isoDate) {
                const date = new Date(`${isoDate}T12:00:00`);
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            }

            function attendanceBadge(status) {
                const map = {
                    'Present': 'bg-emerald-100 text-emerald-700',
                    'Late': 'bg-amber-100 text-amber-700',
                    'Missing Record': 'bg-red-100 text-red-700',
                    'Absent': 'bg-red-100 text-red-700',
                    'Leave': 'bg-purple-100 text-purple-700'
                };
                const cls = map[status] || 'bg-gray-100 text-gray-600';
                return `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold ${cls}">${escapeHtml(status)}</span>`;
            }

            function closeTimesheetReview() {
                document.getElementById('modal-timesheet-review')?.classList.add('hidden');
                currentReviewId = null;
                if (!document.getElementById('modal-timesheet-reason')?.classList.contains('hidden')) return;
                if (!document.getElementById('modal-timesheet-history') || document.getElementById('modal-timesheet-history').classList.contains('hidden')) {
                    document.body.classList.remove('overflow-hidden');
                }
            }

            function approveFromReview() {
                if (!currentReviewId) return;
                const id = currentReviewId;
                approveTimesheet(id);
                closeTimesheetReview();
            }

            function rejectFromReview() {
                if (!currentReviewId) return;
                const id = currentReviewId;
                closeTimesheetReview();
                openTimesheetRejectModal(id);
            }

            function requestCorrectionFromReview() {
                if (!currentReviewId) return;
                const id = currentReviewId;
                closeTimesheetReview();
                openTimesheetCorrectionModal(id);
            }

            function openTimesheetRejectModal(id) {
                const record = getRecordById(id);
                if (!record) return;

                currentReasonId = id;
                currentReasonAction = 'reject';

                document.getElementById('timesheet-reason-kicker').textContent = 'Reject Timesheet';
                document.getElementById('timesheet-reason-title').textContent = `Reject ${record.employee}'s Timesheet`;
                document.getElementById('timesheet-reason-prompt').textContent = 'Please provide a reason for rejecting this timesheet.';
                document.getElementById('timesheet-rejection-options').classList.remove('hidden');
                document.getElementById('timesheet-correction-reason-wrap').classList.add('hidden');
                document.getElementById('timesheet-other-reason-wrap').classList.add('hidden');
                document.getElementById('timesheet-rejection-select').value = '';
                document.getElementById('timesheet-other-reason').value = '';
                document.getElementById('timesheet-reason-submit').className = 'font-button px-4 py-2.5 bg-red-600 text-white rounded-xl text-sm font-semibold hover:bg-red-700';
                document.getElementById('timesheet-reason-submit').innerHTML = '<i class="fa-solid fa-xmark mr-1"></i> Reject Timesheet';

                document.getElementById('modal-timesheet-reason').classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }

            function openTimesheetCorrectionModal(id) {
                const record = getRecordById(id);
                if (!record) return;

                currentReasonId = id;
                currentReasonAction = 'correction';

                document.getElementById('timesheet-reason-kicker').textContent = 'Request Correction';
                document.getElementById('timesheet-reason-title').textContent = `Request Correction for ${record.employee}`;
                document.getElementById('timesheet-reason-prompt').textContent = 'Enter the correction reason that should be sent back to the employee.';
                document.getElementById('timesheet-rejection-options').classList.add('hidden');
                document.getElementById('timesheet-other-reason-wrap').classList.add('hidden');
                document.getElementById('timesheet-correction-reason-wrap').classList.remove('hidden');
                document.getElementById('timesheet-correction-reason-input').value = record.correctionReason || '';
                document.getElementById('timesheet-reason-submit').className = 'font-button px-4 py-2.5 bg-amber-600 text-white rounded-xl text-sm font-semibold hover:bg-amber-700';
                document.getElementById('timesheet-reason-submit').innerHTML = '<i class="fa-solid fa-paper-plane mr-1"></i> Request Correction';

                document.getElementById('modal-timesheet-reason').classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }

            function toggleTimesheetOtherReason() {
                const select = document.getElementById('timesheet-rejection-select');
                const wrap = document.getElementById('timesheet-other-reason-wrap');
                if (!select || !wrap) return;
                wrap.classList.toggle('hidden', select.value !== 'Other');
                if (select.value !== 'Other') {
                    document.getElementById('timesheet-other-reason').value = '';
                }
            }

            async function submitTimesheetReason(event) {
                event.preventDefault();
                if (!currentReasonId || !currentReasonAction) return;

                let reason = '';

                if (currentReasonAction === 'reject') {
                    const select = document.getElementById('timesheet-rejection-select');
                    if (!select.value) {
                        showTimesheetToast('Please select a rejection reason.', 'error');
                        return;
                    }

                    if (select.value === 'Other') {
                        reason = document.getElementById('timesheet-other-reason').value.trim();
                        if (!reason) {
                            showTimesheetToast('Please enter the reason for Other.', 'error');
                            return;
                        }
                    } else {
                        reason = select.value;
                    }

                    if (await changeTimesheetStatus(currentReasonId, 'Rejected', reason)) {
                        closeTimesheetReasonModal();
                        renderTimesheetRecords(getFilteredTimesheets());
                        showTimesheetToast('Timesheet rejected successfully.', 'success');
                    }
                } else if (currentReasonAction === 'correction') {
                    reason = document.getElementById('timesheet-correction-reason-input').value.trim();
                    if (!reason) {
                        showTimesheetToast('Please enter a correction reason.', 'error');
                        return;
                    }

                    if (await changeTimesheetStatus(currentReasonId, 'Correction Requested', reason)) {
                        closeTimesheetReasonModal();
                        renderTimesheetRecords(getFilteredTimesheets());
                        showTimesheetToast('Correction request sent successfully.', 'success');
                    }
                }
            }

            function closeTimesheetReasonModal() {
                document.getElementById('modal-timesheet-reason')?.classList.add('hidden');
                currentReasonId = null;
                currentReasonAction = null;

                if (document.getElementById('modal-timesheet-review')?.classList.contains('hidden') &&
                    document.getElementById('modal-timesheet-history')?.classList.contains('hidden')) {
                    document.body.classList.remove('overflow-hidden');
                }
            }

            function openTimesheetHistory() {
                renderTimesheetHistory();
                document.getElementById('modal-timesheet-history').classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
                if (window.lucide) lucide.createIcons();
            }

            function renderTimesheetHistory() {
                const body = document.getElementById('timesheet-history-body');
                if (!body) return;

                if (!TIMESHEET_HISTORY.length) {
                    body.innerHTML = '<tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">No timesheet history available.</td></tr>';
                    return;
                }

                body.innerHTML = TIMESHEET_HISTORY.map(log => `
            <tr class="hover:bg-background">
                <td class="px-5 py-4 font-semibold text-primary">${escapeHtml(log.employee)}</td>
                <td class="px-5 py-4 text-gray-600">${escapeHtml(log.action)}</td>
                <td class="px-5 py-4">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs font-semibold text-gray-500">${escapeHtml(log.previousStatus)}</span>
                        <i class="fa-solid fa-arrow-right text-gray-400 text-xs"></i>
                        <span class="ts-status-badge ${statusClass(log.newStatus)}">${escapeHtml(log.newStatus)}</span>
                    </div>
                </td>
                <td class="px-5 py-4 text-gray-600">${escapeHtml(log.admin)}</td>
                <td class="px-5 py-4 text-gray-600 whitespace-nowrap">${escapeHtml(log.timestamp)}</td>
                <td class="px-5 py-4 text-gray-600">${escapeHtml(log.reason || 'â€”')}</td>
            </tr>
        `).join('');
            }

            function closeTimesheetHistory() {
                document.getElementById('modal-timesheet-history')?.classList.add('hidden');

                if (document.getElementById('modal-timesheet-review')?.classList.contains('hidden') &&
                    document.getElementById('modal-timesheet-reason')?.classList.contains('hidden')) {
                    document.body.classList.remove('overflow-hidden');
                }
            }

            function showTimesheetToast(message, type = 'info') {
                const containerId = 'timesheet-toast-container';
                let container = document.getElementById(containerId);

                if (!container) {
                    container = document.createElement('div');
                    container.id = containerId;
                    container.className = 'fixed bottom-6 right-6 z-[120] flex flex-col gap-3 pointer-events-none';
                    document.body.appendChild(container);
                }

                const toast = document.createElement('div');

                const styles = {
                    success: { bg: 'bg-emerald-600', icon: 'fa-circle-check' },
                    error: { bg: 'bg-red-600', icon: 'fa-triangle-exclamation' },
                    info: { bg: 'bg-primary', icon: 'fa-circle-info' }
                };
                const style = styles[type] || styles.info;

                toast.className = `${style.bg} text-white px-4 py-3 rounded-xl shadow-xl flex items-center gap-2 text-sm font-semibold translate-y-2 opacity-0 transition-all duration-300 pointer-events-auto max-w-sm`;
                toast.innerHTML = `<i class="fa-solid ${style.icon}"></i><span>${escapeHtml(message)}</span>`;
                container.appendChild(toast);

                requestAnimationFrame(() => {
                    toast.classList.remove('translate-y-2', 'opacity-0');
                });

                setTimeout(() => {
                    toast.classList.add('opacity-0', '-translate-y-2');
                    setTimeout(() => toast.remove(), 300);
                }, 3000);
            }

            async function initTimesheets() {

                TIMESHEET_DATA = [];
                TIMESHEET_HISTORY = [];

                try {

                    const response =
                        await fetch(
                            'api/timesheet-approvals.php',
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
                        !result.ok
                    ) {
                        throw new Error(
                            result.message ||
                            'Unable to load timesheet approvals.'
                        );
                    }

                    TIMESHEET_DATA =
                        Array.isArray(
                            result.timesheets
                        )
                            ? result.timesheets
                            : [];

                    TIMESHEET_HISTORY =
                        Array.isArray(
                            result.history
                        )
                            ? result.history
                            : [];

                } catch (error) {

                    showTimesheetToast(
                        error.message ||
                        'Unable to load timesheet approvals.',
                        'error'
                    );
                }

                populateTimesheetFilters();

                renderTimesheetRecords(
                    TIMESHEET_DATA
                );
            }

            window.filterTimesheets = filterTimesheets;
            window.clearTimesheetFilters = clearTimesheetFilters;
            window.openTimesheetReview = openTimesheetReview;
            window.closeTimesheetReview = closeTimesheetReview;
            window.approveTimesheet = approveTimesheet;
            window.approveAllTimesheets = approveAllTimesheets;
            window.openTimesheetRejectModal = openTimesheetRejectModal;
            window.openTimesheetCorrectionModal = openTimesheetCorrectionModal;
            window.toggleTimesheetOtherReason = toggleTimesheetOtherReason;
            window.submitTimesheetReason = submitTimesheetReason;
            window.closeTimesheetReasonModal = closeTimesheetReasonModal;
            window.openTimesheetHistory = openTimesheetHistory;
            window.closeTimesheetHistory = closeTimesheetHistory;
            window.openTimesheetCorrectionRequests = openTimesheetCorrectionRequests;
            window.viewTimesheetFromCorrectionRequests = viewTimesheetFromCorrectionRequests;
            window.closeTimesheetCorrectionRequests = closeTimesheetCorrectionRequests;
            window.approveFromReview = approveFromReview;
            window.rejectFromReview = rejectFromReview;
            window.requestCorrectionFromReview = requestCorrectionFromReview;

            document.addEventListener('DOMContentLoaded', initTimesheets);

            document.addEventListener('keydown', (event) => {
                if (event.key !== 'Escape') return;

                if (!document.getElementById('modal-timesheet-reason')?.classList.contains('hidden')) {
                    closeTimesheetReasonModal();
                    return;
                }
                if (!document.getElementById('modal-timesheet-history')?.classList.contains('hidden')) {
                    closeTimesheetHistory();
                    return;
                }
                if (!document.getElementById('modal-timesheet-correction-requests')?.classList.contains('hidden')) {
                    closeTimesheetCorrectionRequests();
                    return;
                }
                if (!document.getElementById('modal-timesheet-review')?.classList.contains('hidden')) {
                    closeTimesheetReview();
                }
            });
        })();
    </script>

    <script>
        (function () {
            async function updateAdminOverview() {
                try {
                    const response = await fetch(
                        'api/dashboard/admin.php',
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
                            'Unable to load admin dashboard statistics.'
                        );
                    }

                    const data = result.data || {};

                    const setText = (id, value) => {
                        const el = document.getElementById(id);
                        if (el) {
                            el.textContent = String(value ?? 0);
                        }
                    };

                    const clockedEl = document.getElementById(
                        'overview-clocked-in'
                    );

                    if (clockedEl) {
                        clockedEl.innerHTML =
                            `${Number(data.clocked_in || 0)} ` +
                            `<span class="text-lg text-gray-400 font-normal">` +
                            `/ ${Number(data.total_employees || 0)}</span>`;
                    }

                    setText(
                        'overview-active-tours',
                        data.active_tours || 0
                    );

                    setText(
                        'overview-late-week',
                        data.late_this_week || 0
                    );

                    setText(
                        'overview-on-leave',
                        data.on_leave_today || 0
                    );

                    setText(
                        'overview-pending-timesheets',
                        data.pending_timesheets || 0
                    );

                    setText(
                        'overview-pending-leaves',
                        data.pending_leaves || 0
                    );

                    /*
                     * Keep the Admin Live Attendance summary cards
                     * synchronized with the database.
                     */
                    const liveCards = document.querySelectorAll(
                        '#time-attendance .font-heading.text-3xl'
                    );

                    if (liveCards.length >= 4) {
                        const todayResponse = await fetch(
                            'api/attendance/today.php',
                            {
                                method: 'GET',
                                credentials: 'same-origin',
                                headers: {
                                    'Accept': 'application/json'
                                },
                                cache: 'no-store'
                            }
                        );

                        const todayResult = await todayResponse.json();

                        if (todayResponse.ok && todayResult.success) {
                            const records = Array.isArray(todayResult.data)
                                ? todayResult.data
                                : [];

                            const present = records.filter(
                                record =>
                                    String(record.status || '').toLowerCase() === 'present'
                            ).length;

                            const late = records.filter(
                                record =>
                                    String(record.status || '').toLowerCase() === 'late'
                            ).length;

                            const absent = records.filter(
                                record =>
                                    String(record.status || '').toLowerCase() === 'absent'
                            ).length;

                            const leave = records.filter(
                                record =>
                                    String(record.status || '').toLowerCase() === 'on_leave'
                            ).length;

                            liveCards[0].textContent = String(present);
                            liveCards[1].textContent = String(late);
                            liveCards[2].textContent = String(absent);
                            liveCards[3].textContent = String(leave);
                        }
                    }

                } catch (error) {

                    console.error(
                        'Admin dashboard statistics error:',
                        error
                    );
                }
            }

            window.updateAdminOverview = updateAdminOverview;

            document.addEventListener('DOMContentLoaded', updateAdminOverview);
            window.addEventListener('storage', function (event) {
                if (event.key && event.key.indexOf('ht_v1_') === 0) updateAdminOverview();
            });
            setInterval(updateAdminOverview, 1000);
        })();
    </script>


    <script>
        /* ================= ADMIN HEADER INTERACTIONS ================= */
        (function () {
            'use strict';
            const NOTIFICATION_PREF_KEY = 'workforce_admin_notifications_enabled';

            function escapeAdminHeaderHtml(value) {
                const div = document.createElement('div');
                div.textContent = value == null ? '' : String(value);
                return div.innerHTML;
            }

            function getEmployeeRows() {
                return Array.from(document.querySelectorAll('#employees-table-body tr[data-id]'));
            }

            function setSearchExpanded(expanded) {
                document.getElementById('admin-global-search')?.setAttribute('aria-expanded', expanded ? 'true' : 'false');
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
                    const haystack = [data.name, data.id, data.position, data.department, data.status]
                        .filter(Boolean).join(' ').toLowerCase();
                    return haystack.includes(query);
                }).slice(0, 8);

                if (!matches.length) {
                    results.innerHTML = `
                <div class="px-4 py-5 text-center">
                    <i class="fa-solid fa-user-slash text-gray-300 text-lg"></i>
                    <p class="text-sm font-semibold text-gray-500 mt-2">No employees found</p>
                    <p class="text-xs text-gray-400 mt-1">Try an employee name or ID.</p>
                </div>`;
                    results.classList.remove('hidden');
                    setSearchExpanded(true);
                    return;
                }

                results.innerHTML = matches.map((row, index) => {
                    const data = row.dataset;
                    const status = String(data.status || 'ACTIVE').toUpperCase();
                    const statusClass = status === 'LEAVE' ? 'bg-red-100 text-red-600' : 'bg-emerald-100 text-emerald-700';
                    return `
                <button type="button" class="admin-search-result w-full flex items-center gap-3 p-3 rounded-xl text-left"
                    data-result-index="${index}">
                    <span class="w-10 h-10 rounded-xl bg-background flex items-center justify-center text-primary shrink-0"><i class="fa-solid fa-user"></i></span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-semibold text-primary truncate">${escapeAdminHeaderHtml(data.name)}</span>
                        <span class="block text-xs text-gray-500 truncate">${escapeAdminHeaderHtml(data.id)} â€¢ ${escapeAdminHeaderHtml(data.position || 'Employee')}</span>
                    </span>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-bold ${statusClass}">${escapeAdminHeaderHtml(status)}</span>
                </button>`;
                }).join('');

                Array.from(results.querySelectorAll('.admin-search-result')).forEach((button, index) => {
                    button.addEventListener('click', () => {
                        const row = matches[index];
                        if (!row) return;
                        const employeeName = row.dataset.name || row.dataset.id || '';
                        closeAdminHeaderMenus();

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

            window.getAdminNotificationItems = async function () {
                const items = [];
                const pendingTimesheets = Number.parseInt(document.getElementById('ts-summary-pending')?.textContent || '0', 10) || 0;
                const pendingCorrections = document.querySelectorAll('#attendance-correction-table-body tr[data-status="PENDING"]').length;

                let pendingLeaves = 0;
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

                    if (response.ok && result.success) {
                        const requests = Array.isArray(result.data)
                            ? result.data
                            : [];

                        pendingLeaves = requests.filter(request =>
                            String(request.status || '').toLowerCase() === 'pending'
                        ).length;
                    }
                } catch (error) {
                    console.error(
                        'Admin notification leave request load failed:',
                        error
                    );
                }

                if (pendingTimesheets > 0) {
                    items.push({
                        icon: 'fa-file-signature', iconClass: 'bg-amber-50 text-amber-600',
                        title: `${pendingTimesheets} pending timesheet${pendingTimesheets === 1 ? '' : 's'}`,
                        detail: 'Timesheet approvals need review.',
                        action: () => typeof switchTab === 'function' && switchTab('timesheet')
                    });
                }

                if (pendingCorrections > 0) {
                    items.push({
                        icon: 'fa-user-pen', iconClass: 'bg-blue-50 text-primary',
                        title: `${pendingCorrections} attendance correction${pendingCorrections === 1 ? '' : 's'}`,
                        detail: 'Employee correction requests are waiting.',
                        action: () => {
                            if (typeof switchTab === 'function') switchTab('time-attendance');
                            setTimeout(() => document.getElementById('attendance-correction-wrapper')?.scrollIntoView({ behavior: 'smooth', block: 'start' }), 120);
                        }
                    });
                }

                if (pendingLeaves > 0) {
                    items.push({
                        icon: 'fa-plane-slash', iconClass: 'bg-red-50 text-red-600',
                        title: `${pendingLeaves} pending leave request${pendingLeaves === 1 ? '' : 's'}`,
                        detail: 'Leave requests need approval.',
                        action: () => typeof switchTab === 'function' && switchTab('leave')
                    });
                }

                return items;
            };

            window.refreshAdminNotifications = async function () {
                const list = document.getElementById('admin-notification-list');
                const summary = document.getElementById('admin-notification-summary');
                const dot = document.getElementById('admin-notification-dot');
                if (!list || !summary || !dot) return;

                const enabled = localStorage.getItem(NOTIFICATION_PREF_KEY) !== 'false';
                if (!enabled) {
                    dot.classList.add('admin-alerts-disabled');
                    summary.textContent = 'Notification alerts are disabled.';
                    list.innerHTML = `
                <div class="px-4 py-8 text-center">
                    <i class="fa-solid fa-bell-slash text-gray-300 text-2xl"></i>
                    <p class="text-sm font-semibold text-gray-500 mt-2">Alerts are off</p>
                    <p class="text-xs text-gray-400 mt-1">Turn them back on from Settings.</p>
                </div>`;
                    return;
                }

                const items = await getAdminNotificationItems();
                dot.classList.remove('admin-alerts-disabled');

                if (!items.length) {
                    summary.textContent = 'You are all caught up.';
                    dot.style.display = 'none';
                    list.innerHTML = `
                <div class="px-4 py-8 text-center">
                    <i class="fa-solid fa-circle-check text-emerald-500 text-2xl"></i>
                    <p class="text-sm font-semibold text-primary mt-2">No pending notifications</p>
                    <p class="text-xs text-gray-400 mt-1">There is nothing waiting for your review.</p>
                </div>`;
                    return;
                }

                dot.style.display = '';
                summary.textContent = `${items.length} item${items.length === 1 ? '' : 's'} need your attention.`;
                list.innerHTML = items.map((item, index) => `
            <button type="button" class="admin-notification-item w-full flex items-start gap-3 p-3 rounded-xl text-left hover:bg-background transition-colors"
                data-notification-index="${index}">
                <span class="w-9 h-9 rounded-xl ${item.iconClass} flex items-center justify-center shrink-0"><i class="fa-solid ${item.icon}"></i></span>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-semibold text-primary">${escapeAdminHeaderHtml(item.title)}</span>
                    <span class="block text-xs text-gray-500 mt-0.5">${escapeAdminHeaderHtml(item.detail)}</span>
                </span>
                <i class="fa-solid fa-chevron-right text-[10px] text-gray-300 mt-2"></i>
            </button>`).join('');

                Array.from(list.querySelectorAll('.admin-notification-item')).forEach((button, index) => {
                    button.addEventListener('click', () => {
                        const item = items[index];
                        closeAdminHeaderMenus();
                        if (item?.action) item.action();
                    });
                });
            };

            window.toggleAdminNotifications = function (event) {
                event?.stopPropagation();
                const panel = document.getElementById('admin-notification-panel');
                const button = document.getElementById('admin-notification-button');
                const settings = document.getElementById('admin-settings-panel');
                const results = document.getElementById('admin-global-search-results');
                if (!panel || !button) return;

                const open = panel.classList.contains('hidden');
                settings?.classList.add('hidden');
                results?.classList.add('hidden');
                panel.classList.toggle('hidden', !open);
                button.setAttribute('aria-expanded', open ? 'true' : 'false');
                if (open) refreshAdminNotifications();
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

            window.toggleAdminSettings = function (event) {
                event?.stopPropagation();
                const panel = document.getElementById('admin-settings-panel');
                const button = document.getElementById('admin-settings-button');
                const notifications = document.getElementById('admin-notification-panel');
                const results = document.getElementById('admin-global-search-results');
                if (!panel || !button) return;

                const open = panel.classList.contains('hidden');
                notifications?.classList.add('hidden');
                results?.classList.add('hidden');
                panel.classList.toggle('hidden', !open);
                button.setAttribute('aria-expanded', open ? 'true' : 'false');
                if (open) syncNotificationSettingUI();
            };

            window.openSettingsEmployees = function () {
                closeAdminHeaderMenus();
                if (typeof switchTab === 'function') switchTab('employees');
                document.getElementById('employee-search')?.focus();
            };

            window.toggleAdminNotificationAlerts = function (event) {
                event?.stopPropagation();
                const enabled = localStorage.getItem(NOTIFICATION_PREF_KEY) !== 'false';
                localStorage.setItem(NOTIFICATION_PREF_KEY, enabled ? 'false' : 'true');
                syncNotificationSettingUI();
                refreshAdminNotifications();
                if (typeof showToast === 'function') showToast(`Notification alerts ${enabled ? 'disabled' : 'enabled'}.`, 'info');
            };

            document.addEventListener('click', (event) => {
                const searchWrap = document.getElementById('admin-global-search-wrap');
                const notificationPanel = document.getElementById('admin-notification-panel');
                const notificationButton = document.getElementById('admin-notification-button');
                const settingsPanel = document.getElementById('admin-settings-panel');
                const settingsButton = document.getElementById('admin-settings-button');

                if (!searchWrap?.contains(event.target) &&
                    !notificationPanel?.contains(event.target) &&
                    !notificationButton?.contains(event.target) &&
                    !settingsPanel?.contains(event.target) &&
                    !settingsButton?.contains(event.target)) {
                    closeAdminHeaderMenus();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') closeAdminHeaderMenus();
            });

            document.addEventListener('DOMContentLoaded', () => {
                captureExistingMonthSchedules();
                captureExistingWeekSchedules();
                renderScheduleWeek(scheduleWeekStart);
                syncNotificationSettingUI();
                refreshAdminNotifications();
                document.getElementById('admin-global-search')?.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        event.currentTarget.value = '';
                        closeAdminHeaderMenus();
                    }
                });

                // Click any day in the monthly calendar to open its day-information modal.
                document.getElementById('monthRosterView')?.addEventListener('click', (event) => {
                    const dayCell = event.target.closest('[data-date]');
                    if (!dayCell || !document.getElementById('monthRosterView')?.contains(dayCell)) return;
                    const isoDate = dayCell.getAttribute('data-date');
                    if (isoDate) openScheduleDayModal(isoDate, 'month');
                });

                // Click any day/cell in the weekly roster to open the same day-information modal.
                // This works for scheduled, off, and empty days and is independent of the employee row.
                document.getElementById('weekRosterView')?.addEventListener('click', (event) => {
                    const dayCell = event.target.closest('[data-date]');
                    if (!dayCell || !document.getElementById('weekRosterView')?.contains(dayCell)) return;
                    const isoDate = dayCell.getAttribute('data-date');
                    if (isoDate) openScheduleDayModal(isoDate, 'week');
                });
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') closeScheduleDayModal();
            });

            window.addEventListener('storage', refreshAdminNotifications);
        })();
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            loadEmployeesFromDatabase();
        });
    </script>
    <script>
        document.addEventListener(
            'DOMContentLoaded',
            function () {
                loadAttendanceCorrectionsFromDatabase();
            }
        );
    </script>
    <script>
        document.addEventListener(
            'DOMContentLoaded',
            function () {
                loadTodayAttendanceFromDatabase();
            }
        );
    </script>

    <script>
        document.addEventListener(
            'DOMContentLoaded',
            function () {
                loadAttendanceRecordsFromDatabase();
            }
        );
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const period =
                document.getElementById('attendancePeriodSelect');

            const date =
                document.getElementById('attendancePeriodDate');

            if (period) {
                period.addEventListener(
                    'change',
                    loadAttendanceRecordsFromDatabase
                );
            }

            if (date) {
                date.addEventListener(
                    'change',
                    loadAttendanceRecordsFromDatabase
                );
            }

        });
    </script>

    <script>
        async function loadAdminOvertimeFromDatabase() {

            const tableBody =
                document.getElementById(
                    'overtime-table-body'
                );

            if (!tableBody) {
                return;
            }

            try {

                const response = await fetch(
                    'api/overtime/admin.php',
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
                        'Unable to load overtime records.'
                    );
                }

                const summary =
                    result.summary || {};

                const pendingEl =
                    document.getElementById(
                        'overtime-pending-count'
                    );

                const approvedEl =
                    document.getElementById(
                        'overtime-approved-count'
                    );

                const totalEl =
                    document.getElementById(
                        'overtime-total-hours'
                    );

                if (pendingEl) {
                    pendingEl.textContent =
                        String(
                            summary.pending || 0
                        );
                }

                if (approvedEl) {
                    approvedEl.textContent =
                        String(
                            summary.approved || 0
                        );
                }

                if (totalEl) {
                    totalEl.textContent =
                        `${Number(
                            summary.total_hours || 0
                        ).toFixed(2)} hrs`;
                }

                const records =
                    Array.isArray(result.data)
                        ? result.data
                        : [];

                tableBody.innerHTML = '';

                if (!records.length) {

                    tableBody.innerHTML = `
                    <tr>
                        <td colspan="5"
                            class="px-5 py-8 text-center text-gray-400">
                            No overtime records found.
                        </td>
                    </tr>
                `;

                    return;
                }

                records.forEach(record => {

                    const row =
                        document.createElement('tr');

                    const statusClass =
                        record.status === 'APPROVED'
                            ? 'bg-emerald-100 text-emerald-700'
                            : record.status === 'REJECTED'
                                ? 'bg-red-100 text-red-600'
                                : 'bg-amber-100 text-amber-700';

                    const dateText =
                        new Date(
                            `${record.date}T00:00:00`
                        ).toLocaleDateString(
                            'en-US',
                            {
                                month: 'long',
                                day: 'numeric',
                                year: 'numeric'
                            }
                        );

                    row.innerHTML = `
                    <td class="px-5 py-4 font-semibold text-primary">
                        ${escapeHtml(record.employee)}
                    </td>

                    <td class="px-5 py-4 text-gray-600">
                        ${escapeHtml(dateText)}
                    </td>

                    <td class="px-5 py-4 text-gray-600">
                        ${Number(record.hours || 0).toFixed(2)} hours
                    </td>

                    <td class="px-5 py-4 text-gray-600">
                        ${escapeHtml(record.reason || 'â€”')}
                    </td>

                    <td class="px-5 py-4">
                        <span class="inline-flex items-center ${statusClass} text-xs font-semibold px-3 py-1 rounded-full">
                            ${escapeHtml(record.status)}
                        </span>
                    </td>
                `;

                    tableBody.appendChild(row);
                });

            } catch (error) {

                console.error(
                    'Admin overtime loading error:',
                    error
                );

                tableBody.innerHTML = `
                <tr>
                    <td colspan="5"
                        class="px-5 py-8 text-center text-red-500">
                        Unable to load overtime records.
                    </td>
                </tr>
            `;
            }
        }

        document.addEventListener(
            'DOMContentLoaded',
            function () {
                loadAdminOvertimeFromDatabase();
            }
        );
    </script>

    <script>
        async function loadMasterRosterFromDatabase() {

            const body =
                document.getElementById(
                    'master-roster-body'
                );

            if (!body) {
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
                        'Unable to load Master Roster.'
                    );
                }

                body.innerHTML = '';

                const employees =
                    (Array.isArray(result.data)
                        ? result.data
                        : []
                    ).filter(employee => {
                        const accountRole = String(employee.accountRole || '')
                            .trim()
                            .toLowerCase();

                        return accountRole === 'manager' || accountRole === 'employee';
                    });

                Object.keys(
                    SCHEDULE_EMPLOYEE_ROLES
                ).forEach(key => {
                    delete SCHEDULE_EMPLOYEE_ROLES[key];
                });

                if (!employees.length) {

                    body.innerHTML = `
                    <tr>
                        <td colspan="8"
                            class="p-8 text-center text-gray-400">
                            No employees or managers found.
                        </td>
                    </tr>
                `;

                    return;
                }

                employees.forEach(employee => {

                    const displayName =
                        String(
                            employee.name || ''
                        ).trim();

                    /*
                     * Keep the existing role filter working.
                     */
                    let filterRole = 'Office Staff';

                    const position =
                        String(
                            employee.role || ''
                        ).toLowerCase();

                    if (
                        position.includes('tour') ||
                        position.includes('guide')
                    ) {
                        filterRole = 'Tour Guides';
                    } else if (
                        position.includes('driver')
                    ) {
                        filterRole = 'Drivers';
                    }

                    SCHEDULE_EMPLOYEE_ROLES[
                        displayName
                    ] = filterRole;

                    const row =
                        document.createElement('tr');

                    row.dataset.emp =
                        displayName;

                    row.dataset.employeeId =
                        employee.id;

                    row.dataset.accountRole =
                        employee.accountRole || '';

                    row.className =
                        'hover:bg-background transition-all-300';

                    const avatarUrl =
                        String(employee.avatar_url || '').trim();

                    const avatarHtml = avatarUrl
                        ? `
        <img
            src="${escapeHtml(avatarUrl)}"
            alt="Profile photo"
            class="w-7 h-7 rounded-full object-cover"
            onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');">

        <i
            class="fa-solid fa-user hidden w-7 h-7 rounded-full bg-accent/50 text-accent items-center justify-center text-xs">
        </i>
      `
                        : `
        <i
            class="fa-solid fa-user w-7 h-7 rounded-full bg-accent/50 text-accent flex items-center justify-center text-xs">
        </i>
      `;

                    row.innerHTML = `
                    <td class="p-3 border-r border-border bg-card sticky left-0 z-10 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">
                       <div class="flex items-center space-x-2">

    ${avatarHtml}

    <div>
        <span class="block font-medium text-primary">
            ${escapeHtml(displayName)}
        </span>

        <span class="inline-flex text-xs font-semibold text-gray-500">
            ${escapeHtml(employee.role || 'Employee')}
        </span>

        <p class="text-[10px] text-gray-400 mt-1">
            ${escapeHtml(employee.id)}
        </p>
    </div>

</div>
                    </td>

                    <td class="p-2 border-r border-border">
                        <div class="text-xs text-gray-400 text-center">
                            OFF
                        </div>
                    </td>

                    <td class="p-2 border-r border-border">
                        <div class="text-xs text-gray-400 text-center">
                            OFF
                        </div>
                    </td>

                    <td class="p-2 border-r border-border">
                        <div class="text-xs text-gray-400 text-center">
                            OFF
                        </div>
                    </td>

                    <td class="p-2 border-r border-border">
                        <div class="text-xs text-gray-400 text-center">
                            OFF
                        </div>
                    </td>

                    <td class="p-2 border-r border-border">
                        <div class="text-xs text-gray-400 text-center">
                            OFF
                        </div>
                    </td>

                    <td class="p-2 bg-background">
                        <div class="text-xs text-gray-400 text-center">
                            OFF
                        </div>
                    </td>

                    <td class="p-2 bg-background">
                        <div class="text-xs text-gray-400 text-center">
                            OFF
                        </div>
                    </td>
                `;

                    body.appendChild(row);
                });

                filterScheduleByRole();

            } catch (error) {

                console.error(
                    'Master Roster loading error:',
                    error
                );

                body.innerHTML = `
                <tr>
                    <td colspan="8"
                        class="p-8 text-center text-red-500">
                        Unable to load Master Roster.
                    </td>
                </tr>
            `;
            }
        }

        document.addEventListener(
            'DOMContentLoaded',
            function () {
                loadMasterRosterFromDatabase();
            }
        );
    </script>
    <script>
        document.addEventListener(
            'visibilitychange',
            function () {
                if (
                    !document.hidden &&
                    document.getElementById('leave')?.classList.contains('active')
                ) {
                    renderLeaves();
                }
            }
        );

        window.addEventListener(
            'focus',
            function () {
                if (
                    document.getElementById('leave')?.classList.contains('active')
                ) {
                    renderLeaves();
                }
            }
        );
    </script>

    <div id="modal-assign-manager"
        class="hidden fixed inset-0 bg-primary/80 backdrop-blur-sm flex items-center justify-center z-[70] p-4"
        onclick="if(event.target===this) closeAssignManagerModal()">

        <div class="bg-card rounded-2xl shadow-2xl border border-border max-w-lg w-full overflow-hidden">

            <div class="px-6 py-5 border-b border-border flex items-center justify-between bg-background">

                <div>
                    <h3 class="font-heading font-bold text-primary text-lg">
                        Assign Employee to Manager
                    </h3>

                    <p class="text-xs text-gray-500 mt-1">
                        Assign an employee to an active Manager.
                    </p>
                </div>

                <button type="button" onclick="closeAssignManagerModal()"
                    class="p-1.5 rounded-lg hover:bg-gray-200 text-gray-500">

                    <i data-lucide="x" class="w-5 h-5">
                    </i>

                </button>

            </div>

            <form id="assign-manager-form" class="p-6 space-y-4">

                <div>

                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">

                        Employee

                    </label>

                    <select id="assign-manager-employee" required
                        class="w-full border border-border rounded-xl px-3 py-2.5 text-sm bg-background text-primary focus:outline-none focus:ring-2 focus:ring-accent">

                        <option value="">
                            Select employee
                        </option>

                    </select>

                </div>

                <div>

                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">

                        Manager

                    </label>

                    <select id="assign-manager-manager" required
                        class="w-full border border-border rounded-xl px-3 py-2.5 text-sm bg-background text-primary focus:outline-none focus:ring-2 focus:ring-accent">

                        <option value="">
                            Select manager
                        </option>

                    </select>

                </div>

                <div id="assign-manager-message" class="hidden text-sm rounded-xl px-4 py-3">
                </div>

                <div class="flex gap-3 pt-2">

                    <button type="button" onclick="closeAssignManagerModal()"
                        class="flex-1 py-3 bg-error hover:bg-red-600 text-white font-button font-semibold rounded-xl">

                        Cancel

                    </button>

                    <button id="assign-manager-submit" type="submit"
                        class="flex-1 py-3 bg-secondary hover:bg-[#E08A3B] text-white font-button font-semibold rounded-xl">

                        Assign Employee

                    </button>

                </div>

            </form>

        </div>

    </div>



    <!-- MODAL: PROMOTE EMPLOYEE -->
    <div id="modal-promote-employee"
        class="hidden fixed inset-0 bg-primary/80 backdrop-blur-sm flex items-center justify-center z-[80] p-4"
        onclick="if(event.target===this) closePromoteEmployeeModal()">

        <div class="bg-card rounded-2xl shadow-2xl border border-border max-w-lg w-full overflow-hidden">
            <div class="px-6 py-5 border-b border-border flex items-center justify-between bg-background">
                <h3 class="font-heading font-bold text-primary text-lg">Promote Employee</h3>
                <button type="button" onclick="closePromoteEmployeeModal()"
                    class="p-1.5 rounded-lg hover:bg-gray-200 text-gray-500" aria-label="Close promote employee modal">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="min-h-[220px]"></div>

            <div class="px-6 pb-6">
                <button type="button" onclick="closePromoteEmployeeModal()"
                    class="w-full py-3 bg-gray-200 hover:bg-gray-300 text-primary font-button font-semibold rounded-xl">
                    Back
                </button>
            </div>
        </div>
    </div>

</body>

</html>