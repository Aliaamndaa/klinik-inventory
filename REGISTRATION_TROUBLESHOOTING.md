# 🔧 Registration Troubleshooting Guide

## Problem: "Connection Error" when registering user

### Quick Checks (Do these first!)

1. **Is XAMPP running?**
   - Open XAMPP Control Panel
   - Make sure Apache is GREEN (running)
   - Make sure MySQL is GREEN (running)

2. **Does the database exist?**
   - Open http://localhost/phpmyadmin
   - Check if `klinik_azhar_db` database exists
   - If not, create it and import `schema.sql`

3. **Is the API path correct?**
   - Open browser console (F12)
   - Check the URL being called
   - Should be: `http://localhost/klinik-inventory/backend/api/auth.php?action=register`

---

## Step-by-Step Debugging

### Step 1: Check Browser Console

1. Open login.html in browser
2. Press F12 to open Developer Tools
3. Go to "Console" tab
4. Try to register
5. Look for error messages

**Common errors:**
- `Failed to fetch` → XAMPP not running or wrong URL
- `404 Not Found` → File path is wrong
- `500 Internal Server Error` → PHP error (check Step 2)

### Step 2: Check PHP Error Log

1. Open XAMPP Control Panel
2. Click "Logs" button next to Apache
3. Look for errors related to auth.php
4. Common issues:
   - Database connection failed
   - Session errors
   - Syntax errors

### Step 3: Test API Directly

Open this URL in browser:
```
http://localhost/klinik-inventory/backend/api/auth.php?action=check
```

**Expected response:**
```json
{"success":true,"authenticated":false}
```

**If you get an error:**
- Check file location
- Check database connection settings
- Check PHP syntax

### Step 4: Test Registration with Postman/Thunder Client

If you have Postman or VS Code Thunder Client:

**Request:**
- Method: POST
- URL: `http://localhost/klinik-inventory/backend/api/auth.php?action=register`
- Headers: `Content-Type: application/json`
- Body (raw JSON):
```json
{
  "username": "testuser",
  "password": "test123",
  "full_name": "Test User",
  "role": "staff"
}
```

**Expected response:**
```json
{
  "success": true,
  "message": "Registration successful! You can now login.",
  "user_id": 2
}
```

---

## Common Issues & Solutions

### Issue 1: "Cannot modify header information"

**Cause:** Headers sent before session_start() or output before headers

**Solution:** Use the new `auth_fixed.php` file which starts session first

### Issue 2: "Database connection failed"

**Cause:** Wrong database credentials or MySQL not running

**Solution:**
1. Check XAMPP MySQL is running
2. Verify credentials in auth.php:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Usually empty for XAMPP
define('DB_NAME', 'klinik_azhar_db');
```

### Issue 3: "Table 'users' doesn't exist"

**Cause:** Database not set up properly

**Solution:**
1. Go to phpMyAdmin: http://localhost/phpmyadmin
2. Select `klinik_azhar_db` database
3. Import the `schema.sql` file
4. Verify `users` table exists

### Issue 4: CORS Error

**Cause:** Cross-origin request blocked

**Solution:** Make sure auth.php has these headers at the top:
```php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');
```

### Issue 5: Empty response / no error

**Cause:** PHP fatal error not being displayed

**Solution:**
1. Enable error display in auth.php:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```
2. Refresh and try again
3. Check what error appears

---

## File Locations Checklist

Make sure files are in the correct locations:

```
C:\xampp\htdocs\klinik-inventory\
├── backend\
│   ├── config\
│   │   └── auth_middleware.php       ← Must be here
│   └── api\
│       └── auth.php                  ← Must be here (use auth_fixed.php)
└── frontend\
    └── login.html                    ← Must be here
```

---

## Test Registration Step-by-Step

### Test 1: Default Admin Login

1. Go to: `http://localhost/klinik-inventory/frontend/login.html`
2. Username: `admin`
3. Password: `admin123`
4. Click Login

**Expected:** Should login successfully and redirect to index.html

**If this works**, database is set up correctly!

### Test 2: Register Staff User

1. Click "Register" tab
2. Full Name: `Test Staff`
3. Username: `staff1`
4. Password: `staff123`
5. Role: `Staff (Default)`
6. Click Register

**Expected:** Success message, then switch to login tab

### Test 3: Login with New User

1. Username: `staff1`
2. Password: `staff123`
3. Click Login

**Expected:** Should login successfully

---

## Still Not Working?

### Option 1: Use the Fixed Auth File

Replace your `auth.php` with `auth_fixed.php`:

1. Rename current auth.php to auth_old.php (backup)
2. Copy auth_fixed.php to backend/api/
3. Rename auth_fixed.php to auth.php
4. Try registering again

### Option 2: Manual Database Insert

If all else fails, add a user directly in database:

1. Go to phpMyAdmin
2. Select `klinik_azhar_db` database
3. Click `users` table
4. Click "Insert" tab
5. Fill in:
   - username: `staff1`
   - password: `$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi` (this is 'admin123')
   - full_name: `Staff User`
   - role: `staff`
6. Click "Go"

Now you can login with username: `staff1`, password: `admin123`

### Option 3: Fresh Database Reset

1. Drop the `klinik_azhar_db` database
2. Create new `klinik_azhar_db` database
3. Import `schema.sql` again
4. Try registration

---

## Get More Help

If still stuck, please provide:

1. **Browser console errors** (F12 → Console tab)
2. **Apache error log** (from XAMPP)
3. **Response from** `http://localhost/klinik-inventory/backend/api/auth.php?action=check`
4. **Screenshot** of the error message

---

## Quick Reference: API Endpoints

Test these URLs in your browser:

1. **Check auth status:**
   `http://localhost/klinik-inventory/backend/api/auth.php?action=check`

2. **Check if file exists:**
   `http://localhost/klinik-inventory/backend/api/auth.php`

3. **Check database connection:**
   Create test.php in backend/api/ with:
   ```php
   <?php
   $conn = new mysqli('localhost', 'root', '', 'klinik_azhar_db');
   if ($conn->connect_error) {
       die("Connection failed: " . $conn->connect_error);
   }
   echo "Database connected successfully!";
   ?>
   ```
   Then visit: `http://localhost/klinik-inventory/backend/api/test.php`
