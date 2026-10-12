// ================================================================
// Login Page JavaScript
// ================================================================
// This file contains functionality used only by login.html.
// Admin/dashboard functionality remains in scripts/script.js.

// ================================================================
// Real Database Authentication
// ================================================================
// Credentials are no longer stored in this JavaScript file.
// login.php validates the email and password against MySQL and
// creates the authenticated PHP session.
//
// Role destinations:
//   admin    -> admin.html
//   manager  -> manager.html
//   employee -> employee.html
// ================================================================

const rolePages = {
    admin: 'admin.html',
    manager: 'manager.html',
    employee: 'employee.html'
};

async function handleLogin(event) {
    event.preventDefault();

    const emailInput = document.getElementById('login-email');
    const passwordInput = document.getElementById('login-password');

    const email = emailInput ? emailInput.value.trim() : '';
    const password = passwordInput ? passwordInput.value : '';

    const alertBox = document.getElementById('login-alert');
    const alertText = document.getElementById('login-alert-text');
    const btnText = document.getElementById('login-btn-text');
    const spinner = document.getElementById('login-spinner');

    alertBox?.classList.add('hidden');

    if (!email || !password) {
        if (alertText) {
            alertText.textContent = 'Please enter your email and password.';
        }

        alertBox?.classList.remove('hidden');
        return;
    }

    try {
        if (btnText) btnText.textContent = 'Signing in...';
        if (spinner) spinner.classList.remove('hidden');

        const formData = new FormData();
        formData.append('email', email);
        formData.append('password', password);

        const response = await fetch('api/auth/login.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });

        const result = await response.json();

        if (result.requires_2fa === true) {
            if (btnText) btnText.textContent = 'Login';
            if (spinner) spinner.classList.add('hidden');

            showTwoFactorModal(result.masked_email || email);
            return;
        }

        if (!response.ok || !result.success) {
            throw new Error(
                result.message || 'Invalid email or password.'
            );
        }

        finishClientLogin(result);

    } catch (error) {
        console.error('Login error:', error);

        if (alertText) {
            alertText.textContent =
                error.message || 'Unable to connect to the server.';
        }

        alertBox?.classList.remove('hidden');

        if (btnText) btnText.textContent = 'Login';
        if (spinner) spinner.classList.add('hidden');
    }
}

function finishClientLogin(result) {
    sessionStorage.setItem('account_id', result.account_id ?? '');
    sessionStorage.setItem('employee_id', result.employee_id ?? '');
    sessionStorage.setItem('name', result.name ?? '');
    sessionStorage.setItem('email', result.email ?? '');
    sessionStorage.setItem('role', result.role ?? '');

    if (window.SharedData) {
        const clientSession = {
            id: result.employee_id || result.account_id || null,
            name: result.name || result.email || '',
            email: result.email || '',
            role: result.role || ''
        };

        SharedData.setSession(clientSession);

        const savedSession = SharedData.getSession();

        if (!savedSession || savedSession.role !== clientSession.role) {
            sessionStorage.setItem(
                'ht_v2_session',
                JSON.stringify(clientSession)
            );
        }
    }

    const destination = rolePages[result.role];

    if (!destination) {
        throw new Error('Invalid account role.');
    }

    window.location.href = destination;
}

// Expose these handlers because login.html still uses inline event attributes.
window.handleLogin = handleLogin;

// ================================================================
// Gmail 6-Digit Two-Factor Authentication
// ================================================================
// The existing login form is unchanged. This modal is created only
// after the password has been verified and the server requires 2FA.
// ================================================================

let twoFactorModal = null;
let twoFactorBusy = false;

function createTwoFactorModal() {
    if (twoFactorModal) return twoFactorModal;

    twoFactorModal = document.createElement('div');

    twoFactorModal.id = 'two-factor-modal';

    twoFactorModal.className =
        'fixed inset-0 z-[120] flex items-center justify-center px-4';

    twoFactorModal.innerHTML = `
        <div
            id="two-factor-backdrop"
            class="absolute inset-0 bg-primary/30 backdrop-blur-md">
        </div>

        <div
            class="relative z-10 w-full max-w-md bg-card rounded-2xl border border-border shadow-2xl p-6 sm:p-8">

            <div class="text-center pt-2 mb-6">
                <div
                    class="mx-auto w-14 h-14 rounded-2xl bg-primary/10
                    flex items-center justify-center mb-4">
                    <i data-lucide="shield-check"
                        class="w-7 h-7 text-primary"></i>
                </div>

                <h2
                    class="font-heading text-2xl font-bold text-primary">
                    Multi-Factor Authentication
                </h2>

                <p
                    id="two-factor-description"
                    class="text-sm text-gray-500 mt-2">
                    Enter the 6-digit verification code sent to your email.
                </p>
            </div>

            <div
                id="two-factor-alert"
                class="hidden flex items-start gap-2 text-error text-sm
                font-medium bg-error/10 p-3 rounded-lg
                border border-error/20 mb-4">
                <i data-lucide="alert-circle"
                    class="w-5 h-5 flex-shrink-0"></i>
                <span id="two-factor-alert-text"></span>
            </div>

            <div>
                <label
                    class="block font-body text-sm font-medium
                    text-primary mb-1">
                    Verification Code
                </label>

                <input
                    type="text"
                    id="two-factor-code"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    maxlength="6"
                    pattern="[0-9]{6}"
                    placeholder="000000"
                    class="w-full py-3 px-4 text-center tracking-[0.55em]
                    text-xl font-bold bg-background border border-border
                    rounded-xl focus:outline-none focus:ring-2
                    focus:ring-accent focus:border-transparent
                    font-body text-primary"
                />
            </div>

            <button
                type="button"
                id="two-factor-verify-btn"
                class="w-full mt-4 bg-secondary hover:bg-[#E08A3B]
                text-white font-button font-medium py-3 rounded-xl
                transition-all shadow-lg shadow-secondary/30
                flex justify-center items-center gap-2">
                <span id="two-factor-verify-text">Verify Code</span>
                <div
                    id="two-factor-verify-spinner"
                    class="hidden w-5 h-5 border-2 border-white
                    border-t-transparent rounded-full animate-spin">
                </div>
            </button>

            <div
                class="flex items-center justify-between gap-3 mt-4">
                <button
                    type="button"
                    id="two-factor-cancel-btn"
                    class="text-sm font-medium text-gray-500
                    hover:text-primary transition-colors">
                    Cancel
                </button>

                <button
                    type="button"
                    id="two-factor-resend-btn"
                    class="text-sm font-semibold text-accent
                    hover:text-primary transition-colors">
                    Resend code
                </button>
            </div>

            <p
                class="text-[11px] text-gray-400 text-center mt-4">
                This verification is required once per device every
                24 hours.
            </p>
        </div>
    `;

    document.body.appendChild(twoFactorModal);

    const codeInput = document.getElementById('two-factor-code');
    const verifyBtn = document.getElementById('two-factor-verify-btn');
    const cancelBtn = document.getElementById('two-factor-cancel-btn');
    const resendBtn = document.getElementById('two-factor-resend-btn');
    const backdrop = document.getElementById('two-factor-backdrop');

    codeInput?.addEventListener('input', () => {
        codeInput.value = codeInput.value.replace(/\D/g, '').slice(0, 6);
    });

    codeInput?.addEventListener('keydown', event => {
        if (event.key === 'Enter') {
            event.preventDefault();
            verifyTwoFactorCode();
        }
    });

    verifyBtn?.addEventListener('click', verifyTwoFactorCode);
    cancelBtn?.addEventListener('click', cancelTwoFactor);
    resendBtn?.addEventListener('click', resendTwoFactor);

    backdrop?.addEventListener('click', cancelTwoFactor);

    refreshLoginIcons();

    return twoFactorModal;
}

function refreshLoginIcons() {
    if (
        window.lucide &&
        typeof window.lucide.createIcons === 'function'
    ) {
        window.lucide.createIcons();
    }
}

function showTwoFactorModal(maskedEmail) {
    createTwoFactorModal();

    const modal = document.getElementById('two-factor-modal');
    const description = document.getElementById('two-factor-description');
    const alertBox = document.getElementById('two-factor-alert');
    const codeInput = document.getElementById('two-factor-code');

    if (description) {
        description.textContent =
            `Enter the 6-digit verification code sent to ${maskedEmail}.`;
    }

    alertBox?.classList.add('hidden');

    if (modal) {
        modal.classList.remove('hidden');
    }

    twoFactorBusy = false;

    codeInput?.focus();

    refreshLoginIcons();
}

function hideTwoFactorModal() {
    const modal = document.getElementById('two-factor-modal');

    if (modal) {
        modal.classList.add('hidden');
    }
}

function setTwoFactorBusy(busy) {
    twoFactorBusy = busy;

    const verifyBtn = document.getElementById('two-factor-verify-btn');
    const resendBtn = document.getElementById('two-factor-resend-btn');
    const verifyText = document.getElementById('two-factor-verify-text');
    const spinner = document.getElementById('two-factor-verify-spinner');

    if (verifyBtn) verifyBtn.disabled = busy;
    if (resendBtn) resendBtn.disabled = busy;

    if (verifyText) {
        verifyText.textContent = busy
            ? 'Verifying...'
            : 'Verify Code';
    }

    spinner?.classList.toggle('hidden', !busy);
}

function showTwoFactorError(message) {
    const alertBox = document.getElementById('two-factor-alert');
    const alertText = document.getElementById('two-factor-alert-text');

    if (alertText) {
        alertText.textContent = message;
    }

    alertBox?.classList.remove('hidden');
    refreshLoginIcons();
}

async function verifyTwoFactorCode() {
    if (twoFactorBusy) return;

    const codeInput = document.getElementById('two-factor-code');
    const code = codeInput ? codeInput.value.trim() : '';

    if (!/^\d{6}$/.test(code)) {
        showTwoFactorError('Enter the 6-digit verification code.');
        return;
    }

    const formData = new FormData();
    formData.append('code', code);

    try {
        setTwoFactorBusy(true);

        const response = await fetch('api/auth/verify-otp.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(
                result.message || 'Verification failed.'
            );
        }

        hideTwoFactorModal();

        finishClientLogin(result);

    } catch (error) {
        showTwoFactorError(
            error.message || 'Unable to verify the code.'
        );

    } finally {
        setTwoFactorBusy(false);
    }
}

