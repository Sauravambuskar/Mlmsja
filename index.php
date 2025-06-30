<?php
$pageTitle = "Login";
require_once 'includes/header.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}
?>

<div class="min-h-screen flex items-center justify-center bg-gray-50 p-4">
    <div class="w-full max-w-md">
        
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-8 py-10">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-800"><?php echo SITENAME; ?></h1>
                    <p class="text-gray-500 mt-2">Welcome back! Please login to your account.</p>
                </div>
                
                <!-- Display login error message -->
                <?php if(isset($_GET['status']) && $_GET['status'] === 'login_failed'): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-6" role="alert">
                        <span class="block sm:inline">Invalid email or password. Please try again.</span>
                    </div>
                <?php endif; ?>
                
                <form action="controllers/AuthController.php?action=login" method="POST" id="login-form">
                    <div class="mb-5">
                        <label for="email" class="block mb-2 text-sm font-medium text-gray-600">Email Address</label>
                        <input type="email" name="email" id="email" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary" required>
                    </div>

                    <div class="mb-5">
                        <label for="password" class="block mb-2 text-sm font-medium text-gray-600">Password</label>
                        <input type="password" name="password" id="password" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary" required>
                    </div>

                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center">
                            <input id="rememberMe" type="checkbox" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                            <label for="rememberMe" class="ml-2 block text-sm text-gray-900">Remember me</label>
                        </div>
                        <a href="forgot-password.php" class="text-sm text-primary hover:underline">Forgot Password?</a>
                    </div>

                    <div>
                        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all transform hover:scale-105">
                            Login
                        </button>
                    </div>
                </form>

                <div class="text-center mt-8">
                    <p class="text-sm text-gray-600">
                        Don't have an account? <a href="register.php" class="font-medium text-primary hover:underline">Sign up now</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 