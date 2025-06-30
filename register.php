<?php
$pageTitle = "Create Account";
require_once 'includes/header.php';
?>

<div class="min-h-screen flex items-center justify-center bg-gray-50 p-4">
    <div class="w-full max-w-md">
        
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-8 py-10">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-800"><?php echo SITENAME; ?></h1>
                    <p class="text-gray-500 mt-2">Create your account to get started.</p>
                </div>

                <form action="controllers/AuthController.php?action=register" method="POST" id="register-form">
                    <div class="mb-5">
                        <label for="name" class="block mb-2 text-sm font-medium text-gray-600">Full Name</label>
                        <input type="text" name="name" id="name" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary" required>
                    </div>

                    <div class="mb-5">
                        <label for="email" class="block mb-2 text-sm font-medium text-gray-600">Email Address</label>
                        <input type="email" name="email" id="email" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary" required>
                    </div>

                    <div class="mb-5">
                        <label for="password" class="block mb-2 text-sm font-medium text-gray-600">Password</label>
                        <input type="password" name="password" id="password" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary" required>
                    </div>
                    
                    <div class="mb-5">
                        <label for="confirm_password" class="block mb-2 text-sm font-medium text-gray-600">Confirm Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary" required>
                    </div>
                    
                    <div class="mb-6">
                        <label for="referral_code" class="block mb-2 text-sm font-medium text-gray-600">Referral Code (Optional)</label>
                        <input type="text" name="referral_code" id="referral_code" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary" value="<?php echo isset($_GET['ref']) ? htmlspecialchars($_GET['ref']) : ''; ?>">
                    </div>

                    <div>
                        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all transform hover:scale-105">
                            Create Account
                        </button>
                    </div>
                </form>

                <div class="text-center mt-8">
                    <p class="text-sm text-gray-600">
                        Already have an account? <a href="index.php" class="font-medium text-primary hover:underline">Login here</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 