async function resendTwoFactor() {
    if (twoFactorBusy) return;

    try {
        setTwoFactorBusy(true);

        const response = await fetch('api/auth/resend-otp.php', {
            method: 'POST',
            credentials: 'same-origin'
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(
                result.message || 'Unable to resend the code.'
            );
        }

        const codeInput = document.getElementById('two-factor-code');

        if (codeInput) {
            codeInput.value = '';
            codeInput.focus();
        }

        const alertBox = document.getElementById('two-factor-alert');
        alertBox?.classList.add('hidden');

    } catch (error) {
        showTwoFactorError(
            error.message || 'Unable to resend the code.'
        );

    } finally {
        setTwoFactorBusy(false);
    }
}

async function cancelTwoFactor() {
    hideTwoFactorModal();

    const emailInput = document.getElementById('login-email');
    const passwordInput = document.getElementById('login-password');
    const btnText = document.getElementById('login-btn-text');
    const spinner = document.getElementById('login-spinner');

    // Clear only the local login form. The server-side pending 2FA state
    // will be replaced if the user submits the credentials again.
    if (emailInput) emailInput.focus();
    if (passwordInput) passwordInput.value = '';
    if (btnText) btnText.textContent = 'Login';
    spinner?.classList.add('hidden');
}




// --- Loading overlay (needed by login.js independently of script.js) ---
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

window.showLoadingOverlay = showLoadingOverlay;
window.hideLoadingOverlay = hideLoadingOverlay;

// --- Password Visibility ---
function togglePasswordVisibility() {
    const passInput = document.getElementById('login-password');
    const icon = document.getElementById('password-toggle-icon');
    if (!passInput || !icon) return;

    if (passInput.type === 'password') {
        passInput.type = 'text';
        icon.setAttribute('data-lucide', 'eye-off');
    } else {
        passInput.type = 'password';
        icon.setAttribute('data-lucide', 'eye');
    }

    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }
}

