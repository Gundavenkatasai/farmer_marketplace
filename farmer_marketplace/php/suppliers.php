<?php
session_start();
require_once '../includes/db_connect.php';


$stmt = $pdo->query("
    SELECT s.*, sc.contact_person, sc.email, sc.phone, sc.address, sc.city, sc.state, sc.country
    FROM suppliers s
    LEFT JOIN supplier_contact sc ON s.id = sc.supplier_id AND sc.is_primary = 1
    ORDER BY s.name ASC
");
$suppliers = $stmt->fetchAll();


$stmt = $pdo->query("SELECT DISTINCT category FROM suppliers");
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);


$business_types = ['manufacturer', 'distributor', 'retailer', 'wholesaler'];


$stmt = $pdo->query("
    SELECT s.*, sc.contact_person, sc.email, sc.phone
    FROM suppliers s
    LEFT JOIN supplier_contact sc ON s.id = sc.supplier_id AND sc.is_primary = 1
    WHERE s.rating >= 4.5
    ORDER BY s.reviews_count DESC
    LIMIT 3
");
$featured_suppliers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suppliers - AgriMarket</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body class="bg-gray-50">

    <header class="bg-white shadow-sm">
        <nav class="container mx-auto px-6 py-3">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <a href="../index.php" class="text-2xl font-bold text-green-600 flex items-center">
                        <svg class="w-8 h-8 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12z" />
                        </svg>
                        AgriMarket
                    </a>
                </div>
                <div class="flex items-center space-x-8">
                    <a href="../index.php" class="text-gray-600 hover:text-green-600">Home</a>
                    <a href="products.php" class="text-gray-600 hover:text-green-600">Products</a>
                    <a href="suppliers.php" class="text-gray-600 hover:text-green-600">Suppliers</a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="logout.php"
                            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">Logout</a>
                    <?php else: ?>
                        <a href="login.php"
                            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <main class="container mx-auto px-6 py-8">

        <?php if ($featured_suppliers): ?>
            <div class="mb-12">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-3xl font-bold text-gray-800">Featured Suppliers</h2>
                    <a href="supplier_register.php"
                        class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors flex items-center">
                        <i class="fas fa-plus-circle mr-2"></i>Register as Supplier
                    </a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php foreach ($featured_suppliers as $supplier): ?>
                        <div
                            class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow transform hover:-translate-y-1 duration-300 max-w-sm mx-auto w-full">
                            <div class="relative">
                                <img src="../<?php echo htmlspecialchars($supplier['logo_url']); ?>"
                                    alt="<?php echo htmlspecialchars($supplier['name']); ?>" class="w-full h-40 object-cover">
                                <div
                                    class="absolute top-4 right-4 bg-green-600 text-white px-3 py-1 rounded-full text-sm font-medium">
                                    Featured
                                </div>
                            </div>
                            <div class="p-5">
                                <h3 class="text-xl font-semibold mb-2 text-gray-800">
                                    <?php echo htmlspecialchars($supplier['name']); ?>
                                </h3>
                                <p class="text-gray-600 mb-4 line-clamp-3 text-sm">
                                    <?php echo htmlspecialchars($supplier['description']); ?>
                                </p>
                                <div class="flex items-center space-x-4 mb-4">
                                    <div class="flex text-yellow-400">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if ($i <= $supplier['rating']): ?>
                                                <i class="fas fa-star"></i>
                                            <?php else: ?>
                                                <i class="far fa-star"></i>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="text-gray-600 text-sm"><?php echo number_format($supplier['rating'], 1); ?>
                                        (<?php echo $supplier['reviews_count']; ?> reviews)</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-500">
                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                        <?php echo htmlspecialchars($supplier['location']); ?>
                                    </span>
                                    <a href="supplier_details.php?id=<?php echo $supplier['id']; ?>"
                                        class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors text-sm font-medium">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>


        <div>
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-bold text-gray-800">All Suppliers</h2>
                <div class="flex space-x-4">
                    <a href="supplier_register.php"
                        class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors flex items-center">
                        <i class="fas fa-plus-circle mr-2"></i>Register as Supplier
                    </a>
                </div>
            </div>


            <div class="bg-white p-5 rounded-lg shadow-md mb-8">
                <form id="filterForm" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="category" class="block text-gray-700 font-medium mb-2">Category</label>
                        <select id="category" name="category"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category); ?>">
                                    <?php echo ucfirst(htmlspecialchars($category)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="business_type" class="block text-gray-700 font-medium mb-2">Business Type</label>
                        <select id="business_type" name="business_type"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">All Types</option>
                            <?php foreach ($business_types as $type): ?>
                                <option value="<?php echo htmlspecialchars($type); ?>">
                                    <?php echo ucfirst(htmlspecialchars($type)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="rating" class="block text-gray-700 font-medium mb-2">Minimum Rating</label>
                        <select id="rating" name="rating"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">Any Rating</option>
                            <option value="4">4+ Stars</option>
                            <option value="3">3+ Stars</option>
                            <option value="2">2+ Stars</option>
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button type="submit"
                            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors w-full font-medium">
                            Apply Filters
                        </button>
                    </div>
                </form>
            </div>


            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php foreach ($suppliers as $supplier): ?>
                    <div
                        class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow transform hover:-translate-y-1 duration-300 max-w-sm mx-auto w-full">
                        <div class="relative">
                            <img src="../<?php echo htmlspecialchars($supplier['logo_url']); ?>"
                                alt="<?php echo htmlspecialchars($supplier['name']); ?>" class="w-full h-40 object-cover">
                        </div>
                        <div class="p-5">
                            <h3 class="text-xl font-semibold mb-2 text-gray-800">
                                <?php echo htmlspecialchars($supplier['name']); ?>
                            </h3>
                            <p class="text-gray-600 mb-4 line-clamp-3 text-sm">
                                <?php echo htmlspecialchars($supplier['description']); ?>
                            </p>
                            <div class="flex items-center space-x-4 mb-4">
                                <div class="flex text-yellow-400">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= $supplier['rating']): ?>
                                            <i class="fas fa-star"></i>
                                        <?php else: ?>
                                            <i class="far fa-star"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                <span class="text-gray-600 text-sm"><?php echo number_format($supplier['rating'], 1); ?>
                                    (<?php echo $supplier['reviews_count']; ?> reviews)</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">
                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                    <?php echo htmlspecialchars($supplier['location']); ?>
                                </span>
                                <a href="supplier_details.php?id=<?php echo $supplier['id']; ?>"
                                    class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors text-sm font-medium">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>


    <div id="contactModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white rounded-lg p-8 max-w-md w-full">
                <h3 class="text-xl font-semibold mb-4">Contact Supplier</h3>
                <form id="contactForm" class="space-y-4">
                    <input type="hidden" id="supplierId" name="supplier_id">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Your Name</label>
                        <input type="text" name="name" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Your Email</label>
                        <input type="email" name="email" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Subject</label>
                        <input type="text" name="subject" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Message</label>
                        <textarea name="message" required rows="4"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500"></textarea>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <button type="button" onclick="closeContactModal()"
                            class="px-4 py-2 border rounded-md hover:bg-gray-50">Cancel</button>
                        <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Send</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div id="quoteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white rounded-lg p-8 max-w-md w-full">
                <h3 class="text-xl font-semibold mb-4">Request Quote</h3>
                <form id="quoteForm" class="space-y-4">
                    <input type="hidden" id="quoteSupplierId" name="supplier_id">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Your Name</label>
                        <input type="text" name="name" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Your Email</label>
                        <input type="email" name="email" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Product Details</label>
                        <textarea name="product_details" required rows="4"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500"
                            placeholder="Please provide details about the products you're interested in, including quantities and specifications"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Additional Requirements</label>
                        <textarea name="requirements" rows="4"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500"
                            placeholder="Any specific requirements or questions"></textarea>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <button type="button" onclick="closeQuoteModal()"
                            class="px-4 py-2 border rounded-md hover:bg-gray-50">Cancel</button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>

        const categoryFilter = document.getElementById('categoryFilter');
        const businessTypeFilter = document.getElementById('businessTypeFilter');
        const sortOrder = document.getElementById('sortOrder');
        const supplierCards = document.querySelectorAll('.supplier-card');

        function filterAndSortSuppliers() {
            const selectedCategory = categoryFilter.value;
            const selectedBusinessType = businessTypeFilter.value;
            const selectedSort = sortOrder.value;


            const cardsArray = Array.from(supplierCards);


            cardsArray.forEach(card => {
                const categoryMatch = !selectedCategory || card.dataset.category === selectedCategory;
                const businessTypeMatch = !selectedBusinessType || card.dataset.businessType === selectedBusinessType;

                if (categoryMatch && businessTypeMatch) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });


            const visibleCards = cardsArray.filter(card => card.style.display !== 'none');
            const sortedCards = visibleCards.sort((a, b) => {
                const nameA = a.querySelector('h3').textContent;
                const nameB = b.querySelector('h3').textContent;
                const ratingA = parseFloat(a.querySelector('.text-gray-600').textContent);
                const ratingB = parseFloat(b.querySelector('.text-gray-600').textContent);
                const establishedA = parseInt(a.querySelector('.fa-calendar-alt').nextSibling.textContent.split(':')[1]);
                const establishedB = parseInt(b.querySelector('.fa-calendar-alt').nextSibling.textContent.split(':')[1]);

                switch (selectedSort) {
                    case 'name_asc':
                        return nameA.localeCompare(nameB);
                    case 'name_desc':
                        return nameB.localeCompare(nameA);
                    case 'rating_desc':
                        return ratingB - ratingA;
                    case 'established_desc':
                        return establishedB - establishedA;
                    default:
                        return 0;
                }
            });


            const container = document.querySelector('.grid');
            sortedCards.forEach(card => container.appendChild(card));
        }

        categoryFilter.addEventListener('change', filterAndSortSuppliers);
        businessTypeFilter.addEventListener('change', filterAndSortSuppliers);
        sortOrder.addEventListener('change', filterAndSortSuppliers);


        function contactSupplier(supplierId) {
            document.getElementById('supplierId').value = supplierId;
            document.getElementById('contactModal').classList.remove('hidden');
        }

        function closeContactModal() {
            document.getElementById('contactModal').classList.add('hidden');
        }


        function requestQuote(supplierId) {
            document.getElementById('quoteSupplierId').value = supplierId;
            document.getElementById('quoteModal').classList.remove('hidden');
        }

        function closeQuoteModal() {
            document.getElementById('quoteModal').classList.add('hidden');
        }


        document.getElementById('contactForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);


            alert('Message sent successfully!');
            closeContactModal();
        });


        document.getElementById('quoteForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);


            alert('Quote request submitted successfully!');
            closeQuoteModal();
        });


        document.querySelectorAll('img').forEach(img => {
            img.addEventListener('load', function () {
                this.classList.add('loaded');
            });
        });
    </script>

    <style>
        img {
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }

        img.loaded {
            opacity: 1;
        }


        .hover-card {
            transition: transform 0.3s ease-in-out;
        }

        .hover-card:hover {
            transform: translateY(-5px);
        }


        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }


        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
        }
    </style>
</body>

</html>