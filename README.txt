Workforce Management - Admin RFID Password Fallback Patch

This patch adds a password fallback below the existing Admin RFID scanner in the employee-edit verification modal. The currently signed-in active Admin can enter the same password used at login if their RFID card is unavailable. Laravel checks the password on the server against the stored password hash using Hash::check. The password is not stored or logged. Successful verification grants the existing short-lived employee-edit authorization.

Apply only these three files to your existing project root (merge folders and replace the matching files):
- app/Http/Controllers/Api/RfidAuthController.php
- resources/views/admin/index.blade.php
- routes/api.php

No database schema change, migration, seeder, theme change, or frontend rebuild is required.

Validation:
- PHP syntax checks passed for the changed controller and route file.
- Laravel route:list confirmed api/employees/verify-admin-password.php is registered.
- The new JavaScript handler passed node --check.
- Full view-cache validation was blocked in this build environment because the PHP DOM extension is unavailable.
