# La Flamme Restaurant Reservation System

## Deployment Guide (XAMPP, Windows)

### 1. Prerequisites

- [XAMPP](https://www.apachefriends.org/) installed (Apache + MySQL)
- PHP 8.0+ recommended
- Git (optional, for cloning)

### 2. Clone or Copy Files

- Place all project files in:  
  `C:\xampp\htdocs\Laflamme`

### 3. Database Setup

- Start XAMPP Control Panel (run Apache & MySQL)
- Open [phpMyAdmin](http://localhost/phpmyadmin)
- Import `init_db.sql`:
  - Click "Import"
  - Choose `init_db.sql` from the project folder
  - Execute

### 4. Images

- Place all menu and gallery images in:  
  `C:\xampp\htdocs\Laflamme\images`
- Required images:
  - All menu item images (600x400px, see menu.php)
  - Hero images: `hero1.jpg`, `hero2.jpg`, `hero3.jpg`
  - Logo: `logo.png` (recommended size: 600x400px or larger)
  - Gallery images: `gallery1.jpg` ... `gallery6.jpg`

### 5. Configure Database Connection

- Edit `db.php` if your MySQL password/user differs:
  ```php
  $mysqli = new mysqli('localhost','root','','laflamme');
  ```

### 6. Start the Application

- Visit [http://localhost/Laflamme](http://localhost/Laflamme) in your browser

### 7. Usage

- Sign up for an account
- Log in
- Browse menu, make reservations, view booking history
- Use reservation summary for print/PDF (with QR code)
- Admin features not included (user-only system)

### 8. Notes

- No frameworks or package managers required
- Pure PHP, MySQL, Bootstrap, JS
- For best results, use Chrome or Edge
- For production, secure `db.php` and set strong MySQL password

---

**Troubleshooting:**
- If you see database errors, check that MySQL is running and DB credentials are correct.
- If images do not appear, verify filenames and image folder.
- For PHP errors, check file permissions and PHP version.

---

Enjoy your fine dining reservation experience!