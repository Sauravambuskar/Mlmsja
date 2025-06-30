<?php
require_once 'classes/Auth.php';
require_once 'classes/Database.php';
require_once 'classes/KYC.php';

$db = new Database();
$auth = new Auth($db);

if (!$auth->isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$user = $auth->user();
$kyc_handler = new KYC($db);
$documents = $kyc_handler->getDocumentsByUserId($user['id']);

$pageTitle = "Profile & KYC";
require_once 'client/includes/header.php';
require_once 'client/includes/sidebar.php';
?>

<!-- Main Content -->
<div class="flex-1 p-6 md:p-10">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Profile & KYC</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column: Profile & Password -->
        <div class="lg:col-span-1 space-y-8">
            <!-- Profile Information -->
            <div class="bg-white p-6 rounded-2xl shadow-md">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Profile Information</h3>
                <form>
                    <div class="mb-4">
                        <label for="username" class="block mb-2 text-sm font-medium text-gray-600">Username</label>
                        <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" class="w-full px-4 py-3 rounded-lg bg-gray-100 border-transparent focus:outline-none" disabled>
                    </div>
                    <div class="mb-4">
                        <label for="email" class="block mb-2 text-sm font-medium text-gray-600">Email Address</label>
                        <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="w-full px-4 py-3 rounded-lg bg-gray-100 border-transparent focus:outline-none" disabled>
                    </div>
                </form>
            </div>

            <!-- Change Password -->
            <div class="bg-white p-6 rounded-2xl shadow-md">
                 <h3 class="text-xl font-bold text-gray-800 mb-4">Change Password</h3>
                <form action="controllers/AuthController.php?action=change_password" method="POST">
                    <div class="mb-4">
                        <label for="current_password" class="block mb-2 text-sm font-medium text-gray-600">Current Password</label>
                        <input type="password" name="current_password" id="current_password" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary" required>
                    </div>
                    <div class="mb-4">
                        <label for="new_password" class="block mb-2 text-sm font-medium text-gray-600">New Password</label>
                        <input type="password" name="new_password" id="new_password" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary" required>
                    </div>
                    <div class="mb-6">
                        <label for="confirm_password" class="block mb-2 text-sm font-medium text-gray-600">Confirm New Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary" required>
                    </div>
                    <button type="submit" class="w-full py-3 px-4 rounded-lg bg-primary text-white font-semibold hover:bg-opacity-90 transition">Update Password</button>
                </form>
            </div>
        </div>

        <!-- Right Column: KYC -->
        <div class="lg:col-span-2">
            <div class="bg-white p-6 rounded-2xl shadow-md">
                <h3 class="text-xl font-bold text-gray-800 mb-4">KYC Verification</h3>
                
                <?php if ($user['kyc_status'] === 'approved'): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg" role="alert">
                        <p class="font-bold">KYC Approved</p>
                        <p>Your documents have been verified successfully.</p>
                    </div>
                <?php elseif ($user['kyc_status'] === 'pending'): ?>
                    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-lg" role="alert">
                        <p class="font-bold">KYC Pending</p>
                        <p>Your documents are under review. Please wait.</p>
                    </div>
                     <div class="mt-6">
                        <h4 class="font-semibold text-gray-700 mb-2">Submitted Document:</h4>
                        <a href="<?php echo URLROOT . '/' . htmlspecialchars($documents[0]['document_path']); ?>" target="_blank" class="text-primary hover:underline">View Document</a>
                    </div>
                <?php elseif ($user['kyc_status'] === 'rejected'): ?>
                     <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg" role="alert">
                        <p class="font-bold">KYC Rejected</p>
                        <p>Reason: <?php echo htmlspecialchars($documents[0]['rejection_reason'] ?? 'Not specified'); ?></p>
                    </div>
                    <!-- Allow re-upload -->
                    <h4 class="font-semibold text-gray-700 mt-6 mb-2">Re-upload Your Document:</h4>
                    <form action="controllers/KYCController.php?action=upload" method="POST" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label for="document_type" class="block mb-2 text-sm font-medium text-gray-600">Document Type</label>
                            <select name="document_type" id="document_type" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary" required>
                                <option value="passport">Passport</option>
                                <option value="national_id">National ID</option>
                                <option value="drivers_license">Driver's License</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="document" class="block mb-2 text-sm font-medium text-gray-600">Upload Document</label>
                            <input type="file" name="document" id="document" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-primary hover:file:bg-blue-100" required>
                        </div>
                        <button type="submit" class="w-full py-3 px-4 rounded-lg bg-primary text-white font-semibold hover:bg-opacity-90 transition">Upload and Submit</button>
                    </form>
                <?php else: // Not submitted yet ?>
                    <p class="text-gray-600 mb-4">Please upload your document for verification.</p>
                    <form action="controllers/KYCController.php?action=upload" method="POST" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label for="document_type" class="block mb-2 text-sm font-medium text-gray-600">Document Type</label>
                            <select name="document_type" id="document_type" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary" required>
                                <option value="passport">Passport</option>
                                <option value="national_id">National ID</option>
                                <option value="drivers_license">Driver's License</option>
                            </select>
                        </div>
                        <div class="mb-6">
                            <label for="document" class="block mb-2 text-sm font-medium text-gray-600">Upload Document</label>
                            <input type="file" name="document" id="document" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-primary hover:file:bg-blue-100" required>
                        </div>
                        <button type="submit" class="w-full py-3 px-4 rounded-lg bg-primary text-white font-semibold hover:bg-opacity-90 transition">Upload and Submit</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'client/includes/footer.php'; ?>