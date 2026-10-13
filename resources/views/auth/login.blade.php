<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="/scripts/api-fetch.js"></script>
    <title>Holiday Travelers - Admin Portal</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@500;700&display=swap"
        rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .transition-all-300 {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @media (max-width: 640px) {
            #login-screen {
                min-height: 100dvh;
                padding: max(20px, env(safe-area-inset-top)) 14px max(24px, env(safe-area-inset-bottom));
            }

            #login-card {
                max-width: 420px;
                padding: 24px 18px 20px;
                border-radius: 22px;
                box-shadow: 0 18px 45px rgba(22, 59, 109, 0.10);
            }

            #company-logo-frame {
                width: 88px;
                height: 88px;
            }

            #company-logo {
                width: 76px;
                height: 76px;
            }

            #company-name {
                font-size: 1.9rem;
            }

            #login-form input,
            #login-form button[type="submit"] {
                min-height: 48px;
                border-radius: 13px;
            }
        }

        @media (max-width: 380px) {
            #login-card {
                padding-left: 15px;
                padding-right: 15px;
            }

            #company-name {
                font-size: 1.7rem;
            }
        }
    </style>
</head>

<body class="min-h-screen bg-background font-body text-primary antialiased">

    <main id="login-screen"
        class="relative flex min-h-screen items-center justify-center overflow-hidden bg-background">

        <div
            class="pointer-events-none absolute left-0 top-0 h-1/3 w-full bg-linear-to-b from-accent/20 to-transparent">
        </div>
        <div class="pointer-events-none absolute bottom-0 right-0 h-96 w-96 rounded-full bg-secondary/10 blur-3xl">
        </div>

        <section id="login-card"
            class="relative z-10 mx-4 w-full max-w-md rounded-2xl border border-border bg-card p-10 shadow-xl transition-all-300">

            <header class="mb-6 flex flex-col items-center">
                <div id="company-logo-frame"
                    class="flex h-28 w-28 items-center justify-center overflow-hidden rounded-full border-2 border-orange-100 bg-white shadow-xl">

                    <img id="company-logo" src="{{ asset('assets/images/holiday-logo.jpg') }}"
                        alt="Holiday Travelers Inc." class="h-24 w-24 object-contain">
                </div>

                <h1 id="company-name" class="mt-3 font-heading text-4xl font-black leading-none text-primary">
                    Holiday Travelers
                </h1>

                <p class="mt-1 text-lg font-medium text-secondary">
                    Travel &amp; Tours Inc.
                </p>

                <p class="mt-4 text-center text-sm uppercase tracking-wider text-gray-500">
                    Workforce Management Portal
                </p>
            </header>

            <div id="auth-feedback" role="status" aria-live="polite"
                class="hidden items-start gap-2 rounded-lg border p-3 text-sm font-medium">

                <i id="auth-feedback-icon" data-lucide="alert-circle" class="h-5 w-5 shrink-0"></i>

                <span id="auth-feedback-text"></span>
            </div>

            <form id="login-form" class="space-y-5">

                <div id="login-alert" role="alert" aria-live="polite"
                    class="hidden items-start gap-2 rounded-lg border border-error/20 bg-error/10 p-3 text-sm font-medium text-error">

                    <i data-lucide="alert-circle" class="h-5 w-5 shrink-0"></i>
                    <span id="login-alert-text">Invalid login.</span>
                </div>

                <div>
                    <label for="login-email" class="mb-1 block font-body text-sm font-medium text-primary">
                        Email Address
                    </label>

                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <i data-lucide="mail" class="h-5 w-5 text-gray-400"></i>
                        </div>

                        <input type="email" id="login-email" name="email" autocomplete="username" required
                            class="w-full rounded-xl border border-border bg-background py-3 pl-10 pr-4 font-body text-sm text-primary transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-accent"
                            placeholder="Enter your email">
                    </div>
                </div>

                <div>
                    <label for="login-password" class="mb-1 block font-body text-sm font-medium text-primary">
                        Password
                    </label>

                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <i data-lucide="lock" class="h-5 w-5 text-gray-400"></i>
                        </div>

                        <input type="password" id="login-password" name="password" autocomplete="current-password"
                            required
                            class="w-full rounded-xl border border-border bg-background py-3 pl-10 pr-12 font-body text-sm text-primary transition-all focus:border-transparent focus:outline-none focus:ring-2 focus:ring-accent"
                            placeholder="••••••••">

                        <button type="button" id="toggle-password" aria-label="Show or hide password"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 transition-colors hover:text-primary">

                            <i id="password-toggle-icon" data-lucide="eye" class="h-5 w-5"></i>
                        </button>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" id="login-btn-submit"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-secondary py-3 font-button font-medium text-white shadow-lg shadow-secondary/30 transition-all hover:bg-[#E08A3B] active:scale-[0.98]">

                        <span id="login-btn-text">Login</span>

                        <span id="login-spinner"
                            class="hidden h-5 w-5 animate-spin rounded-full border-2 border-white border-t-transparent">
                        </span>
                    </button>
                </div>
            </form>
            <section id="otp-panel" class="hidden space-y-5">

                <div class="text-center">
                    <h2 class="font-heading text-xl font-bold text-primary">
                        Verify your email
                    </h2>

                    <p id="otp-description" class="mt-2 text-sm text-gray-500">
                        Enter the six-digit verification code sent to your email.
                    </p>
                </div>

                <form id="otp-form" class="space-y-4">
                    <div>
                        <label for="otp-code" class="mb-1 block text-sm font-medium text-primary">
                            Verification Code
                        </label>

                        <input type="text" id="otp-code" name="code" inputmode="numeric" autocomplete="one-time-code"
                            pattern="[0-9]{6}" minlength="6" maxlength="6" required
                            class="w-full rounded-xl border border-border bg-background px-4 py-3 text-center text-xl tracking-[0.4em] text-primary focus:border-transparent focus:outline-none focus:ring-2 focus:ring-accent"
                            placeholder="000000">
                    </div>

                    <button type="submit" id="otp-submit"
                        class="w-full rounded-xl bg-secondary py-3 font-button font-medium text-white shadow-lg transition-all hover:bg-[#E08A3B] disabled:opacity-60">
                        Verify &amp; Continue
                    </button>
                </form>

                <div class="flex items-center justify-between text-sm">
                    <button type="button" id="resend-otp-btn" class="font-medium text-primary underline">
                        Resend code
                    </button>

                    <button type="button" id="back-to-login-btn" class="text-gray-500 underline">
                        Back to login
                    </button>
                </div>

            </section>
        </section>
    </main>

    <script>
        if (window.lucide) {
            window.lucide.createIcons();
        }

        const loginForm = document.getElementById('login-form');
        const otpForm = document.getElementById('otp-form');
        const otpPanel = document.getElementById('otp-panel');
        const otpDescription = document.getElementById('otp-description');
        const otpCode = document.getElementById('otp-code');

        const feedback = document.getElementById('auth-feedback');
        const feedbackText = document.getElementById('auth-feedback-text');
        const feedbackIcon = document.getElementById('auth-feedback-icon');

        const loginButton = document.getElementById('login-btn-submit');
        const loginButtonText = document.getElementById('login-btn-text');
        const loginSpinner = document.getElementById('login-spinner');

        const otpButton = document.getElementById('otp-submit');
        const resendButton = document.getElementById('resend-otp-btn');

        function showFeedback(message, type = 'error') {
            feedbackText.textContent = message;

            const isSuccess = type === 'success';

            feedback.className = isSuccess
                ? 'flex items-start gap-2 rounded-lg border border-success/20 bg-success/10 p-3 text-sm font-medium text-success'
                : 'flex items-start gap-2 rounded-lg border border-error/20 bg-error/10 p-3 text-sm font-medium text-error';

            feedbackIcon.setAttribute(
                'data-lucide',
                isSuccess ? 'check-circle' : 'alert-circle'
            );

            if (window.lucide) {
                window.lucide.createIcons();
            }
        }

        function clearFeedback() {
            feedback.className =
                'hidden items-start gap-2 rounded-lg border p-3 text-sm font-medium';
            feedbackText.textContent = '';
        }

        async function getCsrfToken() {
            const response = await fetch('/sanctum/csrf-cookie', {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Unable to initialize secure login. Please reload the page.');
            }

            const cookie = document.cookie
                .split('; ')
                .find(item => item.startsWith('XSRF-TOKEN='));

            if (!cookie) {
                throw new Error('Security cookie is missing. Please reload the page.');
            }

            return decodeURIComponent(cookie.substring('XSRF-TOKEN='.length));
        }

        async function postJson(url, payload) {
            const csrfToken = await getCsrfToken();

            const response = await fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-XSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            });

            let data;

            try {
                data = await response.json();
            } catch {
                throw new Error('The server returned an unexpected response.');
            }

            return { response, data };
        }

        function showLoginSuccess(data) {
            const role = String(data.role || '').toLowerCase();

            const destinations = {
                admin: '/admin',
                manager: '/manager',
                employee: '/employee'
            };

            const destination = destinations[role];

            if (!destination) {
                showFeedback(
                    `Login successful, but the ${role || 'user'} dashboard is not connected.`,
                    'success'
                );
                return;
            }

            // Preserve the session format expected by the existing dashboard scripts.
            sessionStorage.setItem('ht_v2_session', JSON.stringify({
                id: data.employee_id || data.account_id || null,
                account_id: data.account_id || null,
                employee_id: data.employee_id || null,
                name: data.name || '',
                email: data.email || '',
                role: role
            }));

            window.location.assign(destination);
        }

        // Password visibility
        document.getElementById('toggle-password')?.addEventListener('click', function () {
            const password = document.getElementById('login-password');
            const icon = document.getElementById('password-toggle-icon');

            if (!password || !icon) return;

            const showPassword = password.type === 'password';

            password.type = showPassword ? 'text' : 'password';
            icon.setAttribute('data-lucide', showPassword ? 'eye-off' : 'eye');

            if (window.lucide) {
                window.lucide.createIcons();
            }
        });

        // Login
        loginForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            clearFeedback();

            loginButton.disabled = true;
            loginButtonText.textContent = 'Signing in...';
            loginSpinner.classList.remove('hidden');

            const email = document.getElementById('login-email').value.trim();
            const password = document.getElementById('login-password').value;

            try {
                const { response, data } = await postJson('/api/auth/login', {
                    email,
                    password
                });

                if (data.requires_2fa) {
                    loginForm.classList.add('hidden');
                    otpPanel.classList.remove('hidden');

                    otpDescription.textContent =
                        `${data.message} ${data.masked_email || ''}`.trim();

                    document.getElementById('login-password').value = '';

                    showFeedback(
                        'Check your email for the six-digit verification code.',
                        'success'
                    );

                    otpCode.focus();
                    return;
                }

                if (response.ok && data.success) {
                    showLoginSuccess(data);
                    return;
                }

                showFeedback(data.message || 'Unable to sign in.');
            } catch (error) {
                showFeedback(error.message || 'A connection error occurred.');
            } finally {
                loginButton.disabled = false;
                loginButtonText.textContent = 'Login';
                loginSpinner.classList.add('hidden');
            }
        });

        // Verify OTP
        otpForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            clearFeedback();

            otpButton.disabled = true;
            otpButton.textContent = 'Verifying...';

            try {
                const { response, data } = await postJson('/api/auth/verify-otp', {
                    code: otpCode.value.trim()
                });

                if (response.ok && data.success) {
                    showLoginSuccess(data);
                    return;
                }

                showFeedback(data.message || 'Verification failed.');
                otpCode.focus();
            } catch (error) {
                showFeedback(error.message || 'A connection error occurred.');
            } finally {
                otpButton.disabled = false;
                otpButton.textContent = 'Verify & Continue';
            }
        });

        // Resend OTP
        resendButton.addEventListener('click', async function () {
            clearFeedback();

            resendButton.disabled = true;
            resendButton.textContent = 'Sending...';

            try {
                const { response, data } = await postJson('/api/auth/resend-otp', {});

                if (response.ok && data.success) {
                    otpCode.value = '';
                    showFeedback(data.message, 'success');
                    otpCode.focus();
                } else {
                    showFeedback(data.message || 'Unable to resend the code.');
                }
            } catch (error) {
                showFeedback(error.message || 'A connection error occurred.');
            } finally {
                resendButton.disabled = false;
                resendButton.textContent = 'Resend code';
            }
        });

        // Return to the login form
        document.getElementById('back-to-login-btn').addEventListener('click', function () {
            otpPanel.classList.add('hidden');
            loginForm.classList.remove('hidden');

            otpForm.reset();
            clearFeedback();
            document.getElementById('login-email').focus();
        });
    </script>

</body>

</html>