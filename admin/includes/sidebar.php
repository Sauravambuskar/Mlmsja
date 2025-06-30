<!-- Sidebar -->
<aside class="w-64 bg-gray-800 text-white min-h-screen flex flex-col">
    <div class="p-6 text-center border-b border-gray-700">
        <h1 class="text-2xl font-bold"><?php echo SITENAME; ?> <span class="text-sm font-normal text-gray-400">Admin</span></h1>
    </div>
    
    <nav class="flex-1 px-4 py-4">
        <?php
            $nav_items = [
                'Dashboard' => ['icon' => 'bxs-dashboard', 'url' => 'index.php'],
                'Investments' => ['icon' => 'bxs-briefcase-alt-2', 'url' => 'investment_plans.php'],
                'KYC Requests' => ['icon' => 'bxs-user-check', 'url' => 'kyc.php'],
                // Add more admin links here
            ];
            
            // A simple way to get the current page name
            $current_page = basename($_SERVER['PHP_SELF']);
        ?>

        <?php foreach($nav_items as $name => $item): ?>
            <a href="<?php echo URLROOT . '/admin/' . $item['url']; ?>" 
               class="flex items-center px-4 py-3 my-1 rounded-lg transition-colors duration-200 
                      <?php echo ($current_page === $item['url']) 
                           ? 'bg-gray-700 text-white font-semibold' 
                           : 'text-gray-400 hover:bg-gray-700 hover:text-white'; ?>">
                <i class='bx <?php echo $item['icon']; ?> text-xl'></i>
                <span class="ml-4"><?php echo $name; ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
    
    <div class="p-4 border-t border-gray-700">
        <a href="<?php echo URLROOT; ?>/logout.php" class="flex items-center justify-center w-full px-4 py-3 rounded-lg bg-red-500 text-white font-semibold hover:bg-red-600 transition-colors duration-200">
            <i class='bx bx-log-out text-xl'></i>
            <span class="ml-3">Logout</span>
        </a>
    </div>
</aside>

<!-- Main Content Area -->
<main class="flex-1 bg-gray-100">

</main> 