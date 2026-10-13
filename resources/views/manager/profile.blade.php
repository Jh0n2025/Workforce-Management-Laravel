<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="/scripts/api-fetch.js"></script>
    <title>Holiday Travelers - Manager Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@500;700&display=swap"
        rel="stylesheet">
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
        };
    </script>
    <style>
        ::-webkit-scrollbar {
            height: 6px;
            width: 6px;
        }

        ::-webkit-scrollbar-thumb {
            background: #E5E7EB;
            border-radius: 4px;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .tab-panel {
            display: none;
        }

        .tab-panel.active {
            display: block;
            animation: fadeIn .25s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(4px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .profile-tab {
            border-top: 2px solid transparent;
        }

        .profile-tab.active {
            border-top-color: #163B6D;
            color: #163B6D;
        }

        .tile {
            position: relative;
            aspect-ratio: 1 / 1;
            overflow: hidden;
            border-radius: .75rem;
        }

        .tile-overlay {
            position: absolute;
            inset: 0;
            background: rgba(12, 20, 38, .55);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1.25rem;
            opacity: 0;
            transition: opacity .2s ease-in-out;
        }

        .tile:hover .tile-overlay {
            opacity: 1;
        }

        .toast {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%) translateY(20px);
            opacity: 0;
            pointer-events: none;
            transition: all .25s ease-in-out;
        }

        .toast.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
    </style>
</head>

<body class="bg-background text-primary font-body antialiased">

    <header class="sticky top-0 z-30 bg-card/95 backdrop-blur border-b border-border">
        <div class="max-w-3xl mx-auto h-14 px-5 flex items-center justify-between">
            <a href="/manager"
                class="flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-primary transition-colors">
                <i class="fa-solid fa-arrow-left"></i>
                <span class="hidden sm:inline">Dashboard</span>
            </a>
            <h1 class="font-heading text-sm font-bold text-primary tracking-tight" id="profile-username">@manager</h1>
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-5 pt-8 pb-16">

        <section class="flex items-start gap-8">
            <div class="flex-shrink-0">
                <button type="button" id="avatar-wrap"
                    class="h-32 w-32 rounded-full border-2 border-border overflow-hidden flex items-center justify-center bg-accent/15 cursor-pointer hover:opacity-90 transition-opacity"
                    aria-label="Change profile photo">

                    <img id="avatar-img" alt="Manager profile photo" class="hidden h-full w-full object-cover">

                    <span id="avatar-fallback" class="hidden font-heading text-3xl font-bold text-primary">
                    </span>

                </button>
            </div>

            <div class="flex-1 min-w-0 pt-1">
                <div class="flex flex-wrap items-center gap-3 mb-4">
                    <h2 class="font-heading text-xl font-bold text-primary" data-session="name">Manager Account</h2>
                    <button type="button" id="edit-profile-btn"
                        class="px-4 py-1.5 rounded-lg bg-background border border-border text-sm font-semibold font-button hover:bg-border/60 transition-colors">
                        Edit Profile
                    </button>
                    <button type="button" id="share-profile-btn"
                        class="px-4 py-1.5 rounded-lg bg-background border border-border text-sm font-semibold font-button hover:bg-border/60 transition-colors">
                        Share Profile
                    </button>
                </div>

                <div class="flex items-center gap-10 mb-4 flex-wrap">
                    <div class="text-sm"><span class="font-bold text-primary" id="team-count-stat">0</span> <span
                            class="text-gray-500">team members</span></div>
                    <div class="text-sm"><span class="font-bold text-primary" id="active-team-stat">0%</span> <span
                            class="text-gray-500">team active</span></div>
                    <div class="text-sm"><span class="font-bold text-primary">Manager</span> <span
                            class="text-gray-500">account</span></div>
                </div>

                <div class="text-sm leading-relaxed">
                    <p class="font-semibold text-primary" id="profile-position">Operations Manager</p>
                    <p class="text-gray-500" id="profile-department">Operations Department</p>
                    <p class="text-gray-600 mt-1.5 max-w-md">Managing daily workforce operations, coordinating team
                        schedules, monitoring attendance, and supporting leave and timesheet activities.</p>
                    <p class="text-accent font-medium mt-1.5">
                        <i class="fa-solid fa-location-dot text-xs mr-1"></i>Manila, Philippines
                    </p>
                </div>
            </div>
        </section>

        <section class="mt-8 flex items-start gap-6 overflow-x-auto no-scrollbar pb-1">
            <div class="flex flex-col items-center gap-2 flex-shrink-0">
                <div
                    class="h-16 w-16 rounded-full bg-secondary/15 text-secondary flex items-center justify-center text-xl border border-border">
                    <i class="fa-solid fa-users"></i>
                </div>
                <span class="text-xs font-medium text-gray-500">Team Leader</span>
            </div>
            <div class="flex flex-col items-center gap-2 flex-shrink-0">
                <div
                    class="h-16 w-16 rounded-full bg-success/15 text-success flex items-center justify-center text-xl border border-border">
                    <i class="fa-solid fa-certificate"></i>
                </div>
                <span class="text-xs font-medium text-gray-500">Certified</span>
            </div>
            <div class="flex flex-col items-center gap-2 flex-shrink-0">
                <div
                    class="h-16 w-16 rounded-full bg-accent/15 text-accent flex items-center justify-center text-xl border border-border">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <span class="text-xs font-medium text-gray-500">Punctual</span>
            </div>
            <div class="flex flex-col items-center gap-2 flex-shrink-0">
                <div
                    class="h-16 w-16 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xl border border-border">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <span class="text-xs font-medium text-gray-500">Workforce</span>
            </div>
        </section>

        <nav
            class="mt-8 border-t border-border flex items-center justify-center gap-16 text-xs font-semibold uppercase tracking-wide text-gray-400">
            <button type="button" class="profile-tab active flex items-center gap-2 py-3.5" data-tab="overview">
                <i class="fa-solid fa-table-cells-large"></i> Overview
            </button>
            <button type="button" class="profile-tab flex items-center gap-2 py-3.5" data-tab="documents">
                <i class="fa-regular fa-bookmark"></i> Documents
            </button>
            <button type="button" class="profile-tab flex items-center gap-2 py-3.5" data-tab="reviews">
                <i class="fa-regular fa-star"></i> Reviews
            </button>
        </nav>

        <div id="overview" class="tab-panel active mt-2">
            <div class="grid grid-cols-3 gap-1.5">
                <div class="tile bg-gradient-to-br from-primary to-accent">
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center px-2">
                        <i class="fa-solid fa-calendar-days text-2xl mb-2"></i>
                        <p class="text-xs font-semibold">Team Schedule</p>
                    </div>
                    <div class="tile-overlay text-white text-sm font-semibold">
                        <span><i class="fa-solid fa-users mr-1.5"></i>Team</span>
                        <span><i class="fa-solid fa-calendar-check mr-1.5"></i>Schedule</span>
                    </div>
                </div>
                <div class="tile bg-gradient-to-br from-secondary to-primary">
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center px-2">
                        <i class="fa-solid fa-file-lines text-2xl mb-2"></i>
                        <p class="text-xs font-semibold">Timesheets</p>
                    </div>
                    <div class="tile-overlay text-white text-sm font-semibold">
                        <span><i class="fa-solid fa-clock mr-1.5"></i>Review</span>
                        <span><i class="fa-solid fa-check mr-1.5"></i>Track</span>
                    </div>
                </div>
                <div class="tile bg-gradient-to-br from-accent to-primary">
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center px-2">
                        <i class="fa-solid fa-chart-pie text-2xl mb-2"></i>
                        <p class="text-xs font-semibold">Workforce Analytics</p>
                    </div>
                    <div class="tile-overlay text-white text-sm font-semibold">
                        <span><i class="fa-solid fa-chart-line mr-1.5"></i>Insights</span>
                        <span><i class="fa-solid fa-user-clock mr-1.5"></i>Attendance</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-5">
                <div class="bg-card border border-border rounded-xl p-4">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="h-10 w-10 rounded-full bg-primary/10 text-primary flex items-center justify-center">
                            <i class="fa-solid fa-id-card"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-primary">Manager Account</p>
                            <p class="text-xs text-gray-400">Role: Manager</p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500">Email</p>
                    <p class="text-sm font-medium text-gray-700 break-all" data-session="email"></p>
                </div>
                <div class="bg-card border border-border rounded-xl p-4">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="h-10 w-10 rounded-full bg-success/10 text-success flex items-center justify-center">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-primary">Account Status</p>
                            <p class="text-xs text-gray-400">Current access</p>
                        </div>
                    </div>
                    <p class="inline-flex items-center gap-2 text-sm font-medium text-success"><span
                            class="h-2.5 w-2.5 rounded-full bg-success"></span>Active</p>
                </div>
            </div>
        </div>

        <div id="documents" class="tab-panel mt-4 space-y-3">
            <div class="bg-card border border-border rounded-xl p-4 flex items-center gap-4">
                <div
                    class="h-11 w-11 rounded-full bg-primary/10 text-primary flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-id-badge"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-primary">Manager Account Record</p>
                    <p class="text-xs text-gray-400">Workforce Management System</p>
                </div>
                <span class="ml-auto text-xs font-medium text-success">Active</span>
            </div>
            <div class="bg-card border border-border rounded-xl p-4 flex items-center gap-4">
                <div
                    class="h-11 w-11 rounded-full bg-accent/10 text-accent flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-primary">Account Security</p>
                    <p class="text-xs text-gray-400">Password-protected manager access</p>
                </div>
                <span class="ml-auto text-xs font-medium text-primary">Protected</span>
            </div>
        </div>

        <div id="reviews" class="tab-panel mt-4 space-y-3">
            <div class="bg-card border border-border rounded-xl p-4">
                <div class="flex items-center justify-between mb-1.5">
                    <p class="text-sm font-semibold text-primary">Operations Management</p>
                    <span class="text-warning text-xs"><i class="fa-solid fa-star"></i><i
                            class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                            class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></span>
                </div>
                <p class="text-sm text-gray-600">Manager responsibilities include coordinating schedules, attendance,
                    leave, timesheets, and workforce activity.</p>
                <p class="text-xs text-gray-400 mt-2">Manager profile overview</p>
            </div>
        </div>

    </main>

    <!-- Change Profile Photo Modal -->
    <div id="profile-photo-modal"
        class="hidden fixed inset-0 z-[80] items-center justify-center bg-black/60 backdrop-blur-sm p-4">

        <div class="w-full max-w-[560px] rounded-2xl bg-white text-primary shadow-2xl overflow-hidden">

            <!-- Modal Header -->
            <div class="px-6 py-6 text-center border-b border-border">
                <h2 class="text-lg font-semibold">
                    Change Profile Photo
                </h2>
            </div>

            <!-- Modal Options -->
            <div class="divide-y divide-border">

                <button type="button" id="upload-profile-photo-btn"
                    class="w-full px-6 py-5 text-center text-[#6B7CFF] font-semibold hover:bg-white/5 transition-colors">
                    Upload Photo
                </button>

                <button type="button" id="remove-profile-photo-btn"
                    class="w-full px-6 py-5 text-center text-red-500 font-semibold hover:bg-white/5 transition-colors">
                    Remove Current Photo
                </button>

                <button type="button" id="cancel-profile-photo-btn"
                    class="w-full px-6 py-5 text-center text-primary font-semibold hover:bg-slate-50 transition-colors">
                    Cancel
                </button>

            </div>

        </div>
    </div>

    <!-- Hidden File Input -->
    <input type="file" id="profile-photo-input" accept="image/jpeg,image/png,image/webp" class="hidden">

    <div id="toast" class="toast bg-primary text-white text-sm font-medium px-4 py-2.5 rounded-xl shadow-lg">Profile
        link copied</div>

    <script src="/scripts/shared-data.js"></script>
    <script>
        (function () {
            // Replaced server-side PHP session check with client-side validation
            const session = typeof SharedData !== 'undefined' ? SharedData.getSession() : null;



            const record = session ? SharedData.findEmployeeByEmail(session.email) : null;
            const displayName = session?.name || (record && record.name) || 'Manager Account';

            document.getElementById('profile-username').textContent = '@' + displayName.toLowerCase().replace(/[^a-z0-9]+/g, '');

            if (record) {
                const position = record.position || 'Operations Manager';
                const department = record.department || 'Operations';
                document.getElementById('profile-position').textContent = position;
                document.getElementById('profile-department').textContent = department + ' Department';

                const team = SharedData.getTeamForManager(record.id) || [];
                const activeCount = team.filter(employee => String(employee.status || '').toUpperCase() === 'ACTIVE').length;
                const activePercent = team.length ? Math.round((activeCount / team.length) * 100) : 0;
                document.getElementById('team-count-stat').textContent = String(team.length);
                document.getElementById('active-team-stat').textContent = activePercent + '%';

                const img = document.getElementById('avatar-img');
                const fallback = document.getElementById('avatar-fallback');
                const wrap = document.getElementById('avatar-wrap');

                function setManagerAvatar(url) {
                    if (!img || !fallback) return;

                    if (!url) {
                        img.removeAttribute('src');
                        img.classList.add('hidden');
                        fallback.textContent = record?.initials || 'MA';
                        fallback.classList.remove('hidden');
                        return;
                    }

                    const photoUrl =
                        /^(https?:)?\/\//i.test(url) || url.startsWith('/')
                            ? url
                            : '/' + url;

                    img.src = photoUrl
                        + (photoUrl.includes('?') ? '&' : '?')
                        + 'v=' + Date.now();

                    img.classList.remove('hidden');
                    fallback.classList.add('hidden');
                }

                img.addEventListener('error', () => {

                    img.classList.add('hidden');

                    fallback.textContent =
                        record.initials || 'MA';

                    fallback.classList.remove('hidden');

                    wrap.style.backgroundColor =
                        (record.color || '#6FA9E6') + '26';

                });
            }

            document.querySelectorAll('[data-session="email"]').forEach(el => {
                el.textContent = session?.email || '';
            });

            // Replaced php names with html
            document.querySelectorAll('[data-session="name"]').forEach(el => {
                el.textContent = displayName;
            });

            const tabs = document.querySelectorAll('.profile-tab');
            const panels = document.querySelectorAll('.tab-panel');
            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    tabs.forEach(item => item.classList.remove('active'));
                    panels.forEach(panel => panel.classList.remove('active'));
                    tab.classList.add('active');
                    document.getElementById(tab.dataset.tab).classList.add('active');
                });
            });

            document.getElementById('edit-profile-btn').addEventListener('click', () => {
                window.location.href = '/settings';
            });

            const toast = document.getElementById('toast');
            document.getElementById('share-profile-btn').addEventListener('click', async () => {
                const url = window.location.href;
                try {
                    if (navigator.clipboard) {
                        await navigator.clipboard.writeText(url);
                        toast.textContent = 'Profile link copied';
                    } else {
                        toast.textContent = 'Profile link: ' + url;
                    }
                } catch (e) {
                    toast.textContent = 'Profile link: ' + url;
                }
                toast.classList.add('show');
                setTimeout(() => toast.classList.remove('show'), 2200);
            });
            /* ============================================================
   MANAGER PROFILE PHOTO
   ============================================================ */

            async function loadManagerProfilePhoto() {

                try {

                    const response = await fetch(
                        '/api/account/profile.php',
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
                            'Unable to load profile photo.'
                        );
                    }

                    const profile =
                        result.data || {};

                    const img =
                        document.getElementById(
                            'avatar-img'
                        );

                    const fallback =
                        document.getElementById(
                            'avatar-fallback'
                        );

                    if (!img || !fallback) {
                        return;
                    }

                    if (profile.avatar_url) {
                        const savedUrl = String(profile.avatar_url).trim();

                        const photoUrl =
                            /^(https?:)?\/\//i.test(savedUrl) || savedUrl.startsWith('/')
                                ? savedUrl
                                : '/' + savedUrl.replace(/^\/+/, '');

                        img.src = photoUrl
                            + (photoUrl.includes('?') ? '&' : '?')
                            + 'v=' + Date.now();

                        img.classList.remove('hidden');
                        fallback.classList.add('hidden');
                    } else {
                        img.removeAttribute('src');
                        img.classList.add('hidden');

                        fallback.textContent = record?.initials || 'MA';
                        fallback.classList.remove('hidden');
                    }

                } catch (error) {

                    console.error(
                        'Manager profile photo loading error:',
                        error
                    );
                }
            }


            function openProfilePhotoModal() {

                const modal =
                    document.getElementById(
                        'profile-photo-modal'
                    );

                if (!modal) {
                    return;
                }

                modal.classList.remove(
                    'hidden'
                );

                modal.classList.add(
                    'flex'
                );
            }


            function closeProfilePhotoModal() {

                const modal =
                    document.getElementById(
                        'profile-photo-modal'
                    );

                if (!modal) {
                    return;
                }

                modal.classList.add(
                    'hidden'
                );

                modal.classList.remove(
                    'flex'
                );
            }


            /* ============================================================
               UPLOAD PHOTO
               ============================================================ */

            async function uploadManagerProfilePhoto(file) {

                if (!file) {
                    return;
                }

                const allowedTypes = [
                    'image/jpeg',
                    'image/png',
                    'image/webp'
                ];

                if (
                    !allowedTypes.includes(
                        file.type
                    )
                ) {

                    alert(
                        'Only JPG, PNG, and WEBP photos are allowed.'
                    );

                    return;
                }

                if (
                    file.size >
                    5 * 1024 * 1024
                ) {

                    alert(
                        'Profile photo must be 5 MB or smaller.'
                    );

                    return;
                }

                const formData =
                    new FormData();

                formData.append(
                    'action',
                    'upload'
                );

                formData.append(
                    'photo',
                    file
                );

                try {

                    const response =
                        await fetch(
                            '/api/account/profile-photo.php',
                            {
                                method: 'POST',
                                credentials: 'same-origin',
                                body: formData
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
                            'Unable to update profile photo.'
                        );
                    }

                    const img =
                        document.getElementById(
                            'avatar-img'
                        );

                    const fallback =
                        document.getElementById(
                            'avatar-fallback'
                        );

                    if (img) {

                        img.src =
                            result.avatar_url +
                            '?' +
                            'v=' +
                            Date.now();

                        img.classList.remove(
                            'hidden'
                        );
                    }

                    if (fallback) {
                        fallback.classList.add(
                            'hidden'
                        );
                    }

                    /*
                     * Store the photo URL in the current
                     * browser session too.
                     */
                    const currentSession =
                        SharedData.getSession();

                    if (currentSession) {

                        SharedData.setSession({
                            ...currentSession,
                            avatar_url:
                                result.avatar_url
                        });
                    }

                    closeProfilePhotoModal();

                    const toast =
                        document.getElementById(
                            'toast'
                        );

                    if (toast) {

                        toast.textContent =
                            'Profile photo updated';

                        toast.classList.add(
                            'show'
                        );

                        setTimeout(() => {
                            toast.classList.remove(
                                'show'
                            );
                        }, 2200);
                    }

                } catch (error) {

                    console.error(
                        'Profile photo upload error:',
                        error
                    );

                    alert(
                        error.message ||
                        'Unable to update profile photo.'
                    );
                }
            }


            /* ============================================================
               REMOVE PHOTO
               ============================================================ */

            async function removeManagerProfilePhoto() {

                try {

                    const formData =
                        new FormData();

                    formData.append(
                        'action',
                        'remove'
                    );

                    const response =
                        await fetch(
                            '/api/account/profile-photo.php',
                            {
                                method: 'POST',
                                credentials: 'same-origin',
                                body: formData
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
                            'Unable to remove profile photo.'
                        );
                    }

                    const img =
                        document.getElementById(
                            'avatar-img'
                        );

                    const fallback =
                        document.getElementById(
                            'avatar-fallback'
                        );

                    if (img) {

                        img.src =
                            '/assets/images/employee.png';

                        img.classList.remove(
                            'hidden'
                        );
                    }

                    if (fallback) {
                        fallback.classList.add(
                            'hidden'
                        );
                    }

                    const currentSession =
                        SharedData.getSession();

                    if (currentSession) {

                        SharedData.setSession({
                            ...currentSession,
                            avatar_url: null
                        });
                    }

                    closeProfilePhotoModal();

                    const toast =
                        document.getElementById(
                            'toast'
                        );

                    if (toast) {

                        toast.textContent =
                            'Profile photo removed';

                        toast.classList.add(
                            'show'
                        );

                        setTimeout(() => {
                            toast.classList.remove(
                                'show'
                            );
                        }, 2200);
                    }

                } catch (error) {

                    console.error(
                        'Profile photo removal error:',
                        error
                    );

                    alert(
                        error.message ||
                        'Unable to remove profile photo.'
                    );
                }
            }


            /* ============================================================
               BUTTON EVENTS
               ============================================================ */

            document
                .getElementById(
                    'avatar-wrap'
                )
                ?.addEventListener(
                    'click',
                    openProfilePhotoModal
                );


            document
                .getElementById(
                    'upload-profile-photo-btn'
                )
                ?.addEventListener(
                    'click',
                    () => {

                        document
                            .getElementById(
                                'profile-photo-input'
                            )
                            ?.click();

                    }
                );


            document
                .getElementById(
                    'remove-profile-photo-btn'
                )
                ?.addEventListener(
                    'click',
                    removeManagerProfilePhoto
                );


            document
                .getElementById(
                    'cancel-profile-photo-btn'
                )
                ?.addEventListener(
                    'click',
                    closeProfilePhotoModal
                );


            document
                .getElementById(
                    'profile-photo-input'
                )
                ?.addEventListener(
                    'change',
                    event => {

                        const file =
                            event.target.files?.[0];

                        event.target.value = '';

                        uploadManagerProfilePhoto(
                            file
                        );
                    }
                );


            document
                .getElementById(
                    'profile-photo-modal'
                )
                ?.addEventListener(
                    'click',
                    event => {

                        if (
                            event.target.id ===
                            'profile-photo-modal'
                        ) {

                            closeProfilePhotoModal();
                        }
                    }
                );


            /* Load the photo stored in MySQL */
            loadManagerProfilePhoto();
        })();
    </script>
</body>

</html>