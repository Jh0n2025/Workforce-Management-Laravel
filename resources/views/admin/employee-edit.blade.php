<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="/scripts/api-fetch.js"></script>

    <title>Edit Employee Information</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#17324D',
                        secondary: '#2E6F95',
                        accent: '#D9A441',
                        card: '#FFFFFF',
                        background: '#F4F7FA',
                        border: '#D9E1E8'
                    }
                }
            }
        };
    </script>
</head>

<body class="bg-background min-h-screen text-primary">

    <div class="max-w-5xl mx-auto p-4 md:p-8">

        <!-- Header -->
        <div class="bg-card border border-border rounded-2xl shadow-sm p-6 mb-6">

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                <div>
                    <h1 class="text-2xl font-bold">
                        Edit Employee Information
                    </h1>

                    <p id="employee-id-label" class="text-sm text-gray-500 mt-1">
                        Loading...
                    </p>
                </div>

                <button type="button" onclick="window.close()"
                    class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 font-semibold">
                    Close
                </button>

            </div>
        </div>

        <!-- Loading -->
        <div id="loading-message" class="bg-card border border-border rounded-2xl p-8 text-center">
            Loading employee information...
        </div>

        <!-- Form -->
        <form id="employee-edit-form" class="hidden space-y-6">

            <!-- Personal -->
            <div class="bg-card border border-border rounded-2xl p-6">

                <h2 class="text-lg font-bold mb-5">
                    Personal Information
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <div>
                        <label class="block text-sm font-semibold mb-2">
                            First Name
                        </label>

                        <input id="first-name" name="first_name" type="text" required
                            class="w-full px-4 py-3 rounded-xl border border-border bg-white">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">
                            Middle Name
                        </label>

                        <input id="middle-name" name="middle_name" type="text"
                            class="w-full px-4 py-3 rounded-xl border border-border bg-white">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">
                            Last Name
                        </label>

                        <input id="last-name" name="last_name" type="text" required
                            class="w-full px-4 py-3 rounded-xl border border-border bg-white">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">
                            Date of Birth
                        </label>

                        <input id="date-of-birth" name="date_of_birth" type="date"
                            class="w-full px-4 py-3 rounded-xl border border-border bg-white">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">
                            Gender
                        </label>

                        <input id="gender" name="gender" type="text"
                            class="w-full px-4 py-3 rounded-xl border border-border bg-white">
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-sm font-semibold mb-2">
                            Address
                        </label>

                        <textarea id="address" name="address" rows="3"
                            class="w-full px-4 py-3 rounded-xl border border-border bg-white"></textarea>
                    </div>

                </div>
            </div>

            <!-- Contact -->
            <div class="bg-card border border-border rounded-2xl p-6">

                <h2 class="text-lg font-bold mb-5">
                    Contact Information
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <label class="block text-sm font-semibold mb-2">
                            Phone
                        </label>

                        <input id="phone" name="phone" type="text"
                            class="w-full px-4 py-3 rounded-xl border border-border bg-white">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">
                            Email
                        </label>

                        <input id="email" name="email" type="email"
                            class="w-full px-4 py-3 rounded-xl border border-border bg-white">
                    </div>

                </div>
            </div>

            <!-- Password -->
            <div class="bg-card border border-border rounded-2xl p-6">

                <h2 class="text-lg font-bold mb-2">
                    Change Password
                </h2>

                <p class="text-sm text-gray-500 mb-5">
                    Your password must be at least 6 characters and should include a combination of numbers, letters and
                    special characters (!@%).
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <div
                            class="min-h-[52px] flex items-center px-4 py-3 rounded-xl border border-border bg-gray-50 text-sm">
                            <span id="password-updated-label">Current Password (updated 00/00/00)</span>
                        </div>
                    </div>

                    <div class="space-y-4">

                        <input id="new-password" name="new_password" type="password" autocomplete="new-password"
                            minlength="6" placeholder="New Password"
                            class="w-full px-4 py-3 rounded-xl border border-border bg-white">

                        <input id="retype-new-password" name="retype_new_password" type="password"
                            autocomplete="new-password" minlength="6" placeholder="Re-Type New Password"
                            class="w-full px-4 py-3 rounded-xl border border-border bg-white">

                        <div>
                            <p id="password-message" class="text-sm mb-3 text-gray-500"></p>

                            <button id="password-save-button" type="submit"
                                class="w-full px-8 py-3 rounded-xl bg-secondary text-white font-semibold hover:opacity-90">
                                SAVE CHANGES
                            </button>
                        </div>

                    </div>

                </div>
            </div>

            <!-- Employment -->
            <div class="bg-card border border-border rounded-2xl p-6">

                <h2 class="text-lg font-bold mb-5">
                    Employment Information
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <label class="block text-sm font-semibold mb-2">
                            Department
                        </label>

                        <input id="department-name" name="department_name" type="text"
                            class="w-full px-4 py-3 rounded-xl border border-border bg-white">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">
                            Position
                        </label>

                        <input id="position-name" name="position_name" type="text"
                            class="w-full px-4 py-3 rounded-xl border border-border bg-white">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">
                            Date Hired
                        </label>

                        <input id="hire-date" name="hire_date" type="date"
                            class="w-full px-4 py-3 rounded-xl border border-border bg-white">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">
                            Employment Type
                        </label>

                        <input id="employment-type" name="employment_type" type="text"
                            class="w-full px-4 py-3 rounded-xl border border-border bg-white">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">
                            Employment Status
                        </label>

                        <select id="employment-status" name="employment_status"
                            class="w-full px-4 py-3 rounded-xl border border-border bg-white">

                            <option value="active">
                                Active
                            </option>

                            <option value="inactive">
                                Inactive
                            </option>

                            <option value="on_leave">
                                On Leave
                            </option>

                            <option value="terminated">
                                Terminated
                            </option>

                        </select>
                    </div>

                </div>
            </div>

            <!-- Emergency -->
            <div class="bg-card border border-border rounded-2xl p-6">

                <h2 class="text-lg font-bold mb-5">
                    Emergency Contact
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <div>
                        <label class="block text-sm font-semibold mb-2">
                            Name
                        </label>

                        <input id="emergency-name" name="emergency_name" type="text"
                            class="w-full px-4 py-3 rounded-xl border border-border bg-white">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">
                            Relationship
                        </label>

                        <input id="emergency-relationship" name="emergency_relationship" type="text"
                            class="w-full px-4 py-3 rounded-xl border border-border bg-white">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">
                            Phone
                        </label>

                        <input id="emergency-phone" name="emergency_phone" type="text"
                            class="w-full px-4 py-3 rounded-xl border border-border bg-white">
                    </div>

                </div>
            </div>

            <!-- Save -->
            <div class="bg-card border border-border rounded-2xl p-6">

                <p id="save-message" class="text-sm mb-4 text-gray-500">
                </p>

                <button id="save-button" type="submit"
                    class="w-full md:w-auto px-8 py-3 rounded-xl bg-secondary text-white font-semibold hover:opacity-90">

                    <i class="fa-solid fa-floppy-disk mr-2"></i>
                    Save Changes

                </button>

            </div>

        </form>

    </div>

    <script>
        const params = new URLSearchParams(window.location.search);

        const employeeId =
            String(params.get('id') || '').trim();

        const employeeEditForm =
            document.getElementById('employee-edit-form');

        const loadingMessage =
            document.getElementById('loading-message');

        const employeeIdLabel =
            document.getElementById('employee-id-label');

        const saveMessage =
            document.getElementById('save-message');

        const saveButton =
            document.getElementById('save-button');

        const passwordUpdatedLabel =
            document.getElementById('password-updated-label');

        const passwordMessage =
            document.getElementById('password-message');

        const passwordSaveButton =
            document.getElementById('password-save-button');

        function setValue(id, value) {

            const element =
                document.getElementById(id);

            if (element) {
                element.value = value ?? '';
            }
        }

        async function loadEmployee() {

            if (!employeeId) {

                loadingMessage.textContent =
                    'Employee ID is missing.';

                return;
            }

            employeeIdLabel.textContent =
                'Employee ID: ' + employeeId;

            try {

                const response =
                    await fetch(
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

                const result =
                    await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(
                        result.message ||
                        'Unable to load employee information.'
                    );
                }

                const employee =
                    result.data?.employee || {};

                const passwordUpdatedAt =
                    employee.password_updated_at || '';

                if (passwordUpdatedLabel) {
                    passwordUpdatedLabel.textContent =
                        passwordUpdatedAt
                            ? 'Current Password (updated ' + passwordUpdatedAt + ')'
                            : 'Current Password (updated 00/00/00)';
                }

                setValue(
                    'first-name',
                    employee.first_name
                );

                setValue(
                    'middle-name',
                    employee.middle_name
                );

                setValue(
                    'last-name',
                    employee.last_name
                );

                setValue(
                    'date-of-birth',
                    employee.date_of_birth
                );

                setValue(
                    'gender',
                    employee.gender
                );

                setValue(
                    'address',
                    employee.address
                );

                setValue(
                    'phone',
                    employee.phone
                );

                setValue(
                    'email',
                    employee.email
                );

                setValue(
                    'department-name',
                    employee.department
                );

                setValue(
                    'position-name',
                    employee.position
                );

                setValue(
                    'hire-date',
                    employee.hire_date
                );

                setValue(
                    'employment-type',
                    employee.employment_type
                );

                setValue(
                    'employment-status',
                    employee.employment_status || 'active'
                );

                setValue(
                    'emergency-name',
                    employee.emergency_name
                );

                setValue(
                    'emergency-relationship',
                    employee.emergency_relationship
                );

                setValue(
                    'emergency-phone',
                    employee.emergency_phone
                );

                loadingMessage.classList.add('hidden');
                employeeEditForm.classList.remove('hidden');

            } catch (error) {

                loadingMessage.textContent =
                    error.message ||
                    'Unable to load employee information.';
            }
        }

        employeeEditForm.addEventListener(
            'submit',
            async function (event) {

                event.preventDefault();

                saveButton.disabled = true;

                if (passwordSaveButton) {
                    passwordSaveButton.disabled = true;
                }

                saveMessage.textContent =
                    'Saving changes...';

                saveMessage.className =
                    'text-sm mb-4 text-gray-500';

                try {

                    const formData =
                        new FormData(employeeEditForm);

                    const newPassword =
                        String(formData.get('new_password') || '');

                    const retypeNewPassword =
                        String(formData.get('retype_new_password') || '');

                    passwordMessage.textContent = '';
                    passwordMessage.className =
                        'text-sm mb-3 text-gray-500';

                    if (newPassword !== '' || retypeNewPassword !== '') {

                        if (newPassword === '' || retypeNewPassword === '') {
                            throw new Error('Please enter and re-type the new password.');
                        }

                        if (newPassword !== retypeNewPassword) {
                            throw new Error('New passwords do not match.');
                        }

                        if (
                            newPassword.length < 6 ||
                            !/[A-Za-z]/.test(newPassword) ||
                            !/[0-9]/.test(newPassword) ||
                            !/[!@%]/.test(newPassword)
                        ) {
                            throw new Error(
                                'Password must be at least 6 characters and include a letter, a number, and a special character (!@%).'
                            );
                        }

                    } else {
                        formData.delete('new_password');
                        formData.delete('retype_new_password');
                    }

                    formData.append(
                        'employee_id',
                        employeeId
                    );

                    const response =
                        await fetch(
                            'api/employees/update.php',
                            {
                                method: 'POST',
                                credentials: 'same-origin',
                                body: formData
                            }
                        );

                    const result =
                        await response.json();

                    if (!response.ok || !result.success) {
                        throw new Error(
                            result.message ||
                            'Unable to save employee information.'
                        );
                    }

                    saveMessage.textContent =
                        'Employee information saved successfully.';

                    saveMessage.className =
                        'text-sm mb-4 text-emerald-600';

                    if (newPassword !== '') {

                        document.getElementById('new-password').value = '';
                        document.getElementById('retype-new-password').value = '';

                        if (result.password_updated_at) {
                            passwordUpdatedLabel.textContent =
                                'Current Password (updated ' + result.password_updated_at + ')';
                        }

                        passwordMessage.textContent =
                            'Password updated successfully.';

                        passwordMessage.className =
                            'text-sm mb-3 text-emerald-600';
                    }

                } catch (error) {

                    saveMessage.textContent =
                        error.message ||
                        'Unable to save employee information.';

                    saveMessage.className =
                        'text-sm mb-4 text-red-600';

                } finally {

                    saveButton.disabled = false;

                    if (passwordSaveButton) {
                        passwordSaveButton.disabled = false;
                    }
                }
            }
        );

        loadEmployee();
    </script>

</body>

</html>