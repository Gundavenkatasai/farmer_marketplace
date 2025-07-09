<header class="bg-green-600 text-white shadow-lg">
    <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
        <div class="flex items-center">
            <a href="../index.php" class="text-2xl font-bold">Farmer's Marketplace</a>
        </div>
        <div class="flex items-center space-x-4">
            <a href="../index.php" class="hover:text-green-200">Home</a>
            <a href="products.php" class="hover:text-green-200">Products</a>
            <a href="suppliers.php" class="hover:text-green-200">Suppliers</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="cart.php" class="hover:text-green-200">
                    <i class="fas fa-shopping-cart"></i> Cart
                </a>
                <a href="compare_products.php" class="hover:text-green-200">
                    <i class="fas fa-balance-scale"></i> Compare
                </a>
                <a href="logout.php" class="hover:text-green-200">Logout</a>
            <?php else: ?>
                <a href="login.php" class="hover:text-green-200">Login</a>
                <a href="register.php" class="hover:text-green-200">Register</a>
            <?php endif; ?>
        </div>
    </nav>
</header> 