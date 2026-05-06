# 🏠 Smart Home Shop - E-Commerce Platform & Project Report

## 📖 Project Overview
Smart Home Shop is a fully custom, full-stack e-commerce web application built using PHP, standard SQL, and Vanilla HTML/CSS/JavaScript. 

This project was developed to demonstrate a deep understanding of frontend User Experience (UX), backend session management, secure database interactions, and dynamic CSS architecture. It features a complete customer-facing storefront and a secure, feature-rich Administrative Dashboard.

---

## ✨ Key Features

### 🛒 Customer Frontend
* **Dynamic Shopping Cart**: Utilizes PHP `$_SESSION` to track cart state (Product IDs and Quantities) without forcing immediate user registration.
* **Advanced Product Search & Pagination**: Users can filter products by Category and Search Query simultaneously. Pagination URLs are dynamically rebuilt using `http_build_query()` to ensure active filters are preserved when navigating between pages.
* **Fallback Image System**: If a product image is missing, the system automatically falls back to rendering a category-specific SVG icon, preventing broken UI layouts.
* **Live Form Validation**: Custom JavaScript provides a live character-count for text areas and strictly enforces minimum/maximum stock inputs before submission.
* **Real-time Password Strength Meter**: During registration, a JavaScript function scores the password using Regular Expressions (checking for symbols, casing, and length) and dynamically updates the width and color of a CSS progress bar.

### ⚙️ Administrative Backend
* **Secure Gatekeeping**: All admin routes are protected by a `requireAdmin()` check.
* **Single-File CRUD Architecture**: The Product Editor (`admin-product-edit.php`) ingeniously handles both Creating and Updating products in a single file by detecting the presence of an ID in the URL (`isset($_GET['id'])`) and toggling an `$isEdit` boolean.
* **Order Management**: Admins can quickly update order statuses (Pending, Processing, Shipped, Delivered) using inline HTML forms within the data tables.
* **User Role Toggling**: Admins can Promote or Demote users via a streamlined interface that uses ternary operators to dynamically assign button colors and labels. A safety check prevents the active admin from demoting themselves.
* **Activity Logging**: Critical actions (deleting products, changing roles, updating order statuses) are automatically recorded in the database and displayed in an Admin Log viewer.

---

## 🏗 Technical Architecture & Engineering Highlights

### 1. Security & Database Design
* **PDO Prepared Statements**: All database interactions use prepared statements (e.g., `execute([$val1, $val2])`) to protect against SQL Injection.
* **Strict Pagination Types**: SQL `LIMIT` and `OFFSET` commands are secured using `bindValue(':limit', $limit, PDO::PARAM_INT)` to force the database driver to treat inputs strictly as integers.
* **Connection Safety**: The database connection uses `require_once` rather than `include`. Because the database is mandatory for the application to function, `require` ensures a missing file results in a safe Fatal Error rather than cascading warnings.

### 2. Advanced PHP Logic
* **Null Coalescing Operator (`??`)**: Used extensively to provide safe fallback values. For example, `$order['username'] ?? 'Unknown'` prevents the application from crashing if a user account was deleted after placing an order.
* **Data Aggregation**: Complex SQL queries utilizing `LEFT JOIN`, `GROUP BY`, and `COALESCE(SUM(quantity), 0)` are used to accurately calculate best-selling products even if a product currently has zero sales.

### 3. Dynamic CSS Architecture
* **Auto-fill Grid**: Product cards utilize `grid-template-columns: repeat(auto-fill, minmax(260px, 1fr))`. This creates a perfectly responsive, wrapping layout on any device size without the need for complex media queries.
* **The "Sticky Footer" Pattern**: The global layout applies `min-height: 100vh` and `flex-direction: column` to the body, ensuring the footer is always pushed to the bottom of the viewport even on pages with minimal content.
* **CSS Variables**: Theme colors (e.g., `var(--accent)`) and border radii are defined in the `:root` pseudo-class for instantaneous global theme changes.
* **Object-Fit Cover**: Product images utilize `object-fit: cover` to act as a smart crop, ensuring uploaded images of varying dimensions perfectly fill uniform card boxes without distortion.

---

## 🚀 Installation & Setup (Local Development)

1. **Environment Requirement**: Install XAMPP, MAMP, or a similar AMP stack.
2. **Clone/Copy**: Place the project folder into your `htdocs` (or equivalent `www`) directory.
3. **Database Setup**:
   * Open phpMyAdmin.
   * Create a new database.
   * Import the provided `.sql` schema file to generate the tables (`users`, `products`, `categories`, `orders`, `order_items`, `logs`).
4. **Configuration**: Ensure the credentials in `includes/db-connection.php` match your local MySQL setup (default: root / no password).
5. **Run**: Navigate to `http://localhost/WebProject/` in your browser.

---

*Report generated for final academic submission.*
