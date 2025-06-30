<?php
require_once 'classes/Auth.php';
require_once 'classes/Database.php';
require_once 'classes/Investment.php';

$db = new Database();
$auth = new Auth($db);

if (!$auth->isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$user = $auth->user();
$investment = new Investment($db);
$plans = $investment->getAllPlans();

$pageTitle = "Investments";
require_once 'client/includes/header.php';
require_once 'client/includes/sidebar.php';
?>

<!-- Main Content -->
<div class="flex-1 p-6 md:p-10">
    <!-- Top Bar -->
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Choose Your Plan</h1>
        <p class="text-gray-500">Select an investment plan to get started.</p>
    </div>

    <!-- Investment Plans Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php foreach ($plans as $plan): ?>
            <?php if ($plan['status'] === 'active'): ?>
            
            <!-- Plan Card -->
            <div class="bg-white rounded-2xl shadow-md overflow-hidden transition-all duration-300 hover:shadow-xl hover:-translate-y-1 flex flex-col">
                <div class="p-6 bg-gradient-to-br from-gray-700 to-gray-900 text-white">
                    <h4 class="text-xl font-bold"><?php echo htmlspecialchars($plan['name']); ?></h4>
                </div>
                
                <div class="p-6 flex-1">
                    <div class="text-center mb-6">
                        <span class="text-5xl font-bold text-gray-800"><?php echo $plan['return_percentage']; ?>%</span>
                        <span class="text-gray-500">/ <?php echo $plan['duration_days']; ?> Days</span>
                    </div>
                    
                    <ul class="space-y-3 text-gray-600 mb-8">
                        <li class="flex items-center">
                            <i class='bx bx-check-circle text-green-500 mr-3'></i>
                            Min. Investment: <strong class="ml-auto">$<?php echo number_format($plan['min_amount'], 2); ?></strong>
                        </li>
                        <li class="flex items-center">
                            <i class='bx bx-check-circle text-green-500 mr-3'></i>
                            Max. Investment: <strong class="ml-auto">$<?php echo number_format($plan['max_amount'], 2); ?></strong>
                        </li>
                        <li class="flex items-center">
                            <i class='bx bx-check-circle text-green-500 mr-3'></i>
                            Status: <strong class="ml-auto capitalize text-green-600"><?php echo $plan['status']; ?></strong>
                        </li>
                    </ul>
                </div>
                
                <div class="p-6 mt-auto">
                    <button class="w-full py-3 px-4 rounded-lg bg-primary text-white font-semibold hover:bg-opacity-90 transition-all transform hover:scale-105"
                            data-bs-toggle="modal" 
                            data-bs-target="#investModal"
                            data-plan-id="<?php echo $plan['id']; ?>"
                            data-plan-name="<?php echo htmlspecialchars($plan['name']); ?>"
                            data-min-amount="<?php echo $plan['min_amount']; ?>"
                            data-max-amount="<?php echo $plan['max_amount']; ?>">
                        Invest Now
                    </button>
                </div>
            </div>
            
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>

<!-- Investment Modal -->
<div class="modal fade" id="investModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-xl shadow-lg">
            <div class="modal-header border-b-0 px-6 pt-6">
                <h5 class="modal-title text-xl font-bold text-gray-800" id="investModalLabel">Invest in Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-6 pb-6">
                <p class="text-gray-600 mb-4">Enter the amount you wish to invest.</p>
                <form action="controllers/InvestmentController.php?action=invest" method="POST" id="investment-form">
                    <input type="hidden" name="plan_id" id="plan_id">
                    <div class="mb-4">
                        <label for="amount" class="block mb-2 text-sm font-medium text-gray-600">Investment Amount</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">$</span>
                            <input type="number" step="0.01" name="amount" id="amount" class="w-full pl-7 pr-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary" required>
                        </div>
                        <div class="text-xs text-gray-500 mt-2" id="amount-range"></div>
                    </div>
                    <button type="submit" class="w-full py-3 px-4 rounded-lg bg-primary text-white font-semibold hover:bg-opacity-90 transition">Confirm Investment</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // This script should still work as IDs are the same
    const investModal = document.getElementById('investModal');
    investModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const planId = button.getAttribute('data-plan-id');
        const planName = button.getAttribute('data-plan-name');
        const minAmount = parseFloat(button.getAttribute('data-min-amount'));
        const maxAmount = parseFloat(button.getAttribute('data-max-amount'));

        const modalTitle = investModal.querySelector('.modal-title');
        const planIdInput = investModal.querySelector('#plan_id');
        const amountInput = investModal.querySelector('#amount');
        const amountRange = investModal.querySelector('#amount-range');

        modalTitle.textContent = 'Invest in ' + planName;
        planIdInput.value = planId;
        amountInput.min = minAmount;
        amountInput.max = maxAmount;
        amountInput.placeholder = `${minAmount.toFixed(2)}`;
        amountRange.textContent = `Min: $${minAmount.toFixed(2)}, Max: $${maxAmount.toFixed(2)}`;
    });
});
</script>

<?php require_once 'client/includes/footer.php'; ?> 