<?php
require_once '../classes/Auth.php';
require_once '../classes/Database.php';

$db = new Database();
$auth = new Auth($db);

if (!$auth->isLoggedIn() || !$auth->user()['is_admin']) {
    header('Location: ' . URLROOT . '/index.php');
    exit();
}

// Fetching dashboard data (placeholders for now)
$stats = [
    'total_users' => 150,
    'total_investments' => 500,
    'total_revenue' => 50000,
    'pending_kyc' => 15
];

$recent_users = [
    ['username' => 'john.doe', 'email' => 'john.doe@example.com', 'created_at' => '2023-10-27'],
    ['username' => 'jane.doe', 'email' => 'jane.doe@example.com', 'created_at' => '2023-10-26'],
    ['username' => 'peter.p', 'email' => 'peter.p@example.com', 'created_at' => '2023-10-25'],
];


$pageTitle = "Dashboard";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle . ' - ' . SITENAME; ?> (Admin)</title>
    <link href="<?php echo URLROOT; ?>/assets/css/output.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        .glass-card {
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body class="bg-gray-200 font-sans">
    <div class="flex min-h-screen">
        <?php require_once 'includes/sidebar.php'; ?>

        <main class="flex-1">
            <div class="p-6 md:p-10">
                <h1 class="text-3xl font-bold text-gray-800 mb-8">Admin Dashboard</h1>

                <!-- Stat Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="glass-card rounded-lg shadow-lg p-6 flex items-center">
                        <div class="bg-blue-500 text-white rounded-full p-4 mr-4">
                            <i class="bx bxs-user text-2xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Total Users</p>
                            <p class="text-2xl font-bold text-gray-800"><?php echo $stats['total_users']; ?></p>
                        </div>
                    </div>
                    <div class="glass-card rounded-lg shadow-lg p-6 flex items-center">
                        <div class="bg-green-500 text-white rounded-full p-4 mr-4">
                            <i class="bx bxs-wallet text-2xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Total Investments</p>
                            <p class="text-2xl font-bold text-gray-800"><?php echo $stats['total_investments']; ?></p>
                        </div>
                    </div>
                    <div class="glass-card rounded-lg shadow-lg p-6 flex items-center">
                        <div class="bg-yellow-500 text-white rounded-full p-4 mr-4">
                            <i class="bx bxs-dollar-circle text-2xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Total Revenue</p>
                            <p class="text-2xl font-bold text-gray-800">$<?php echo number_format($stats['total_revenue']); ?></p>
                        </div>
                    </div>
                    <div class="glass-card rounded-lg shadow-lg p-6 flex items-center">
                        <div class="bg-red-500 text-white rounded-full p-4 mr-4">
                            <i class="bx bxs-user-check text-2xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Pending KYC</p>
                            <p class="text-2xl font-bold text-gray-800"><?php echo $stats['pending_kyc']; ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Users Table -->
                <div class="glass-card rounded-lg shadow-lg p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Recent Users</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b border-gray-300/50 text-gray-600">
                                    <th class="py-3 px-4 font-semibold">Username</th>
                                    <th class="py-3 px-4 font-semibold">Email</th>
                                    <th class="py-3 px-4 font-semibold">Join Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_users as $user): ?>
                                <tr class="border-b border-gray-300/20">
                                    <td class="py-4 px-4 text-gray-800 font-medium"><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td class="py-4 px-4 text-gray-700"><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td class="py-4 px-4 text-gray-700"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 