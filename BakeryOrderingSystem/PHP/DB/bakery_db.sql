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

-- Run this once to add forgot-password columns if not already present:
-- ALTER TABLE users ADD COLUMN reset_token VARCHAR(64) DEFAULT NULL;
-- ALTER TABLE users ADD COLUMN reset_expiry DATETIME DEFAULT NULL;

-- NOTE: Do not insert admin via SQL with a hardcoded hash.
-- Instead, run create_admin.php once to generate a valid bcrypt hash.
-- Then delete create_admin.php.
