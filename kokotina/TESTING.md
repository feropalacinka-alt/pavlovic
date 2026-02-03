# ✅ TESTING & VERIFICATION GUIDE

## Pre-Test Checklist

- [ ] `complete.sql` has been imported to MySQL
- [ ] `uploads/` folder has 16 image files
- [ ] `admin-login.php` file exists
- [ ] `inc/db.php` points to correct DB credentials
- [ ] XAMPP/Apache is running
- [ ] MySQL is running

---

## Test Suite

### Test 1: Database Structure ✓
**Objective:** Verify all tables exist with correct structure

```bash
# In phpMyAdmin or MySQL CLI:
USE auto_demo;
SHOW TABLES;
```

**Expected Output:**
```
admin_users
car_images
cars
order_items
orders
users
```

**Verify users table:**
```sql
DESCRIBE users;
```

Expected columns:
- `id` (INT, PK, AUTO_INCREMENT)
- `email` (VARCHAR 255, UNIQUE, NOT NULL)
- `password` (VARCHAR 255, NOT NULL)
- `first_name` (VARCHAR 100)
- `last_name` (VARCHAR 100)
- `phone` (VARCHAR 30)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

✅ **PASS** if all columns exist and types match

---

### Test 2: User Registration Flow
**Objective:** Register a new user successfully

**Steps:**
1. Open: http://localhost/projekttt/login.php
2. Click "Registrácia" tab (right panel)
3. Fill form:
   ```
   Meno: John
   Priezvisko: Doe
   Email: john.doe@test.com
   Heslo: Test123456
   Potvrdenie: Test123456
   ```
4. Click "Zaregistrovať sa"

**Expected Result:**
- Message: "Registrácia úspešná! Môžete sa teraz prihlásiť."
- User can see login form
- Email appears in `users` table in phpMyAdmin

**Test Error Cases:**
```
❌ Empty email → "Email je povinný"
❌ Invalid email (no @) → "Neplatný email"
❌ Short password (< 6 chars) → "Heslo musí mať aspoň 6 znakov"
❌ Passwords don't match → "Heslá sa nezhodujú"
❌ Duplicate email → "Email je už zaregistrovaný"
```

✅ **PASS** if registration works and password is hashed in DB

---

### Test 3: User Login Flow
**Objective:** Login with registered credentials

**Steps:**
1. Open: http://localhost/projekttt/login.php
2. Click "Prihlásenie" tab (left panel)
3. Enter:
   ```
   Email: john.doe@test.com
   Heslo: Test123456
   ```
4. Click "Prihlásiť sa"

**Expected Result:**
- Redirects to home (index.php)
- Flash message: "Vitajte! Ste prihlásený/á."
- Header shows: "👤 John" and "Odhlásenie"
- User profile accessible at /profile.php

**Test Error Cases:**
```
❌ Non-existent email → "Používateľ s týmto emailom neexistuje"
❌ Wrong password → "Nesprávne heslo"
❌ Empty fields → "Vyplňte email a heslo"
```

✅ **PASS** if login works and session is created

---

### Test 4: Admin Login
**Objective:** Admin can login with test credentials

**Steps:**
1. Open: http://localhost/projekttt/admin-login.php
2. Enter:
   ```
   Meno: admin
   Heslo: admin123
   ```
3. Click "Prihlásiť sa ako Admin"

**Expected Result:**
- Redirects to /admin.php
- Admin panel loads with car list
- "Odhlásiť sa" button visible

**Test Rate Limiting:**
1. Enter wrong password 5 times
2. 6th attempt → "⚠️ Príliš veľa pokusov. Skúste neskôr."
3. Wait 15 minutes (or clear session) → Should reset

✅ **PASS** if admin login works and rate limiting activates

---

### Test 5: Image Display
**Objective:** All car images load correctly

**A) Home Page Images**
1. Open: http://localhost/projekttt/index.php
2. Scroll down to car list
3. Should see 5 cars with images:
   - Volkswagen Golf
   - BMW 3 Series
   - Mercedes-Benz C-Class
   - Toyota Corolla
   - Audi A4

**Check:** Each card has an image (not broken image icon)

**B) Detail Page Gallery**
1. Click on any car (e.g., Golf)
2. Should see:
   - Large main image
   - Thumbnails below
   - Click thumbnail → changes main image

