# Farmer's Marketplace

A web application that connects farmers with suppliers of high-quality seeds and fertilizers at competitive prices.

## Features

- User Registration and Authentication
- Product Catalog with Categories
- Advanced Filtering and Sorting
- Shopping Cart Functionality
- Responsive Design using Tailwind CSS

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)

## Installation

1. Clone the repository to your web server's root directory:

```bash
git clone https://github.com/yourusername/farmer-marketplace.git
```

2. Create a MySQL database and import the schema:

```bash
mysql -u root -p < database.sql
```

3. Configure the database connection:

   - Open `includes/db_connect.php`
   - Update the database credentials if needed

4. Set up the web server:

   - For Apache: Ensure mod_rewrite is enabled
   - For Nginx: Configure the server block to handle PHP files

5. Create the required directories:

```bash
mkdir -p images/products
chmod 777 images/products
```

## Directory Structure

```
farmer_marketplace/
├── css/
├── js/
├── php/
│   ├── login.php
│   ├── register.php
│   ├── products.php
│   └── logout.php
├── images/
│   └── products/
├── includes/
│   └── db_connect.php
├── database.sql
├── index.php
└── README.md
```

## Usage

1. Start your web server and MySQL database
2. Navigate to the project URL in your web browser
3. Register a new account or login with existing credentials
4. Browse products, apply filters, and add items to cart

## Security Considerations

- All user passwords are hashed using PHP's password_hash() function
- SQL injection prevention using prepared statements
- XSS prevention using htmlspecialchars()
- CSRF protection implemented
- Input validation and sanitization

## Contributing

1. Fork the repository
2. Create a new branch
3. Make your changes
4. Submit a pull request

## License

This project is licensed under the MIT License.
