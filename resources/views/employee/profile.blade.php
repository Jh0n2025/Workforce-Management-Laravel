<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="/scripts/api-fetch.js"></script>
    <title>Holiday Travelers - Profile</title>
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
        }
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

        .tile {
            position: relative;
            aspect-ratio: 1 / 1;
            overflow: hidden;
            border-radius: 0.75rem;
        }

        .tile-overlay {
            position: absolute;
            inset: 0;
            background: rgba(12, 20, 38, 0.55);
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

        .profile-tab {
            border-top: 2px solid transparent;
        }

        .profile-tab.active {
            border-top-color: #163B6D;
            color: #163B6D;
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

    <!-- Slim top bar -->
    <header class="sticky top-0 z-30 bg-card/95 backdrop-blur border-b border-border">
        <div class="max-w-3xl mx-auto h-14 px-5 flex items-center justify-between">
            <a href="/employee"
                class="flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-primary transition-colors">
                <i class="fa-solid fa-arrow-left"></i>
                <span class="hidden sm:inline">Dashboard</span>
            </a>
            <h1 class="font-heading text-sm font-bold text-primary tracking-tight" id="profile-username">@employee</h1>
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-5 pt-8 pb-16">

        <!-- ============ Profile header ============ -->
        <section class="flex items-start gap-8">
            <div class="flex-shrink-0">
                <button type="button" id="avatar-wrap"
                    class="h-32 w-32 rounded-full border-2 border-border overflow-hidden flex items-center justify-center bg-accent/15 cursor-pointer hover:opacity-90 transition-opacity">
                    <img id="avatar-img" src="/assets/images/employee.png" alt="Profile photo"
                        class="h-full w-full object-cover">
                    <span id="avatar-fallback" class="hidden font-heading text-3xl font-bold text-primary"></span>
                </button>
            </div>

            <div class="flex-1 min-w-0 pt-1">
                <div class="flex flex-wrap items-center gap-3 mb-4">
                    <h2 class="font-heading text-xl font-bold text-primary" data-session="name">Employee Name</h2>
                    <button type="button" id="edit-profile-btn"
                        class="px-4 py-1.5 rounded-lg bg-background border border-border text-sm font-semibold font-button hover:bg-border/60 transition-colors">
                        Edit Profile
                    </button>
                    <button type="button" id="share-profile-btn"
                        class="px-4 py-1.5 rounded-lg bg-background border border-border text-sm font-semibold font-button hover:bg-border/60 transition-colors">
                        Share Profile
                    </button>
                </div>

                <!-- Stats -->
                <div class="flex items-center gap-10 mb-4">
                    <div class="text-sm"><span class="font-bold text-primary">156</span> <span
                            class="text-gray-500">tours led</span></div>
                    <div class="text-sm"><span class="font-bold text-primary">96.4%</span> <span
                            class="text-gray-500">attendance</span></div>
                    <div class="text-sm"><span class="font-bold text-primary">3</span> <span class="text-gray-500">years
                            of service</span></div>
                </div>

                <!-- Bio -->
                <div class="text-sm leading-relaxed">
                    <p class="font-semibold text-primary" id="profile-position">Tour Guide</p>
                    <p class="text-gray-500" id="profile-department">Operations Department</p>
                    <p class="text-gray-600 mt-1.5 max-w-md">Showing guests the best of every city, one tour at a time.
                        Fluent in English &amp; Spanish.</p>
                    <p class="text-accent font-medium mt-1.5">
                        <i class="fa-solid fa-location-dot text-xs mr-1"></i>Manila, Philippines
                    </p>
                </div>
            </div>
        </section>

        <!-- ============ Highlights ============ -->
        <section class="mt-8 flex items-start gap-6 overflow-x-auto no-scrollbar pb-1">
            <div class="flex flex-col items-center gap-2 flex-shrink-0">
                <div
                    class="h-16 w-16 rounded-full bg-secondary/15 text-secondary flex items-center justify-center text-xl border border-border">
                    <i class="fa-solid fa-star"></i>
                </div>
                <span class="text-xs font-medium text-gray-500">5-Star Rated</span>
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
                    <i class="fa-solid fa-people-group"></i>
                </div>
                <span class="text-xs font-medium text-gray-500">Team Player</span>
            </div>
        </section>

        <!-- ============ Tabs ============ -->
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

        <!-- ============ Overview grid ============ -->
        <div id="overview" class="tab-panel active mt-2">
            <div class="grid grid-cols-3 gap-1.5">
                <div class="tile bg-gradient-to-br from-primary to-accent">
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center px-2">
                        <i class="fa-solid fa-umbrella-beach text-2xl mb-2"></i>
                        <p class="text-xs font-semibold">Bali Sunset Tour</p>
                    </div>
                    <div class="tile-overlay text-white text-sm font-semibold">
                        <span><i class="fa-solid fa-heart mr-1.5"></i>42</span>
                        <span><i class="fa-solid fa-comment mr-1.5"></i>6</span>
                    </div>
                </div>
                <div class="tile bg-gradient-to-br from-secondary to-primary">
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center px-2">
                        <i class="fa-solid fa-star text-2xl mb-2"></i>
                        <p class="text-xs font-semibold">5-Star Guest Review</p>
                    </div>
                    <div class="tile-overlay text-white text-sm font-semibold">
                        <span><i class="fa-solid fa-heart mr-1.5"></i>58</span>
                        <span><i class="fa-solid fa-comment mr-1.5"></i>9</span>
                    </div>
                </div>
                <div class="tile bg-gradient-to-br from-accent to-secondary">
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center px-2">
                        <i class="fa-solid fa-people-group text-2xl mb-2"></i>
                        <p class="text-xs font-semibold">Team Building Day</p>
                    </div>
                    <div class="tile-overlay text-white text-sm font-semibold">
                        <span><i class="fa-solid fa-heart mr-1.5"></i>31</span>
                        <span><i class="fa-solid fa-comment mr-1.5"></i>4</span>
                    </div>
                </div>
                <div class="tile bg-gradient-to-br from-primary to-secondary">
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center px-2">
                        <i class="fa-solid fa-certificate text-2xl mb-2"></i>
                        <p class="text-xs font-semibold">Certified: First Aid</p>
                    </div>
                    <div class="tile-overlay text-white text-sm font-semibold">
                        <span><i class="fa-solid fa-heart mr-1.5"></i>25</span>
                        <span><i class="fa-solid fa-comment mr-1.5"></i>2</span>
                    </div>
                </div>
                <div class="tile bg-gradient-to-br from-secondary to-accent">
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center px-2">
                        <i class="fa-solid fa-trophy text-2xl mb-2"></i>
                        <p class="text-xs font-semibold">Employee of the Month</p>
                    </div>
                    <div class="tile-overlay text-white text-sm font-semibold">
                        <span><i class="fa-solid fa-heart mr-1.5"></i>77</span>
                        <span><i class="fa-solid fa-comment mr-1.5"></i>12</span>
                    </div>
                </div>
                <div class="tile bg-gradient-to-br from-accent to-primary">
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center px-2">
                        <i class="fa-solid fa-road text-2xl mb-2"></i>
                        <p class="text-xs font-semibold">Historic City Walk</p>
                    </div>
                    <div class="tile-overlay text-white text-sm font-semibold">
                        <span><i class="fa-solid fa-heart mr-1.5"></i>19</span>
                        <span><i class="fa-solid fa-comment mr-1.5"></i>3</span>
                    </div>
                </div>
                <div class="tile bg-gradient-to-br from-primary to-accent">
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center px-2">
                        <i class="fa-solid fa-mountain text-2xl mb-2"></i>
                        <p class="text-xs font-semibold">Mountain Trek Tour</p>
                    </div>
                    <div class="tile-overlay text-white text-sm font-semibold">
                        <span><i class="fa-solid fa-heart mr-1.5"></i>34</span>
                        <span><i class="fa-solid fa-comment mr-1.5"></i>5</span>
                    </div>
                </div>
                <div class="tile bg-gradient-to-br from-secondary to-primary">
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center px-2">
                        <i class="fa-solid fa-calendar-check text-2xl mb-2"></i>
                        <p class="text-xs font-semibold">Perfect Attendance</p>
                    </div>
                    <div class="tile-overlay text-white text-sm font-semibold">
                        <span><i class="fa-solid fa-heart mr-1.5"></i>21</span>
                        <span><i class="fa-solid fa-comment mr-1.5"></i>1</span>
                    </div>
                </div>
                <div class="tile bg-gradient-to-br from-accent to-secondary">
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-white text-center px-2">
                        <i class="fa-solid fa-handshake text-2xl mb-2"></i>
                        <p class="text-xs font-semibold">Guest Appreciation</p>
                    </div>
                    <div class="tile-overlay text-white text-sm font-semibold">
                        <span><i class="fa-solid fa-heart mr-1.5"></i>15</span>
                        <span><i class="fa-solid fa-comment mr-1.5"></i>2</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============ Documents ============ -->
        <div id="documents" class="tab-panel mt-4">
            <div class="grid sm:grid-cols-2 gap-3">
                <div class="bg-card border border-border rounded-xl p-4 flex items-center gap-4">
                    <div
                        class="h-11 w-11 rounded-full bg-primary/10 text-primary flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-file-contract"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-primary truncate">Employment Contract</p>
                        <p class="text-xs text-gray-400">Signed Jan 12, 2023</p>
                    </div>
                    <button class="ml-auto text-gray-400 hover:text-primary"><i
                            class="fa-solid fa-download"></i></button>
                </div>
                <div class="bg-card border border-border rounded-xl p-4 flex items-center gap-4">
                    <div
                        class="h-11 w-11 rounded-full bg-accent/15 text-accent flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-id-card"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-primary truncate">Company ID Badge</p>
                        <p class="text-xs text-gray-400">Expires Dec 2027</p>
                    </div>
                    <button class="ml-auto text-gray-400 hover:text-primary"><i
                            class="fa-solid fa-download"></i></button>
                </div>
                <div class="bg-card border border-border rounded-xl p-4 flex items-center gap-4">
                    <div
                        class="h-11 w-11 rounded-full bg-success/15 text-success flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-certificate"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-primary truncate">Tour Guide License</p>
                        <p class="text-xs text-gray-400">Renewed Mar 2026</p>
                    </div>
                    <button class="ml-auto text-gray-400 hover:text-primary"><i
                            class="fa-solid fa-download"></i></button>
                </div>
                <div class="bg-card border border-border rounded-xl p-4 flex items-center gap-4">
                    <div
                        class="h-11 w-11 rounded-full bg-secondary/15 text-secondary flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-notes-medical"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-primary truncate">First Aid Certification</p>
                        <p class="text-xs text-gray-400">Valid until Nov 2026</p>
                    </div>
                    <button class="ml-auto text-gray-400 hover:text-primary"><i
                            class="fa-solid fa-download"></i></button>
                </div>
            </div>
        </div>

        <!-- ============ Reviews ============ -->
        <div id="reviews" class="tab-panel mt-4 space-y-3">
            <div class="bg-card border border-border rounded-xl p-4">
                <div class="flex items-center justify-between mb-1.5">
                    <p class="text-sm font-semibold text-primary">Manager — Operations</p>
                    <span class="text-warning text-xs"><i class="fa-solid fa-star"></i><i
                            class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                            class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></span>
                </div>
                <p class="text-sm text-gray-600">Consistently reliable and great with guests. Handles last-minute
                    itinerary changes calmly.</p>
                <p class="text-xs text-gray-400 mt-2">Q2 2026 Performance Review</p>
            </div>
            <div class="bg-card border border-border rounded-xl p-4">
                <div class="flex items-center justify-between mb-1.5">
                    <p class="text-sm font-semibold text-primary">Guest Feedback</p>
                    <span class="text-warning text-xs"><i class="fa-solid fa-star"></i><i
                            class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                            class="fa-solid fa-star"></i><i class="fa-regular fa-star"></i></span>
                </div>
                <p class="text-sm text-gray-600">Very knowledgeable about local history and kept the group engaged the
                    whole trip.</p>
                <p class="text-xs text-gray-400 mt-2">Bali Sunset Tour, Aug 14</p>
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

    <input type="file" id="profile-photo-input" accept="image/jpeg,image/png,image/webp" class="hidden">

    <div id="toast" class="toast bg-primary text-white text-sm font-medium px-4 py-2.5 rounded-xl shadow-lg">
        Profile link copied
    </div>

    <script src="/scripts/shared-data.js"></script>
    <script>
            (function () {
                const session = JSON.parse(
                    sessionStorage.getItem('ht_v2_session') || 'null'
                );

                if (!session || String(session.role).toLowerCase() !== 'employee') {
                    window.location.replace('/login');
                    return;
                }

                const record =
                    SharedData.findEmployeeByEmail(session.email);

                const displayName =
                    session.name ||
                    (record && record.name) ||
                    'Employee Name';

                document.getElementById('profile-username').textContent =
                    '@' +
                    displayName
                        .toLowerCase()
                        .replace(/[^a-z0-9]+/g, '');

                document
                    .querySelectorAll('[data-session="name"]')
                    .forEach(el => {
                        el.textContent = displayName;
                    });

                if (record) {
                    document.getElementById('profile-position').textContent =
                        record.position || 'Employee';

                    document.getElementById('profile-department').textContent =
                        (record.department || 'Operations') +
                        ' Department';
                }

                const img =
                    document.getElementById('avatar-img');
                const fallback =
                    document.getElementById('avatar-fallback');
                const wrap =
                    document.getElementById('avatar-wrap');

                function setEmployeeAvatar(url) {
                    if (!img || !fallback) return;

                    if (!url) {
                        img.removeAttribute('src');
                        img.classList.add('hidden');
                        fallback.textContent = record?.initials || 'EA';
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

                img?.addEventListener('error', () => {

                    if (img) {
                        img.classList.add('hidden');
                    }

                    if (fallback) {
                        fallback.textContent =
                            record?.initials || 'EA';
                        fallback.classList.remove('hidden');
                    }

                    if (wrap && record) {
                        wrap.style.backgroundColor =
                            (record.color || '#6FA9E6') +
                            '26';
                    }
                });

                async function loadEmployeeProfilePhoto() {

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

                        const result = await response.json();

                        if (!response.ok || !result.success) {
                            throw new Error(
                                result.message ||
                                'Unable to load profile photo.'
                            );
                        }

                        const profile = result.data || {};

                        if (profile.full_name) {
                            document
                                .querySelectorAll('[data-session="name"]')
                                .forEach(el => {
                                    el.textContent = profile.full_name;
                                });

                            document.getElementById('profile-username').textContent =
                                '@' +
                                profile.full_name
                                    .toLowerCase()
                                    .replace(/[^a-z0-9]+/g, '');
                        }

                        if (profile.position) {
                            document.getElementById('profile-position').textContent =
                                profile.position;
                        }

                        if (profile.department) {
                            document.getElementById('profile-department').textContent =
                                profile.department + ' Department';
                        }

                        setEmployeeAvatar(profile.avatar_url || '');

                    } catch (error) {

                        console.error(
                            'Employee profile photo loading error:',
                            error
                        );

                        setEmployeeAvatar('');
                    }
                }

                function openProfilePhotoModal() {

                    const modal =
                        document.getElementById('profile-photo-modal');

                    if (!modal) {
                        return;
                    }

                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }

                function closeProfilePhotoModal() {

                    const modal =
                        document.getElementById('profile-photo-modal');

                    if (!modal) {
                        return;
                    }

                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }

                async function uploadEmployeeProfilePhoto(file) {

                    if (!file) {
                        return;
                    }

                    const allowedTypes = [
                        'image/jpeg',
                        'image/png',
                        'image/webp'
                    ];

                    if (!allowedTypes.includes(file.type)) {
                        alert(
                            'Only JPG, PNG, and WEBP photos are allowed.'
                        );
                        return;
                    }

                    if (file.size > 5 * 1024 * 1024) {
                        alert(
                            'Profile photo must be 5 MB or smaller.'
                        );
                        return;
                    }

                    const formData = new FormData();
                    formData.append('action', 'upload');
                    formData.append('photo', file);

                    try {

                        const response = await fetch(
                            '/api/account/profile-photo.php',
                            {
                                method: 'POST',
                                credentials: 'same-origin',
                                body: formData
                            }
                        );

                        const result = await response.json();

                        if (!response.ok || !result.success) {
                            throw new Error(
                                result.message ||
                                'Unable to update profile photo.'
                            );
                        }

                        setEmployeeAvatar(result.avatar_url || '');

                        const currentSession =
                            SharedData.getSession();

                        if (currentSession) {
                            SharedData.setSession({
                                ...currentSession,
                                avatar_url: result.avatar_url
                            });
                        }

                        closeProfilePhotoModal();

                        const toast =
                            document.getElementById('toast');

                        if (toast) {
                            toast.textContent =
                                'Profile photo updated';
                            toast.classList.add('show');

                            setTimeout(() => {
                                toast.classList.remove('show');
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

                async function removeEmployeeProfilePhoto() {

                    try {

                        const formData = new FormData();
                        formData.append('action', 'remove');

                        const response = await fetch(
                            '/api/account/profile-photo.php',
                            {
                                method: 'POST',
                                credentials: 'same-origin',
                                body: formData
                            }
                        );

                        const result = await response.json();

                        if (!response.ok || !result.success) {
                            throw new Error(
                                result.message ||
                                'Unable to remove profile photo.'
                            );
                        }

                        setEmployeeAvatar('');

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
                            document.getElementById('toast');

                        if (toast) {
                            toast.textContent =
                                'Profile photo removed';
                            toast.classList.add('show');

                            setTimeout(() => {
                                toast.classList.remove('show');
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

                // ---- Tabs ----
                const tabs = document.querySelectorAll('.profile-tab');
                const panels = document.querySelectorAll('.tab-panel');

                tabs.forEach(tab => {
                    tab.addEventListener('click', () => {
                        tabs.forEach(t => t.classList.remove('active'));
                        panels.forEach(p => p.classList.remove('active'));
                        tab.classList.add('active');
                        document
                            .getElementById(tab.dataset.tab)
                            ?.classList.add('active');
                    });
                });

                // ---- Edit / Share actions ----
                document
                    .getElementById('edit-profile-btn')
                    ?.addEventListener('click', () => {
                        window.location.href = '/settings';
                    });

                const toast =
                    document.getElementById('toast');

                document
                    .getElementById('share-profile-btn')
                    ?.addEventListener('click', async () => {

                        const url = window.location.href;

                        try {
                            if (navigator.clipboard) {
                                await navigator.clipboard.writeText(url);
                                toast.textContent = 'Profile link copied';
                            } else {
                                toast.textContent = 'Profile link: ' + url;
                            }
                        } catch (error) {
                            toast.textContent = 'Profile link: ' + url;
                        }

                        toast.classList.add('show');

                        setTimeout(() => {
                            toast.classList.remove('show');
                        }, 2200);
                    });

                document
                    .getElementById('avatar-wrap')
                    ?.addEventListener('click', openProfilePhotoModal);

                document
                    .getElementById('upload-profile-photo-btn')
                    ?.addEventListener('click', () => {
                        document
                            .getElementById('profile-photo-input')
                            ?.click();
                    });

                document
                    .getElementById('remove-profile-photo-btn')
                    ?.addEventListener(
                        'click',
                        removeEmployeeProfilePhoto
                    );

                document
                    .getElementById('cancel-profile-photo-btn')
                    ?.addEventListener(
                        'click',
                        closeProfilePhotoModal
                    );

                document
                    .getElementById('profile-photo-input')
                    ?.addEventListener('change', event => {

                        const file =
                            event.target.files?.[0];

                        event.target.value = '';

                        uploadEmployeeProfilePhoto(file);
                    });

                document
                    .getElementById('profile-photo-modal')
                    ?.addEventListener('click', event => {

                        if (event.target.id === 'profile-photo-modal') {
                            closeProfilePhotoModal();
                        }
                    });

                loadEmployeeProfilePhoto();

            })();
    </script>
</body>

</html>