**Expected Images for Golf:**
- golf.jpg (main)
- golf-interior.jpg (thumbnail)
- golf-engine.jpg (thumbnail)

✅ **PASS** if all images display and click to swap works

---

### Test 6: Session & Logout
**Objective:** Sessions work correctly

**After Login as User:**
1. Refresh page → Still logged in
2. Open new tab → Still logged in
3. Click "Odhlásenie" → Logs out
4. Try to access /profile.php → Redirects to login

**After Login as Admin:**
1. Click admin logout → Logs out
2. Try to access /admin.php → Redirects to admin-login.php

✅ **PASS** if session persists and logout clears it

---

### Test 7: Cart Functionality
**Objective:** Add items to cart as unregistered user

1. Open: http://localhost/projekttt/index.php
2. Click "Do košíka" on any car
3. Message: "Auto bolo pridané do košíka!"
4. Cart count increases in header
5. Open: http://localhost/projekttt/cart.php
6. Car should appear in cart with price

✅ **PASS** if cart works without login

---

## SQL Verification Commands

```sql
-- Check users table is empty (ready for registrations)
SELECT COUNT(*) FROM users;
-- Expected: 0

-- Check cars exist
SELECT COUNT(*) FROM cars;
-- Expected: 5

-- Check images exist
SELECT COUNT(*) FROM car_images;
-- Expected: 15

-- Check admin exists
SELECT * FROM admin_users WHERE username='admin';
-- Expected: 1 row with bcrypt hash

-- Check email is UNIQUE
INSERT INTO users (email, password) VALUES ('test@test.com', SHA2('pass', 256));
INSERT INTO users (email, password) VALUES ('test@test.com', SHA2('pass', 256));
-- Expected: Error on 2nd insert (duplicate key)
```

---

## Troubleshooting During Tests

### "Obrázky sa neukazujú"
```bash
# Check files exist:
ls -la uploads/*.jpg

# Check permissions:
chmod 644 uploads/*.jpg

# Check in HTML source (F12):
# Should show: src="uploads/golf.jpg"
# NOT: src="imgs/golf.jpg"
```

### "Registrácia zlyhá - Unknown column 'email'"
```bash
# Reimport SQL:
mysql -u root < complete.sql

# Or manually create users table:
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    phone VARCHAR(30),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### "Access denied for ziak_1"
```bash
# Verify credentials:
# In inc/db.php:
echo $username; // Should print: ziak_1

# Test connection:
mysql -u ziak_1 -p auto_demo
# Enter password: 8ggVKh<KYUe2]<OuJ4xq
```

### "Admin login shows wrong password message"
```php
// In admin-login.php, line ~45, verify:
if ($username === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD_HASH)) {
    // Should use password_verify, NOT ==
}

// Regenerate hash if needed:
php -r "echo password_hash('admin123', PASSWORD_BCRYPT);"
// Copy result to inc/config.php ADMIN_PASSWORD_HASH
```

---

## Performance Check

### Load Times
- Home page: < 500ms
- Detail page: < 300ms
- Login page: < 200ms
- Admin panel: < 500ms (after login)

### Database Queries
- Home cars list: 1 query (JOIN)
- Detail page: 2 queries (car + images)
- Login: 1 query (user check)
- Registration: 2 queries (check + insert)

---

## Security Verification

```php
// Check password hashing (phpMyAdmin):
SELECT email, password FROM users LIMIT 1;
// Password should look like: $2y$10$... (bcrypt)
// NOT plain text or MD5

// Check SQL injection protection:
// All queries use prepared statements with bind_param()
// No direct string concatenation in SQL

// Check XSS protection:
// All outputs use escape() function
// Check HTML source has no unescaped user input
```

---

## Final Checklist

- [ ] All 6 tables exist in `auto_demo` database
- [ ] User can register with email
- [ ] User can login with email
- [ ] Password is hashed with bcrypt
- [ ] Admin can login at admin-login.php
- [ ] All 5 cars show with images on home
- [ ] Car detail page shows gallery
- [ ] Image thumbnails are clickable
- [ ] Session persists on refresh
- [ ] Logout clears session
- [ ] Cart works without login
- [ ] Rate limiting on admin (5 attempts)

---

## Sign-Off

**If all tests PASS:**

```
Status: ✅ PRODUCTION READY
Date: [Today]
Tester: [Your Name]
```

---

**Next:** Deploy to production with [SETUP.md](SETUP.md)
