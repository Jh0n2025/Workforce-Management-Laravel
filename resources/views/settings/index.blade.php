<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="/scripts/api-fetch.js"></script>
    <title>Holiday Travelers - Account Settings</title>
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
                    fontFamily: { heading: ['Poppins', 'sans-serif'], body: ['Inter', 'sans-serif'] },
                    colors: { primary: '#163B6D', secondary: '#F59B45', accent: '#6FA9E6', background: '#F8FAFC', border: '#E5E7EB', success: '#22C55E', error: '#EF4444' }
                }
            }
        };
    </script>
    <style>
        ::-webkit-scrollbar {
            width: 7px;
            height: 7px;
        }

        ::-webkit-scrollbar-thumb {
            background: #E5E7EB;
            border-radius: 5px;
        }

        .toast {
            opacity: 0;
            transform: translateY(10px);
            pointer-events: none;
            transition: .2s ease;
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>

<body class="bg-background text-primary font-body antialiased min-h-screen">
    <header class="sticky top-0 z-30 bg-white/95 backdrop-blur border-b border-border">
        <div class="max-w-4xl mx-auto h-16 px-5 flex items-center justify-between">
            <a id="back-link" href="#"
                class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-primary transition-colors">
                <i class="fa-solid fa-arrow-left"></i>
                Dashboard
            </a>
            <div class="text-right">
                <p id="role-label" class="text-xs uppercase tracking-wider text-slate-400"></p>
                <p class="text-sm font-semibold text-primary" data-session="name"></p>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-5 py-8 space-y-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-primary">Account Settings</h1>
                <a id="view-profile-btn" href="manager-profile.html"
                    class="hidden inline-flex items-center gap-1.5 rounded-lg border border-border bg-white px-3 py-1.5 text-xs font-semibold text-primary hover:bg-slate-50">
                    <i class="fa-solid fa-user"></i> View Profile
                </a>
            </div>
            <p class="mt-1 text-sm text-slate-500">Update your account information and password.</p>
        </div>

        <section class="bg-white border border-border rounded-2xl shadow-sm p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="h-10 w-10 rounded-xl bg-blue-50 text-primary flex items-center justify-center"><i
                        class="fa-solid fa-user-pen"></i></div>
                <div>
                    <h2 class="font-bold text-primary">Profile Information</h2>
                    <p class="text-xs text-slate-400">These details are used throughout the system.</p>
                </div>
            </div>

            <form id="profile-form" class="grid grid-cols-1 md:grid-cols-2 gap-5" novalidate>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Full Name</label>
                    <input id="profile-name" type="text" required
                        class="w-full rounded-xl border border-border bg-white px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Email</label>
                    <input id="profile-email" type="email" required
                        class="w-full rounded-xl border border-border bg-white px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Role</label>
                    <input id="profile-role" type="text" readonly
                        class="w-full rounded-xl border border-border bg-slate-50 px-4 py-3 text-sm text-slate-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Position</label>
                    <input id="profile-position" type="text"
                        class="w-full rounded-xl border border-border bg-white px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Department</label>
                    <input id="profile-department" type="text"
                        class="w-full rounded-xl border border-border bg-white px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent">
                </div>
                <div class="md:col-span-2 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-1">
                    <p id="profile-message" class="text-sm min-h-[20px]"></p>
                    <button id="save-profile-btn" type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white hover:bg-[#102f57] disabled:opacity-60">
                        <i class="fa-solid fa-floppy-disk"></i> Save Changes
                    </button>
                </div>
            </form>
        </section>

        <section class="bg-white border border-border rounded-2xl shadow-sm p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="h-10 w-10 rounded-xl bg-amber-50 text-secondary flex items-center justify-center"><i
                        class="fa-solid fa-lock"></i></div>
                <div>
                    <h2 class="font-bold text-primary">Change Password</h2>
                    <p class="text-xs text-slate-400">Use your current password to set a new one.</p>
                </div>
            </div>

            <form id="password-form" class="grid grid-cols-1 md:grid-cols-2 gap-5" novalidate>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Current Password</label>
                    <div class="relative">
                        <input id="current-password" type="password" autocomplete="current-password" required
                            class="w-full rounded-xl border border-border bg-white px-4 py-3 pr-11 text-sm outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent">
                        <button type="button" data-toggle-password="current-password"
                            class="absolute inset-y-0 right-0 px-3 text-slate-400 hover:text-primary"
                            aria-label="Show current password"><i class="fa-solid fa-eye"></i></button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">New Password</label>
                    <div class="relative">
                        <input id="new-password" type="password" autocomplete="new-password" minlength="6" required
                            class="w-full rounded-xl border border-border bg-white px-4 py-3 pr-11 text-sm outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent">
                        <button type="button" data-toggle-password="new-password"
                            class="absolute inset-y-0 right-0 px-3 text-slate-400 hover:text-primary"
                            aria-label="Show new password"><i class="fa-solid fa-eye"></i></button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Confirm New Password</label>
                    <div class="relative">
                        <input id="confirm-password" type="password" autocomplete="new-password" minlength="6" required
                            class="w-full rounded-xl border border-border bg-white px-4 py-3 pr-11 text-sm outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent">
                        <button type="button" data-toggle-password="confirm-password"
                            class="absolute inset-y-0 right-0 px-3 text-slate-400 hover:text-primary"
                            aria-label="Show confirm password"><i class="fa-solid fa-eye"></i></button>
                    </div>
                </div>
                <div class="md:col-span-2 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <p id="password-message" class="text-sm min-h-[20px]"></p>
                    <button id="change-password-btn" type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-primary px-5 py-3 text-sm font-semibold text-primary hover:bg-blue-50 disabled:opacity-60">
                        <i class="fa-solid fa-key"></i> Update Password
                    </button>
                </div>
            </form>
        </section>

        <section
            class="bg-slate-50 border border-border rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-sm font-bold text-primary">Need to sign out?</h3>
                <p class="text-xs text-slate-500 mt-1">Logging out clears your active session before returning to the
                    login page.</p>
            </div>
            <button id="settings-logout-btn" type="button"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </button>
        </section>
    </main>

    <div id="toast"
        class="toast fixed right-5 bottom-5 bg-primary text-white px-4 py-3 rounded-xl shadow-xl text-sm font-medium">
    </div>

    <script src="scripts/shared-data.js"></script>
    <script>
        (function () {
            // Replaced PHP session management with JS check
            const session = JSON.parse(
                sessionStorage.getItem('ht_v2_session') || 'null'
            );
            if (!session || !['manager', 'employee'].includes(
                String(session.role || '').toLowerCase()
            )) {
                window.location.replace('/login');
                return;
            }

            const isManager = session.role === 'manager';

            // Set dynamic header content
            document.getElementById('back-link').href = isManager ? '/manager' : '/employee';
            document.getElementById('role-label').textContent = isManager ? 'Manager Account' : 'Employee Account';
            document.querySelectorAll('[data-session="name"]').forEach(el => {
                el.textContent = session.name || '';
            });

            // Toggle manager profile button visibility
            if (isManager) {
                document.getElementById('view-profile-btn').classList.remove('hidden');
            }

            const record = typeof SharedData.findEmployeeById === 'function' ? SharedData.findEmployeeById(session.id) : null;

            const fields = {
                name: document.getElementById('profile-name'),
                email: document.getElementById('profile-email'),
                role: document.getElementById('profile-role'),
                position: document.getElementById('profile-position'),
                department: document.getElementById('profile-department')
            };

            fields.name.value = session.name || (record && record.name) || '';
            fields.email.value = session.email || (record && record.email) || '';
            fields.role.value = session.role === 'manager' ? 'Manager' : 'Employee';
            fields.position.value = (record && record.position) || '';
            fields.department.value = (record && record.department) || '';

            loadProfileFromDatabase();

            function setMessage(id, message, ok) {
                const el = document.getElementById(id);
                el.textContent = message || '';
                el.className = 'text-sm min-h-[20px] ' + (ok ? 'text-green-600' : 'text-red-600');
            }

            // Mocking the previous API call behavior to make the HTML fully standalone
            async function postJson(payload) {
                return new Promise((resolve, reject) => {
                    setTimeout(() => {
                        if (payload.action === 'profile') {
                            if (!payload.name || !payload.email) return reject(new Error("Name and email are required."));
                            resolve({
                                ok: true,
                                message: 'Profile updated successfully.',
                                user: { ...session, name: payload.name, email: payload.email }
                            });
                        } else if (payload.action === 'password') {
                            if (payload.new_password !== payload.confirm_password) return reject(new Error("Passwords do not match."));
                            if (payload.new_password.length < 6) return reject(new Error("Password too short."));
                            resolve({ ok: true, message: 'Password updated successfully.' });
                        } else {
                            reject(new Error("Unknown action."));
                        }
                    }, 400); // Simulate brief network delay
                });
            }

            async function updateProfileDatabase(payload) {
                const response = await fetch(
                    'api/account/profile.php',
                    {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    }
                );

                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(
                        result.message ||
                        'Unable to update profile.'
                    );
                }

                return result;
            }

            async function loadProfileFromDatabase() {
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
                        throw new Error(
                            result.message ||
                            'Unable to load profile.'
                        );
                    }

                    const profile = result.data || {};

                    fields.name.value =
                        profile.full_name || '';

                    fields.email.value =
                        profile.email || '';

                    fields.position.value =
                        profile.position || '';

                    fields.department.value =
                        profile.department || '';

                } catch (error) {

                    console.error(
                        'Profile loading error:',
                        error
                    );
                }
            }

            document.getElementById('profile-form').addEventListener('submit', async function (event) {
                event.preventDefault();
                setMessage('profile-message', '', true);
                const btn = document.getElementById('save-profile-btn');
                btn.disabled = true;
                try {
                    const result = await updateProfileDatabase({
                        action: 'profile',
                        name: fields.name.value.trim(),
                        email: fields.email.value.trim(),
                        position: fields.position.value.trim(),
                        department: fields.department.value.trim()
                    });
                    SharedData.setSession(result.user);

                    // Update header name if it was changed
                    document.querySelectorAll('[data-session="name"]').forEach(el => {
                        el.textContent = result.user.name || '';
                    });

                    setMessage('profile-message', result.message, true);
                    showToast(result.message);
                } catch (error) {
                    setMessage('profile-message', error.message, false);
                } finally {
                    btn.disabled = false;
                }
            });

            document.getElementById('password-form').addEventListener('submit', async function (event) {
                event.preventDefault();
                setMessage('password-message', '', true);
                const btn = document.getElementById('change-password-btn');
                btn.disabled = true;
                try {
                    const result = await postJson({
                        action: 'password',
                        current_password: document.getElementById('current-password').value,
                        new_password: document.getElementById('new-password').value,
                        confirm_password: document.getElementById('confirm-password').value
                    });
                    this.reset();
                    setMessage('password-message', result.message, true);
                    showToast(result.message);
                } catch (error) {
                    setMessage('password-message', error.message, false);
                } finally {
                    btn.disabled = false;
                }
            });

            document.getElementById('settings-logout-btn').addEventListener('click', async function () {
                this.disabled = true;
                this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Logging out...';

                // Simulate network logout request
                await new Promise(resolve => setTimeout(resolve, 300));

                SharedData.clearSession();
                window.location.href = '/login';
            });

            document.querySelectorAll('[data-toggle-password]').forEach(button => {
                button.addEventListener('click', () => {
                    const input = document.getElementById(button.dataset.togglePassword);
                    if (!input) return;
                    const showing = input.type === 'text';
                    input.type = showing ? 'password' : 'text';
                    button.innerHTML = showing ? '<i class="fa-solid fa-eye"></i>' : '<i class="fa-solid fa-eye-slash"></i>';
                    button.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
                });
            });

            function showToast(message) {
                const toast = document.getElementById('toast');
                toast.textContent = message;
                toast.classList.add('show');
                setTimeout(() => toast.classList.remove('show'), 2200);
            }
        })();
    </script>
</body>

</html>