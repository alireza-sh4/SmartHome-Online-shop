# SmartHome Shop

Welcome to **SmartHome Shop**, a fully functional, responsive e-commerce web application built for buying smart home devices like lighting, security cameras, and climate sensors. 

This project was built from scratch using core web technologies to demonstrate secure authentication, session-based shopping carts, database transactions, and dynamic filtering.

## Features

* **User Authentication:** Secure registration and login with `bcrypt` password hashing and real-time password strength validation.
* **Shopping Cart & Checkout:** A session-based cart that persists items until checkout. The checkout process uses database transactions to guarantee that order creation and stock reduction happen simultaneously and safely.
* **Dynamic Products:** Products can be filtered by category and searched via text. Pagination handles large datasets smoothly without losing your search parameters.
* **Admin Dashboard:** A dedicated backend for administrators to manage products, view customer orders, manage users, and review the activity log.
* **Responsive Design:** Built with pure CSS Flexbox and Grid. It looks great on desktop, tablet, and mobile!

## Tech Stack

* **Frontend:** HTML5, Vanilla JavaScript, Vanilla CSS
* **Backend:** PHP 8+ (Vanilla)
* **Database:** MySQL / MariaDB (using PDO for secure prepared statements)

## Installation & Setup

If you want to run this project locally, follow these steps:

1. **Prerequisites:**
   Make sure you have [XAMPP](https://www.apachefriends.org/index.html) (or MAMP/WAMP) installed on your machine.

2. **Clone the Repo:**
   Clone this repository into your XAMPP `htdocs` folder:
   ```bash
   cd /Applications/XAMPP/xamppfiles/htdocs/
   git clone <your-repo-url> WebProject
   ```

3. **Database Setup:**
   * Start Apache and MySQL from your XAMPP Control Panel.
   * Open `http://localhost/phpmyadmin`.
   * Create a new database called `smarthome_shop`.
   * Import the provided SQL schema file (if available) or simply navigate to `http://localhost/WebProject/install.php` to automatically generate the tables and seed data!

4. **Permissions (Mac Users):**
   If you are on a Mac using XAMPP, the web server needs permission to write to the `logs` and `images/products` folders. Open your terminal and run:
   ```bash
   chmod -R 777 /Applications/XAMPP/xamppfiles/htdocs/WebProject/logs/
   chmod -R 777 /Applications/XAMPP/xamppfiles/htdocs/WebProject/images/products/
   ```

5. **Start Shopping!**
   Navigate to `http://localhost/WebProject/` to view the shop!
