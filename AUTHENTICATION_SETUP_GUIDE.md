# 🔐 Authentication System Setup Guide

## Overview
This authentication system adds login, registration, and role-based access control (Admin/Staff) to your Klinik Dr. Azhar Inventory Management System.

---

## 📁 Files Structure

```
klinik-inventory/
├── backend/
│   ├── config/
│   │   ├── db.php                    # [EXISTING] Database connection
│   │   └── auth_middleware.php       # [NEW] Authentication middleware
│   └── api/
│       ├── auth.php                  # [NEW] Login/Register/Logout API
│       ├── users.php                 # [NEW] User management (admin only)
│       ├── medicines.php             # [UPDATED] Now requires auth
│       ├── categories.php            # [UPDATED] Now requires auth
│       ├── transactions.php          # [UPDATED] Now requires auth
│       └── dashboard.php             # [UPDATED] Now requires auth
└── frontend/
    ├── login.html                    # [NEW] Login/Register page
    └── index.html                    # [UPDATED] Main app with auth check
```

---

## 🚀 Installation Steps

### Step 1: Copy New Files

Copy these NEW files to your project:

1. **`backend/config/auth_middleware.php`**
   - Place: `/backend/config/auth_middleware.php`
   
2. **`backend/api/auth.php`**
   - Place: `/backend/api/auth.php`
   
3. **`backend/api/users.php`**
   - Place: `/backend/api/users.php`
   
4. **`frontend/login.html`**
   - Place: `/frontend/login.html`

### Step 2: Update Existing API Files

Replace the first few lines of each API file with the protected versions:

**medicines.php** - Add after `require_once '../config/db.php';`:
```php
require_once '../config/auth_middleware.php';

// All endpoints require authentication
requireAuth();

// DELETE requires admin role
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    requireAdmin();
}
```

**categories.php** - Add after `require_once '../config/db.php';`:
```php
require_once '../config/auth_middleware.php';

// Require authentication for all actions
requireAuth();

// DELETE requires admin role
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    requireAdmin();
}
```

**transactions.php** - Add after `require_once '../config/db.php';`:
```php
require_once '../config/auth_middleware.php';

// All transaction endpoints require authentication
requireAuth();
```

**dashboard.php** - Add after `require_once '../config/db.php';`:
```php
require_once '../config/auth_middleware.php';

// Dashboard requires authentication
requireAuth();
```

### Step 3: Update index.html

Add the authentication script right before `</body>` in `index.html`:

```html
<!-- Copy entire content from auth-integration.html -->
<script>
// ============================================================
// AUTHENTICATION MODULE
// ... (copy the entire Auth object and initialization code)
// ============================================================
</script>
</body>
</html>
```

### Step 4: Update API URL in Both Files

Make sure the API URL matches your setup in both files:

**login.html** (around line 230):
```javascript
const API = 'http://localhost/klinik-inventory/backend/api';
```

**index.html** (around line 580):
```javascript
const API = 'http://localhost/klinik-inventory/backend/api';
```

---

## 🔑 Default Credentials

The database already includes a default admin user:

- **Username:** `admin`
- **Password:** `admin123`
- **Role:** Admin

**⚠️ IMPORTANT:** Change this password immediately after first login!

---

## 👥 User Roles & Permissions

### Admin Role
✅ View all inventory  
✅ Add/Edit medicines  
✅ **Delete medicines** (staff cannot)  
✅ Record transactions  
✅ Manage categories/suppliers  
✅ **Manage users** (staff cannot)  
✅ Access all features  

### Staff Role
✅ View all inventory  
✅ Add/Edit medicines  
❌ **Cannot delete** medicines  
✅ Record transactions  
✅ View categories/suppliers  
❌ **Cannot manage users**  
❌ No access to admin-only features  

---

## 🧪 Testing the Authentication

### 1. Test Login Page
- Visit: `http://localhost/klinik-inventory/frontend/login.html`
- Try logging in with admin credentials
- Should redirect to dashboard

### 2. Test Session Protection
- Try accessing `index.html` without logging in
- Should automatically redirect to `login.html`

### 3. Test Registration
- Click "Register" tab
- Create a new staff account
- Try accessing admin features (should be blocked)

### 4. Test Logout
- Click logout button in sidebar footer
- Should redirect to login page
- Try accessing `index.html` again (should redirect to login)

### 5. Test Role-Based Access
- Login as **admin** → can delete medicines
- Login as **staff** → delete buttons hidden

---

## 🔧 Customization

### Change Password Requirements
In `auth.php`, line ~80:
```php
if (strlen($body['password']) < 6) {  // Change minimum length
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit();
}
```

### Add More User Roles
In `schema.sql`:
```sql
role ENUM('admin','staff','viewer') DEFAULT 'staff'  -- Add 'viewer' role
```

### Customize Login Page Colors
In `login.html`, change CSS variables:
```css
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
/* Change to your clinic's brand colors */
```

---

## 🛡️ Security Features

✅ **Password Hashing** - Bcrypt with salt  
✅ **Session Management** - Server-side PHP sessions  
✅ **Role-Based Access Control** - Admin/Staff separation  
✅ **CSRF Protection** - SameSite cookie policy  
✅ **SQL Injection Prevention** - Prepared statements  
✅ **XSS Prevention** - JSON encoding  
✅ **401/403 Error Handling** - Auto-redirect on auth failure  

---

## 🐛 Troubleshooting

### Problem: "Unauthorized" error when accessing API
**Solution:** Check that:
1. `auth_middleware.php` is in `/backend/config/`
2. API files include `require_once '../config/auth_middleware.php';`
3. Cookies are enabled in browser

### Problem: Can't login - "Invalid username or password"
**Solution:** 
1. Check database has user: `SELECT * FROM users WHERE username='admin'`
2. If missing, re-import `schema.sql`
3. Try creating new user via Register

### Problem: Session lost after refresh
**Solution:** Make sure all fetch calls include:
```javascript
credentials: 'include'
```

### Problem: CORS errors
**Solution:** In `db.php`, verify these headers:
```php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
```

---

## 📊 Database Schema (Already Included)

The `users` table is already in `schema.sql`:

```sql
CREATE TABLE IF NOT EXISTS users (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    username     VARCHAR(50) NOT NULL UNIQUE,
    password     VARCHAR(255) NOT NULL,
    full_name    VARCHAR(100),
    role         ENUM('admin','staff') DEFAULT 'staff',
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## 🎯 Next Steps (Optional Enhancements)

1. **Password Reset** - Add forgot password functionality
2. **Email Verification** - Verify user emails on registration
3. **Activity Logs** - Track who did what and when
4. **2FA Authentication** - Add two-factor authentication
5. **Session Timeout** - Auto-logout after inactivity
6. **User Profiles** - Allow users to update their info

---

## 📞 Support

If you encounter issues:
1. Check browser console for JavaScript errors
2. Check PHP error logs in XAMPP
3. Verify all files are in correct locations
4. Ensure MySQL service is running
5. Test API endpoints directly with tools like Postman

---

## ✅ Final Checklist

- [ ] All new files copied to correct locations
- [ ] API files updated with auth middleware
- [ ] index.html updated with auth script
- [ ] API URLs updated in both files
- [ ] Can access login.html
- [ ] Can login with admin/admin123
- [ ] Auto-redirects to login when not authenticated
- [ ] Logout button works
- [ ] Staff role cannot delete items
- [ ] Admin can access all features

---

**🎉 Congratulations!** Your inventory system now has secure authentication!
