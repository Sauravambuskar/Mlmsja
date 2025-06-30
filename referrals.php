<?php
require_once 'classes/Auth.php';
require_once 'classes/Database.php';
require_once 'classes/MLM.php';

$db = new Database();
$auth = new Auth($db);

if (!$auth->isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$user = $auth->user();
$mlm = new MLM($db);
$downline = $mlm->getDownline($user['id']);
$total_commission = $mlm->getTotalCommission($user['id']);
$total_referrals = count($downline);

$pageTitle = "Referral System";
require_once 'client/includes/header.php';
require_once 'client/includes/sidebar.php';
?>

<!-- Main Content -->
<div class="flex-1 p-6 md:p-10">
    <!-- Top Bar -->
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Referral System</h1>
    </div>

    <!-- Referral Info Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-10">
        <!-- Referral Link Card -->
        <div class="bg-white p-6 rounded-2xl shadow-md flex flex-col items-center text-center">
            <div class="bg-blue-100 text-primary rounded-full p-4 mb-4">
                <i class='bx bx-link-alt text-4xl'></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Your Referral Link</h3>
            <div class="relative w-full">
                <input type="text" id="referral-link" class="w-full bg-gray-100 border-2 border-gray-200 rounded-lg py-2 px-4 text-center" value="<?php echo URLROOT . '/register.php?ref=' . htmlspecialchars($user['referral_code']); ?>" readonly>
                <button onclick="copyLink()" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-primary">
                    <i class='bx bx-copy text-xl'></i>
                </button>
            </div>
            <p id="copy-message" class="text-sm text-green-600 mt-2 h-4"></p>
        </div>

        <!-- Total Referrals Card -->
        <div class="bg-white p-6 rounded-2xl shadow-md flex flex-col items-center text-center">
            <div class="bg-green-100 text-green-600 rounded-full p-4 mb-4">
                <i class='bx bxs-group text-4xl'></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Total Referrals</h3>
            <p class="text-4xl font-bold text-gray-900"><?php echo $total_referrals; ?></p>
        </div>

        <!-- Total Commissions Card -->
        <div class="bg-white p-6 rounded-2xl shadow-md flex flex-col items-center text-center">
            <div class="bg-yellow-100 text-yellow-600 rounded-full p-4 mb-4">
                <i class='bx bxs-dollar-circle text-4xl'></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Total Commission</h3>
            <p class="text-4xl font-bold text-gray-900">$<?php echo number_format($total_commission, 2); ?></p>
        </div>
    </div>

    <!-- Downline Table -->
    <div class="bg-white p-6 rounded-2xl shadow-md">
        <h3 class="text-xl font-bold text-gray-800 mb-4">My Downline</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b text-gray-500">
                        <th class="py-3 px-4 font-semibold">Username</th>
                        <th class="py-3 px-4 font-semibold">Email</th>
                        <th class="py-3 px-4 font-semibold">Level</th>
                        <th class="py-3 px-4 font-semibold">Join Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($downline)): ?>
                        <tr><td colspan="4" class="text-center py-10 text-gray-500">You have no referrals yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($downline as $referral): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-4 px-4 text-gray-800 font-medium"><?php echo htmlspecialchars($referral['username']); ?></td>
                            <td class="py-4 px-4 text-gray-600"><?php echo htmlspecialchars($referral['email']); ?></td>
                            <td class="py-4 px-4 text-gray-600"><?php echo $referral['level']; ?></td>
                            <td class="py-4 px-4 text-gray-600"><?php echo date('M d, Y', strtotime($referral['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function copyLink() {
    const linkInput = document.getElementById('referral-link');
    linkInput.select();
    linkInput.setSelectionRange(0, 99999); // For mobile devices
    document.execCommand('copy');
    
    const copyMessage = document.getElementById('copy-message');
    copyMessage.innerText = 'Copied!';
    setTimeout(() => {
        copyMessage.innerText = '';
    }, 2000);
}
</script>

<?php require_once 'client/includes/footer.php'; ?> 