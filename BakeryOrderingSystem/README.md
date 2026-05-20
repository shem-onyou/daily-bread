# The DailyBread — Bakery Ordering System

A full-stack PHP/MySQL web application for a local Filipino bakery. Customers can browse products, place orders, and manage their profiles. Admins can manage products, orders, and customers through a dedicated dashboard.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8+ |
| Database | MySQL (via XAMPP) |
| Frontend | HTML, CSS, Vanilla JavaScript |
| Email | PHPMailer (SMTP via Gmail) |
| Local Server | XAMPP (Apache + MySQL) |

---

## Prerequisites

- [XAMPP](https://www.apachefriends.org/) installed (includes Apache and MySQL)
- A Gmail account with an **App Password** enabled (for email features)

---

## Installation

### 1. Clone or copy the project

Place the project folder inside XAMPP's web root:

```
C:\xampp\htdocs\BakeryOrderingSystem\
```

The folder structure should look like:

```
BakeryOrderingSystem/
├── CSS/
├── Image/
├── JS/
├── PHP/
│   ├── DB/
│   │   ├── bakery_db.sql
│   │   ├── alter_users.sql
│   │   └── alter_products_image.sql
│   ├── PHPMailer/
│   └── *.php
└── index.php
```

---

### 2. Start XAMPP

1. Open the **XAMPP Control Panel**
2. Start **Apache** and **MySQL**

---

### 3. Set up the database

Open your browser and go to:

```
http://localhost/phpmyadmin
```

#### Step 1 — Create the database and base tables

1. Click **SQL** in the top menu
2. Paste and run the contents of `PHP/DB/bakery_db.sql`:

```sql
CREATE DATABASE IF NOT EXISTS bakery_db;
USE bakery_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'customer') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### Step 2 — Add extended user profile columns

Run the contents of `PHP/DB/alter_users.sql`:

```sql
USE bakery_db;

ALTER TABLE users
    ADD COLUMN IF NOT EXISTS phone   VARCHAR(30)   DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS dob     DATE          DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS country VARCHAR(100)  DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS city    VARCHAR(100)  DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS postal  VARCHAR(20)   DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS photo   MEDIUMTEXT    DEFAULT NULL;
```

#### Step 3 — Add authentication columns

```sql
ALTER TABLE users ADD COLUMN IF NOT EXISTS is_verified TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE users ADD COLUMN IF NOT EXISTS verify_token VARCHAR(64) DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS token_expiry DATETIME DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS reset_token VARCHAR(64) DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS reset_expiry DATETIME DEFAULT NULL;
```

#### Step 4 — Create the remaining tables

```sql
USE bakery_db;

CREATE TABLE IF NOT EXISTS products (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    image MEDIUMTEXT
);

CREATE TABLE IF NOT EXISTS orders (
    id VARCHAR(30) PRIMARY KEY,
    user_id INT NOT NULL,
    payment VARCHAR(50) NOT NULL,
    proof_image MEDIUMTEXT,
    shipping DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('pending','completed','cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(30) NOT NULL,
    product_id VARCHAR(50) DEFAULT NULL,
    product_name VARCHAR(150) NOT NULL,
    qty INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

#### Step 5 — Widen the products image column

Run the contents of `PHP/DB/alter_products_image.sql`:

```sql
USE bakery_db;
ALTER TABLE products MODIFY COLUMN image MEDIUMTEXT;
```

---

### 4. Create the admin account

The admin password must be hashed with bcrypt — never insert a plain-text password directly into the database.

1. Create a temporary file at `C:\xampp\htdocs\BakeryOrderingSystem\PHP\create_admin.php`:

```php
<?php
require 'db.php';
$name     = 'Admin';
$email    = 'admin@dailybread.com';
$password = 'your_secure_password';
$hash     = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (name, email, password, role, is_verified) VALUES (?, ?, ?, 'admin', 1)");
$stmt->bind_param("sss", $name, $email, $hash);
$stmt->execute();
echo $stmt->affected_rows > 0 ? 'Admin created.' : 'Failed: ' . $conn->error;
?>
```

2. Visit `http://localhost/BakeryOrderingSystem/PHP/create_admin.php` once
3. **Delete `create_admin.php` immediately after**

---

### 5. Configure the database connection

Open `PHP/db.php` and confirm the credentials match your XAMPP setup:

```php
$host = 'localhost';
$db   = 'bakery_db';
$user = 'root';
$pass = '';        // default XAMPP MySQL password is empty
```

---

### 6. Configure email (PHPMailer)

Open `PHP/mailer.php` and replace the SMTP credentials with your own:

```php
$mail->Username = 'your_gmail@gmail.com';
$mail->Password = 'your_gmail_app_password';
$mail->setFrom('your_gmail@gmail.com', 'The DailyBread');
```

> **How to get a Gmail App Password:**
> 1. Go to your Google Account → Security
> 2. Enable **2-Step Verification**
> 3. Go to **App Passwords** → generate one for "Mail"
> 4. Use the 16-character code as the password above

Email is used for:
- Account verification on sign-up (link expires in 24 hours)
- Password reset (link expires in 1 hour)

---

### 7. Run the application

Open your browser and go to:

```
http://localhost/BakeryOrderingSystem/PHP/index.php
```

Or simply:

```
http://localhost/BakeryOrderingSystem/
```

---

## Roles

| Role | Access |
|---|---|
| **Customer** | Browse products, add to cart, place orders, manage profile |
| **Admin** | Dashboard, manage products/orders/customers, view receipts |

---

## Key Pages

| URL | Description |
|---|---|
| `/PHP/index.php` | Public landing page |
| `/PHP/login.php` | Login (customer or admin) |
| `/PHP/signup.php` | Customer registration |
| `/PHP/home.php` | Customer home (requires login) |
| `/PHP/shop.php` | Product listing |
| `/PHP/cart.php` | Shopping cart & checkout |
| `/PHP/orders.php` | Customer order history |
| `/PHP/profile.php` | Customer profile management |
| `/PHP/admin-dashboard.php` | Admin dashboard |
| `/PHP/admin-products.php` | Product management |
| `/PHP/admin-orders.php` | Order management |
| `/PHP/admin-customers.php` | Customer management |
| `/PHP/admin-profile.php` | Admin profile |

---

## Project Structure

```
BakeryOrderingSystem/
├── CSS/                    # Stylesheets for each page
├── Image/                  # Static product and UI images
├── JS/                     # Client-side JavaScript
├── PHP/
│   ├── DB/                 # SQL setup scripts
│   ├── PHPMailer/          # PHPMailer library files
│   ├── db.php              # Database connection
│   ├── mailer.php          # Email sending functions
│   ├── index.php           # Public landing page
│   ├── login.php           # Login page
│   ├── signup.php          # Registration page
│   ├── home.php            # Customer home
│   ├── shop.php            # Product shop
│   ├── cart.php            # Cart & checkout
│   ├── orders.php          # Order history
│   ├── profile.php         # Customer profile
│   ├── admin-*.php         # Admin module pages
│   ├── forgot_password.php # Forgot password flow
│   └── reset_password.php  # Password reset flow
└── index.php               # Root redirect
```

---

## Notes

- Cart data is stored in **localStorage** — it persists across page reloads but is browser-specific
- Product images and proof-of-payment screenshots are stored as **base64 in the database** (MEDIUMTEXT) — no file upload directory is needed
- The `photo` column on `users` also stores profile pictures as base64
- Shipping fee is **₱5** for Dasmariñas customers and **₱10** for all other locations, determined by the city saved in the customer's profile
