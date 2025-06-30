<?php
require_once 'classes/Auth.php';
require_once 'classes/Database.php';
require_once 'classes/Wallet.php';
require_once 'classes/Transaction.php';

$db = new Database();
$auth = new Auth($db);

if (!$auth->isLoggedIn()) {
    header('Location: index.php');
    exit();
}

// Redirect admins to the admin dashboard
$user = $auth->user();
if ($user['role'] === 'admin') {
    header('Location: admin/index.php');
    exit();
}

$wallet_handler = new Wallet($db);
$wallet = $wallet_handler->getWalletByUserId($user['id']);

$transaction_handler = new Transaction($db);
$transactions = $transaction_handler->getByUserId($user['id']);

$pageTitle = "Dashboard";
require_once 'client/includes/header.php';
require_once 'client/includes/sidebar.php';
?>

<main class="flex-1">
    <div class="p-6 md:p-10">
        <!-- Top Bar -->
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
            <div class="flex items-center space-x-4">
                <span class="text-gray-700 font-medium">Welcome, <?php echo htmlspecialchars($user['name']); ?>!</span>
                <button class="relative p-2 bg-white/50 rounded-full hover:bg-white/80 focus:outline-none focus:ring-2 focus:ring-primary">
                    <i class='bx bxs-bell text-xl text-gray-600'></i>
                    <span class="absolute top-0 right-0 h-2.5 w-2.5 bg-danger rounded-full border-2 border-white"></span>
                </button>
            </div>
        </div>

        <!-- Stats Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            
            <!-- Wallet Balance Card -->
            <div class="glass-card rounded-2xl shadow-lg p-6 flex flex-col">
                <h3 class="text-lg font-semibold text-gray-700">Wallet Balance</h3>
                <p class="text-4xl font-bold mt-2 text-gray-800">$<?php echo number_format($wallet['balance'] ?? 0, 2); ?></p>
                <a href="<?php echo URLROOT; ?>/wallet.php" class="mt-4 inline-block bg-white/80 text-primary font-semibold px-4 py-2 rounded-lg text-sm hover:bg-white transition-colors self-start">
                    Manage Wallet
                </a>
            </div>

            <!-- Active Investments Card -->
            <div class="glass-card rounded-2xl shadow-lg p-6">
                 <div class="flex items-center space-x-4">
                    <div class="p-3 rounded-full bg-white/50">
                        <i class='bx bx-trending-up text-2xl text-yellow-600'></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700">Active Investments</h3>
                        <p class="text-3xl font-bold text-gray-800">3</p> <!-- Placeholder -->
                    </div>
                </div>
            </div>

            <!-- KYC Status Card -->
            <div class="glass-card rounded-2xl shadow-lg p-6">
                <div class="flex items-center space-x-4">
                    <div class="p-3 rounded-full bg-white/50">
                        <i class='bx bxs-user-check text-2xl <?php 
                            switch($user['kyc_status']) {
                                case 'approved': echo 'text-green-600'; break;
                                case 'pending': echo 'text-orange-600'; break;
                                case 'rejected': echo 'text-red-600'; break;
                                default: echo 'text-gray-600';
                            }
                        ?>'></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700">KYC Status</h3>
                        <p class="text-3xl font-bold text-gray-800 capitalize"><?php echo $user['kyc_status']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity Table -->
        <div class="mt-10 glass-card rounded-2xl shadow-lg p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Recent Activity</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-white/20 text-gray-600">
                            <th class="py-3 px-4 font-semibold">Date</th>
                            <th class="py-3 px-4 font-semibold">Type</th>
                            <th class="py-3 px-4 font-semibold">Amount</th>
                            <th class="py-3 px-4 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($transactions)): ?>
                            <tr><td colspan="4" class="text-center py-10 text-gray-700">No recent activity.</td></tr>
                        <?php else: ?>
                            <?php foreach (array_slice($transactions, 0, 5) as $tx): ?>
                            <tr class="border-b border-white/20">
                                <td class="py-4 px-4 text-gray-700"><?php echo date('M d, Y', strtotime($tx['created_at'])); ?></td>
                                <td class="py-4 px-4">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        <?php 
                                            switch($tx['type']) {
                                                case 'investment': echo 'bg-red-100 text-red-800'; break;
                                                case 'commission': echo 'bg-green-100 text-green-800'; break;
                                                default: echo 'bg-blue-100 text-blue-800';
                                            }
                                        ?>">
                                        <?php echo ucfirst($tx['type']); ?>
                                    </span>
                                </td>
                                <td class="py-4 px-4 font-bold <?php echo $tx['amount'] > 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                    <?php echo ($tx['amount'] > 0 ? '+' : '-') . '$' . number_format(abs($tx['amount']), 2); ?>
                                </td>
                                <td class="py-4 px-4 text-gray-700">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-200/50">
                                        <?php echo ucfirst($tx['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php require_once 'client/includes/footer.php'; ?> 