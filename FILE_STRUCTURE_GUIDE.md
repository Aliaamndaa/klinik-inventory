# 📁 File Structure Setup - CORRECT VERSION

Based on your URL `http://localhost/klinik-inventory/diagnostic.html`, here's the CORRECT file structure:

## Your Current Structure:
```
C:\xampp\htdocs\klinik-inventory\
├── index.html              ← Your main file
├── login.html              ← Login page  
├── diagnostic.html         ← Diagnostic tool
├── categories.php          ← API files (directly in root)
├── dashboard.php
├── db.php
├── medicines.php
├── README.md
├── schema.sql
└── transactions.php
```

## Where to Put the NEW FILES:

### Option 1: Keep Current Structure (Recommended)
Put all new files directly in `klinik-inventory` folder:

```
C:\xampp\htdocs\klinik-inventory\
├── index.html
├── login.html              ← NEW FILE HERE
├── diagnostic.html         ← NEW FILE HERE
├── test-connection.php     ← NEW FILE HERE
├── auth.php                ← NEW FILE HERE
├── auth_middleware.php     ← NEW FILE HERE
├── users.php               ← NEW FILE HERE
├── categories.php          ← UPDATE THIS
├── dashboard.php           ← UPDATE THIS
├── db.php                  ← KEEP AS IS
├── medicines.php           ← UPDATE THIS
├── transactions.php        ← UPDATE THIS
├── schema.sql
└── README.md
```

### Option 2: Organize into Folders (Better Organization)
Create folders and organize:

```
C:\xampp\htdocs\klinik-inventory\
├── config\
│   ├── db.php              ← MOVE HERE
│   └── auth_middleware.php ← NEW FILE
├── api\
│   ├── auth.php            ← NEW FILE
│   ├── categories.php      ← MOVE HERE
│   ├── dashboard.php       ← MOVE HERE
│   ├── medicines.php       ← MOVE HERE
│   ├── transactions.php    ← MOVE HERE
│   ├── users.php           ← NEW FILE
│   └── test-connection.php ← NEW FILE
├── login.html              ← NEW FILE
├── index.html              ← EXISTING
├── diagnostic.html         ← NEW FILE
├── schema.sql
└── README.md
```

## 🎯 QUICK FIX - Use Option 1 (Easiest):

### Step 1: Put files in root folder
Copy these files to `C:\xampp\htdocs\klinik-inventory\`:
- ✅ auth.php
- ✅ auth_middleware.php
- ✅ users.php
- ✅ login.html
- ✅ diagnostic.html
- ✅ test-connection.php

### Step 2: Update API path in login.html
Find this line (around line 228):
```javascript
const API = 'http://localhost/klinik-inventory/api';
```

**Change it to:**
```javascript
const API = 'http://localhost/klinik-inventory';
```

### Step 3: Update your existing PHP files

**In categories.php, medicines.php, dashboard.php, transactions.php:**

Add these lines at the TOP (after `<?php`):
```php
<?php
require_once 'auth_middleware.php';  // Note: no '../config/' path
requireAuth();

// For files that allow DELETE, add:
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    requireAdmin();
}
```

**Example - medicines.php should start like this:**
```php
<?php
require_once 'db.php';
require_once 'auth_middleware.php';

requireAuth();

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    requireAdmin();
}

$conn   = getConnection();
$method = $_SERVER['REQUEST_METHOD'];
// ... rest of your code
```

### Step 4: Update auth_middleware.php

Since db.php is in the same folder, update auth_middleware.php:

Find any `require_once '../config/db.php';` and change to:
```php
require_once 'db.php';
```

Or just remove it if the file that includes auth_middleware already includes db.php

### Step 5: Test!

1. Visit: `http://localhost/klinik-inventory/diagnostic.html`
2. All tests should pass ✅
3. Then visit: `http://localhost/klinik-inventory/login.html`
4. Try registering!

---

## 🔧 If You Want to Use Folders (Option 2):

If you prefer the organized folder structure:

### Create folders:
```bash
mkdir C:\xampp\htdocs\klinik-inventory\config
mkdir C:\xampp\htdocs\klinik-inventory\api
```

### Move/Copy files:
1. Move `db.php` → `config\db.php`
2. Put `auth_middleware.php` → `config\auth_middleware.php`
3. Move all API files → `api\` folder
4. Keep HTML files in root

### Update ALL `require_once` paths:
```php
require_once '../config/db.php';
require_once '../config/auth_middleware.php';
```

### Update API path in login.html:
```javascript
const API = 'http://localhost/klinik-inventory/api';
```

---

## ⚠️ IMPORTANT: Session Path Issue

Since you're using files in the same folder, make sure db.php does NOT start a session. Only auth.php should start sessions.

**In db.php**, remove any `session_start()` if it exists.

---

## 🎯 Recommended: Use Option 1 First

Get it working with everything in one folder, THEN reorganize into folders later. It's easier to debug!

---

## Quick Test Checklist:

After setup, these URLs should work:

- ✅ `http://localhost/klinik-inventory/test-connection.php` → Should show JSON
- ✅ `http://localhost/klinik-inventory/auth.php?action=check` → Should show JSON
- ✅ `http://localhost/klinik-inventory/diagnostic.html` → All tests green
- ✅ `http://localhost/klinik-inventory/login.html` → Can register/login

---

Need help with any step? Just ask!
