<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle . ' - ' . SITENAME; ?> (Admin)</title>
    <!-- Tailwind CSS -->
    <link href="<?php echo URLROOT; ?>/assets/css/output.css" rel="stylesheet">
    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body class="bg-gray-100 font-sans">
    <div class="flex min-h-screen">
        <!-- The sidebar will be included by the page templates -->
    </div>
</body>
</html> 