<?php
/**
 * Absenin - Named Constants
 */

// Overtime modes
define('OVERTIME_AUTO', 'auto');
define('OVERTIME_CHECKOUT_CLOCKIN', 'checkout');

// Attendance status
define('STATUS_PRESENT', 'hadir');
define('STATUS_LATE', 'terlambat');
define('STATUS_ABSENT', 'tidak_hadir');
define('STATUS_LEAVE', 'cuti');
define('STATUS_PERMISSION', 'izin');
define('STATUS_SICK', 'sakit');
define('STATUS_OVERTIME', 'lembur');

// Attendance source
define('SOURCE_MOBILE', 'mobile');
define('SOURCE_FINGERPRINT', 'fingerprint');

// Leave types
define('LEAVE_ANNUAL', 'cuti_tahunan');
define('LEAVE_PERMISSION', 'izin');
define('LEAVE_SICK', 'sakit');

// Approval status
define('APPROVAL_PENDING', 'pending');
define('APPROVAL_APPROVED', 'disetujui');
define('APPROVAL_REJECTED', 'ditolak');

// GPS mode
define('GPS_SPECIFIC', 'spesifik');
define('GPS_FREE', 'bebas');

// Default leave balance
define('DEFAULT_LEAVE_BALANCE', 12);

// GPS radius tolerance (meters)
define('GPS_RADIUS_TOLERANCE', 10);

// Fingerspot sync interval (seconds)
define('FINGERSPOT_SYNC_INTERVAL', 300); // 5 minutes

// Dashboard polling interval (seconds)
define('DASHBOARD_POLL_INTERVAL', 60);

// Grace period for checkout (seconds)
define('CHECKOUT_GRACE_PERIOD', 1800); // 30 minutes

// Search debounce (milliseconds)
define('SEARCH_DEBOUNCE_MS', 300);

// Pagination
define('PAGE_LIMIT_DEFAULT', 20);
define('PAGE_LIMIT_MAX', 100);
