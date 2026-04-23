# La Flamme Restaurant Reservation - Windows Deployment Guide (XAMPP)

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![MySQL](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)

---

<img width="1920" height="2220" alt="image" src="https://github.com/user-attachments/assets/5b4471b3-b37b-4c1a-ad12-3e5109210e47" />

---

<img width="1920" height="1936" alt="image" src="https://github.com/user-attachments/assets/cd964d72-e6f3-4546-8c47-8e30de4690be" />

---

<img width="1920" height="1215" alt="image" src="https://github.com/user-attachments/assets/7beec3ec-4f7c-45d1-870b-760563f4c23b" />

---

<img width="1920" height="1041" alt="image" src="https://github.com/user-attachments/assets/177120cb-01d6-465d-b338-566a932d5094" />

---

## 🚀 Key Features & Recent Upgrades

1. **Complete Admin Dashboard:** A fully isolated, secure administration panel located at `/admin`. Supports Staff and Super Admin roles, analytics, comprehensive reservation editing, user management, menu editing, and full audit logs.
2. **Unified Premium UI/UX:** Form controls, tables, and alert dialogs have been unified across the entire application using a premium dark-mode aesthetic with gold accents.
3. **Data Integrity & Security:** Hardened against SQL Injection via prepared statements. Foreign keys updated to handle user deletion without wiping reservation history (ON DELETE SET NULL).
4. **Historical Bookings:** Cancelling a reservation now safely marks its status as "cancelled" instead of destroying the record, preserving it for auditing and customer history.

## Deployment Guide (XAMPP, Windows)

### 1. Prerequisites

- [XAMPP](https://www.apachefriends.org/) installed (Apache + MySQL)
- PHP 7.4+ or 8.0+ recommended
- Git (optional, for cloning)

### 2. Clone or Copy Files

- Place all project files in your XAMPP htdocs directory:  
  `C:\xampp\htdocs\Restaurant-reservation-system`

### 3. Database Setup

- Start XAMPP Control Panel (run Apache & MySQL)
- Open [phpMyAdmin](http://localhost/phpmyadmin)
- **Step A:** Import `database/init_db.sql` (Initial customer tables)
- **Step B:** Import `database/admin_schema_update.sql` (Creates the admin roles, accounts, logic, and foreign key fixes)

*(Note: If the SQL files are in the main directory instead of `/database/`, import them from the root folder in the exact order above).*

### 4. Images

- Place all menu and gallery images in:  
  `C:\xampp\htdocs\Restaurant-reservation-system\images`
- Required images:
  - All menu item images (600x400px, see `menu.php`)
  - Hero images: `hero1.jpg`, `hero2.jpg`, `hero3.jpg`
  - Logo: `logo.png` (recommended size: 600x400px or larger)

### 5. Configure Database Connection

- Edit `db.php` if your MySQL password/user differs (Default assumes `root` with no password):
  ```php
  $mysqli = new mysqli('localhost','root','','laflamme');
  ```
- Also ensure `admin/includes/db.php` shares the same configuration.

### 6. Start the Application

- **Public Site:** Visit [http://localhost/Restaurant-reservation-system](http://localhost/Restaurant-reservation-system)
- **Admin Panel:** Visit [http://localhost/Restaurant-reservation-system/admin](http://localhost/Restaurant-reservation-system/admin)

### 7. Usage

**Customer Portal:**
- Sign up for an account / Log in
- Browse the dynamic menu
- Make reservations and view booking history (with live status tracking)
- Generate a reservation summary PDF/Print view with QR codes

**Admin Panel:**
- Login using the default superadmin credentials established in your `admin_schema_update.sql` file.
- View dashboard analytics and statistics.
- Confirm, complete, edit, or cancel reservations securely.
- Toggle active user statuses or promote/delete staff accounts.

### 8. Technical Notes

- No frameworks or package managers required (No Laravel, no Composer).
- Pure procedural PHP, MySQLi, Bootstrap 5, Vanilla JS.
- Fully compatible with XAMPP environments out-of-the-box.
- For production use, please implement HTTPS, set strong MySQL passwords, and rotate the default admin credentials.

---

**Troubleshooting:**
- **Database Errors:** Check that MySQL is running and DB credentials in `db.php` and `admin/includes/db.php` are identical and correct.
- **Images Missing:** Verify exact filenames and ensure the `images/` folder has proper read permissions.
- **Session Issues:** Ensure no whitespace or HTML exists before `<?php` tags in files, which can break `session_start()`.

---

Enjoy your fine dining reservation experience!