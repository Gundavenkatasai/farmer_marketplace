<?php
session_start();
require_once '../includes/db_connect.php';


$supplier_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (!$supplier_id) {
    header('Location: suppliers.php');
    exit();
}


$stmt = $pdo->prepare("
    SELECT s.*, sc.contact_person, sc.email, sc.phone, sc.alternate_phone, 
           sc.address, sc.city, sc.state, sc.country, sc.pincode
    FROM suppliers s
    LEFT JOIN supplier_contact sc ON s.id = sc.supplier_id AND sc.is_primary = 1
    WHERE s.id = ?
");
$stmt->execute([$supplier_id]);
$supplier = $stmt->fetch();

if (!$supplier) {
    header('Location: suppliers.php');
    exit();
}


$stmt = $pdo->prepare("SELECT platform, url FROM supplier_social_media WHERE supplier_id = ?");
$stmt->execute([$supplier_id]);
$social_media = $stmt->fetchAll();


$stmt = $pdo->prepare("
    SELECT * FROM products 
    WHERE supplier_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$supplier_id]);
$products = $stmt->fetchAll();


$reviews_query = "SELECT sr.*, u.name as user_name 
                 FROM supplier_reviews sr 
                 JOIN users u ON sr.user_id = u.id 
                 WHERE sr.supplier_id = :supplier_id 
                 ORDER BY sr.created_at DESC";
$reviews_stmt = $pdo->prepare($reviews_query);
$reviews_stmt->execute(['supplier_id' => $supplier_id]);
$reviews = $reviews_stmt->fetchAll(PDO::FETCH_ASSOC);


$stmt = $pdo->prepare("
    SELECT certification_name, issuing_authority, issue_date, expiry_date 
    FROM supplier_certifications 
    WHERE supplier_id = ? 
    ORDER BY issue_date DESC
");
$stmt->execute([$supplier_id]);
$certifications = $stmt->fetchAll();


$stmt = $pdo->prepare("
    SELECT title, description, year, image_url 
    FROM supplier_achievements 
    WHERE supplier_id = ? 
    ORDER BY year DESC
");
$stmt->execute([$supplier_id]);
$achievements = $stmt->fetchAll();


$stmt = $pdo->prepare("
    SELECT image_url, caption 
    FROM supplier_gallery 
    WHERE supplier_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$supplier_id]);
$gallery = $stmt->fetchAll();


$stmt = $pdo->prepare("
    SELECT question, answer 
    FROM supplier_faqs 
    WHERE supplier_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$supplier_id]);
$faqs = $stmt->fetchAll();


