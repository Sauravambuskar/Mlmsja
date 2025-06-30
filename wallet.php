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

$user = $auth->user();
$wallet_handler = new Wallet($db);
$transaction_handler = new Transaction($db);

$wallet = $wallet_handler->getWalletByUserId($user['id']);
$transactions = $transaction_handler->getByUserId($user['id']);

$pageTitle = "My Wallet";
require_once 'client/includes/header.php';
require_once 'client/includes/sidebar.php';
?>

<!-- Main Content -->
<div class="flex-1 p-6 md:p-10">
    <!-- Top Bar -->
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">My Wallet</h1>
        <!-- Action Button -->
        <button class="bg-primary text-white font-semibold px-5 py-2 rounded-lg hover:bg-opacity-90 transition-colors">
            Request Withdrawal
        </button>
    </div>

    <!-- Wallet Balance Card -->
    <div class="bg-gradient-to-br from-gray-800 to-gray-900 p-8 rounded-2xl shadow-xl text-white relative overflow-hidden mb-10">
        <i class='bx bxs-wallet absolute -right-6 -bottom-6 text-9xl text-white opacity-10'></i>
        <h3 class="text-lg font-semibold text-gray-400">Available Balance</h3>
        <p class="text-5xl font-bold mt-2">$<?php echo number_format($wallet['balance'], 2); ?></p>
    </div>

    <!-- Transaction History Table -->
    <div class="bg-white p-6 rounded-2xl shadow-md">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Transaction History</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b text-gray-500">
                        <th class="py-3 px-4 font-semibold">Date</th>
                        <th class="py-3 px-4 font-semibold">Type</th>
                        <th class="py-3 px-4 font-semibold">Amount</th>
                        <th class="py-3 px-4 font-semibold">Status</th>
                        <th class="py-3 px-4 font-semibold">Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                        <tr><td colspan="5" class="text-center py-10 text-gray-500">You have no transactions yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $tx): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-4 px-4 text-gray-600"><?php echo date('M d, Y, h:i A', strtotime($tx['created_at'])); ?></td>
                            <td class="py-4 px-4">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                    <?php 
                                        switch($tx['type']) {
                                            case 'investment': echo 'bg-red-100 text-red-800'; break;
                                            case 'commission': echo 'bg-green-100 text-green-800'; break;
                                            case 'deposit': echo 'bg-blue-100 text-blue-800'; break;
                                            default: echo 'bg-gray-100 text-gray-800';
                                        }
                                    ?>">
                                    <?php echo ucfirst($tx['type']); ?>
                                </span>
                            </td>
                            <td class="py-4 px-4 font-bold <?php echo $tx['amount'] > 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                <?php echo ($tx['amount'] > 0 ? '+' : '-') . '$' . number_format(abs($tx['amount']), 2); ?>
                            </td>
                            <td class="py-4 px-4 text-gray-600">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-200 text-gray-700">
                                    <?php echo ucfirst($tx['status']); ?>
                                </span>
                            </td>
                            <td class="py-4 px-4 text-gray-500 text-sm"><?php echo htmlspecialchars($tx['description']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'client/includes/footer.php'; ?> 