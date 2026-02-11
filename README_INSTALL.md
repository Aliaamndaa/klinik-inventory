# 🔐 Complete Authentication System - Installation Guide

## 📦 What's Included

This package contains everything you need to add authentication to your Klinik Dr. Azhar Inventory System.

### New Files (7 files):
1. **auth.php** - Login, register, logout API
2. **auth_middleware.php** - Session validation
3. **users.php** - User management (admin only)
4. **login.html** - Login/register page
5. **diagnostic.html** - Connection testing tool
6. **test-connection.php** - API testing endpoint
7. **auth-integration.html** - Script to add to index.html

### Updated Files (4 files):
8. **medicines.php** - Now requires authentication
9. **categories.php** - Now requires authentication
10. **transactions.php** - Now requires authentication
11. **dashboard.php** - Now requires authentication

### Documentation (3 files):
12. **FILE_STRUCTURE_GUIDE.md** - Setup instructions
13. **AUTHENTICATION_SETUP_GUIDE.md** - Detailed guide
14. **REGISTRATION_TROUBLESHOOTING.md** - Problem solving

---

## ⚡ QUICK INSTALL (5 Minutes)

### Step 1: Copy ALL Files

Copy **ALL 15 FILES** from this package to:
```
C:\xampp\htdocs\klinik-inventory\
```

**IMPORTANT:** Replace existing files when prompted:
- ✅ Replace medicines.php
- ✅ Replace categories.php
- ✅ Replace transactions.php
- ✅ Replace dashboard.php

### Step 2: Make Sure Database Exists

1. Start XAMPP (Apache + MySQL must be GREEN)
2. Open: http://localhost/phpmyadmin
3. Check if `klinik_azhar_db` database exists
4. If NOT, create it and import `schema.sql`

### Step 3: Test the Setup

Visit: http://localhost/klinik-inventory/diagnostic.html

**You should see:**
- ✅ Test 1: Can we reach the server? - GREEN
- ✅ Test 2: Is PHP working? - GREEN
- ✅ Test 3: Can PHP connect to database? - GREEN
- ✅ Test 4: Is auth.php accessible? - GREEN
- ✅ Test 5: Registration endpoint test - GREEN

**If any test is RED**, it will tell you exactly how to fix it!

### Step 4: Login & Register

1. Visit: http://localhost/klinik-inventory/login.html

2. **First Login** (default admin):
   - Username: `admin`
   - Password: `admin123`

3. **Register New User**:
   - Click "Register" tab
   - Fill in details
   - Select role: Staff or Admin
   - Click Register

### Step 5: Update index.html (Optional)

To add logout button and session protection to your main app:

1. Open `index.html`
2. Find `</body>` at the bottom
3. Copy everything from `auth-integration.html`
4. Paste it BEFORE `</body>`
5. Save

---

## 📁 Your Final File Structure

After installation, your folder should look like:

```
C:\xampp\htdocs\klinik-inventory\
├── index.html              (your existing file)
├── login.html              ⭐ NEW - login page
├── diagnostic.html         ⭐ NEW - testing tool
│
├── auth.php                ⭐ NEW - authentication API
├── auth_middleware.php     ⭐ NEW - session validator
├── users.php               ⭐ NEW - user management
├── test-connection.php     ⭐ NEW - testing endpoint
│
├── medicines.php           ✏️ UPDATED - now requires login
├── categories.php          ✏️ UPDATED - now requires login
├── transactions.php        ✏️ UPDATED - now requires login
├── dashboard.php           ✏️ UPDATED - now requires login
│
├── db.php                  (keep your existing file)
├── schema.sql              (keep your existing file)
├── README.md               (keep your existing file)
│
└── Documentation/
    ├── FILE_STRUCTURE_GUIDE.md
    ├── AUTHENTICATION_SETUP_GUIDE.md
    └── REGISTRATION_TROUBLESHOOTING.md
```

---

## 🎯 Key Features

### User Roles:

**Admin:**
- ✅ Full access to all features
- ✅ Can delete medicines/categories
- ✅ Can manage users
- ✅ Can promote/demote users

**Staff:**
- ✅ View inventory
- ✅ Add/edit medicines
- ✅ Record transactions
- ❌ Cannot delete items
- ❌ Cannot manage users

---

## 🔧 Troubleshooting

### Problem: "Connection Error" when trying to register

**Solution:**
1. Run diagnostic tool: http://localhost/klinik-inventory/diagnostic.html
2. Check which test fails
3. Follow the fix instructions shown in red

### Problem: Can't login with admin/admin123

**Solution:**
1. Go to: http://localhost/phpmyadmin
2. Select `klinik_azhar_db` database
3. Click `users` table
4. Check if admin user exists
5. If not, re-import `schema.sql`

### Problem: Registration says "Connection error"

**Check these:**
1. ✅ XAMPP Apache is running (GREEN)
2. ✅ XAMPP MySQL is running (GREEN)
3. ✅ Database `klinik_azhar_db` exists
4. ✅ All files are in `C:\xampp\htdocs\klinik-inventory\`
5. ✅ URL is `http://localhost/klinik-inventory/login.html`

### Problem: After login, redirects back to login

**Solution:**
Your index.html needs the auth script. Copy content from `auth-integration.html` and paste before `</body>` in index.html.

---

## 📞 Testing Checklist

After installation, test these URLs:

1. **Database Test:**
   http://localhost/klinik-inventory/test-connection.php
   - Should show: `{"status":"success","message":"Database connected successfully!"...}`

2. **Auth Check:**
   http://localhost/klinik-inventory/auth.php?action=check
   - Should show: `{"success":true,"authenticated":false}`

3. **Diagnostic Tool:**
   http://localhost/klinik-inventory/diagnostic.html
   - All 5 tests should be GREEN

4. **Login Page:**
   http://localhost/klinik-inventory/login.html
   - Should load without errors

5. **Default Login:**
   - Username: admin
   - Password: admin123
   - Should redirect to index.html

---

## 🔒 Security Notes

✅ Passwords are hashed with bcrypt  
✅ SQL injection protected (prepared statements)  
✅ Session-based authentication  
✅ Role-based access control  
✅ CSRF protection via session tokens  

**⚠️ IMPORTANT:** Change the default admin password after first login!

---

## 🆘 Still Having Issues?

1. **Check browser console** (F12 → Console tab)
2. **Run diagnostic tool**
3. **Check Apache error log** (XAMPP Control Panel → Logs)
4. **Verify file locations** (all files in same folder)
5. **Read troubleshooting guides** included in package

---

## 📚 Additional Resources

- **FILE_STRUCTURE_GUIDE.md** - Detailed file organization
- **AUTHENTICATION_SETUP_GUIDE.md** - Step-by-step setup
- **REGISTRATION_TROUBLESHOOTING.md** - Common problems & fixes

---

## ✅ Installation Complete Checklist

- [ ] All 15 files copied to klinik-inventory folder
- [ ] XAMPP Apache & MySQL running (GREEN)
- [ ] Database klinik_azhar_db exists
- [ ] Diagnostic tool shows all tests GREEN
- [ ] Can access login.html
- [ ] Can login with admin/admin123
- [ ] Can register new staff user
- [ ] Staff user cannot delete items
- [ ] Admin can access all features
- [ ] Logout button works

---

**🎉 Once all checks pass, you're done!**

Your inventory system now has secure authentication with role-based access control.

---

## 📊 Default Credentials

**Admin Account:**
- Username: `admin`
- Password: `admin123`
- Role: Administrator

**⚠️ Change this password immediately after first login!**

---

Need more help? Check the documentation files or run the diagnostic tool!
