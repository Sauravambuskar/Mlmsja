<?php
require_once '../classes/Auth.php';
require_once '../classes/Database.php';
require_once '../classes/Investment.php';

$db = new Database();
$auth = new Auth($db);

if (!$auth->isLoggedIn() || !$auth->user()['is_admin']) {
    header('Location: ' . URLROOT . '/index.php');
    exit();
}

$investment = new Investment($db);
$plans = $investment->getPlans();

$pageTitle = "Investment Plans";
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<!-- Main content -->
<div class="flex-1 p-6 md:p-10">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Manage Investment Plans</h1>
        <button data-bs-toggle="modal" data-bs-target="#planModal" class="bg-primary text-white font-semibold px-5 py-2 rounded-lg hover:bg-opacity-90 transition-colors">
            <i class="bx bx-plus"></i> Add New Plan
        </button>
    </div>

    <!-- Plans Table -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b text-gray-500">
                        <th class="py-3 px-4 font-semibold">Name</th>
                        <th class="py-3 px-4 font-semibold">Return %</th>
                        <th class="py-3 px-4 font-semibold">Duration (Days)</th>
                        <th class="py-3 px-4 font-semibold">Min Amount</th>
                        <th class="py-3 px-4 font-semibold">Max Amount</th>
                        <th class="py-3 px-4 font-semibold">Status</th>
                        <th class="py-3 px-4 font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($plans as $plan): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-4 px-4 text-gray-800 font-medium"><?php echo htmlspecialchars($plan['name']); ?></td>
                        <td class="py-4 px-4 text-gray-600"><?php echo $plan['return_percentage']; ?>%</td>
                        <td class="py-4 px-4 text-gray-600"><?php echo $plan['duration_days']; ?></td>
                        <td class="py-4 px-4 text-gray-600">$<?php echo number_format($plan['min_amount'], 2); ?></td>
                        <td class="py-4 px-4 text-gray-600">$<?php echo number_format($plan['max_amount'], 2); ?></td>
                        <td class="py-4 px-4">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $plan['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo ucfirst($plan['status']); ?>
                            </span>
                        </td>
                        <td class="py-4 px-4">
                            <button class="text-blue-500 hover:text-blue-700 mr-2 edit-plan-btn"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#planModal"
                                    data-plan='<?php echo json_encode($plan); ?>'>
                                <i class="bx bxs-edit text-xl"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Plan Modal -->
<div class="modal fade" id="planModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-xl shadow-lg">
            <div class="modal-header border-b-0 px-6 pt-6">
                <h5 class="modal-title text-xl font-bold text-gray-800" id="planModalLabel">Add New Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-6 pb-6">
                <form action="../controllers/InvestmentController.php" method="POST" id="plan-form">
                    <input type="hidden" name="action" id="form-action" value="add_plan">
                    <input type="hidden" name="plan_id" id="plan_id">

                    <div class="mb-4">
                        <label for="name" class="block mb-2 text-sm font-medium text-gray-600">Plan Name</label>
                        <input type="text" name="name" id="name" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary" required>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label for="return_percentage" class="block mb-2 text-sm font-medium text-gray-600">Return (%)</label>
                            <input type="number" step="0.01" name="return_percentage" id="return_percentage" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary" required>
                        </div>
                        <div class="mb-4">
                            <label for="duration_days" class="block mb-2 text-sm font-medium text-gray-600">Duration (Days)</label>
                            <input type="number" name="duration_days" id="duration_days" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary" required>
                        </div>
                    </div>
                     <div class="grid grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label for="min_amount" class="block mb-2 text-sm font-medium text-gray-600">Min Amount ($)</label>
                            <input type="number" step="0.01" name="min_amount" id="min_amount" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary" required>
                        </div>
                        <div class="mb-4">
                            <label for="max_amount" class="block mb-2 text-sm font-medium text-gray-600">Max Amount ($)</label>
                            <input type="number" step="0.01" name="max_amount" id="max_amount" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary" required>
                        </div>
                    </div>
                     <div class="mb-4">
                        <label for="status" class="block mb-2 text-sm font-medium text-gray-600">Status</label>
                        <select name="status" id="status" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full py-3 px-4 rounded-lg bg-primary text-white font-semibold hover:bg-opacity-90 transition">Save Plan</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const planModal = document.getElementById('planModal');
    const planModalLabel = document.getElementById('planModalLabel');
    const planForm = document.getElementById('plan-form');
    const actionInput = document.getElementById('form-action');
    const planIdInput = document.getElementById('plan_id');

    planModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const planData = button.dataset.plan;

        // Reset form for adding new plan
        planForm.reset();
        actionInput.value = 'add_plan';
        planIdInput.value = '';
        planModalLabel.textContent = 'Add New Plan';

        // If editing, populate form
        if (planData) {
            const plan = JSON.parse(planData);
            planModalLabel.textContent = 'Edit Plan';
            actionInput.value = 'edit_plan';
            planIdInput.value = plan.id;
            
            document.getElementById('name').value = plan.name;
            document.getElementById('return_percentage').value = plan.return_percentage;
            document.getElementById('duration_days').value = plan.duration_days;
            document.getElementById('min_amount').value = plan.min_amount;
            document.getElementById('max_amount').value = plan.max_amount;
            document.getElementById('status').value = plan.status;
        }
    });
});
</script>


<?php require_once 'includes/footer.php'; ?> 