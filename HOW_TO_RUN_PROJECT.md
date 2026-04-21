# How to Run the La Flamme Restaurant Reservation System

This guide explains how to set up and run the entire project, including the customer-facing website and the newly integrated Admin Panel.

## 1. Prerequisites

You will need a local web server environment installed on your computer.

* **Windows**: Download and install [XAMPP](https://www.apachefriends.org/index.html) or WAMP.
* **macOS**: Download and install MAMP or XAMPP for Mac.
* **Linux**: LAMP stack.

---

## 2. Setting Up the Project Files

1. Navigate to your local server's web server directory:
   * **XAMPP Windows**: `C:\xampp\htdocs\`
   * **WAMP**: `C:\wamp\www\`
   * **MAMP macOS**: `/Applications/MAMP/htdocs/`
2. Place the entire `Restaurant-reservation-system` folder inside this directory.
   *(Example Path: `C:\xampp\htdocs\Restaurant-reservation-system`)*

---

## 3. Starting the Server

1. Open the **XAMPP Control Panel** (or your respective server control panel).
2. Click **Start** for both **Apache** and **MySQL**.
3. Ensure both services have started successfully (they usually turn green in XAMPP or indicate \"Running\").

---

## 4. Database Setup

The system relies on a MySQL database to store users, menu items, reservations, and admin data.

### Step A: Initialize the Base Database
1. Open your web browser and go to **`http://localhost/phpmyadmin/`**.
2. Go to the **Import** tab located at the top.
3. Click "Choose File" and select `init_db.sql` located in your project folder.
4. Click **Import/Go** at the bottom. This creates the `laflamme` database and base tables with dummy data.

### Step B: Initialize the Admin Extensions
1. Stay in phpMyAdmin and ensure you have selected the `laflamme` database on the left sidebar.
2. Go to the **Import** tab again.
3. Click "Choose File" and select **`admin_schema_update.sql`** located in your project folder.
4. Click **Import/Go**. This adds the necessary tables and columns (Admins, statuses, admin logs) to support the new Admin Panel.

---

## 5. Running the Application

With the server running and the database fully configured, you can now access both parts of the system.

### A. The Customer Website
This is the main public-facing site where users can view the menu, sign up, and create reservations.

* **URL**: `http://localhost/Restaurant-reservation-system/`
* **Test Customer Account**: You can create a new account via the "Sign Up" page on the site to test making reservations.

### B. The Admin Panel
This is the secure backend used by staff and administrators to manage the restaurant.

* **URL**: `http://localhost/Restaurant-reservation-system/admin/`
* **Default Super Admin Login**:
  * **Username**: `superadmin`
  * **Password**: `Admin@1234`

*(From the Admin Panel, navigate to "Admin Accounts" to create more profiles or deactivate existing ones.)*

---

## Troubleshooting

* **Database Connection Error ("DB Error")**: Ensure your MySQL server in XAMPP is running. If you have a custom MySQL password setup in XAMPP, open `db.php` and `admin/includes/db.php` to update the connection credentials accordingly (default password is set to `1234` inside `db.php`).
* **404 Not Found**: Verify that the project folder is placed in the correct `htdocs` directory and that the URL exactly matches the folder name.
