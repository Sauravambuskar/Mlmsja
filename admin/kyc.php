<?php
require_once '../classes/Auth.php';
require_once '../classes/Database.php';
require_once '../classes/KYC.php';

$db = new Database();
$auth = new Auth($db);

if (!$auth->isLoggedIn() || !$auth->user()['is_admin']) {
    header('Location: ' . URLROOT . '/index.php');
    exit();
}

$kyc_handler = new KYC($db);
$requests = $kyc_handler->getAllDocuments();

$pageTitle = "KYC Requests";
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<!-- Main content -->
<div class="flex-1 p-6 md:p-10">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">KYC Management</h1>
    </div>

    <!-- KYC Requests Table -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b text-gray-500">
                        <th class="py-3 px-4 font-semibold">User ID</th>
                        <th class="py-3 px-4 font-semibold">Username</th>
                        <th class="py-3 px-4 font-semibold">Document Type</th>
                        <th class="py-3 px-4 font-semibold">Submitted At</th>
                        <th class="py-3 px-4 font-semibold">Status</th>
                        <th class="py-3 px-4 font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $request): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-4 px-4 text-gray-600"><?php echo $request['user_id']; ?></td>
                        <td class="py-4 px-4 text-gray-800 font-medium"><?php echo htmlspecialchars($request['username']); ?></td>
                        <td class="py-4 px-4 text-gray-600"><?php echo ucfirst(str_replace('_', ' ', $request['document_type'])); ?></td>
                        <td class="py-4 px-4 text-gray-600"><?php echo date('M d, Y', strtotime($request['created_at'])); ?></td>
                        <td class="py-4 px-4">
                             <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                <?php 
                                    switch($request['status']) {
                                        case 'approved': echo 'bg-green-100 text-green-800'; break;
                                        case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                        case 'rejected': echo 'bg-red-100 text-red-800'; break;
                                    }
                                ?>">
                                <?php echo ucfirst($request['status']); ?>
                            </span>
                        </td>
                        <td class="py-4 px-4">
                            <button class="text-blue-500 hover:text-blue-700 view-request-btn"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#kycModal"
                                    data-request='<?php echo json_encode($request); ?>'>
                                View
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- KYC Details Modal -->
<div class="modal fade" id="kycModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-xl shadow-lg">
            <div class="modal-header border-b-0 px-6 pt-6">
                <h5 class="modal-title text-xl font-bold text-gray-800" id="kycModalLabel">KYC Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-6 pb-6">
                <div class="mb-4">
                    <p><strong class="text-gray-600">Username:</strong> <span id="modal-username"></span></p>
                    <p><strong class="text-gray-600">Document Type:</strong> <span id="modal-doc-type"></span></p>
                    <p><strong class="text-gray-600">Submitted:</strong> <span id="modal-submitted-at"></span></p>
                </div>
                <div class="mb-6">
                    <a href="#" id="modal-doc-link" target="_blank" class="w-full text-center block py-3 px-4 rounded-lg bg-blue-50 text-primary font-semibold hover:bg-blue-100 transition">
                        View Submitted Document
                    </a>
                </div>
                
                <form action="../controllers/KYCController.php" method="POST" id="kyc-action-form">
                    <input type="hidden" name="action" id="kyc-action-type">
                    <input type="hidden" name="kyc_id" id="modal-kyc-id">
                    
                    <div id="rejection-reason-container" class="hidden mb-4">
                         <label for="rejection_reason" class="block mb-2 text-sm font-medium text-gray-600">Rejection Reason</label>
                         <textarea name="rejection_reason" id="rejection_reason" rows="3" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                    </div>

                    <div class="flex space-x-4">
                        <button type="submit" name="approve" value="1" class="w-full py-3 px-4 rounded-lg bg-green-500 text-white font-semibold hover:bg-green-600 transition">Approve</button>
                        <button type="submit" name="reject" value="1" id="reject-btn" class="w-full py-3 px-4 rounded-lg bg-red-500 text-white font-semibold hover:bg-red-600 transition">Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const kycModal = document.getElementById('kycModal');
    const kycActionForm = document.getElementById('kyc-action-form');
    const kycActionType = document.getElementById('kyc-action-type');
    const rejectionContainer = document.getElementById('rejection-reason-container');

    kycModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const requestData = JSON.parse(button.dataset.request);

        // Populate modal with data
        document.getElementById('modal-username').textContent = requestData.username;
        document.getElementById('modal-doc-type').textContent = requestData.document_type.replace('_', ' ');
        document.getElementById('modal-submitted-at').textContent = new Date(requestData.created_at).toLocaleDateString();
        document.getElementById('modal-kyc-id').value = requestData.id;
        document.getElementById('modal-doc-link').href = `<?php echo URLROOT . '/'; ?>${requestData.document_path}`;
        
        // Hide rejection reason by default
        rejectionContainer.classList.add('hidden');
    });

    // Handle form submission logic
    kycActionForm.addEventListener('submit', function(e) {
        // Find which button was clicked
        const approveBtn = e.submitter.name === 'approve';
        
        if(approveBtn) {
            kycActionType.value = 'approve_kyc';
        } else {
            kycActionType.value = 'reject_kyc';
            // If rejecting, and reason is empty, prevent submission and show the field
            const reason = document.getElementById('rejection_reason').value.trim();
            if (rejectionContainer.classList.contains('hidden')) {
                e.preventDefault();
                rejectionContainer.classList.remove('hidden');
            } else if (reason === '') {
                e.preventDefault();
                alert('Please provide a rejection reason.');
            }
        }
    });
});
</script>


<?php require_once 'includes/footer.php'; ?> 