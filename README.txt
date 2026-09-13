MODEX Website v3
================

ADMIN LOGIN
  URL:      https://yourdomain.com/admin/login.php
  Username: admin
  Password: modex2024
  !! Change password immediately after first login !!

SETUP STEPS
-----------
1. Upload ALL files to public_html/

2. Create MySQL database in DirectAdmin:
   - Go to MySQL Management
   - Create database  (e.g. modex)  -> becomes yourusername_modex
   - Create user + assign ALL PRIVILEGES

3. Import database:
   - Open database.sql in Notepad
   - DELETE the first 2 lines (CREATE DATABASE + USE)
   - Save and import via phpMyAdmin

4. Edit includes/config.php:
   define('DB_NAME', 'yourusername_modex');
   define('DB_USER', 'yourusername_modex');
   define('DB_PASS', 'your_password');
   define('SITE_URL', 'https://yourdomain.com');

5. Set assets/images/ permission to 755
   (DirectAdmin File Manager -> right-click -> Change Permissions)

6. Visit https://yourdomain.com
   Visit https://yourdomain.com/admin/login.php

CHANGE DELIVERY CHARGES
  Edit includes/config.php:
  define('DELIVERY_INSIDE_DHAKA', 80);
  define('DELIVERY_OUTSIDE_DHAKA', 120);

CHANGE WHATSAPP NUMBER
  Edit includes/layout_bottom.php
  Replace 8801XXXXXXXXX with your number (with country code, no +)
