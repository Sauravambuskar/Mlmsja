<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Database Credentials ---
// Replace with your actual database details
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'msja_crm');

// --- Application Paths ---
// App Root Directory
define('APPROOT', dirname(dirname(__FILE__)));

// URL Root (Replace 'http://localhost/MSJA' if your URL is different)
define('URLROOT', 'http://localhost/MSJA');

// --- Site Information ---
// Site Name
define('SITENAME', 'MSJA Investment CRM');

// --- General Settings ---
// Set default timezone
date_default_timezone_set('UTC');

// --- Security ---
// A secret key for hashing and other security purposes.
// IMPORTANT: Change this to a random, long string for production!
define('SECRET_KEY', 'd7a8f7c6e5d4c3b2a19876543210fe dc');

// --- MLM & Commission Settings ---
// Defines the percentage for each referral level.
// Example: Level 1 gets 10%, Level 2 gets 5%, etc.
define('COMMISSION_LEVELS', [
    1 => 10,   // 10% for direct referral (Level 1)
    2 => 5,    // 5% for Level 2
    3 => 2.5   // 2.5% for Level 3
]);

?> 