<!-- Sidebar -->
<aside class="w-64 bg-white shadow-md min-h-screen flex flex-col">
    <div class="p-6 text-center border-b">
        <h1 class="text-2xl font-bold text-primary"><?php echo SITENAME; ?></h1>
    </div>
    
    <nav class="flex-1 px-4 py-4">
        <?php
            $nav_items = [
                'Dashboard' => ['icon' => 'bxs-dashboard', 'url' => 'dashboard.php'],
                'Investments' => ['icon' => 'bxs-briefcase-alt-2', 'url' => 'invest.php'],
                'My Wallet' => ['icon' => 'bxs-wallet', 'url' => 'wallet.php'],
                'Referral System' => ['icon' => 'bxs-group', 'url' => 'referrals.php'],
                'Profile & KYC' => ['icon' => 'bxs-user', 'url' => 'profile.php'],
            ];
            
            // A simple way to get the current page name
            $current_page = basename($_SERVER['PHP_SELF']);
        ?>

        <?php foreach($nav_items as $name => $item): ?>
            <a href="<?php echo URLROOT . '/' . $item['url']; ?>" 
               class="flex items-center px-4 py-3 my-1 rounded-lg transition-colors duration-200 
                      <?php echo ($current_page === $item['url']) 
                           ? 'bg-blue-50 text-primary font-semibold' 
                           : 'text-gray-500 hover:bg-gray-100 hover:text-gray-700'; ?>">
                <i class='bx <?php echo $item['icon']; ?> text-xl'></i>
                <span class="ml-4"><?php echo $name; ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
    
    <div class="p-4 border-t">
        <a href="<?php echo URLROOT; ?>/logout.php" class="flex items-center justify-center w-full px-4 py-3 rounded-lg bg-red-500 text-white font-semibold hover:bg-red-600 transition-colors duration-200">
            <i class='bx bx-log-out text-xl'></i>
            <span class="ml-3">Logout</span>
        </a>
    </div>
</aside>

<!-- Main Content Area -->
<main class="flex-1">

</main>