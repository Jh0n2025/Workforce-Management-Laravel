<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="/scripts/api-fetch.js"></script>
    <title>Holiday Travelers - Employee Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@500;700&display=swap"
        rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- PDF Export Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.4/jspdf.plugin.autotable.min.js"></script>

    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        heading: ['Poppins', 'sans-serif'],
                        button: ['Poppins', 'sans-serif'],
                        body: ['Inter', 'sans-serif']
                    },
                    colors: {
                        primary: '#163B6D',
                        secondary: '#F59B45',
                        accent: '#6FA9E6',
                        background: '#F8FAFC',
                        card: '#FFFFFF',
                        border: '#E5E7EB',
                        success: '#22C55E',
                        warning: '#FBBF24',
                        error: '#EF4444'
                    }
                }
            }
        }
    </script>
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
    </style>
</head>

<body class="bg-background text-primary font-body antialiased flex h-screen overflow-hidden">

    <aside class="w-64 bg-primary text-gray-300 flex-shrink-0 hidden md:flex flex-col shadow-xl z-20 pb-6">
        <!-- Brand -->
        <div class="h-16 flex items-center px-6 border-b border-white/10 bg-primary">
            <img src="assets/images/Company_logo.png" alt="Company Logo" class="mr-3 object-contain"
                style="height: 72px; width: auto;">
            <span class="font-bold text-lg text-white tracking-tight">Holiday Travelers Inc.</span>
        </div>

        <!---------------------- Navigation Links ------------------------->
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">

            <a href="#"
                class="nav-btn active flex items-center px-3 py-2.5 text-sm font-medium rounded-2xl bg-secondary text-white"
                data-target="dashboard">
                <i class="fa-solid fa-user-clock w-6 text-center mr-2 text-[17px]"></i> Attendance
            </a>
            <a href="#"
                class="nav-btn flex items-center px-3 py-2.5 text-sm font-medium rounded-2xl text-gray-400 hover:bg-white/10 hover:text-white transition-colors"
                data-target="time-attendance">
                <span class="inline-flex items-center justify-center gap-0.5 w-6 mr-2 text-center text-[15px]">
                    <i class="fa-solid fa-calendar-days text-[13px]"></i>
                    <i class="fa-solid fa-users text-[10px] -ml-1 mt-2"></i>
                </span> Team Schedule
            </a>
            <a href="#"
                class="nav-btn flex items-center px-3 py-2.5 text-sm font-medium rounded-2xl text-gray-400 hover:bg-white/10 hover:text-white transition-colors"
                data-target="schedule">
                <span class="relative inline-flex items-center justify-center w-6 h-5 mr-2 text-center text-[17px]">
                    <i class="fa-solid fa-file-lines"></i>
                    <i
                        class="fa-solid fa-clock absolute bottom-[-2px] right-[-3px] text-[10px] text-tomato bg-inherit rounded-full p-[1px]"></i>
                </span> Timesheets
            </a>
            <a href="#"
                class="nav-btn flex items-center px-3 py-2.5 text-sm font-medium rounded-2xl text-gray-400 hover:bg-white/10 hover:text-white transition-colors"
                data-target="leave">
                <i class="fa-solid fa-plane-slash w-6 text-center mr-2"></i> Leave Management
            </a>
            <a href="#"
                class="nav-btn flex items-center px-3 py-2.5 text-sm font-medium rounded-2xl text-gray-400 hover:bg-white/10 hover:text-white transition-colors"
                data-target="analytics">
                <i class="fa-solid fa-chart-pie w-6 text-center mr-2"></i> Workforce Analytics
            </a>
        </nav>
    </aside>

    <!---------------------- MAIN CONTENT ---------------------->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        <!----------------------- Top Bar (Desktop)------------------------>
        <header
            class="bg-card border-b border-border items-center justify-between px-8 h-16 hidden md:flex z-10 shadow-sm">
            <h1 class="font-heading text-xl font-bold text-primary flex items-center" id="page-title">
                Attendance
            </h1>

            <div class="flex items-center space-x-6">
                <!------------ Search ------------->
                <div class="relative w-64" id="searchContainer">
                    <i class="fa-solid fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input id="searchInput" type="text" placeholder="Search staff, tours..." autocomplete="off"
                        class="pl-10 pr-4 py-2 w-full border border-border rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-accent bg-background"
                        aria-label="Search staff and manager sections">

                    <!-- Functional Search Results -->
                    <ul id="suggestionsBox"
                        class="absolute left-0 top-full mt-2 w-full bg-card border border-border rounded-xl shadow-xl hidden z-50 max-h-80 overflow-y-auto">
                    </ul>
                </div>

                <!-------------NOTIFICATION BELL------------------------------>
                <div class="relative inline-block" id="notification-container">
                    <button id="notification-btn" type="button" aria-expanded="false" aria-controls="notification-menu"
                        class="font-button relative p-2 text-gray-400 hover:text-primary transition-colors focus:outline-none">
                        <i class="fa-solid fa-bell text-xl"></i>
                        <span id="notification-badge"
                            class="absolute top-1.5 right-1.5 block h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-white hidden"></span>
                    </button>

                    <!--------- Notification Dropdown Panel --------->
                    <div id="notification-menu"
                        class="hidden absolute right-0 mt-2 w-80 sm:w-96 rounded-xl border border-border bg-card shadow-xl ring-1 ring-black/5 z-50 overflow-hidden transition-all">

                        <div class="flex items-center justify-between px-4 py-3 border-b border-border bg-card">
                            <h3 class="text-sm font-semibold text-primary">Notifications</h3>
                            <button id="mark-all-read-btn" type="button"
                                class="text-xs font-medium text-accent hover:text-primary transition-colors">
                                Mark all as read
                            </button>
                        </div>

                        <div id="notification-list" class="max-h-[28rem] overflow-y-auto">
                            <button type="button" data-notification-action="analytics"
                                class="notification-item w-full flex items-start gap-4 px-4 py-3 bg-blue-50/50 hover:bg-background border-b border-border transition-colors text-left">
                                <div class="flex-shrink-0 mt-1">
                                    <div
                                        class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                                        <i class="fa-solid fa-chart-line text-xs"></i>
                                    </div>
                                </div>
                                <div class="flex-1 space-y-1">
                                    <p class="text-sm text-primary font-medium">Workforce analytics</p>
                                    <p class="text-xs text-gray-500 line-clamp-2">Open the team attendance analytics and
                                        review current workforce activity.</p>
                                    <p class="text-[11px] font-medium text-accent">Available now</p>
                                </div>
                                <div class="flex-shrink-0 mt-2 notification-dot">
                                    <span class="block h-2 w-2 rounded-full bg-blue-600"></span>
                                </div>
                            </button>

                            <button type="button" data-notification-action="leave"
                                class="notification-item w-full flex items-start gap-4 px-4 py-3 hover:bg-background border-b border-border transition-colors text-left">
                                <div class="flex-shrink-0 mt-1">
                                    <div
                                        class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                                        <i class="fa-solid fa-plane-slash text-xs"></i>
                                    </div>
                                </div>
                                <div class="flex-1 space-y-1">
                                    <p class="text-sm text-primary">Leave requests</p>
                                    <p id="notification-leave-text" class="text-xs text-gray-500 line-clamp-2">Open the
                                        team leave requests to review pending requests.</p>
                                    <p class="text-[11px] text-gray-400">Open now</p>
                                </div>
                            </button>

                            <button type="button" data-notification-action="schedule"
                                class="notification-item w-full flex items-start gap-4 px-4 py-3 hover:bg-background border-b border-border transition-colors text-left">
                                <div class="flex-shrink-0 mt-1">
                                    <div
                                        class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100 text-green-600">
                                        <i class="fa-solid fa-calendar-check text-xs"></i>
                                    </div>
                                </div>
                                <div class="flex-1 space-y-1">
                                    <p class="text-sm text-primary">Team schedule</p>
                                    <p id="notification-schedule-text" class="text-xs text-gray-500 line-clamp-2">Open
                                        the team schedule and review assignments or shift requests.</p>
                                    <p class="text-[11px] text-gray-400">Open now</p>
                                </div>
                            </button>

                            <div id="notification-empty" class="hidden px-4 py-8 text-center">
                                <i class="fa-regular fa-bell-slash text-2xl text-gray-300"></i>
                                <p class="mt-2 text-sm font-medium text-gray-600">You're all caught up.</p>
                                <p class="text-xs text-gray-400">No unread notifications.</p>
                            </div>
                        </div>

                        <div class="border-t border-border bg-background">
                            <button id="view-all-activity-btn" type="button"
                                class="block w-full px-4 py-2.5 text-center text-sm font-medium text-gray-500 hover:text-primary hover:bg-background transition-colors">
                                View All Activity
                            </button>
                        </div>
                    </div>
                </div>

                <!---------END----------------->
                <button id="profile-btn"
                    class="group inline-flex items-center gap-3 rounded-full border border-border bg-card px-4 py-2 shadow-sm transition hover:bg-background">
                    <img id="manager-profile-avatar" src="assets/images/employee.png" alt="Manager Avatar"
                        class="h-10 w-10 rounded-full object-cover border-2 border-accent">
                    <div class="text-left">
                        <p class="text-sm font-semibold text-primary" data-session="name">Manager Name</p>
                        <p class="text-xs text-gray-500" data-session="role">Role</p>
                    </div>
                    <i class="fa-solid fa-chevron-down text-gray-400 group-hover:text-primary"></i>
                </button>
            </div>
        </header>

        <div id="profile-menu"
            class="hidden absolute right-8 top-20 z-20 w-56 rounded-xl border border-border bg-card shadow-xl ring-1 ring-black/5 transition-all">

            <!-- User Info Header -->
            <div class="px-4 py-3 border-b border-border">
                <p class="text-sm font-semibold text-primary truncate" data-session="name">Manager Name</p>
                <p class="text-xs text-gray-400 truncate" data-session="role">Role</p>
            </div>

            <!--------- PROFILE HEADERDROPDOWN ------------------>
            <div class="p-1.5">
                <a href="/manager/profile"
                    class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-gray-600 hover:bg-background hover:text-primary transition-colors">
                    <i data-lucide="user" class="w-4 h-4 text-gray-400"></i>
                    View Profile
                </a>
                <a href="/settings"
                    class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-gray-600 hover:bg-background hover:text-primary transition-colors">
                    <i data-lucide="settings" class="w-4 h-4 text-gray-400"></i>
                    Account Settings
                </a>
            </div>

            <!-- Logout Action -->
            <div class="p-1.5 border-t border-border">
                <a href="login.html" onclick="handleManagerLogout(event)"
                    class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 transition-colors text-left">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    Logout System
                </a>
            </div>
        </div>

        <main class="flex-1 p-6 overflow-y-auto bg-background">

            <!-- ================= ATTENDANCE TAB ================= -->
            <div id="dashboard" class="tab-content active p-6 md:px-8 md:py-6 overflow-y-auto">
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 md:gap-6 w-full max-w-[1400px]">
                    <div class="bg-card border border-border rounded-2xl p-5 md:p-6 shadow-sm">
                        <div class="flex justify-between items-start mb-6">
                            <div
                                class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600">
                                <i class="fa-solid fa-user-check text-xl"></i>
                            </div>
                            <span
                                class="bg-[#e6f9ed] text-[#00a651] text-xs font-semibold px-2.5 py-1 rounded-md">Live</span>
                        </div>
                        <div>
                            <div class="flex items-baseline gap-1.5">
                                <span id="present-count"
                                    class="text-[32px] leading-none font-bold text-primary">0</span>
                                <span id="team-count" class="text-xl font-medium text-gray-400">/ 0</span>
                            </div>
                            <p class="text-[13px] text-gray-500 font-medium mt-2">Present Today</p>
                        </div>
                    </div>

                    <div class="bg-card border border-border rounded-2xl p-5 md:p-6 shadow-sm">
                        <div class="flex justify-between items-start mb-6">
                            <div
                                class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center text-purple-600">
                                <i class="fa-solid fa-clock text-xl"></i>
                            </div>
                        </div>
                        <div>
                            <span id="late-count" class="text-[32px] leading-none font-bold text-primary">0</span>
                            <p class="text-[13px] text-gray-500 font-medium mt-2">Late Arrivals</p>
                        </div>
                    </div>

                    <div class="bg-card border border-border rounded-2xl p-5 md:p-6 shadow-sm">
                        <div class="flex justify-between items-start mb-6">
                            <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center text-red-500">
                                <i class="fa-solid fa-user-xmark text-xl"></i>
                            </div>
                        </div>
                        <div>
                            <span id="absent-count" class="text-[32px] leading-none font-bold text-primary">0</span>
                            <p class="text-[13px] text-gray-500 font-medium mt-2">Absent Today</p>
                        </div>
                    </div>

                    <div class="bg-card border border-border rounded-2xl p-5 md:p-6 shadow-sm">
                        <div class="flex justify-between items-start mb-6">
                            <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center text-rose-500">
                                <i class="fa-solid fa-plane-slash text-xl"></i>
                            </div>
                        </div>
                        <div>
                            <span id="leave-count" class="text-[32px] leading-none font-bold text-primary">0</span>
                            <p class="text-[13px] text-gray-500 font-medium mt-2">On Leave Today</p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 bg-card border border-border rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-5">
                        <h2 class="text-lg font-bold text-primary">Employee Attendance</h2>
                        <span id="attendance-date-label"
                            class="text-xs font-semibold px-2.5 py-1 rounded-md bg-background text-gray-500">Today</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b-2 border-border text-primary text-sm font-mono">
                                    <th class="pb-3 font-semibold pr-6">Employee</th>
                                    <th class="pb-3 font-semibold pr-6">Schedule</th>
                                    <th class="pb-3 font-semibold pr-6">Time In</th>
                                    <th class="pb-3 font-semibold pr-6">Time Out</th>
                                    <th class="pb-3 font-semibold">Status</th>
                                </tr>
                            </thead>
                            <tbody id="manager-attendance-body"
                                class="font-mono text-sm text-primary divide-y divide-border"></tbody>
                        </table>
                    </div>

                    <div class="mt-4 pt-4 border-t border-border flex justify-center">
                        <button onclick="switchTab('time-attendance')"
                            class="text-sm font-semibold text-primary hover:text-indigo-600 flex items-center gap-1.5 transition-colors">
                            View team schedule
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ================= TEAM SCHEDULE TAB ================= -->
            <div id="time-attendance" class="tab-content max-w-7xl mx-auto space-y-6 p-6 md:px-8 md:py-6">
                <div class="bg-card border border-border rounded-2xl p-6 shadow-sm">
                    <div
                        class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6 pb-5 border-b border-border">
                        <div>
                            <div class="flex items-center gap-3">
                                <h2 class="text-lg font-bold text-primary">Team Schedule</h2>
                                <span id="schedule-week-label"
                                    class="text-xs font-mono font-medium px-2.5 py-1 rounded-md bg-background text-gray-600">Team
                                    week</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Weekly shift assignments and team availability
                                overview</p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <button onclick="createSchedule()"
                                class="px-3.5 py-2 text-xs font-mono font-semibold text-white bg-primary hover:bg-slate-800 rounded-lg shadow-sm transition-colors flex items-center gap-1.5">
                                <i class="fa-solid fa-plus text-[11px]"></i> Create Schedule
                            </button>
                            <button onclick="assignShift()"
                                class="px-3.5 py-2 text-xs font-mono font-semibold text-gray-600 bg-background hover:bg-border rounded-lg transition-colors">
                                Assign Shift
                            </button>
                            <button onclick="showShiftRequests()"
                                class="px-3.5 py-2 text-xs font-mono font-semibold text-primary bg-accent/10 hover:bg-accent/20 border border-accent/30 rounded-lg transition-colors flex items-center gap-1.5">
                                Shift Requests
                                <span id="shift-request-badge" class="w-2 h-2 rounded-full bg-accent"></span>
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse font-mono text-sm">
                            <thead>
                                <tr class="border-b-2 border-border text-primary">
                                    <th class="pb-3 font-semibold pr-6">Employee</th>
                                    <th class="pb-3 font-semibold px-4 text-center">Mon</th>
                                    <th class="pb-3 font-semibold px-4 text-center">Tue</th>
                                    <th class="pb-3 font-semibold px-4 text-center">Wed</th>
                                    <th class="pb-3 font-semibold px-4 text-center">Thu</th>
                                    <th class="pb-3 font-semibold px-4 text-center">Fri</th>
                                </tr>
                            </thead>
                            <tbody id="manager-schedule-body" class="divide-y divide-border text-primary"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ================= TIMESHEETS TAB ================= -->
            <div id="schedule" class="tab-content max-w-7xl mx-auto space-y-6 p-6 md:px-8 md:py-6">

                <!-- Header -->
                <div
                    class="bg-card border border-border rounded-2xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-3">
                            <h2 class="text-lg font-bold text-primary">Timesheets</h2>
                            <span id="timesheet-team-label"
                                class="text-xs font-semibold px-2.5 py-1 rounded-md bg-background text-gray-500">Team</span>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">Review, approve, and flag timesheets for the employees on
                            your team.</p>
                    </div>
                    <button id="timesheet-export-btn" onclick="exportTimesheetsPDF()"
                        class="px-3.5 py-2 text-xs font-mono font-semibold text-white bg-primary hover:bg-slate-800 rounded-lg transition-colors flex items-center justify-center gap-1.5 self-start md:self-auto shadow-sm">
                        <i class="fa-solid fa-file-pdf text-[13px]"></i> Export PDF
                    </button>
                </div>

                <!-- Summary cards -->
                <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
                    <div class="bg-card border border-border rounded-2xl p-5 shadow-sm">
                        <div class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center text-accent mb-5">
                            <i class="fa-solid fa-clock text-lg"></i>
                        </div>
                        <div class="flex items-baseline gap-1.5">
                            <span id="timesheet-total-hours"
                                class="text-[28px] leading-none font-bold text-primary">0</span>
                            <span class="text-sm font-medium text-gray-400">hrs</span>
                        </div>
                        <p class="text-[13px] text-gray-500 font-medium mt-2">Total Hours Logged</p>
                    </div>

                    <div class="bg-card border border-border rounded-2xl p-5 shadow-sm">
                        <div
                            class="w-11 h-11 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600 mb-5">
                            <i class="fa-solid fa-circle-check text-lg"></i>
                        </div>
                        <span id="timesheet-completed-shifts"
                            class="text-[28px] leading-none font-bold text-primary">0</span>
                        <p class="text-[13px] text-gray-500 font-medium mt-2">Completed Shifts</p>
                    </div>

                    <div class="bg-card border border-border rounded-2xl p-5 shadow-sm">
                        <div class="w-11 h-11 rounded-xl bg-sky-100 flex items-center justify-center text-sky-600 mb-5">
                            <i class="fa-solid fa-hourglass-half text-lg"></i>
                        </div>
                        <span id="timesheet-open-shifts"
                            class="text-[28px] leading-none font-bold text-primary">0</span>
                        <p class="text-[13px] text-gray-500 font-medium mt-2">In Progress</p>
                    </div>

                    <div class="bg-card border border-border rounded-2xl p-5 shadow-sm">
                        <div
                            class="w-11 h-11 rounded-xl bg-purple-100 flex items-center justify-center text-purple-600 mb-5">
                            <i class="fa-solid fa-business-time text-lg"></i>
                        </div>
                        <div class="flex items-baseline gap-1.5">
                            <span id="timesheet-overtime-hours"
                                class="text-[28px] leading-none font-bold text-primary">0</span>
                            <span class="text-sm font-medium text-gray-400">hrs</span>
                        </div>
                        <p class="text-[13px] text-gray-500 font-medium mt-2">Overtime Hours</p>
                    </div>

                    <div class="bg-card border border-border rounded-2xl p-5 shadow-sm col-span-2 lg:col-span-1">
                        <div class="w-11 h-11 rounded-xl bg-red-100 flex items-center justify-center text-red-500 mb-5">
                            <i class="fa-solid fa-file-circle-exclamation text-lg"></i>
                        </div>
                        <span id="timesheet-missing-count"
                            class="text-[28px] leading-none font-bold text-primary">0</span>
                        <p class="text-[13px] text-gray-500 font-medium mt-2">Missing Timesheets</p>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-card border border-border rounded-2xl p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-primary">Filters</h3>
                        <button id="timesheet-reset" type="button"
                            class="text-xs font-semibold text-gray-500 hover:text-primary transition-colors flex items-center gap-1.5">
                            <i class="fa-solid fa-rotate-left text-[11px]"></i> Reset filters
                        </button>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div>
                            <label for="timesheet-search" class="block text-xs font-medium text-gray-500 mb-1.5">Search
                                Employee</label>
                            <div class="relative">
                                <i
                                    class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                <input id="timesheet-search" type="text" autocomplete="off" placeholder="Name or ID"
                                    class="w-full rounded-lg border border-border bg-card pl-8 pr-3 py-2 text-sm text-gray-600 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-accent/40 focus:border-accent">
                            </div>
                        </div>
                        <div>
                            <label for="timesheet-period" class="block text-xs font-medium text-gray-500 mb-1.5">Week /
                                Period</label>
                            <select id="timesheet-period"
                                class="w-full rounded-lg border border-border bg-card px-3 py-2 text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-accent/40 focus:border-accent">
                                <option value="week">This Week</option>
                                <option value="today">Today</option>
                                <option value="lastweek">Last Week</option>
                                <option value="all">All Records</option>
                                <option value="date">Specific Date</option>
                            </select>
                        </div>
                        <div>
                            <label for="timesheet-date"
                                class="block text-xs font-medium text-gray-500 mb-1.5">Date</label>
                            <input id="timesheet-date" type="date"
                                class="w-full rounded-lg border border-border bg-card px-3 py-2 text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-accent/40 focus:border-accent">
                        </div>
                        <div>
                            <label for="timesheet-status"
                                class="block text-xs font-medium text-gray-500 mb-1.5">Status</label>
                            <select id="timesheet-status"
                                class="w-full rounded-lg border border-border bg-card px-3 py-2 text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-accent/40 focus:border-accent">
                                <option value="">All Statuses</option>
                                <option>Completed</option>
                                <option>Late</option>
                                <option>In Progress</option>
                                <option>Absent</option>
                                <option>Overtime</option>
                                <option>Incomplete</option>
                            </select>
                        </div>
                        <div>
                            <label for="timesheet-member" class="block text-xs font-medium text-gray-500 mb-1.5">Team
                                Member</label>
                            <select id="timesheet-member"
                                class="w-full rounded-lg border border-border bg-card px-3 py-2 text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-accent/40 focus:border-accent">
                                <option value="">All Team Members</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Timesheet table -->
                <div class="bg-card border border-border rounded-2xl p-6 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-5">
                        <div class="flex items-center gap-3">
                            <h2 class="text-lg font-bold text-primary">Team Timesheet Records</h2>
                            <span id="timesheet-range-label"
                                class="text-xs font-mono font-medium px-2.5 py-1 rounded-md bg-background text-gray-600">This
                                week</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <p id="timesheet-result-count" class="text-xs text-gray-500">Showing 0 records</p>
                            <button type="button" id="timesheet-delete-open"
                                class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-red-200 bg-red-50 text-red-700 text-xs font-semibold hover:bg-red-100 transition-colors">
                                <i class="fa-solid fa-trash-can"></i> Delete
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[1100px] text-left border-collapse">
                            <thead>
                                <tr class="border-b-2 border-border text-primary text-sm font-mono">
                                    <th class="pb-3 font-semibold pr-4 whitespace-nowrap">Employee</th>
                                    <th class="pb-3 font-semibold pr-4 whitespace-nowrap">Date</th>
                                    <th class="pb-3 font-semibold pr-4 whitespace-nowrap">Schedule</th>
                                    <th class="pb-3 font-semibold pr-4 whitespace-nowrap">Time In</th>
                                    <th class="pb-3 font-semibold pr-4 whitespace-nowrap">Time Out</th>
                                    <th class="pb-3 font-semibold pr-4 whitespace-nowrap">Break</th>
                                    <th class="pb-3 font-semibold pr-4 whitespace-nowrap text-right">Regular Hours</th>
                                    <th class="pb-3 font-semibold pr-4 whitespace-nowrap text-right">Overtime</th>
                                    <th class="pb-3 font-semibold pr-4 whitespace-nowrap text-right">Total Hours</th>
                                    <th class="pb-3 font-semibold pr-4 whitespace-nowrap">Status</th>
                                    <th class="pb-3 font-semibold whitespace-nowrap">Action</th>
                                </tr>
                            </thead>
                            <tbody id="manager-timesheet-body"
                                class="font-mono text-sm text-primary divide-y divide-border"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ================= LEAVE REQUESTS TAB ================= -->
            <div id="leave" class="tab-content max-w-7xl mx-auto space-y-6 p-6 md:px-8 md:py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-primary">Leave Requests</h2>
                        <button type="button" id="manager-file-leave-btn"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-primary text-white text-sm font-semibold hover:opacity-90 transition">
                            <i class="fa-solid fa-calendar-plus text-xs"></i>
                            File Leave Request
                        </button>
                        <p class="text-sm text-gray-500 mt-1">Review and action of your own leave requests.</p>
                    </div>
                    <span id="pending-leave-count"
                        class="text-xs font-semibold px-3 py-1 rounded-full bg-amber-100 text-amber-700">0
                        pending</span>
                </div>
                <div class="bg-card border border-border rounded-2xl p-5 shadow-sm">

                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-base font-bold text-primary">
                                My Leave
                            </h3>

                            <p class="text-sm text-gray-500 mt-1">
                                Your own leave requests and approval status.
                            </p>
                        </div>

                        <span id="manager-my-leave-count"
                            class="text-xs font-semibold px-3 py-1 rounded-full bg-background text-gray-500">
                            0 requests
                        </span>
                    </div>

                    <div id="manager-my-leave-grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    </div>

                </div>

                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h3 class="text-base font-bold text-primary">
                                Team Leave Requests
                            </h3>

                            <p class="text-sm text-gray-500 mt-1">
                                Leave requests submitted by your team members.
                            </p>
                        </div>
                    </div>
                </div>
                <div id="manager-leaves-grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4"></div>
            </div>

            <!-- ================= ANALYTICS TAB ================= -->
            <div id="analytics" class="tab-content max-w-7xl mx-auto space-y-6 p-6 md:px-8 md:py-6">
                <div class="bg-card border border-border rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h2 class="text-lg font-bold text-primary">Team Attendance Trend</h2>
                            <p class="text-sm text-gray-500 mt-1">Team-wide attendance rate based on MySQL attendance
                                records.</p>
                        </div>
                        <span id="analytics-team-label"
                            class="text-xs font-semibold px-2.5 py-1 rounded-md bg-background text-gray-500">Team</span>
                    </div>
                    <div class="h-80">
                        <canvas id="team-attendance-trend-chart"></canvas>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 mt-6">

                        <div class="bg-background border border-border rounded-xl p-4">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">
                                Team Members
                            </p>
                            <p id="analytics-team-members" class="text-2xl font-bold text-primary mt-2">
                                0
                            </p>
                        </div>

                        <div class="bg-background border border-border rounded-xl p-4">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">
                                7-Day Attendance Rate
                            </p>
                            <p id="analytics-attendance-rate" class="text-2xl font-bold text-primary mt-2">
                                0%
                            </p>
                        </div>

                        <div class="bg-background border border-border rounded-xl p-4">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">
                                Late Arrivals
                            </p>
                            <p id="analytics-late-arrivals" class="text-2xl font-bold text-primary mt-2">
                                0
                            </p>
                        </div>

                        <div class="bg-background border border-border rounded-xl p-4">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">
                                Approved Leave Days
                            </p>
                            <p id="analytics-approved-leave-days" class="text-2xl font-bold text-primary mt-2">
                                0
                            </p>
                        </div>

                        <div class="bg-background border border-border rounded-xl p-4">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">
                                Regular Hours
                            </p>
                            <p id="analytics-regular-hours" class="text-2xl font-bold text-primary mt-2">
                                0 hrs
                            </p>
                        </div>

                        <div class="bg-background border border-border rounded-xl p-4">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">
                                Overtime Hours
                            </p>
                            <p id="analytics-overtime-hours" class="text-2xl font-bold text-primary mt-2">
                                0 hrs
                            </p>
                        </div>

                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="scripts/shared-data.js"></script>
    <script src="scripts/manager.js"></script>

    <!-- Search & Notification Initialization -->
    <script>
            (function () {
                'use strict';

                function managerHeaderInit() {
                    const searchInput = document.getElementById('searchInput');
                    const suggestionsBox = document.getElementById('suggestionsBox');
                    const searchContainer = document.getElementById('searchContainer');

                    const notificationBtn = document.getElementById('notification-btn');
                    const notificationMenu = document.getElementById('notification-menu');
                    const notificationBadge = document.getElementById('notification-badge');
                    const markAllReadBtn = document.getElementById('mark-all-read-btn');
                    const viewAllActivityBtn = document.getElementById('view-all-activity-btn');

                    if (!searchInput || !suggestionsBox || !notificationBtn || !notificationMenu) return;

                    const searchSections = [
                        { label: 'Attendance', target: 'dashboard', icon: 'fa-user-clock', keywords: 'attendance present late absent clock in' },
                        { label: 'Team Schedule', target: 'time-attendance', icon: 'fa-calendar-days', keywords: 'schedule shifts team roster' },
                        { label: 'Timesheets', target: 'schedule', icon: 'fa-file-lines', keywords: 'timesheet hours overtime records' },
                        { label: 'Leave Management', target: 'leave', icon: 'fa-plane-slash', keywords: 'leave vacation sick request' },
                        { label: 'Workforce Analytics', target: 'analytics', icon: 'fa-chart-pie', keywords: 'analytics workforce trends chart' }
                    ];

                    function getEmployees() {
                        try {
                            if (window.SharedData && typeof SharedData.getEmployees === 'function') {
                                const session = typeof SharedData.getSession === 'function' ? SharedData.getSession() : null;
                                if (session && typeof SharedData.getTeamForManager === 'function') {
                                    const team = SharedData.getTeamForManager(session.id);
                                    if (team && team.length) return team;
                                }
                                const employees = SharedData.getEmployees();
                                return (employees || []).filter(emp => emp.role === 'user');
                            }
                        } catch (error) {
                            console.warn('Header search data unavailable:', error);
                        }
                        return [];
                    }

                    function escapeHtml(value) {
                        return String(value ?? '').replace(/[&<>"']/g, char => ({
                            '&': '&amp;',
                            '<': '&lt;',
                            '>': '&gt;',
                            '"': '&quot;',
                            "'": '&#039;'
                        }[char]));
                    }

                    function openSection(target) {
                        if (typeof window.switchTab === 'function') {
                            window.switchTab(target);
                        } else {
                            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
                            const section = document.getElementById(target);
                            if (section) section.classList.add('active');
                        }
                    }

                    function highlightEmployee(employee) {
                        openSection('dashboard');

                        const employeeId = employee && employee.id ? String(employee.id) : '';
                        const employeeName = employee && employee.name ? String(employee.name).toLowerCase() : '';

                        setTimeout(() => {
                            const rows = Array.from(document.querySelectorAll(
                                '#manager-attendance-body tr, #manager-timesheet-body tr, #manager-schedule-body tr'
                            ));

                            let row = rows.find(el => {
                                const text = el.textContent.toLowerCase();
                                return (employeeId && text.includes(employeeId.toLowerCase())) ||
                                    (employeeName && text.includes(employeeName));
                            });

                            if (row) {
                                row.classList.add('bg-blue-50', 'ring-2', 'ring-blue-300');
                                row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                setTimeout(() => row.classList.remove('bg-blue-50', 'ring-2', 'ring-blue-300'), 2500);
                            } else {
                                const attendanceBody = document.getElementById('manager-attendance-body');
                                if (attendanceBody) {
                                    attendanceBody.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                }
                            }
                        }, 150);
                    }

                    function renderSearchResults(query) {
                        const term = query.trim().toLowerCase();

                        if (!term) {
                            suggestionsBox.innerHTML = '';
                            suggestionsBox.classList.add('hidden');
                            return;
                        }

                        const employees = getEmployees();
                        const employeeResults = employees.filter(emp => {
                            const haystack = [
                                emp.name, emp.id, emp.email, emp.position, emp.department
                            ].filter(Boolean).join(' ').toLowerCase();
                            return haystack.includes(term);
                        }).slice(0, 6);

                        const sectionResults = searchSections.filter(item => {
                            return `${item.label} ${item.keywords}`.toLowerCase().includes(term);
                        }).slice(0, 5);

                        const total = employeeResults.length + sectionResults.length;

                        if (!total) {
                            suggestionsBox.innerHTML = `
                            <li class="px-4 py-4 text-sm text-gray-500">
                                No staff or page found for "<span class="font-semibold text-gray-700">${escapeHtml(query)}</span>".
                            </li>`;
                            suggestionsBox.classList.remove('hidden');
                            return;
                        }

                        const employeeHtml = employeeResults.map(emp => `
                        <li>
                            <button type="button" class="w-full flex items-center gap-3 px-3 py-2.5 hover:bg-background text-left transition-colors" data-search-employee="${escapeHtml(emp.id)}">
                                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-background text-gray-600 text-xs font-bold">
                                    ${escapeHtml(emp.initials || emp.name.split(' ').map(part => part[0]).join('').slice(0, 2))}
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm font-semibold text-primary truncate">${escapeHtml(emp.name)}</span>
                                    <span class="block text-xs text-gray-400 truncate">${escapeHtml(emp.position || emp.department || emp.id)}</span>
                                </span>
                                <i class="fa-solid fa-arrow-right text-xs text-gray-300"></i>
                            </button>
                        </li>
                    `).join('');

                        const sectionHtml = sectionResults.map(item => `
                        <li>
                            <button type="button" class="w-full flex items-center gap-3 px-3 py-2.5 hover:bg-background text-left transition-colors border-t border-background" data-search-section="${escapeHtml(item.target)}">
                                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-accent">
                                    <i class="fa-solid ${escapeHtml(item.icon)} text-sm"></i>
                                </span>
                                <span class="flex-1">
                                    <span class="block text-sm font-semibold text-primary">${escapeHtml(item.label)}</span>
                                    <span class="block text-xs text-gray-400">Open section</span>
                                </span>
                                <i class="fa-solid fa-arrow-right text-xs text-gray-300"></i>
                            </button>
                        </li>
                    `).join('');

                        suggestionsBox.innerHTML = employeeHtml + sectionHtml;
                        suggestionsBox.classList.remove('hidden');

                        suggestionsBox.querySelectorAll('[data-search-employee]').forEach(button => {
                            button.addEventListener('click', () => {
                                const employee = getEmployees().find(emp => String(emp.id) === String(button.dataset.searchEmployee));
                                searchInput.value = employee ? employee.name : '';
                                suggestionsBox.classList.add('hidden');
                                if (employee) highlightEmployee(employee);
                            });
                        });

                        suggestionsBox.querySelectorAll('[data-search-section]').forEach(button => {
                            button.addEventListener('click', () => {
                                const target = button.dataset.searchSection;
                                searchInput.value = '';
                                suggestionsBox.classList.add('hidden');
                                openSection(target);
                            });
                        });
                    }

                    searchInput.addEventListener('input', () => renderSearchResults(searchInput.value));

                    searchInput.addEventListener('keydown', (event) => {
                        if (event.key === 'Escape') {
                            searchInput.value = '';
                            suggestionsBox.classList.add('hidden');
                            searchInput.blur();
                        } else if (event.key === 'Enter') {
                            const firstResult = suggestionsBox.querySelector('button');
                            if (firstResult) firstResult.click();
                        }
                    });

                    document.addEventListener('click', (event) => {
                        if (!searchContainer.contains(event.target)) {
                            suggestionsBox.classList.add('hidden');
                        }
                    });

                    // ---------------- NOTIFICATIONS ----------------
                    function updateNotificationState() {
                        const unreadItems = Array.from(document.querySelectorAll('.notification-item.unread'));
                        const hasUnread = unreadItems.length > 0;

                        notificationBadge.classList.toggle('hidden', !hasUnread);

                        const emptyState = document.getElementById('notification-empty');
                        const allRead = document.querySelectorAll('.notification-item').length > 0 &&
                            document.querySelectorAll('.notification-item.unread').length === 0;

                        if (emptyState) emptyState.classList.toggle('hidden', !allRead);
                    }

                    async function markNotificationRead(item) {

                        if (!item) return;

                        const notificationType =
                            item.dataset.notificationAction;

                        if (!notificationType) return;

                        // Update the screen immediately
                        item.classList.remove(
                            'unread',
                            'bg-blue-50/50'
                        );

                        const dot =
                            item.querySelector(
                                '.notification-dot'
                            );

                        if (dot) {
                            dot.classList.add('hidden');
                        }

                        updateNotificationState();

                        // Save the read status to MySQL
                        try {

                            const body =
                                new URLSearchParams();

                            body.append(
                                'notification_type',
                                notificationType
                            );

                            const response =
                                await fetch(
                                    'api/notifications/manager.php',
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

                            if (
                                !response.ok ||
                                !result.success
                            ) {
                                throw new Error(
                                    result.message ||
                                    'Unable to save notification status.'
                                );
                            }

                        } catch (error) {

                            console.error(
                                'Notification read status failed:',
                                error
                            );
                        }
                    }


                    notificationBtn.addEventListener('click', (event) => {
                        event.stopPropagation();
                        const open = notificationMenu.classList.contains('hidden');
                        notificationMenu.classList.toggle('hidden', !open);
                        notificationBtn.setAttribute('aria-expanded', String(open));
                        suggestionsBox.classList.add('hidden');
                    });

                    document.querySelectorAll('.notification-item').forEach(item => {
                        item.addEventListener('click', () => {
                            const target = item.dataset.notificationAction;
                            markNotificationRead(item);

                            if (target) openSection(target);

                            notificationMenu.classList.add('hidden');
                            notificationBtn.setAttribute('aria-expanded', 'false');
                        });
                    });

                    markAllReadBtn.addEventListener(
                        'click',
                        async (event) => {

                            event.stopPropagation();

                            const notificationItems =
                                document.querySelectorAll(
                                    '.notification-item'
                                );

                            for (
                                const item of notificationItems
                            ) {
                                await markNotificationRead(item);
                            }

                            notificationMenu.classList.remove(
                                'hidden'
                            );

                            notificationBtn.setAttribute(
                                'aria-expanded',
                                'true'
                            );
                        }
                    );

                    viewAllActivityBtn.addEventListener('click', () => {
                        notificationMenu.classList.add('hidden');
                        notificationBtn.setAttribute('aria-expanded', 'false');
                        openSection('analytics');
                    });

                    document.addEventListener('click', (event) => {
                        const container = document.getElementById('notification-container');
                        if (container && !container.contains(event.target)) {
                            notificationMenu.classList.add('hidden');
                            notificationBtn.setAttribute('aria-expanded', 'false');
                        }
                    });

                    document.addEventListener('keydown', (event) => {
                        if (event.key === 'Escape') {
                            notificationMenu.classList.add('hidden');
                            notificationBtn.setAttribute('aria-expanded', 'false');
                        }
                    });

                    async function loadManagerNotifications() {

                        try {

                            const response =
                                await fetch(
                                    'api/notifications/manager.php',
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
                                    'Unable to load manager notifications.'
                                );
                            }

                            const notifications =
                                Array.isArray(result.data)
                                    ? result.data
                                    : [];

                            const unreadCount =
                                Number(result.unread_count || 0);

                            if (unreadCount > 0) {
                                notificationBadge.classList.remove('hidden');
                            } else {
                                notificationBadge.classList.add('hidden');
                            }

                            const leave =
                                notifications.find(
                                    item =>
                                        item.type === 'leave'
                                );

                            const schedule =
                                notifications.find(
                                    item =>
                                        item.type === 'schedule'
                                );

                            const leaveText =
                                document.getElementById(
                                    'notification-leave-text'
                                );

                            const scheduleText =
                                document.getElementById(
                                    'notification-schedule-text'
                                );

                            if (leaveText && leave) {
                                leaveText.textContent =
                                    leave.message;
                            }

                            if (
                                scheduleText &&
                                schedule
                            ) {
                                scheduleText.textContent =
                                    schedule.message;
                            }

                            document
                                .querySelectorAll(
                                    '.notification-item'
                                )
                                .forEach(item => {
                                    item.classList.remove(
                                        'unread',
                                        'bg-blue-50/50'
                                    );

                                    const dot =
                                        item.querySelector(
                                            '.notification-dot'
                                        );

                                    if (dot) {
                                        dot.classList.add(
                                            'hidden'
                                        );
                                    }
                                });

                            notifications.forEach(
                                notification => {

                                    if (!notification.unread) {
                                        return;
                                    }

                                    const item =
                                        document.querySelector(
                                            `.notification-item[data-notification-action="${notification.type}"]`
                                        );

                                    if (!item) {
                                        return;
                                    }

                                    item.classList.add(
                                        'unread',
                                        'bg-blue-50/50'
                                    );

                                    const dot =
                                        item.querySelector(
                                            '.notification-dot'
                                        );

                                    if (dot) {
                                        dot.classList.remove(
                                            'hidden'
                                        );
                                    }
                                }
                            );

                            updateNotificationState();

                        } catch (error) {

                            console.error(
                                'Manager notification loading error:',
                                error
                            );
                        }
                    }

                    loadManagerNotifications();


                    updateNotificationState();
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', managerHeaderInit, { once: true });
                } else {
                    managerHeaderInit();
                }
            })();
    </script>

    <!-- PDF Export Initialization -->
    <script>
        function exportTimesheetsPDF() {
            try {

                if (
                    !window.jspdf ||
                    !window.jspdf.jsPDF
                ) {
                    alert(
                        'PDF preview is still loading. Please try again in a moment.'
                    );

                    return;
                }

                const { jsPDF } =
                    window.jspdf;

                const doc =
                    new jsPDF({
                        orientation: 'landscape',
                        unit: 'mm',
                        format: 'a4'
                    });

                const table =
                    document.getElementById(
                        'manager-timesheet-body'
                    );

                if (!table) {
                    alert(
                        'Timesheet records could not be found.'
                    );

                    return;
                }


                /*
                 * Export only the rows currently rendered by
                 * the manager's Timesheet table.
                 *
                 * Those rows already come from:
                 * api/timesheet/manager.php
                 *
                 * which filters using the logged-in manager's
                 * assigned team.
                 */
                /*
 * The employee list for the PDF must come from
 * the employees assigned to the logged-in manager.
 */
                const team =
                    typeof getTimesheetTeam === 'function'
                        ? getTimesheetTeam()
                        : [];


                /*
                 * Get the actual timesheet records that exist.
                 */
                const allRecords =
                    typeof getTeamTimesheetRecords === 'function'
                        ? getTeamTimesheetRecords()
                        : [];


                /*
                 * Get the current Timesheet filters.
                 */
                const filters =
                    typeof getTimesheetFilters === 'function'
                        ? getTimesheetFilters()
                        : {
                            search: '',
                            period: 'week',
                            date: '',
                            status: '',
                            member: ''
                        };


                /*
                 * Apply the current filters to actual timesheet records.
                 */
                const filteredRecords =
                    typeof applyTimesheetFilters === 'function'
                        ? applyTimesheetFilters(
                            allRecords,
                            filters
                        )
                        : allRecords;


                /*
                 * Start with the employees assigned to this manager.
                 */
                let selectedTeam =
                    team.slice();


                /*
                 * Team Member filter.
                 */
                if (filters.member) {

                    selectedTeam =
                        selectedTeam.filter(
                            employee =>
                                String(employee.id) ===
                                String(filters.member)
                        );

                }


                /*
                 * Search Employee filter.
                 */
                if (filters.search) {

                    selectedTeam =
                        selectedTeam.filter(employee => {

                            const name =
                                String(
                                    employee.name || ''
                                ).toLowerCase();

                            const id =
                                String(
                                    employee.id || ''
                                ).toLowerCase();

                            return (
                                name.includes(filters.search) ||
                                id.includes(filters.search)
                            );

                        });

                }


                /*
                 * Build PDF rows from the assigned employees.
                 */
                const rows = [];


                selectedTeam.forEach(employee => {

                    const employeeRecords =
                        filteredRecords.filter(
                            record =>
                                String(record.employeeId) ===
                                String(employee.id)
                        );


                    /*
                     * If the employee has actual timesheet
                     * records, export those records.
                     */
                    if (employeeRecords.length) {

                        employeeRecords.forEach(record => {

                            rows.push([

                                `${record.employeeName} (${record.employeeId})`,

                                record.date
                                    ? tsFmtDate(
                                        record.date,
                                        true
                                    )
                                    : '—',

                                record.scheduleStart &&
                                    record.scheduleEnd
                                    ? `${tsFmtTime(record.scheduleStart)} - ${tsFmtTime(record.scheduleEnd)}`
                                    : '—',

                                record.timeIn
                                    ? tsFmtTime(record.timeIn)
                                    : '—',

                                record.timeOut
                                    ? tsFmtTime(record.timeOut)
                                    : '—',

                                record.breakMin !== undefined
                                    ? `${record.breakMin} min`
                                    : '—',

                                record.hasHours
                                    ? tsHrs(record.regular)
                                    : '—',

                                record.hasHours
                                    ? tsHrs(record.overtime)
                                    : '—',

                                record.hasHours
                                    ? tsHrs(record.total)
                                    : '—',

                                record.status || '—',

                                '—'

                            ]);

                        });


                    } else {

                        /*
                         * The employee is assigned to this manager,
                         * but has no timesheet record for the
                         * selected period.
                         */
                        rows.push([

                            `${employee.name} (${employee.id})`,

                            '—',

                            '—',

                            '—',

                            '—',

                            '—',

                            '—',

                            '—',

                            '—',

                            'No Record',

                            '—'

                        ]);

                    }

                });

                const headers =
                    Array.from(
                        document.querySelectorAll(
                            '#schedule table thead th'
                        )
                    ).map(th =>
                        th.innerText
                            .replace(/\s+/g, ' ')
                            .trim()
                    );

                const teamLabel =
                    document
                        .getElementById(
                            'timesheet-team-label'
                        )
                        ?.innerText
                        .trim() ||
                    'Team';

                const rangeLabel =
                    document
                        .getElementById(
                            'timesheet-range-label'
                        )
                        ?.innerText
                        .trim() ||
                    'Current period';

                const resultCount =
                    `Showing ${rows.length} of ${rows.length} records`;

                const generated =
                    new Date().toLocaleString();

                doc.setFont(
                    'helvetica',
                    'bold'
                );

                doc.setFontSize(16);

                doc.text(
                    'Holiday Travelers Inc. - Timesheet Report',
                    14,
                    14
                );

                doc.setFont(
                    'helvetica',
                    'normal'
                );

                doc.setFontSize(9);

                doc.text(
                    `Team: ${teamLabel}`,
                    14,
                    21
                );

                doc.text(
                    `Period: ${rangeLabel}`,
                    14,
                    26
                );

                doc.text(
                    `Generated: ${generated}`,
                    14,
                    31
                );

                if (resultCount) {
                    doc.text(
                        resultCount,
                        283,
                        21,
                        {
                            align: 'right'
                        }
                    );
                }

                if (!rows.length) {

                    doc.setFontSize(11);

                    doc.text(
                        'No employees are assigned to this manager.',
                        14,
                        42
                    );

                } else {

                    doc.autoTable({
                        head: [headers],
                        body: rows,
                        startY: 36,

                        theme: 'grid',

                        styles: {
                            font: 'helvetica',
                            fontSize: 6.5,
                            cellPadding: 1.5,
                            overflow: 'linebreak',
                            valign: 'middle'
                        },

                        headStyles: {
                            fontStyle: 'bold',
                            fontSize: 6.5
                        },

                        margin: {
                            left: 10,
                            right: 10,
                            top: 36,
                            bottom: 12
                        },

                        didDrawPage: function (data) {

                            doc.setFontSize(7);

                            doc.setFont(
                                'helvetica',
                                'normal'
                            );

                            doc.text(
                                `Page ${data.pageNumber}`,
                                287,
                                202,
                                {
                                    align: 'right'
                                }
                            );
                        }
                    });
                }

                /*
                 * Open the generated PDF in the browser's
                 * PDF viewer instead of downloading it.
                 */
                const pdfBlob =
                    doc.output('blob');

                const pdfUrl =
                    URL.createObjectURL(
                        pdfBlob
                    );

                const previewWindow =
                    window.open(
                        pdfUrl,
                        '_blank'
                    );

                if (!previewWindow) {

                    URL.revokeObjectURL(
                        pdfUrl
                    );

                    alert(
                        'Please allow pop-ups to preview the PDF.'
                    );

                    return;
                }

                /*
                 * Release the temporary object URL
                 * after the preview window has loaded.
                 */
                setTimeout(
                    () => {
                        URL.revokeObjectURL(
                            pdfUrl
                        );
                    },
                    60000
                );

            } catch (error) {

                console.error(
                    'PDF preview failed:',
                    error
                );

                alert(
                    'Unable to preview the PDF. Please try again.'
                );
            }
        }
    </script>
    <div id="manager-leave-request-modal"
        class="hidden fixed inset-0 z-[100] bg-black/40 backdrop-blur-sm flex items-center justify-center p-4">

        <div class="bg-card w-full max-w-lg rounded-2xl shadow-xl border border-border">

            <div class="flex items-center justify-between px-6 py-5 border-b border-border">
                <div>
                    <h3 class="text-lg font-bold text-primary">
                        File Leave Request
                    </h3>

                    <p class="text-xs text-gray-500 mt-1">
                        Your request will be reviewed by the Admin.
                    </p>
                </div>

                <button type="button" id="manager-close-leave-modal" class="text-gray-400 hover:text-gray-600 text-lg">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="manager-leave-request-form" class="p-6 space-y-4">

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                        Applicant
                    </label>

                    <input type="text" value="Current Manager" disabled
                        class="w-full bg-background border border-border rounded-xl px-3 py-2.5 text-sm text-gray-500">
                </div>

                <div>
                    <label for="manager-leave-type" class="block text-xs font-semibold text-gray-600 mb-1.5">
                        Type of Leave
                    </label>

                    <select id="manager-leave-type" required
                        class="w-full bg-background border border-border rounded-xl px-3 py-2.5 text-sm text-primary focus:outline-none focus:ring-2 focus:ring-accent">

                        <option value="Service Incentive Leave (SIL)">
                            Service Incentive Leave (SIL)
                        </option>

                        <option value="Vacation Leave">
                            Vacation Leave
                        </option>

                        <option value="Sick Leave">
                            Sick Leave
                        </option>

                        <option value="Maternity Leave">
                            Maternity Leave
                        </option>

                        <option value="Paternity Leave">
                            Paternity Leave
                        </option>

                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    <div>
                        <label for="manager-leave-start" class="block text-xs font-semibold text-gray-600 mb-1.5">
                            Start Date
                        </label>

                        <input id="manager-leave-start" type="date" required
                            class="w-full bg-background border border-border rounded-xl px-3 py-2.5 text-sm text-primary focus:outline-none focus:ring-2 focus:ring-accent">
                    </div>

                    <div>
                        <label for="manager-leave-end" class="block text-xs font-semibold text-gray-600 mb-1.5">
                            End Date
                        </label>

                        <input id="manager-leave-end" type="date" required
                            class="w-full bg-background border border-border rounded-xl px-3 py-2.5 text-sm text-primary focus:outline-none focus:ring-2 focus:ring-accent">
                    </div>

                </div>

                <div>
                    <label for="manager-leave-reason" class="block text-xs font-semibold text-gray-600 mb-1.5">
                        Reason
                    </label>

                    <textarea id="manager-leave-reason" rows="4" required placeholder="Enter reason for leave..."
                        class="w-full bg-background border border-border rounded-xl px-3 py-2.5 text-sm text-primary focus:outline-none focus:ring-2 focus:ring-accent"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2">

                    <button type="button" id="manager-cancel-leave-btn"
                        class="px-4 py-2 rounded-xl border border-border text-sm font-semibold text-gray-600 hover:bg-background">
                        Cancel
                    </button>

                    <button type="submit"
                        class="px-4 py-2 rounded-xl bg-primary text-white text-sm font-semibold hover:opacity-90">
                        Submit Leave Request
                    </button>

                </div>

            </form>
        </div>
    </div>
    <script>
        (async function () {
            try {
                const response = await fetch(
                    'api/account/profile.php',
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
                    return;
                }

                const avatar =
                    document.getElementById(
                        'manager-profile-avatar'
                    );

                if (!avatar) {
                    return;
                }

                const profile =
                    result.data || {};

                if (profile.avatar_url) {
                    avatar.src =
                        profile.avatar_url +
                        '?' +
                        'v=' +
                        Date.now();
                } else {
                    avatar.src =
                        'assets/images/employee.png';
                }

            } catch (error) {
                console.error(
                    'Manager profile avatar loading error:',
                    error
                );
            }
        })();
    </script>
</body>

</html>