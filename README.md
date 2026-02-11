<<<<<<< HEAD
# 🏥 Klinik Dr. Azhar — Inventory Management System

A production-ready **web-based medical inventory management system** built with PHP, MySQL, and vanilla HTML/CSS/JS. Developed as a final year project (FYP) to replace manual stock-tracking processes at a clinic with a fully digital solution.

> 🥈 **Silver Medal** — FYP Competition 2022, Politeknik Tuanku Syed Sirajuddin (PTSS)

---

## 📸 Features

| Feature | Description |
|---|---|
| 📊 **Dashboard** | Real-time summary of total stock, alerts, inventory value, and recent transactions |
| 💊 **Medicine Management** | Full CRUD — add, edit, delete medicines with category, supplier, unit, price, location |
| 🔄 **Stock Transactions** | Record stock in / stock out / manual adjustments with full history log |
| ⚠️ **Low Stock Alerts** | Auto-alert when stock drops to or below the configurable reorder level |
| 📅 **Expiry Tracking** | Flags medicines expiring within 90 days and highlights already-expired items |
| 🔍 **Search & Filter** | Filter by name, category, or status (low stock / expiring / expired) |
| 📦 **Category & Supplier Management** | Organize medicines with categories and link to suppliers |

---

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| Frontend | HTML5, CSS3, Vanilla JavaScript |
| Backend | PHP 8.x (REST API) |
| Database | MySQL 8.x |
| Architecture | MVC-inspired, Client-Server, RESTful API |
| Server | Apache (XAMPP / WAMP / Laragon) |

---

## 📁 Project Structure

```
klinik-inventory/
├── backend/
│   ├── config/
│   │   └── db.php              # Database connection & CORS headers
│   ├── api/
│   │   ├── medicines.php       # CRUD API for medicines
│   │   ├── transactions.php    # Stock in/out/adjustment API
│   │   ├── categories.php      # Categories & suppliers API
│   │   └── dashboard.php       # Summary stats & alerts API
│   └── database/
│       └── schema.sql          # Full database schema + seed data
└── frontend/
    └── index.html              # Single-page application UI
```

---

## ⚙️ Setup & Installation

### Prerequisites
- XAMPP / WAMP / Laragon (PHP 8.x + MySQL)
- A browser (Chrome / Firefox)

### Steps

**1. Clone the repository**
```bash
git clone https://github.com/Aliaamndaa/klinik-inventory.git
cd klinik-inventory
```

**2. Move to your server's web root**
```bash
# For XAMPP on Windows:
xcopy /E . C:\xampp\htdocs\klinik-inventory\

# For XAMPP on Mac/Linux:
cp -r . /Applications/XAMPP/htdocs/klinik-inventory/
```

**3. Import the database**
- Start Apache & MySQL from XAMPP Control Panel
- Open [phpMyAdmin](http://localhost/phpmyadmin)
- Create a new database: `klinik_azhar_db`
- Import `backend/database/schema.sql`

**4. Configure the database connection**

Edit `backend/config/db.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // Your MySQL username
define('DB_PASS', '');            // Your MySQL password
define('DB_NAME', 'klinik_azhar_db');
```

**5. Update the API URL in the frontend**

Edit `frontend/index.html`, line ~580:
```javascript
const API = 'http://localhost/klinik-inventory/backend/api';
```

**6. Open the app**

Visit: `http://localhost/klinik-inventory/frontend/index.html`

---

## 🗄️ Database Schema

```
categories         — Medicine categories (Antibiotics, Analgesics, etc.)
suppliers          — Supplier details (name, contact, phone, email)
medicines          — Main inventory table (stock, expiry, reorder level, price)
stock_transactions — History of all stock in/out/adjustment records
users              — Admin login (role-based: admin / staff)
```

### Key Design Decisions
- **Normalized relational schema** — foreign keys between medicines → categories → suppliers
- **Reorder level per item** — configurable threshold triggers alert when stock ≤ reorder level
- **Transaction log** — every stock change is recorded; stock quantity updated atomically
- **Expiry tracking** — expiry_date column with query-time classification (expired / expiring_soon / ok)

---

## 🔌 REST API Endpoints

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/medicines.php` | Get all medicines (supports `?search=`, `?category=`, `?status=`) |
| `GET` | `/api/medicines.php?id={id}` | Get single medicine |
| `POST` | `/api/medicines.php` | Add new medicine |
| `PUT` | `/api/medicines.php?id={id}` | Update medicine |
| `DELETE` | `/api/medicines.php?id={id}` | Delete medicine |
| `GET` | `/api/transactions.php` | Get all transactions |
| `GET` | `/api/transactions.php?id={medicine_id}` | Get transactions for one medicine |
| `POST` | `/api/transactions.php` | Record stock in/out/adjustment |
| `GET` | `/api/dashboard.php` | Get dashboard stats, alerts, recent activity |
| `GET` | `/api/categories.php` | Get categories |
| `GET` | `/api/categories.php?type=suppliers` | Get suppliers |
| `POST` | `/api/categories.php` | Add category or supplier |

---

## 💡 Future Improvements
- [ ] User authentication & session management
- [ ] PDF/CSV report export
- [ ] Barcode scanner integration
- [ ] Email/SMS notifications for reorder alerts
- [ ] Multi-clinic / multi-branch support

---

## 👩‍💻 Author

**Nuralia Amanda Binti Mohamad Akhsan**  
Computer Science (Software Development) — UTeM  
[GitHub](https://github.com/Aliaamndaa) · [LinkedIn](https://linkedin.com/in/nuralia-amanda)
=======
# klinik-inventory
>>>>>>> b8100e058d9061305303b54e7c01df69c7279a4c
