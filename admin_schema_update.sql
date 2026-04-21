-- ============================================================
-- La Flamme Admin Panel — Schema Updates
-- Run AFTER init_db.sql
-- ============================================================
USE laflamme;

-- 1. Add status, admin_notes, updated_at to reservations
ALTER TABLE reservations
  ADD COLUMN status ENUM('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending' AFTER guest_count,
  ADD COLUMN admin_notes TEXT NULL AFTER status,
  ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER admin_notes;

-- 2. Add description + is_available to menu_items
ALTER TABLE menu_items
  ADD COLUMN description TEXT NULL AFTER image,
  ADD COLUMN is_available TINYINT(1) NOT NULL DEFAULT 1 AFTER description;

-- 3. Admin roles
CREATE TABLE IF NOT EXISTS admin_roles (
  id   TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL
);
INSERT INTO admin_roles (id, name) VALUES (1, 'Super Admin'), (2, 'Staff');

-- 4. Admins table
CREATE TABLE IF NOT EXISTS admins (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(60) UNIQUE NOT NULL,
  email         VARCHAR(120) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role_id       TINYINT UNSIGNED NOT NULL DEFAULT 2,
  is_active     TINYINT(1) NOT NULL DEFAULT 1,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  last_login    TIMESTAMP NULL,
  FOREIGN KEY (role_id) REFERENCES admin_roles(id)
);

-- Default super admin
-- IMPORTANT: Replace the hash below with: php -r "echo password_hash('Admin@1234', PASSWORD_BCRYPT);"
INSERT INTO admins (username, email, password_hash, role_id) VALUES (
  'superadmin',
  'admin@laflamme.com',
  '$2y$12$jXT.7vxltmKUW4AwM8Utm.JFoiK.QojJrW/9oWrs3G7ua52oZjOu.',
  1
);

-- 5. Admin activity log
CREATE TABLE IF NOT EXISTS admin_logs (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  admin_id   INT NOT NULL,
  action     VARCHAR(120) NOT NULL,
  target     VARCHAR(120) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
);
