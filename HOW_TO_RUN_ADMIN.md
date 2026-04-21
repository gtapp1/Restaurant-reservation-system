# How to Run the La Flamme Admin Panel

This guide explains how to set up and start using the newly integrated Admin Panel for the existing La Flamme Restaurant Reservation System.

## Prerequisites

1. **XAMPP/WAMP (Windows)**: Ensure you have Apache and MySQL services running via your XAMPP Control Panel.
2. **Project Folder**: Verify that the `Restaurant-reservation-system` folder is inside your local server's web root (e.g., `C:\xampp\htdocs\Restaurant-reservation-system`).

---

## Step 1: Database Setup

The original system relies on your `laflamme` database. To upgrade it for the new admin panel:

1. Open phpMyAdmin (`http://localhost/phpmyadmin/`).
2. Ensure you have already executed the original `init_db.sql` to create the base tables (users, reservations, menu_items, etc.). If not, import and run it first.
3. Import and execute the newly generated file **`admin_schema_update.sql`**.
   * *This will add new required tables (`admins`, `admin_roles`, `admin_logs`) and modify existing ones to support admin statuses.*

---

## Step 2: Access the Admin Panel

With the database updated, the application is ready.

1. Open your web browser.
2. Navigate to the admin path:  
   **URL**: `http://localhost/Restaurant-reservation-system/admin/`
3. You will be redirected to the secure login screen natively built for the admin system.

---

## Step 3: Default Login Credentials

A default Super Admin account has been created for you during the database setup.

* **Username**: `superadmin`
* **Password**: `Admin@1234`

> **Note:** Once logged in as a Super Admin, you can navigate to the **Admin Accounts** section to create additional staff accounts or deactivate users.

---

## Main Features Overview

Once inside, you will have access to:

* **Dashboard**: Key metrics like confirmed revenue, today's reservations, and a quick list of recent bookings.
* **Reservations Management**: Approve/cancel bookings, view order details per guest, and track table preferences.
* **User Management**: View user profiles, their total spent amount, and active bookings.
* **Menu Items Management**: Use full CRUD operations to add new steak or seafood dishes, upload images, and toggle availability.
* **Reports**: Check detailed range-based analytics, including the most popular menu items and table preferences.