$stmt = $pdo->prepare("
    SELECT t.*, u.name as user_name
    FROM supplier_testimonials t
    JOIN users u ON t.user_id = u.id
    WHERE t.supplier_id = ? 
    ORDER BY t.created_at DESC
");
$stmt->execute([$supplier_id]);
$testimonials = $stmt->fetchAll();


$stmt = $pdo->prepare("
    SELECT shipping_method, delivery_time, shipping_cost, free_shipping_threshold 
    FROM supplier_shipping 
    WHERE supplier_id = ?
");
$stmt->execute([$supplier_id]);
$shipping = $stmt->fetchAll();


$stmt = $pdo->prepare("
    SELECT payment_method, details 
    FROM supplier_payment_methods 
    WHERE supplier_id = ?
");
$stmt->execute([$supplier_id]);
$payment_methods = $stmt->fetchAll();


$average_rating = $supplier['rating'];
$total_reviews = $supplier['reviews_count'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($supplier['name']); ?> - Supplier Details | AgriMarket</title>
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

        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex flex-col md:flex-row items-start md:items-center space-y-4 md:space-y-0 md:space-x-6">
                <img src="../<?php echo htmlspecialchars($supplier['logo_url']); ?>"
                    alt="<?php echo htmlspecialchars($supplier['name']); ?>" class="w-32 h-32 object-cover rounded-lg">
                <div class="flex-grow">
                    <h1 class="text-3xl font-bold mb-2"><?php echo htmlspecialchars($supplier['name']); ?></h1>
                    <div class="flex items-center space-x-4 mb-2">
                        <span class="text-gray-600">
                            <i class="fas fa-building mr-2"></i>
                            <?php echo htmlspecialchars(ucfirst($supplier['business_type'])); ?>
                        </span>
                        <span class="text-gray-600">
                            <i class="fas fa-map-marker-alt mr-2"></i>
                            <?php echo htmlspecialchars($supplier['location']); ?>
                        </span>
                        <span class="text-gray-600">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            Established: <?php echo htmlspecialchars($supplier['established_year']); ?>
                        </span>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="flex text-yellow-400">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php if ($i <= $average_rating): ?>
                                    <i class="fas fa-star"></i>
                                <?php elseif ($i - 0.5 <= $average_rating): ?>
                                    <i class="fas fa-star-half-alt"></i>
                                <?php else: ?>
                                    <i class="far fa-star"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                        <span class="text-gray-600"><?php echo number_format($average_rating, 1); ?>
                            (<?php echo $total_reviews; ?> reviews)</span>
                    </div>
                </div>
                <div class="flex flex-col space-y-2">
                    <button onclick="contactSupplier(<?php echo $supplier_id; ?>)"
                        class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                        Contact Supplier
                    </button>
                    <button onclick="requestQuote(<?php echo $supplier_id; ?>)"
                        class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        Request Quote
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="lg:col-span-2 space-y-8">

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-2xl font-semibold mb-4">About</h2>
                    <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($supplier['description']); ?></p>

                    <?php if ($supplier['certifications']): ?>
                        <div class="mt-4">
                            <h3 class="text-lg font-semibold mb-2">Certifications</h3>
                            <p class="text-gray-600"><?php echo htmlspecialchars($supplier['certifications']); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($supplier['website']): ?>
                        <div class="mt-4">
                            <h3 class="text-lg font-semibold mb-2">Website</h3>
                            <a href="<?php echo htmlspecialchars($supplier['website']); ?>" target="_blank"
                                class="text-blue-600 hover:underline">
                                <?php echo htmlspecialchars($supplier['website']); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>


                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-2xl font-bold mb-4">Products</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($products as $product): ?>
                            <div class="border rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                                <img src="../<?php echo htmlspecialchars($product['image_url']); ?>"
                                    alt="<?php echo htmlspecialchars($product['name']); ?>"
                                    class="w-full h-48 object-cover">
                                <div class="p-4">
                                    <h3 class="text-lg font-semibold mb-2"><?php echo htmlspecialchars($product['name']); ?>
                                    </h3>
                                    <p class="text-gray-600 mb-2"><?php echo htmlspecialchars($product['description']); ?>
                                    </p>
                                    <div class="flex justify-between items-center">
                                        <span
                                            class="text-green-600 font-bold">₹<?php echo number_format($product['price'], 2); ?></span>
                                        <span class="text-sm text-gray-500">Stock: <?php echo $product['stock']; ?></span>
                                    </div>
                                    <div class="mt-4">
                                        <button onclick="addToCart(<?php echo $product['id']; ?>)"
                                            class="btn-primary w-full">Add to Cart</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>


                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-2xl font-semibold mb-4">Customer Reviews</h2>
                    <?php if ($reviews): ?>
                        <?php foreach ($reviews as $review): ?>
                            <div class="border-b border-gray-200 py-4">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h3 class="font-semibold"><?php echo htmlspecialchars($review['user_name']); ?></h3>
                                        <div class="flex text-yellow-400">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <?php if ($i <= $review['rating']): ?>
                                                    <i class="fas fa-star"></i>
                                                <?php else: ?>
                                                    <i class="far fa-star"></i>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <span
                                        class="text-gray-500 text-sm"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></span>
                                </div>
                                <p class="text-gray-600"><?php echo htmlspecialchars($review['comment']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-gray-600">No reviews yet.</p>
                    <?php endif; ?>
                </div>


                <?php if ($gallery): ?>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-2xl font-semibold mb-4">Gallery</h2>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <?php foreach ($gallery as $image): ?>
                                <div class="relative group">
                                    <img src="../<?php echo htmlspecialchars($image['image_url']); ?>"
                                        alt="<?php echo htmlspecialchars($image['caption']); ?>"
                                        class="w-full h-48 object-cover rounded-lg cursor-pointer"
                                        onclick="openImageModal(this.src, '<?php echo htmlspecialchars($image['caption']); ?>')">
                                    <?php if ($image['caption']): ?>
                                        <div
                                            class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all duration-300 rounded-lg flex items-center justify-center opacity-0 group-hover:opacity-100">
                                            <p class="text-white text-center px-2">
                                                <?php echo htmlspecialchars($image['caption']); ?>
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>


                <?php if ($achievements): ?>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-2xl font-semibold mb-4">Achievements & Awards</h2>
                        <div class="space-y-6">
                            <?php foreach ($achievements as $achievement): ?>
                                <div class="flex items-start space-x-4">
                                    <?php if ($achievement['image_url']): ?>
                                        <img src="../<?php echo htmlspecialchars($achievement['image_url']); ?>"
                                            alt="<?php echo htmlspecialchars($achievement['title']); ?>"
                                            class="w-24 h-24 object-cover rounded-lg">
                                    <?php endif; ?>
                                    <div>
                                        <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($achievement['title']); ?>
                                        </h3>
                                        <p class="text-gray-600"><?php echo htmlspecialchars($achievement['description']); ?>
                                        </p>
                                        <span class="text-sm text-gray-500">Year:
                                            <?php echo htmlspecialchars($achievement['year']); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>


                <?php if ($testimonials): ?>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-2xl font-semibold mb-4">Customer Testimonials</h2>
                        <div class="space-y-6">
                            <?php foreach ($testimonials as $testimonial): ?>
                                <div class="border-b border-gray-200 pb-6">
                                    <div class="flex items-start space-x-4">
                                        <?php if ($testimonial['profile_image']): ?>
                                            <img src="../<?php echo htmlspecialchars($testimonial['profile_image']); ?>"
                                                alt="<?php echo htmlspecialchars($testimonial['user_name']); ?>"
                                                class="w-12 h-12 rounded-full object-cover">
                                        <?php endif; ?>
                                        <div class="flex-grow">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <h3 class="font-semibold">
                                                        <?php echo htmlspecialchars($testimonial['user_name']); ?>
                                                    </h3>
                                                    <div class="flex text-yellow-400">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <?php if ($i <= $testimonial['rating']): ?>
                                                                <i class="fas fa-star"></i>
                                                            <?php else: ?>
                                                                <i class="far fa-star"></i>
                                                            <?php endif; ?>
                                                        <?php endfor; ?>
                                                    </div>
                                                </div>
                                                <span
                                                    class="text-gray-500 text-sm"><?php echo date('M d, Y', strtotime($testimonial['created_at'])); ?></span>
                                            </div>
                                            <p class="text-gray-600 mt-2">
                                                <?php echo htmlspecialchars($testimonial['comment']); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>


                <?php if ($faqs): ?>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-2xl font-semibold mb-4">Frequently Asked Questions</h2>
                        <div class="space-y-4">
                            <?php foreach ($faqs as $faq): ?>
                                <div class="border rounded-lg">
                                    <button class="w-full text-left p-4 focus:outline-none" onclick="toggleFAQ(this)">
                                        <div class="flex justify-between items-center">
                                            <h3 class="font-semibold"><?php echo htmlspecialchars($faq['question']); ?></h3>
                                            <i class="fas fa-chevron-down transition-transform"></i>
                                        </div>
                                    </button>
                                    <div class="hidden p-4 border-t">
                                        <p class="text-gray-600"><?php echo htmlspecialchars($faq['answer']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>


            <div class="space-y-8">

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-2xl font-semibold mb-4">Contact Information</h2>
                    <div class="space-y-4">
                        <div>
                            <h3 class="font-semibold">Contact Person</h3>
                            <p class="text-gray-600"><?php echo htmlspecialchars($supplier['contact_person']); ?></p>
                        </div>
                        <div>
                            <h3 class="font-semibold">Email</h3>
                            <p class="text-gray-600"><?php echo htmlspecialchars($supplier['email']); ?></p>
                        </div>
                        <div>
                            <h3 class="font-semibold">Phone</h3>
                            <p class="text-gray-600"><?php echo htmlspecialchars($supplier['phone']); ?></p>
                            <?php if ($supplier['alternate_phone']): ?>
                                <p class="text-gray-600"><?php echo htmlspecialchars($supplier['alternate_phone']); ?></p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h3 class="font-semibold">Address</h3>
                            <p class="text-gray-600">
                                <?php echo htmlspecialchars($supplier['address']); ?><br>
                                <?php echo htmlspecialchars($supplier['city']); ?>,
                                <?php echo htmlspecialchars($supplier['state']); ?><br>
                                <?php echo htmlspecialchars($supplier['country']); ?>
                                <?php if ($supplier['pincode']): ?>
                                    - <?php echo htmlspecialchars($supplier['pincode']); ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>


                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-2xl font-semibold mb-4">Business Information</h2>
                    <div class="space-y-4">
                        <div>
                            <h3 class="font-semibold">Business Type</h3>
                            <p class="text-gray-600">
                                <?php echo htmlspecialchars(ucfirst($supplier['business_type'])); ?>
                            </p>
                        </div>
                        <div>
                            <h3 class="font-semibold">Minimum Order Amount</h3>
                            <p class="text-gray-600">₹<?php echo number_format($supplier['minimum_order_amount'], 2); ?>
                            </p>
                        </div>
                        <div>
                            <h3 class="font-semibold">Delivery Time</h3>
                            <p class="text-gray-600"><?php echo htmlspecialchars($supplier['delivery_time']); ?></p>
                        </div>
                        <div>
                            <h3 class="font-semibold">Payment Terms</h3>
                            <p class="text-gray-600"><?php echo htmlspecialchars($supplier['payment_terms']); ?></p>
                        </div>
                    </div>
                </div>


                <?php if ($social_media): ?>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-2xl font-semibold mb-4">Social Media</h2>
                        <div class="flex space-x-4">
                            <?php foreach ($social_media as $social): ?>
                                <a href="<?php echo htmlspecialchars($social['url']); ?>" target="_blank"
                                    class="text-gray-600 hover:text-green-600">
                                    <i class="fab fa-<?php echo strtolower($social['platform']); ?> text-2xl"></i>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>


                <?php if ($certifications): ?>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-2xl font-semibold mb-4">Certifications</h2>
                        <div class="space-y-4">
                            <?php foreach ($certifications as $cert): ?>
                                <div class="border rounded-lg p-4">
                                    <h3 class="font-semibold"><?php echo htmlspecialchars($cert['certification_name']); ?></h3>
                                    <p class="text-gray-600">Issued by:
                                        <?php echo htmlspecialchars($cert['issuing_authority']); ?>
                                    </p>
                                    <div class="flex justify-between text-sm text-gray-500 mt-2">
                                        <span>Issued: <?php echo date('M d, Y', strtotime($cert['issue_date'])); ?></span>
                                        <?php if ($cert['expiry_date']): ?>
                                            <span>Expires: <?php echo date('M d, Y', strtotime($cert['expiry_date'])); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>


                <?php if ($shipping): ?>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-2xl font-semibold mb-4">Shipping Information</h2>
                        <div class="space-y-4">
                            <?php foreach ($shipping as $ship): ?>
                                <div>
                                    <h3 class="font-semibold"><?php echo htmlspecialchars($ship['shipping_method']); ?></h3>
                                    <p class="text-gray-600">Delivery Time:
                                        <?php echo htmlspecialchars($ship['delivery_time']); ?>
                                    </p>
                                    <p class="text-gray-600">Shipping Cost:
                                        ₹<?php echo number_format($ship['shipping_cost'], 2); ?></p>
                                    <?php if ($ship['free_shipping_threshold']): ?>
                                        <p class="text-green-600">Free shipping on orders above
                                            ₹<?php echo number_format($ship['free_shipping_threshold'], 2); ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>


                <?php if ($payment_methods): ?>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-2xl font-semibold mb-4">Payment Methods</h2>
                        <div class="space-y-4">
                            <?php foreach ($payment_methods as $method): ?>
                                <div class="flex items-center space-x-2">
                                    <i class="fab fa-<?php echo strtolower($method['payment_method']); ?> text-2xl"></i>
                                    <span class="text-gray-600"><?php echo htmlspecialchars($method['details']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>


    <div id="contactModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white rounded-lg p-8 max-w-md w-full">
                <h3 class="text-xl font-semibold mb-4">Contact <?php echo htmlspecialchars($supplier['name']); ?></h3>
                <form id="contactForm" class="space-y-4">
                    <input type="hidden" id="supplierId" name="supplier_id" value="<?php echo $supplier_id; ?>">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Your Name</label>
                        <input type="text" name="name" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Your Email</label>
                        <input type="email" name="email" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Subject</label>
                        <input type="text" name="subject" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Message</label>
                        <textarea name="message" required rows="4"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <button type="button" onclick="closeContactModal()"
                            class="px-4 py-2 border rounded-md">Cancel</button>
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
                    <input type="hidden" id="quoteSupplierId" name="supplier_id" value="<?php echo $supplier_id; ?>">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Your Name</label>
                        <input type="text" name="name" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Your Email</label>
                        <input type="email" name="email" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Product Details</label>
                        <textarea name="product_details" required rows="4"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            placeholder="Please provide details about the products you're interested in, including quantities and specifications"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Additional Requirements</label>
                        <textarea name="requirements" rows="4"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            placeholder="Any specific requirements or questions"></textarea>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <button type="button" onclick="closeQuoteModal()"
                            class="px-4 py-2 border rounded-md">Cancel</button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white rounded-lg p-4 max-w-4xl w-full">
                <div class="flex justify-between items-center mb-4">
                    <h3 id="imageCaption" class="text-xl font-semibold"></h3>
                    <button onclick="closeImageModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>
                <img id="modalImage" src="" alt="" class="w-full h-auto rounded-lg">
            </div>
        </div>
    </div>

    <script>

        function contactSupplier(supplierId) {
            document.getElementById('contactModal').classList.remove('hidden');
        }

        function closeContactModal() {
            document.getElementById('contactModal').classList.add('hidden');
        }


        function requestQuote(supplierId) {
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


        function openImageModal(src, caption) {
            document.getElementById('modalImage').src = src;
            document.getElementById('imageCaption').textContent = caption;
            document.getElementById('imageModal').classList.remove('hidden');
        }

        function closeImageModal() {
            document.getElementById('imageModal').classList.add('hidden');
        }


        function toggleFAQ(button) {
            const content = button.nextElementSibling;
            const icon = button.querySelector('.fa-chevron-down');

            content.classList.toggle('hidden');
            icon.style.transform = content.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
        }


        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
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
    </style>
</body>

</html>