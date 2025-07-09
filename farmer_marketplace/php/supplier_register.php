<?php
session_start();
require_once '../includes/db_connect.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $business_name = filter_input(INPUT_POST, 'business_name', FILTER_SANITIZE_STRING);
    $business_type = filter_input(INPUT_POST, 'business_type', FILTER_SANITIZE_STRING);
    $category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING);
    $state = filter_input(INPUT_POST, 'state', FILTER_SANITIZE_STRING);
    $country = filter_input(INPUT_POST, 'country', FILTER_SANITIZE_STRING);
    $pincode = filter_input(INPUT_POST, 'pincode', FILTER_SANITIZE_STRING);
    $website = filter_input(INPUT_POST, 'website', FILTER_SANITIZE_URL);
    $established_year = filter_input(INPUT_POST, 'established_year', FILTER_SANITIZE_NUMBER_INT);
    $certifications = filter_input(INPUT_POST, 'certifications', FILTER_SANITIZE_STRING);


    if (
        empty($name) || empty($email) || empty($phone) || empty($business_name) ||
        empty($business_type) || empty($category) || empty($address) ||
        empty($city) || empty($state) || empty($country) || empty($pincode)
    ) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {

            $stmt = $pdo->prepare("SELECT id FROM supplier_registration WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->rowCount() > 0) {
                $error = 'This email is already registered as a supplier.';
            } else {

                $logo_url = '';
                if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = '../images/suppliers/';


                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }

                    $file_extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                    $file_name = uniqid('supplier_') . '.' . $file_extension;
                    $target_file = $upload_dir . $file_name;


                    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
                    if (!in_array(strtolower($file_extension), $allowed_types)) {
                        $error = 'Only JPG, JPEG, PNG & GIF files are allowed.';
                    } else {

                        if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_file)) {
                            $logo_url = 'images/suppliers/' . $file_name;
                        } else {
                            $error = 'Failed to upload logo. Please try again.';
                        }
                    }
                }

                if (empty($error)) {

                    $stmt = $pdo->prepare("
                        INSERT INTO supplier_registration (
                            name, email, phone, business_name, business_type, category, 
                            description, address, city, state, country, pincode, 
                            website, established_year, certifications, logo_url
                        ) VALUES (
                            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                        )
                    ");

                    $stmt->execute([
                        $name,
                        $email,
                        $phone,
                        $business_name,
                        $business_type,
                        $category,
                        $description,
                        $address,
                        $city,
                        $state,
                        $country,
                        $pincode,
                        $website,
                        $established_year,
                        $certifications,
                        $logo_url
                    ]);

                    $success = 'Your supplier registration has been submitted successfully. We will review your application and contact you soon.';
                }
            }
        } catch (PDOException $e) {
            $error = 'An error occurred. Please try again later.';
        }
    }
}


$stmt = $pdo->query("SELECT DISTINCT category FROM suppliers");
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);


$business_types = ['manufacturer', 'distributor', 'retailer', 'wholesaler'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Registration - AgriMarket</title>
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
        <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md p-8">
            <h1 class="text-3xl font-bold mb-6 text-center text-green-600">Supplier Registration</h1>

            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="supplier_register.php" enctype="multipart/form-data" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div class="col-span-2">
                        <h2 class="text-xl font-semibold mb-4 text-gray-700">Personal Information</h2>
                    </div>

                    <div>
                        <label for="name" class="block text-gray-700 font-medium mb-2">Full Name *</label>
                        <input type="text" id="name" name="name" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>

                    <div>
                        <label for="email" class="block text-gray-700 font-medium mb-2">Email Address *</label>
                        <input type="email" id="email" name="email" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>

                    <div>
                        <label for="phone" class="block text-gray-700 font-medium mb-2">Phone Number *</label>
                        <input type="tel" id="phone" name="phone" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>

                    <div>
                        <label for="website" class="block text-gray-700 font-medium mb-2">Website (Optional)</label>
                        <input type="url" id="website" name="website"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>


                    <div class="col-span-2">
                        <h2 class="text-xl font-semibold mb-4 text-gray-700 mt-6">Business Information</h2>
                    </div>

                    <div>
                        <label for="business_name" class="block text-gray-700 font-medium mb-2">Business Name *</label>
                        <input type="text" id="business_name" name="business_name" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>

                    <div>
                        <label for="business_type" class="block text-gray-700 font-medium mb-2">Business Type *</label>
                        <select id="business_type" name="business_type" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">Select Business Type</option>
                            <?php foreach ($business_types as $type): ?>
                                <option value="<?php echo htmlspecialchars($type); ?>">
                                    <?php echo ucfirst(htmlspecialchars($type)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="category" class="block text-gray-700 font-medium mb-2">Product Category *</label>
                        <select id="category" name="category" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category); ?>">
                                    <?php echo ucfirst(htmlspecialchars($category)); ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label for="established_year" class="block text-gray-700 font-medium mb-2">Established
                            Year</label>
                        <input type="number" id="established_year" name="established_year" min="1900"
                            max="<?php echo date('Y'); ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>

                    <div class="col-span-2">
                        <label for="description" class="block text-gray-700 font-medium mb-2">Business
                            Description</label>
                        <textarea id="description" name="description" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
                    </div>

                    <div class="col-span-2">
                        <label for="certifications" class="block text-gray-700 font-medium mb-2">Certifications
                            (Optional)</label>
                        <input type="text" id="certifications" name="certifications"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                            placeholder="e.g., ISO 9001, USDA Organic">
                    </div>

                    <div class="col-span-2">
                        <label for="logo" class="block text-gray-700 font-medium mb-2">Business Logo (Optional)</label>
                        <input type="file" id="logo" name="logo" accept="image/*"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                        <p class="text-sm text-gray-500 mt-1">Accepted formats: JPG, JPEG, PNG, GIF. Max size: 2MB</p>
                    </div>


                    <div class="col-span-2">
                        <h2 class="text-xl font-semibold mb-4 text-gray-700 mt-6">Address Information</h2>
                    </div>

                    <div class="col-span-2">
                        <label for="address" class="block text-gray-700 font-medium mb-2">Street Address *</label>
                        <input type="text" id="address" name="address" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>

                    <div>
                        <label for="city" class="block text-gray-700 font-medium mb-2">City *</label>
                        <input type="text" id="city" name="city" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>

                    <div>
                        <label for="state" class="block text-gray-700 font-medium mb-2">State/Province *</label>
                        <input type="text" id="state" name="state" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>

                    <div>
                        <label for="country" class="block text-gray-700 font-medium mb-2">Country *</label>
                        <input type="text" id="country" name="country" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>

                    <div>
                        <label for="pincode" class="block text-gray-700 font-medium mb-2">Postal/ZIP Code *</label>
                        <input type="text" id="pincode" name="pincode" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                </div>

                <div class="flex justify-center mt-8">
                    <button type="submit"
                        class="bg-green-600 text-white px-6 py-3 rounded-md hover:bg-green-700 transition-colors">
                        Submit Registration
                    </button>
                </div>

                <p class="text-center text-gray-500 text-sm mt-4">
                    * Required fields
                </p>
            </form>
        </div>
    </main>


    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="container mx-auto px-6 text-center">
            <p>&copy; <?php echo date('Y'); ?> AgriMarket. All rights reserved.</p>
        </div>
    </footer>
</body>

</html>