<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkforceDataSeeder extends Seeder
{
    public function run(): void
    {
        // Imported from the original Workforce Management MariaDB dump.
        // No existing values are transformed or replaced here.

        DB::table('employees')->insert([
            [
                'id' => 1,
                'employee_id' => '26011001',
                'first_name' => 'Admin',
                'middle_name' => null,
                'last_name' => 'Account',
                'avatar_url' => null,
                'date_of_birth' => null,
                'gender' => null,
                'email' => 'faingasonjhonie@gmail.com',
                'phone' => null,
                'department_name' => 'Management',
                'position_name' => 'System Administrator',
                'hire_date' => null,
                'address' => null,
                'emergency_name' => null,
                'emergency_relationship' => null,
                'emergency_phone' => null,
                'employment_type' => 'REGULAR',
                'employment_status' => 'active',
                'manager_id' => null,
                'created_at' => '2026-09-25 14:29:43',
                'updated_at' => '2026-09-25 14:29:43'
            ],
            [
                'id' => 2,
                'employee_id' => '26011002',
                'first_name' => 'Nicole',
                'middle_name' => 'Jane Anne Q.',
                'last_name' => 'Mayo',
                'avatar_url' => 'uploads/profile_photos/manager_26011002_6e2082db66f61cd21e161b41.jpg',
                'date_of_birth' => null,
                'gender' => null,
                'email' => 'nicolemayo059@gmail.com',
                'phone' => null,
                'department_name' => 'Operations',
                'position_name' => 'Operations Manager',
                'hire_date' => null,
                'address' => null,
                'emergency_name' => null,
                'emergency_relationship' => null,
                'emergency_phone' => null,
                'employment_type' => 'REGULAR',
                'employment_status' => 'active',
                'manager_id' => null,
                'created_at' => '2026-09-25 14:29:43',
                'updated_at' => '2026-09-30 19:10:37'
            ],
            [
                'id' => 12,
                'employee_id' => '26011003',
                'first_name' => 'Hibino',
                'middle_name' => 'Kai',
                'last_name' => 'Kafka',
                'avatar_url' => 'uploads/profile_photos/manager_26011003_355746f846a613519e644dab.jpg',
                'date_of_birth' => '1999-12-23',
                'gender' => 'Male',
                'email' => 'gogetsunaiyaba03@gmail.com',
                'phone' => '09360118322',
                'department_name' => 'HR',
                'position_name' => 'Office Assistant',
                'hire_date' => '2026-09-30',
                'address' => 'Maria Huzawa St. Poblacion Caloocan City',
                'emergency_name' => 'April Ticalo',
                'emergency_relationship' => 'Cousin',
                'emergency_phone' => '09193329657',
                'employment_type' => 'REGULAR',
                'employment_status' => 'active',
                'manager_id' => 2,
                'created_at' => '2026-09-30 21:57:19',
                'updated_at' => '2026-09-30 23:31:55'
            ]
        ]);

        DB::table('accounts')->insert([
            [
                'id' => 1,
                'employee_id' => '26011001',
                'email' => 'faingasonjhonie@gmail.com',
                'password_hash' => '$2y$12$Mq.XRV0ZMrAJN3kkzlbKseU60VZw1SuJ4z0CBl2oVl7c.AO4Xc0.m',
                'role' => 'admin',
                'status' => 'active',
                'created_at' => '2026-09-25 12:31:43',
                'updated_at' => '2026-09-25 14:30:28'
            ],
            [
                'id' => 2,
                'employee_id' => '26011002',
                'email' => 'nicolemayo059@gmail.com',
                'password_hash' => '$2y$12$7YWgsAhO1Odtn3bSYMneeOpz5oh4axNzUWG4oNCBvNOhSFq4zriXe',
                'role' => 'manager',
                'status' => 'active',
                'created_at' => '2026-09-25 12:31:43',
                'updated_at' => '2026-09-25 14:30:28'
            ],
            [
                'id' => 13,
                'employee_id' => '26011003',
                'email' => 'gogetsunaiyaba03@gmail.com',
                'password_hash' => '$2y$10$RGJ/qcr5z64Xgg0PNXFCHuutSiSb8hutnWNLSXuCmA59i3MwGR6U2',
                'role' => 'employee',
                'status' => 'active',
                'created_at' => '2026-09-30 21:57:19',
                'updated_at' => '2026-09-30 21:57:19'
            ]
        ]);

        DB::table('attendance_records')->insert([
            [
                'id' => 5,
                'employee_id' => '26011001',
                'attendance_date' => '2026-09-27',
                'time_in' => '2026-09-27 13:30:52',
                'time_out' => null,
                'status' => 'present',
                'source' => 'kiosk',
                'notes' => 'Recorded by RFID kiosk.',
                'created_at' => '2026-09-27 13:30:52',
                'updated_at' => '2026-09-27 13:30:52'
            ],
            [
                'id' => 6,
                'employee_id' => '26011002',
                'attendance_date' => '2026-09-27',
                'time_in' => '2026-09-27 13:31:04',
                'time_out' => null,
                'status' => 'present',
                'source' => 'kiosk',
                'notes' => 'Recorded by RFID kiosk.',
                'created_at' => '2026-09-27 13:31:04',
                'updated_at' => '2026-09-27 13:31:04'
            ]
        ]);

        DB::table('rfid_cards')->insert([
            [
                'id' => 1,
                'employee_id' => '26011001',
                'rfid_uid' => '3219410377',
                'status' => 'active',
                'created_at' => '2026-09-25 14:35:04'
            ],
            [
                'id' => 2,
                'employee_id' => '26011002',
                'rfid_uid' => '3219246537',
                'status' => 'active',
                'created_at' => '2026-09-25 14:35:04'
            ],
            [
                'id' => 8,
                'employee_id' => '26011003',
                'rfid_uid' => '3215545033',
                'status' => 'active',
                'created_at' => '2026-09-30 21:57:35'
            ]
        ]);

        DB::table('kiosk_log_entries')->insert([
            [
                'id' => 5,
                'employee_id' => '26011001',
                'rfid_card_id' => 1,
                'rfid_uid' => '3219410377',
                'kiosk_name' => 'Main Kiosk',
                'scan_status' => 'APPROVED',
                'scanned_at' => '2026-09-27 13:30:52'
            ],
            [
                'id' => 6,
                'employee_id' => '26011002',
                'rfid_card_id' => 2,
                'rfid_uid' => '3219246537',
                'kiosk_name' => 'Main Kiosk',
                'scan_status' => 'APPROVED',
                'scanned_at' => '2026-09-27 13:31:04'
            ]
        ]);

        DB::table('leave_requests')->insert([
            [
                'id' => 18,
                'employee_id' => '26011002',
                'leave_type' => 'Vacation Leave',
                'start_date' => '2026-10-01',
                'end_date' => '2026-10-05',
                'reason' => 'try lng',
                'status' => 'rejected',
                'approved_by' => 1,
                'approved_at' => '2026-09-30 19:52:47',
                'created_at' => '2026-09-30 19:33:19',
                'updated_at' => '2026-09-30 19:52:47'
            ],
            [
                'id' => 19,
                'employee_id' => '26011003',
                'leave_type' => 'Vacation Leave',
                'start_date' => '2026-10-03',
                'end_date' => '2026-10-30',
                'reason' => 'May gala kame nyan. 
-Testing',
                'status' => 'rejected',
                'approved_by' => 1,
                'approved_at' => '2026-09-30 23:45:44',
                'created_at' => '2026-09-30 22:16:15',
                'updated_at' => '2026-09-30 23:45:44'
            ]
        ]);

        DB::table('manager_notification_reads')->insert([
            [
                'id' => 1,
                'manager_id' => 2,
                'notification_type' => 'analytics',
                'read_at' => '2026-09-30 15:09:54'
            ],
            [
                'id' => 2,
                'manager_id' => 2,
                'notification_type' => 'leave',
                'read_at' => '2026-09-29 17:34:10'
            ],
            [
                'id' => 6,
                'manager_id' => 2,
                'notification_type' => 'schedule',
                'read_at' => '2026-09-29 17:34:07'
            ]
        ]);

        DB::table('otp_challenges')->insert([
            [
                'id' => 93,
                'account_id' => 2,
                'code_hash' => '$2y$10$0nUAJUkeIWtVt2xO5FHRXej4bwK4QPPnS.u2ujdfmrCkBszOZbGoO',
                'expires_at' => '2026-09-29 11:38:30',
                'attempts' => 0,
                'consumed_at' => '2026-09-29 17:29:00',
                'created_at' => '2026-09-29 17:28:30'
            ],
            [
                'id' => 94,
                'account_id' => 1,
                'code_hash' => '$2y$10$YmkAm.WLADUmSXU0cNZjAOMoI55EH4nc5EoxxmeJnPM8ck7YIydXi',
                'expires_at' => '2026-09-29 11:52:35',
                'attempts' => 0,
                'consumed_at' => '2026-09-29 17:42:59',
                'created_at' => '2026-09-29 17:42:35'
            ],
            [
                'id' => 95,
                'account_id' => 2,
                'code_hash' => '$2y$10$qdYAskq3oyK28AGX9vXfLea8BrBDIZA7DaUBhMYuoVfv9AUAeIVa6',
                'expires_at' => '2026-09-30 08:49:20',
                'attempts' => 0,
                'consumed_at' => '2026-09-30 14:39:58',
                'created_at' => '2026-09-30 14:39:20'
            ],
            [
                'id' => 96,
                'account_id' => 1,
                'code_hash' => '$2y$10$oKsYFaVROAwoG3BRj7s7eeqX7DoBrzQBsdpxlUTb19M41i81fbdU2',
                'expires_at' => '2026-09-30 09:49:53',
                'attempts' => 0,
                'consumed_at' => '2026-09-30 15:40:16',
                'created_at' => '2026-09-30 15:39:53'
            ],
            [
                'id' => 97,
                'account_id' => 2,
                'code_hash' => '$2y$10$3/Hoh/UBA7AwYoks93ljb.sfv6RYe9m3WUQIEha/ael5kD.SGCLDe',
                'expires_at' => '2026-09-30 09:57:59',
                'attempts' => 0,
                'consumed_at' => '2026-09-30 15:48:15',
                'created_at' => '2026-09-30 15:47:59'
            ],
            [
                'id' => 98,
                'account_id' => 1,
                'code_hash' => '$2y$10$VnFRFA0T.Lol6nXH9rJgOuT5P53w/UPBHQj0Bi8RD92/4lfF412hC',
                'expires_at' => '2026-09-30 13:09:41',
                'attempts' => 0,
                'consumed_at' => '2026-09-30 19:00:19',
                'created_at' => '2026-09-30 18:59:41'
            ],
            [
                'id' => 99,
                'account_id' => 2,
                'code_hash' => '$2y$10$lvaU5BYMQp0XnyCTjL/zk.37O2s/t.HXrK09mgjKC91jwApC0eUr.',
                'expires_at' => '2026-09-30 13:16:50',
                'attempts' => 0,
                'consumed_at' => '2026-09-30 19:07:17',
                'created_at' => '2026-09-30 19:06:50'
            ],
            [
                'id' => 100,
                'account_id' => 1,
                'code_hash' => '$2y$10$nJVqvYFdL0.NSjsCMXJip.DPx.ZBSFew7Nj78YTiZdAtpyOSHN6La',
                'expires_at' => '2026-09-30 13:27:15',
                'attempts' => 0,
                'consumed_at' => '2026-09-30 19:17:45',
                'created_at' => '2026-09-30 19:17:15'
            ],
            [
                'id' => 101,
                'account_id' => 13,
                'code_hash' => '$2y$10$c1f6VW19xiyRV8KOZcpZZ.RQQVtfpmyYQ74bw4OZ1AEXDytHx/oJ6',
                'expires_at' => '2026-09-30 16:13:12',
                'attempts' => 0,
                'consumed_at' => '2026-09-30 22:03:35',
                'created_at' => '2026-09-30 22:03:12'
            ],
            [
                'id' => 102,
                'account_id' => 1,
                'code_hash' => '$2y$10$xANBOkXq0u9CcITJnYm8sufsPa7T974UobKM9UnO.K9GcbBtWnCna',
                'expires_at' => '2026-09-30 17:54:14',
                'attempts' => 0,
                'consumed_at' => '2026-09-30 23:44:40',
                'created_at' => '2026-09-30 23:44:14'
            ]
        ]);

        DB::table('trusted_devices')->insert([
            [
                'id' => 77,
                'account_id' => 13,
                'device_token_hash' => '872475e75ae9e806520e8e34d46b11c05218ec0c06e0ac3c5039d8c80052bd6f',
                'verified_at' => '2026-09-30 22:03:35',
                'expires_at' => '2026-10-01 22:03:35',
                'created_at' => '2026-09-30 22:03:35',
                'last_used_at' => '2026-09-30 23:30:21'
            ],
            [
                'id' => 78,
                'account_id' => 1,
                'device_token_hash' => '0a1a2a5d879c776d2b436d2fd5a80911773632f3ce5e950f678d10aa613438ab',
                'verified_at' => '2026-09-30 23:44:40',
                'expires_at' => '2026-10-01 23:44:40',
                'created_at' => '2026-09-30 23:44:40',
                'last_used_at' => '2026-09-30 23:44:40'
            ]
        ]);

        // Keep PostgreSQL identity counters ahead of imported primary keys.
        DB::statement("SELECT setval(pg_get_serial_sequence('\"employees\"', 'id'), 12, true)");
        DB::statement("SELECT setval(pg_get_serial_sequence('\"accounts\"', 'id'), 13, true)");
        DB::statement("SELECT setval(pg_get_serial_sequence('\"attendance_records\"', 'id'), 6, true)");
        DB::statement("SELECT setval(pg_get_serial_sequence('\"rfid_cards\"', 'id'), 8, true)");
        DB::statement("SELECT setval(pg_get_serial_sequence('\"kiosk_log_entries\"', 'id'), 6, true)");
        DB::statement("SELECT setval(pg_get_serial_sequence('\"leave_requests\"', 'id'), 19, true)");
        DB::statement("SELECT setval(pg_get_serial_sequence('\"manager_notification_reads\"', 'id'), 6, true)");
        DB::statement("SELECT setval(pg_get_serial_sequence('\"otp_challenges\"', 'id'), 102, true)");
        DB::statement("SELECT setval(pg_get_serial_sequence('\"trusted_devices\"', 'id'), 78, true)");
    }
}
