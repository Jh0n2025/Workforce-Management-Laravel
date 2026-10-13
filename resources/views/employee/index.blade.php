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


    <div id="app-workspace" class="h-screen w-full flex">

        <aside class="w-64 bg-primary text-gray-300 flex-shrink-0 hidden md:flex flex-col shadow-xl z-20 pb-6">
            <!-- Brand -->
            <div class="h-16 flex items-center px-6 border-b border-white/10 bg-primary">
                <img src="assets/images/Company_logo.png" alt="Company Logo" class="mr-3 object-contain"
                    style="height: 72px; width: auto;">
                <span class="font-bold text-lg text-white tracking-tight">Holiday Travelers Inc.</span>
            </div>

            <!---------------------- Navigation Links ------------------------->
            <!--------------------------SIDEBAR-------------------------------->
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">

                <a href="#"
                    class="nav-btn active flex items-center px-3 py-2.5 text-sm font-medium rounded-2xl bg-secondary text-white"
                    data-target="analytics">
                    <i class="fa-solid fa-chart-pie w-6 text-center mr-2"></i> My Growth
                </a>
                <div class="nav-group">
                    <a href="#"
                        class="nav-btn nav-dropdown-toggle w-full flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-2xl text-gray-400 hover:bg-white/10 hover:text-white transition-colors"
                        data-target="my-attendance">
                        <span class="flex items-center">
                            <i class="fa-solid fa-users-viewfinder w-6 text-center mr-2"></i> My Attendance
                        </span>
                        <i class="fa-solid fa-chevron-down text-xs nav-dropdown-arrow transition-transform"></i>
                    </a>
                    <div class="nav-dropdown-menu hidden mt-1 ml-6 space-y-1 border-l border-white/10 pl-3">
                        <a href="#"
                            class="nav-btn flex items-center px-3 py-2 text-sm font-medium rounded-xl text-gray-400 hover:bg-white/10 hover:text-white transition-colors"
                            data-target="time-attendance">
                            <i class="fa-solid fa-calendar-days w-5 text-center mr-2"></i> Schedule
                        </a>
                    </div>
                </div>
                <a href="#"
                    class="nav-btn flex items-center px-3 py-2.5 text-sm font-medium rounded-2xl text-gray-400 hover:bg-white/10 hover:text-white transition-colors"
                    data-target="schedule">
                    <i class="fa-solid fa-calendar-check w-6 text-center mr-2"></i> My Timesheet
                </a>
                <a href="#"
                    class="nav-btn flex items-center px-3 py-2.5 text-sm font-medium rounded-2xl text-gray-400 hover:bg-white/10 hover:text-white transition-colors"
                    data-target="leave">
                    <i class="fa-solid fa-plane-slash w-6 text-center mr-2"></i> Leave Requests
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            <!----------------------- Top Bar (Desktop)------------------------>

            <header
                class="bg-card border-b border-border items-center justify-between px-8 h-16 hidden md:flex z-10 shadow-sm">
                <h1 class="font-heading text-xl font-bold text-primary flex items-center" id="page-title">
                    My Growth
                </h1>

                <div class="flex items-center space-x-6">
                    <!------------ Search ------------->
                    <div class="relative w-64" id="searchContainer">
                        <i
                            class="fa-solid fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>

                        <!-- Added id="searchInput", autocomplete="off", and changed w-64 to w-full -->
                        <input id="searchInput" type="text" placeholder="Search staff, tours..." autocomplete="off"
                            class="pl-10 pr-4 py-2 w-full border border-border rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-accent bg-background">

                        <!-- Suggestions Dropdown (Hidden by default) -->
                        <ul id="suggestionsBox"
                            class="absolute left-0 top-full mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg hidden z-50 max-h-48 overflow-y-auto">
                        </ul>
                    </div>
                    <!-------------NOTIFICATION BELL------------------------------>

                    <div class="relative inline-block">

                        <!------- Your Notification Button (Trigger) -------->
                        <button id="notification-btn"
                            class="font-button relative p-2 text-slate-400 hover:text-blue-600 transition-colors focus:outline-none">
                            <i class="fa-solid fa-bell text-xl"></i>
                            <span
                                class="absolute top-1.5 right-1.5 block h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-white"></span>
                        </button>

                        <!--------- Notification Dropdown Panel --------->
                        <div id="notification-menu"
                            class="hidden absolute right-0 mt-2 w-80 sm:w-96 rounded-xl border border-slate-200 bg-white shadow-xl ring-1 ring-black/5 z-50 overflow-hidden transition-all">

                            <!--------- Header --------->
                            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 bg-white">
                                <h3 class="text-sm font-semibold text-slate-900">Notifications</h3>
                                <button class="text-xs font-medium text-blue-600 hover:text-blue-700 transition-colors">
                                    Mark all as read
                                </button>
                            </div>

                            <!------- Notification List (Scrollable) ------->
                            <div class="max-h-[28rem] overflow-y-auto">

                                <!--------- Unread Notification Item --------->
                                <a href="#" data-notification-id="new-system-report"
                                    class="flex items-start gap-4 px-4 py-3 bg-blue-50/50 hover:bg-slate-50 border-b border-slate-50 transition-colors">
                                    <div class="flex-shrink-0 mt-1">
                                        <div
                                            class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                                            <i class="fa-solid fa-file-invoice text-xs"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 space-y-1">
                                        <p class="text-sm text-slate-900 font-medium">New System Report</p>
                                        <p class="text-xs text-slate-500 line-clamp-2">The monthly financial report for
                                            Q3 has been generated and is ready for review.</p>
                                        <p class="text-[11px] font-medium text-blue-600">5 minutes ago</p>
                                    </div>
                                    <!--------- Unread Blue Dot --------->
                                    <div class="flex-shrink-0 mt-2">
                                        <span class="block h-2 w-2 rounded-full bg-blue-600"></span>
                                    </div>
                                </a>

                                <!--------- Read Notification Item (Success) --------->
                                <a href="#"
                                    class="flex items-start gap-4 px-4 py-3 hover:bg-slate-50 border-b border-slate-50 transition-colors">
                                    <div class="flex-shrink-0 mt-1">
                                        <div
                                            class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100 text-green-600">
                                            <i class="fa-solid fa-check text-xs"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 space-y-1">
                                        <p class="text-sm text-slate-700">Update Successful</p>
                                        <p class="text-xs text-slate-500 line-clamp-2">Database migration completed
                                            successfully without any errors.</p>
                                        <p class="text-[11px] text-slate-400">2 hours ago</p>
                                    </div>
                                </a>

                                <!--------- Read Notification Item (Warning) --------->
                                <a href="#"
                                    class="flex items-start gap-4 px-4 py-3 hover:bg-slate-50 transition-colors">
                                    <div class="flex-shrink-0 mt-1">
                                        <div
                                            class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                                            <i class="fa-solid fa-triangle-exclamation text-xs"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 space-y-1">
                                        <p class="text-sm text-slate-700">Storage Warning</p>
                                        <p class="text-xs text-slate-500 line-clamp-2">Server cluster B is currently at
                                            89% capacity. Please clear temporary files.</p>
                                        <p class="text-[11px] text-slate-400">Yesterday</p>
                                    </div>
                                </a>

                            </div>

                        </div>
                    </div>
                    <!---------END----------------->
                    <button id="profile-btn"
                        class="group inline-flex items-center gap-3 rounded-full border border-border bg-white px-4 py-2 shadow-sm transition hover:bg-slate-50">
                        <img id="employee-profile-avatar" src="assets/images/profile.png" alt="Employee Avatar"
                            class="h-10 w-10 rounded-full object-cover border-2 border-accent">
                        <div class="text-left">
                            <p class="text-sm font-semibold text-primary" data-session="name">Employee Name</p>
                            <p class="text-xs text-gray-500" data-session="role">Role</p>
                        </div>
                        <i class="fa-solid fa-chevron-down text-gray-400 group-hover:text-slate-600"></i>
                    </button>
                </div>
            </header>

            <div id="profile-menu"
                class="hidden absolute right-8 top-20 z-20 w-56 rounded-xl border border-slate-200 bg-white shadow-xl ring-1 ring-black/5 transition-all">

                <!-- User Info Header -->
                <div class="px-4 py-3 border-b border-slate-100">
                    <p class="text-sm font-semibold text-slate-900 truncate" data-session="name">Employee Name</p>
                    <p class="text-xs text-slate-400 truncate" data-session="role">Role</p>
                </div>

                <!--------- PROFILE HEADERDROPDOWN ------------------>
                <div class="p-1.5">
                    <a href="/employee/profile"
                        class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                        <i data-lucide="user" class="w-4 h-4 text-slate-400"></i>
                        View Profile
                    </a>
                    <a href="/settings"
                        class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors">
                        <i data-lucide="settings" class="w-4 h-4 text-slate-400"></i>
                        Account Settings
                    </a>
                </div>

                <!-- Logout Action -->
                <div class="p-1.5 border-t border-slate-100">
                    <a href="login.html" onclick="handleEmployeeLogout(event)"
                        class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 transition-colors text-left">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        Logout System
                    </a>
                </div>

            </div>

            <main class="flex-1 p-6 overflow-y-auto">

                <!-- ================= MY ATTENDANCE TAB (main) ================= -->
                <div id="my-attendance" class="tab-content max-w-7xl mx-auto space-y-6">

                    <!-- Today's Attendance Card -->
                    <div class="bg-card border border-border rounded-2xl shadow-sm p-6">
                        <h2 class="font-heading text-base font-bold text-primary pb-3 mb-5 border-b border-border">
                            Today's Attendance
                        </h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5">
                            <div>
                                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1.5">Status</p>
                                <p id="employee-attendance-status"
                                    class="flex items-center gap-2 text-sm font-semibold text-primary">
                                    <span class="h-2 w-2 rounded-full bg-success"></span>
                                    Clocked In
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1.5">Time In</p>
                                <p id="employee-time-in" class="text-sm font-semibold text-primary">--:--</p>
                            </div>

                            <div>
                                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1.5">Current Work
                                    Time</p>
                                <p id="employee-current-work-time" class="text-sm font-semibold text-primary">--h --m
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1.5">Expected
                                    Time Out</p>
                                <p id="employee-clock-out-time" class="text-sm font-semibold text-primary">05:00 PM</p>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-wrap gap-2">
                            <button type="button" id="clock-in-btn"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-success text-white text-sm font-semibold font-button shadow-sm hover:bg-emerald-600 transition-colors">
                                <i class="fa-solid fa-right-to-bracket"></i>
                                Clock In
                            </button>
                            <button type="button" id="clock-out-btn"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-error text-white text-sm font-semibold font-button shadow-sm hover:bg-red-600 transition-colors">
                                <i class="fa-solid fa-right-from-bracket"></i>
                                Clock Out
                            </button>
                        </div>
                    </div>

                    <!-- Attendance History Card -->
                    <div class="bg-card border border-border rounded-2xl shadow-sm overflow-hidden">
                        <h2 class="font-heading text-base font-bold text-primary px-6 pt-6 pb-4">
                            Attendance History
                        </h2>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead>
                                    <tr
                                        class="border-y border-border bg-background text-xs uppercase tracking-wide text-gray-400">
                                        <th class="px-6 py-3 font-medium">Date</th>
                                        <th class="px-6 py-3 font-medium">Time In</th>
                                        <th class="px-6 py-3 font-medium">Time Out</th>
                                        <th class="px-6 py-3 font-medium">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="employee-attendance-history-body" class="divide-y divide-border">
                                    <tr class="hover:bg-background transition-colors">
                                        <td class="px-6 py-3.5 text-primary font-medium">Aug 16</td>
                                        <td class="px-6 py-3.5 text-gray-500">08:02</td>
                                        <td class="px-6 py-3.5 text-gray-400">&mdash;</td>
                                        <td class="px-6 py-3.5">
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                                <span class="h-1.5 w-1.5 rounded-full bg-success"></span>
                                                Present
                                            </span>
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-background transition-colors">
                                        <td class="px-6 py-3.5 text-primary font-medium">Aug 15</td>
                                        <td class="px-6 py-3.5 text-gray-500">07:58</td>
                                        <td class="px-6 py-3.5 text-gray-500">17:01</td>
                                        <td class="px-6 py-3.5">
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                                <span class="h-1.5 w-1.5 rounded-full bg-success"></span>
                                                Present
                                            </span>
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-background transition-colors">
                                        <td class="px-6 py-3.5 text-primary font-medium">Aug 14</td>
                                        <td class="px-6 py-3.5 text-gray-500">08:15</td>
                                        <td class="px-6 py-3.5 text-gray-500">17:00</td>
                                        <td class="px-6 py-3.5">
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                                <span class="h-1.5 w-1.5 rounded-full bg-warning"></span>
                                                Late
                                            </span>
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-background transition-colors">
                                        <td class="px-6 py-3.5 text-primary font-medium">Aug 13</td>
                                        <td class="px-6 py-3.5 text-gray-500">08:01</td>
                                        <td class="px-6 py-3.5 text-gray-500">17:03</td>
                                        <td class="px-6 py-3.5">
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                                <span class="h-1.5 w-1.5 rounded-full bg-success"></span>
                                                Present
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <!-- ================= SCHEDULE TAB (dropdown sub-item) ================= -->
                <div id="time-attendance" class="tab-content max-w-7xl mx-auto space-y-6">

                    <!-- Day-by-Day Schedule Card -->
                    <div class="bg-card border border-border rounded-2xl shadow-sm p-6">
                        <div class="flex items-center justify-between mb-1">
                            <h2 class="font-heading text-base font-bold text-primary">My Schedule</h2>

                            <div class="flex items-center gap-2">
                                <button type="button" id="employee-shift-request-btn"
                                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold text-primary border border-border hover:bg-background transition-colors">
                                    <i class="fa-solid fa-calendar-plus text-[11px]"></i>
                                    Request Shift Change
                                </button>


                            </div>
                        </div>
                        <p id="employee-schedule-week-label" class="text-sm text-gray-400 mb-5">
                            August 10&ndash;16, 2026
                        </p>

                        <div id="employee-schedule-day-grid"
                            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <!-- Monday -->
                            <div class="border border-dashed border-border rounded-xl p-4">
                                <p class="text-xs font-semibold text-primary uppercase tracking-wide">Monday</p>
                                <p class="text-xs text-gray-400 mb-3">Aug 10</p>
                                <p class="text-sm font-semibold text-primary">Morning Shift</p>
                                <p class="text-sm text-gray-500">08:00 AM &ndash; 05:00 PM</p>
                                <p class="text-xs text-gray-400 mt-1">Office / Operations</p>
                            </div>

                            <!-- Tuesday -->
                            <div class="border border-dashed border-border rounded-xl p-4">
                                <p class="text-xs font-semibold text-primary uppercase tracking-wide">Tuesday</p>
                                <p class="text-xs text-gray-400 mb-3">Aug 11</p>
                                <p class="text-sm font-semibold text-primary">Morning Shift</p>
                                <p class="text-sm text-gray-500">08:00 AM &ndash; 05:00 PM</p>
                                <p class="text-xs text-gray-400 mt-1">Office / Operations</p>
                            </div>

                            <!-- Wednesday -->
                            <div class="border border-dashed border-border rounded-xl p-4">
                                <p class="text-xs font-semibold text-primary uppercase tracking-wide">Wednesday</p>
                                <p class="text-xs text-gray-400 mb-3">Aug 12</p>
                                <p class="text-sm font-semibold text-primary">Mid Shift</p>
                                <p class="text-sm text-gray-500">09:00 AM &ndash; 06:00 PM</p>
                                <p class="text-xs text-gray-400 mt-1">Office / Operations</p>
                            </div>

                            <!-- Thursday (Off) -->
                            <div class="border border-dashed border-border rounded-xl p-4 bg-background/60">
                                <p class="text-xs font-semibold text-primary uppercase tracking-wide">Thursday</p>
                                <p class="text-xs text-gray-400 mb-3">Aug 13</p>
                                <p class="text-sm font-semibold text-gray-400">Day Off</p>
                                <p class="text-sm text-gray-400">&mdash;</p>
                                <p class="text-xs text-gray-400 mt-1">&nbsp;</p>
                            </div>

                            <!-- Friday -->
                            <div class="border border-dashed border-border rounded-xl p-4">
                                <p class="text-xs font-semibold text-primary uppercase tracking-wide">Friday</p>
                                <p class="text-xs text-gray-400 mb-3">Aug 14</p>
                                <p class="text-sm font-semibold text-primary">Morning Shift</p>
                                <p class="text-sm text-gray-500">08:00 AM &ndash; 05:00 PM</p>
                                <p class="text-xs text-gray-400 mt-1">Office / Operations</p>
                            </div>
                        </div>
                    </div>

                    <!-- Weekly Overview Card -->
                    <div class="bg-card border border-border rounded-2xl shadow-sm overflow-hidden">
                        <div class="flex items-center justify-between px-6 pt-6 pb-5">
                            <div>
                                <h2 class="font-heading text-base font-bold text-primary">Weekly Overview</h2>
                                <p id="employee-schedule-overview-label" class="text-xs text-gray-400 mt-0.5">
                                    August 10&ndash;16, 2026
                                </p>
                            </div>

                        </div>

                        <div class="px-6 pb-6">
                            <div id="employee-schedule-overview-grid" class="grid grid-cols-4 sm:grid-cols-7 gap-3">
                                <!-- Monday -->
                                <div
                                    class="rounded-xl border border-border overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition-all-300">
                                    <div class="bg-background px-2 py-2 text-center border-b border-border">
                                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Mon
                                        </p>
                                        <p class="text-sm font-bold text-primary">10</p>
                                    </div>
                                    <div class="p-3 flex flex-col items-center gap-2">
                                        <div
                                            class="h-7 w-7 rounded-full bg-accent/15 text-accent flex items-center justify-center text-[11px] font-bold">
                                            A</div>
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-600">8&ndash;5</span>
                                    </div>
                                </div>

                                <!-- Tuesday -->
                                <div
                                    class="rounded-xl border border-border overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition-all-300">
                                    <div class="bg-background px-2 py-2 text-center border-b border-border">
                                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Tue
                                        </p>
                                        <p class="text-sm font-bold text-primary">11</p>
                                    </div>
                                    <div class="p-3 flex flex-col items-center gap-2">
                                        <div
                                            class="h-7 w-7 rounded-full bg-accent/15 text-accent flex items-center justify-center text-[11px] font-bold">
                                            A</div>
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-600">8&ndash;5</span>
                                    </div>
                                </div>

                                <!-- Wednesday -->
                                <div
                                    class="rounded-xl border border-border overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition-all-300">
                                    <div class="bg-background px-2 py-2 text-center border-b border-border">
                                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Wed
                                        </p>
                                        <p class="text-sm font-bold text-primary">12</p>
                                    </div>
                                    <div class="p-3 flex flex-col items-center gap-2">
                                        <div
                                            class="h-7 w-7 rounded-full bg-secondary/15 text-secondary flex items-center justify-center text-[11px] font-bold">
                                            A</div>
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-orange-50 text-secondary">9&ndash;6</span>
                                    </div>
                                </div>

                                <!-- Thursday (Off) -->
                                <div
                                    class="rounded-xl border border-border overflow-hidden bg-background/60 hover:shadow-md hover:-translate-y-0.5 transition-all-300">
                                    <div class="bg-background px-2 py-2 text-center border-b border-border">
                                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Thu
                                        </p>
                                        <p class="text-sm font-bold text-primary">13</p>
                                    </div>
                                    <div class="p-3 flex flex-col items-center gap-2">
                                        <div
                                            class="h-7 w-7 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center text-[11px] font-bold">
                                            A</div>
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-400">OFF</span>
                                    </div>
                                </div>

                                <!-- Friday -->
                                <div
                                    class="rounded-xl border border-border overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition-all-300">
                                    <div class="bg-background px-2 py-2 text-center border-b border-border">
                                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Fri
                                        </p>
                                        <p class="text-sm font-bold text-primary">14</p>
                                    </div>
                                    <div class="p-3 flex flex-col items-center gap-2">
                                        <div
                                            class="h-7 w-7 rounded-full bg-accent/15 text-accent flex items-center justify-center text-[11px] font-bold">
                                            A</div>
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-600">8&ndash;5</span>
                                    </div>
                                </div>

                                <!-- Saturday (Off) -->
                                <div
                                    class="rounded-xl border border-border overflow-hidden bg-background/60 hover:shadow-md hover:-translate-y-0.5 transition-all-300">
                                    <div class="bg-background px-2 py-2 text-center border-b border-border">
                                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Sat
                                        </p>
                                        <p class="text-sm font-bold text-primary">15</p>
                                    </div>
                                    <div class="p-3 flex flex-col items-center gap-2">
                                        <div
                                            class="h-7 w-7 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center text-[11px] font-bold">
                                            A</div>
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-400">OFF</span>
                                    </div>
                                </div>

                                <!-- Sunday (Off) -->
                                <div
                                    class="rounded-xl border border-border overflow-hidden bg-background/60 hover:shadow-md hover:-translate-y-0.5 transition-all-300">
                                    <div class="bg-background px-2 py-2 text-center border-b border-border">
                                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Sun
                                        </p>
                                        <p class="text-sm font-bold text-primary">16</p>
                                    </div>
                                    <div class="p-3 flex flex-col items-center gap-2">
                                        <div
                                            class="h-7 w-7 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center text-[11px] font-bold">
                                            A</div>
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-400">OFF</span>
                                    </div>
                                </div>

                            </div>

                            <!-- Legend -->
                            <div class="flex flex-wrap items-center gap-4 mt-5 pt-4 border-t border-border">
                                <div class="flex items-center gap-1.5">
                                    <span class="h-2.5 w-2.5 rounded-full bg-accent"></span>
                                    <span class="text-xs text-gray-400">Morning Shift</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="h-2.5 w-2.5 rounded-full bg-secondary"></span>
                                    <span class="text-xs text-gray-400">Mid Shift</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="h-2.5 w-2.5 rounded-full bg-gray-300"></span>
                                    <span class="text-xs text-gray-400">Day Off</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- ================= MY TIMESHEET TAB ================= -->
                <div id="schedule" class="tab-content max-w-7xl mx-auto space-y-6">

                    <!-- My Timesheet Card -->
                    <div class="bg-card border border-border rounded-2xl shadow-sm p-6">
                        <div class="flex items-center justify-between mb-1">
                            <h2 class="font-heading text-base font-bold text-primary">My Timesheet</h2>
                            <span id="employee-timesheet-status"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-warning"></span>
                                Pending Approval
                            </span>
                        </div>
                        <p id="employee-timesheet-period" class="text-sm text-gray-400 mb-5">
                            Current Period &middot; August 10 &ndash; 16, 2026
                        </p>

                        <!-- Daily Hours -->
                        <div id="employee-timesheet-daily" class="divide-y divide-border border-y border-border">
                            <div class="flex items-center justify-between py-3">
                                <span class="text-sm text-gray-500">Monday</span>
                                <span class="text-sm font-semibold text-primary">8h</span>
                            </div>
                            <div class="flex items-center justify-between py-3">
                                <span class="text-sm text-gray-500">Tuesday</span>
                                <span class="text-sm font-semibold text-primary">8h</span>
                            </div>
                            <div class="flex items-center justify-between py-3">
                                <span class="text-sm text-gray-500">Wednesday</span>
                                <span class="text-sm font-semibold text-primary">8h</span>
                            </div>
                            <div class="flex items-center justify-between py-3">
                                <span class="text-sm text-gray-500">Thursday</span>
                                <span class="text-sm font-semibold text-primary">8h</span>
                            </div>
                            <div class="flex items-center justify-between py-3">
                                <span class="text-sm text-gray-500">Friday</span>
                                <span class="text-sm font-semibold text-primary">8h</span>
                            </div>
                        </div>

                        <!-- Summary -->
                        <div class="grid grid-cols-3 gap-4 mt-5">
                            <div class="rounded-xl bg-background p-4 text-center">
                                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Regular Hours
                                </p>
                                <p id="employee-timesheet-regular" class="text-lg font-bold text-primary">40h</p>
                            </div>
                            <div class="rounded-xl bg-background p-4 text-center">
                                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Overtime</p>
                                <p id="employee-timesheet-overtime" class="text-lg font-bold text-secondary">2h</p>
                            </div>
                            <div class="rounded-xl bg-primary/5 p-4 text-center">
                                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Total Hours
                                </p>
                                <p id="employee-timesheet-total" class="text-lg font-bold text-primary">42h</p>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- ================= LEAVE REQUESTS TAB ================= -->
                <div id="leave" class="tab-content max-w-7xl mx-auto space-y-6">

                    <!-- Leave Balance Card -->
                    <div class="bg-card border border-border rounded-2xl shadow-sm p-6">
                        <div class="flex items-center justify-between mb-5">
                            <h2 class="font-heading text-base font-bold text-primary">My Leave</h2>
                            <button type="button" id="open-request-leave-btn"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-secondary text-white text-sm font-semibold font-button shadow-sm hover:bg-orange-500 transition-colors">
                                <i class="fa-solid fa-plus"></i>
                                Request Leave
                            </button>
                        </div>

                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-3">Leave Balance</p>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="rounded-xl border border-border p-4 flex items-center gap-4">
                                <div
                                    class="h-11 w-11 rounded-full bg-accent/15 text-accent flex items-center justify-center flex-shrink-0">
                                    <i class="fa-solid fa-umbrella-beach"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Vacation Leave</p>
                                    <p class="text-lg font-bold text-primary">8 days</p>
                                </div>
                            </div>

                            <div class="rounded-xl border border-border p-4 flex items-center gap-4">
                                <div
                                    class="h-11 w-11 rounded-full bg-success/15 text-success flex items-center justify-center flex-shrink-0">
                                    <i class="fa-solid fa-notes-medical"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Sick Leave</p>
                                    <p class="text-lg font-bold text-primary">5 days</p>
                                </div>
                            </div>

                            <div class="rounded-xl border border-border p-4 flex items-center gap-4">
                                <div
                                    class="h-11 w-11 rounded-full bg-error/15 text-error flex items-center justify-center flex-shrink-0">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Emergency Leave</p>
                                    <p class="text-lg font-bold text-primary">2 days</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- My Leave Requests Table -->
                    <div class="bg-card border border-border rounded-2xl shadow-sm overflow-hidden">
                        <h2 class="font-heading text-base font-bold text-primary px-6 pt-6 pb-4">
                            My Leave Requests
                        </h2>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead>
                                    <tr
                                        class="border-y border-border bg-background text-xs uppercase tracking-wide text-gray-400">
                                        <th class="px-6 py-3 font-medium">Type</th>
                                        <th class="px-6 py-3 font-medium">Dates</th>
                                        <th class="px-6 py-3 font-medium">Days</th>
                                        <th class="px-6 py-3 font-medium">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="employee-leave-requests-body" class="divide-y divide-border">
                                    <tr class="hover:bg-background transition-colors">
                                        <td class="px-6 py-3.5 text-primary font-medium">Vacation</td>
                                        <td class="px-6 py-3.5 text-gray-500">Aug 20&ndash;22</td>
                                        <td class="px-6 py-3.5 text-gray-500">3</td>
                                        <td class="px-6 py-3.5">
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                                <span class="h-1.5 w-1.5 rounded-full bg-warning"></span>
                                                Pending
                                            </span>
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-background transition-colors">
                                        <td class="px-6 py-3.5 text-primary font-medium">Sick</td>
                                        <td class="px-6 py-3.5 text-gray-500">Jul 18</td>
                                        <td class="px-6 py-3.5 text-gray-500">1</td>
                                        <td class="px-6 py-3.5">
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                                <span class="h-1.5 w-1.5 rounded-full bg-success"></span>
                                                Approved
                                            </span>
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-background transition-colors">
                                        <td class="px-6 py-3.5 text-primary font-medium">Vacation</td>
                                        <td class="px-6 py-3.5 text-gray-500">Jun 05</td>
                                        <td class="px-6 py-3.5 text-gray-500">1</td>
                                        <td class="px-6 py-3.5">
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                                <span class="h-1.5 w-1.5 rounded-full bg-error"></span>
                                                Rejected
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <!-- ================= REQUEST LEAVE MODAL ================= -->
                <div id="request-leave-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div id="request-leave-backdrop" class="absolute inset-0 bg-primary/40 backdrop-blur-sm"></div>

                    <div
                        class="relative bg-card w-full max-w-md rounded-2xl shadow-xl border border-border overflow-hidden">
                        <div class="flex items-center justify-between px-6 py-4 border-b border-border">
                            <h3 class="font-heading text-base font-bold text-primary">Request Leave</h3>
                            <button type="button" id="close-request-leave-btn"
                                class="text-gray-400 hover:text-gray-600 transition-colors">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        <form id="request-leave-form" class="px-6 py-5 space-y-4">
                            <div>
                                <label for="leave-type"
                                    class="block text-xs font-medium text-gray-400 uppercase tracking-wide mb-1.5">Leave
                                    Type</label>
                                <select id="leave-type"
                                    class="w-full bg-background border border-border rounded-xl p-3 text-sm focus:outline-none focus:ring-2 focus:ring-accent text-primary">

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

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label for="leave-start"
                                        class="block text-xs font-medium text-gray-400 uppercase tracking-wide mb-1.5">Start
                                        Date</label>
                                    <input type="date" id="leave-start"
                                        class="w-full px-3 py-2.5 border border-border rounded-xl text-sm text-primary focus:outline-none focus:ring-2 focus:ring-accent bg-background">
                                </div>
                                <div>
                                    <label for="leave-end"
                                        class="block text-xs font-medium text-gray-400 uppercase tracking-wide mb-1.5">End
                                        Date</label>
                                    <input type="date" id="leave-end"
                                        class="w-full px-3 py-2.5 border border-border rounded-xl text-sm text-primary focus:outline-none focus:ring-2 focus:ring-accent bg-background">
                                </div>
                            </div>

                            <div>
                                <label for="leave-reason"
                                    class="block text-xs font-medium text-gray-400 uppercase tracking-wide mb-1.5">Reason</label>
                                <textarea id="leave-reason" rows="3" placeholder="Briefly describe your reason"
                                    class="w-full px-3 py-2.5 border border-border rounded-xl text-sm text-primary focus:outline-none focus:ring-2 focus:ring-accent bg-background resize-none"></textarea>
                            </div>

                            <div>
                                <label for="leave-attachment"
                                    class="block text-xs font-medium text-gray-400 uppercase tracking-wide mb-1.5">Attachment</label>
                                <input type="file" id="leave-attachment"
                                    class="w-full text-sm text-gray-500 file:mr-3 file:px-3 file:py-2 file:rounded-xl file:border-0 file:bg-background file:text-primary file:text-sm file:font-medium hover:file:bg-border transition-colors">
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-2">
                                <button type="button" id="cancel-request-leave-btn"
                                    class="px-4 py-2 rounded-xl text-sm font-medium text-gray-500 hover:bg-background transition-colors">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="px-4 py-2 rounded-xl bg-secondary text-white text-sm font-semibold font-button shadow-sm hover:bg-orange-500 transition-colors">
                                    Submit Request
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- ================= MY GROWTH TAB (formerly Performance, now the default/home tab) ================= -->
                <div id="analytics" class="tab-content active max-w-7xl mx-auto space-y-6">

                    <!-- My Analytics Card -->
                    <div class="bg-card border border-border rounded-2xl shadow-sm p-6">
                        <h2 class="font-heading text-base font-bold text-primary">
                            My Analytics
                        </h2>

                        <p id="employee-growth-period" class="text-sm text-gray-400 mb-5">
                            Loading...
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="rounded-xl border border-border p-4 flex items-center gap-4">
                                <div
                                    class="h-11 w-11 rounded-full bg-success/15 text-success flex items-center justify-center flex-shrink-0">
                                    <i class="fa-solid fa-calendar-check"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 uppercase tracking-wide">Attendance</p>
                                    <p id="employee-growth-attendance-rate" class="text-lg font-bold text-primary">
                                        0.0%
                                    </p>
                                </div>
                            </div>

                            <div class="rounded-xl border border-border p-4 flex items-center gap-4">
                                <div
                                    class="h-11 w-11 rounded-full bg-accent/15 text-accent flex items-center justify-center flex-shrink-0">
                                    <i class="fa-solid fa-clock"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 uppercase tracking-wide">Work Hours</p>
                                    <p id="employee-growth-work-hours" class="text-lg font-bold text-primary">
                                        0 hrs
                                    </p>
                                </div>
                            </div>

                            <div class="rounded-xl border border-border p-4 flex items-center gap-4">
                                <div
                                    class="h-11 w-11 rounded-full bg-secondary/15 text-secondary flex items-center justify-center flex-shrink-0">
                                    <i class="fa-solid fa-hourglass-half"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 uppercase tracking-wide">Overtime</p>
                                    <p id="employee-growth-overtime-hours" class="text-lg font-bold text-primary">
                                        0 hrs
                                    </p>
                                </div>
                            </div>

                            <div class="rounded-xl border border-border p-4 flex items-center gap-4">
                                <div
                                    class="h-11 w-11 rounded-full bg-error/15 text-error flex items-center justify-center flex-shrink-0">
                                    <i class="fa-solid fa-plane-departure"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 uppercase tracking-wide">Leave Used</p>
                                    <p id="employee-growth-leave-used" class="text-lg font-bold text-primary">
                                        0 days
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- My Attendance Trend Chart -->
                    <div class="bg-card border border-border rounded-2xl shadow-sm p-6">
                        <h2 class="font-heading text-base font-bold text-primary mb-5">My Attendance Trend</h2>
                        <div class="h-64">
                            <canvas id="attendance-trend-chart"></canvas>
                        </div>
                    </div>

                    <!-- Employee Metrics -->
                    <div class="bg-card border border-border rounded-2xl shadow-sm p-6">
                        <h2 class="font-heading text-base font-bold text-primary pb-3 mb-4 border-b border-border">
                            Employee Metrics
                        </h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div class="rounded-xl bg-background p-4">
                                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Personal
                                    Attendance Rate</p>
                                <p id="employee-metric-attendance-rate" class="text-base font-bold text-primary">0.0%
                                </p>
                            </div>
                            <div class="rounded-xl bg-background p-4">
                                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Total Hours
                                    Worked</p>
                                <p id="employee-metric-work-hours" class="text-base font-bold text-primary">0 hrs</p>
                            </div>
                            <div class="rounded-xl bg-background p-4">
                                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Overtime Hours
                                </p>
                                <p id="employee-metric-overtime-hours" class="text-base font-bold text-primary">0 hrs
                                </p>
                            </div>
                            <div class="rounded-xl bg-background p-4">
                                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Late
                                    Occurrences</p>
                                <p id="employee-metric-late-occurrences" class="text-base font-bold text-primary">0</p>
                            </div>
                            <div class="rounded-xl bg-background p-4">
                                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Leave Used</p>
                                <p id="employee-metric-leave-used" class="text-base font-bold text-primary">0 days</p>
                            </div>
                            <div class="rounded-xl bg-background p-4">
                                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Timesheet
                                    Completion</p>
                                <p id="employee-metric-timesheet-completion" class="text-base font-bold text-primary">0%
                                </p>
                            </div>
                        </div>
                    </div>

                </div>

            </main>
        </div>




    </div>

    <script src="scripts/shared-data.js"></script>
    <script src="scripts/employee.js"></script>

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
                            'employee-profile-avatar'
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
                            'assets/images/profile.png';
                    }

                } catch (error) {
                    console.error(
                        'Employee profile avatar loading error:',
                        error
                    );
                }
            })();
    </script>

</body>

</html>