window.togglePasswordVisibility = togglePasswordVisibility;

// ================================================================
// Invisible Admin RFID Login
// ================================================================
// The RFID reader is treated like a keyboard reader. Nothing is added to
// the login-page UI. An RFID UID is only acted on when the reader finishes
// with Enter and the buffered value is numeric / long enough to be an RFID UID.
// ================================================================

let rfidLoginBusy = false;

async function handleAdminRfidScan(rfidUid) {
    if (rfidLoginBusy) return;

    const normalizedUid = String(rfidUid || '').trim();

    if (!/^[0-9A-Za-z:-]{8,40}$/.test(normalizedUid)) {
        return;
    }

    rfidLoginBusy = true;

    try {
        const formData = new FormData();
        formData.append('rfid_uid', normalizedUid);

        const response = await fetch('api/auth/rfid-login.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });

        const result = await response.json();

        // Non-admin RFID scans are intentionally silent on the login page.
        if (response.status === 403 || response.status === 404) {
            return;
        }

        if (result.requires_2fa === true) {
            showTwoFactorModal(result.masked_email || '');
            return;
        }

        if (!response.ok || !result.success) {
            throw new Error(
                result.message || 'RFID login failed.'
            );
        }

        finishClientLogin(result);

    } catch (error) {
        console.error('Admin RFID login error:', error);

        const alertBox = document.getElementById('login-alert');
        const alertText = document.getElementById('login-alert-text');

        if (alertText) {
            alertText.textContent =
                error.message || 'Unable to process the RFID login.';
        }

        alertBox?.classList.remove('hidden');

    } finally {
        rfidLoginBusy = false;
    }
}

/*
|--------------------------------------------------------------------------
| USB / HID RFID reader listener
|--------------------------------------------------------------------------
| Most readers type the UID and finish with Enter.
| The listener remains invisible and does not add any scanner UI.
|--------------------------------------------------------------------------
*/

(function initInvisibleAdminRfidListener() {
    let buffer = '';
    let lastKeyTime = 0;
    let finalizeTimer = null;

    const RESET_TIMEOUT_MS = 1200;
    const MIN_UID_LENGTH = 8;
    const MAX_UID_LENGTH = 40;

    function resetBuffer() {
        buffer = '';
        lastKeyTime = 0;
        if (finalizeTimer) {
            clearTimeout(finalizeTimer);
            finalizeTimer = null;
        }
    }

    function isValidUid(value) {
        return /^[0-9A-Za-z:-]{8,40}$/.test(value);
    }

    function submitBuffer() {
        const scannedValue = buffer.trim();
        resetBuffer();
        if (isValidUid(scannedValue)) {
            handleAdminRfidScan(scannedValue);
        }
    }

    window.addEventListener('keydown', event => {
        const now = Date.now();

        if (buffer && now - lastKeyTime > RESET_TIMEOUT_MS) {
            resetBuffer();
        }

        lastKeyTime = now;

        const isTerminator =
            event.key === 'Enter' ||
            event.key === 'NumpadEnter' ||
            event.key === 'Tab';

        if (isTerminator) {
            if (buffer.length >= MIN_UID_LENGTH && buffer.length <= MAX_UID_LENGTH) {
                event.preventDefault();
                event.stopPropagation();
                if (typeof event.stopImmediatePropagation === 'function') {
                    event.stopImmediatePropagation();
                }
                submitBuffer();
            } else {
                resetBuffer();
            }
            return;
        }

        if (event.key.length === 1 && /^[0-9A-Za-z:-]$/.test(event.key)) {
            if (!buffer) {
                lastKeyTime = now;
            }
            buffer += event.key;
            if (buffer.length > MAX_UID_LENGTH) {
                resetBuffer();
                return;
            }

            // Some readers do not append Enter. Give the buffer a brief idle window.
            if (finalizeTimer) clearTimeout(finalizeTimer);
            finalizeTimer = setTimeout(() => {
                if (buffer.length >= MIN_UID_LENGTH && isValidUid(buffer)) {
                    submitBuffer();
                } else {
                    resetBuffer();
                }
            }, 250);
        }
    }, true);
})();


// Initialize Lucide icons once login.js is loaded.
if (window.lucide && typeof window.lucide.createIcons === 'function') {
    window.lucide.createIcons();
}
