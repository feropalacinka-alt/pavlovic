# 🚀 SETUP & DEPLOYMENT

## Quick Start (5 minút)

### 1. Database Import
```bash
cd c:\xampp\htdocs\projekttt
mysql -u root < complete.sql
```

Alebo v phpMyAdmin:
- Prejsť na: http://localhost/phpmyadmin
- Kliknúť "Import"
- Vybrať `complete.sql`
- Kliknúť "Import"

### 2. Skontrolovať Databázu
```bash
mysql -u root -e "USE auto_demo; SHOW TABLES;"
```

Má zobraziť: `cars`, `car_images`, `users`, `orders`, `order_items`, `admin_users`

### 3. Otestovať v Prehliadači

**Registrácia:**
- http://localhost/projekttt/login.php
- Vyplniť registračný formulár
- Email: `test@example.com`
- Heslo: `test123456`
- Kliknúť "Zaregistrovať sa"

**Prihlásenie (User):**
- http://localhost/projekttt/login.php
- Vyplniť email a heslo
- Kliknúť "Prihlásiť sa"

**Admin Login:**
- http://localhost/projekttt/admin-login.php
- Meno: `admin`
- Heslo: `admin123`
- Kliknúť "Prihlásiť sa ako Admin"

**Obrázky:**
- http://localhost/projekttt/index.php → 5 áut s obrázkami
- http://localhost/projekttt/detail.php?id=1 → Galéria

---

## Production Checklist

### 🔐 Bezpečnosť
- [ ] Zmeniť admin heslo v `inc/config.php`
- [ ] Zmeniť DB credentials (ziak_1 → prod username)
- [ ] Nahradiť placeholder obrázky skutočnými
- [ ] Zapnúť HTTPS
- [ ] Nastaviť Secure cookies v `inc/config.php`
- [ ] Zakázať `error_reporting` v produkcii

### 📧 Email (Voliteľné)
- [ ] Nainštalovať PHPMailer alebo SendGrid
- [ ] Nastaviť notifikácie pri objednávke

### 📊 Monitoring
- [ ] Nastaviť error logging
- [ ] Backup databázy (daily)
- [ ] Sledovať disk space pre `uploads/`

---

## Troubleshooting

### "Access denied for user 'ziak_1'"
**Riešenie:** Skontrolujte DB credentials v `inc/db.php`
```php
$servername = "db.r6.websupport.sk";
$username = "ziak_1";
$password = "8ggVKh<KYUe2]<OuJ4xq";
$dbname = "auto_demo";
```

### "Table 'auto_demo.users' doesn't exist"
**Riešenie:** Znova importovať `complete.sql`
```bash
mysql -u root < complete.sql
```

### "Obrázky sa neukazujú"
**Riešenie:** Skontrolovať `uploads/` priečinok
```bash
ls -la uploads/
# Mal by obsahovať: golf.jpg, bmw.jpg, mercedes.jpg, corolla.jpg, audi.jpg, toyota.jpg
```

### "Registrácia zlyhá - Chyba pri registrácii"
**Riešenie:** Skontrolovať MySQL error log
```bash
# V phpMyAdmin: Structure tabuľky users
# Check: email column UNIQUE, password VARCHAR(255)
```

---

## Config Files

### `inc/config.php` - ZMENIŤ V PRODUKCII
```php
define('SITE_URL', 'http://localhost/projekttt'); // → https://vasa-domena.sk

define('ADMIN_PASSWORD_HASH', '$2y$10$...');
// → Generovať novo: php -r "echo password_hash('vase-heslo', PASSWORD_BCRYPT);"

define('MAX_FILE_SIZE', 5242880); // OK (5MB)
```

### `inc/db.php` - ZMENIŤ V PRODUKCII
```php
$servername = "localhost"; // → prod server
$username = "prod_user";   // → prod username
$password = "prod_pass";   // → prod password
$dbname = "auto_demo";     // OK
```

---

## Files Modified

✅ [complete.sql](complete.sql) - Fixed & Ready  
✅ [index.php](index.php) - Image paths  
✅ [detail.php](detail.php) - Image paths  
✅ [login.php](login.php) - User registration + login  
✅ [admin-login.php](admin-login.php) - NEW  
✅ [admin.php](admin.php) - Redirect fixed  
✅ [uploads/](uploads/) - 16 image files  

---

## Next Steps

1. **Obrázky:** Nahraďte placeholder JPG obrázkami v `uploads/`
2. **Admin:** Zmeniť heslo `admin123` na bezpečné
3. **Email:** Nastaviť SMTP pre notifikácie (optional)
4. **SSL:** Zapnúť HTTPS v produkcii
5. **Backup:** Nastaviť automatické zálohování DB

---

**Status:** ✅ Ready to Deploy

Kontakt na support → [FIXES_SUMMARY.md](FIXES_SUMMARY.md)
