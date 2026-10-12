WFM Mobile Attendance DB Patch

Apply this patch by copying public/scripts/employee-mobile.js over the same file in your Laravel project.

This changes the mobile employee clock-in/clock-out buttons to POST to the existing Laravel endpoint /api/attendance/my-clock.php and reloads today's attendance and attendance history from the read-only endpoints. It does not change the database schema or insert demo records.

After copying the file, run the validation command block provided in the chat, then test in a real employee session. Do not click clock-in/out in an administrator session.
