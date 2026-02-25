# Helpdesk System - Free Hosting Deployment Guide

## 📦 What's Included
- Complete helpdesk PHP application
- MySQL database export
- PWA (Progressive Web App) configuration
- All necessary files for deployment

---

## 🚀 Step-by-Step Deployment Instructions

### Step 1: Choose Free Hosting Provider

**Recommended: InfinityFree**
- Website: https://infinityfree.net
- Free features: Unlimited bandwidth, 5GB storage, MySQL, PHP, No ads
- Sign up with your email

**Alternative: 000webhost**
- Website: https://www.000webhost.com
- Free features: 300MB storage, 3GB bandwidth, MySQL, PHP

---

### Step 2: Sign Up for Hosting

1. Go to **InfinityFree.net**
2. Click **"Sign Up"**
3. Enter your email and create password
4. Verify your email
5. Click **"Create Account"** in the dashboard

---

### Step 3: Create Hosting Account

1. Choose a **subdomain** (e.g., `myhelpdesk.infinityfreeapp.com`)
2. Or use your own domain if you have one
3. Click **"Create Account"**
4. Wait 2-5 minutes for account activation

---

### Step 4: Access Control Panel (cPanel)

1. From InfinityFree dashboard, click **"Control Panel"**
2. You'll see cPanel interface
3. Note your **FTP credentials** (found in Account Settings)

---

### Step 5: Create MySQL Database

1. In cPanel, find **"MySQL Databases"**
2. Click it
3. Create a new database:
   - Database name: `helpdesk` (or any name)
   - Click **"Create Database"**
4. Create a database user:
   - Username: `helpdesk_user` (or any name)
   - Password: Create a strong password
   - Click **"Create User"**
5. Add user to database:
   - Select your database
   - Select your user
   - Grant **"All Privileges"**
   - Click **"Add"**
6. **IMPORTANT: Write down these details:**
   ```
   Database Host: localhost (usually)
   Database Name: (your_database_name)
   Database User: (your_username)
   Database Password: (your_password)
   ```

---

### Step 6: Upload Files

**Method A: File Manager (Easier)**
1. In cPanel, click **"File Manager"**
2. Navigate to **"htdocs"** or **"public_html"** folder
3. Delete any default files (index.html, etc.)
4. Click **"Upload"** button
5. Select all files from your helpdesk folder
6. Wait for upload to complete

**Method B: FTP (Faster for large files)**
1. Download **FileZilla** (free FTP client)
2. Use FTP credentials from hosting account
3. Connect to your server
4. Upload all files to **htdocs** or **public_html**

---

### Step 7: Import Database

1. In cPanel, click **"phpMyAdmin"**
2. Select your database from left sidebar
3. Click **"Import"** tab
4. Click **"Choose File"**
5. Select `database/helpdesk_core_php.sql`
6. Click **"Go"** at bottom
7. Wait for import to complete
8. You should see success message

---

### Step 8: Update Database Configuration

1. In File Manager, navigate to your files
2. Open **`src/Database.php`**
3. Click **"Edit"**
4. Update line 12 with your database details:

```php
private static $connection = null;

private function __construct() {
    self::$connection = new mysqli(
        'localhost',              // Keep as localhost
        'YOUR_DATABASE_USER',     // Replace with your username
        'YOUR_DATABASE_PASSWORD', // Replace with your password
        'YOUR_DATABASE_NAME'      // Replace with your database name
    );
```

5. Click **"Save Changes"**

---

### Step 9: Update PWA Configuration

1. Open **`manifest.json`**
2. Update the `start_url` and `scope`:

```json
{
  "name": "ICT Helpdesk System",
  "short_name": "Helpdesk",
  "start_url": "/dashboard.php",
  "scope": "/",
  ...
}
```

3. Open **`pwa-head.php`**
4. Update paths to remove `/helpdesk-core-php/`:

```php
<link rel="manifest" href="/manifest.json">
<link rel="apple-touch-icon" href="/pwa-icon-192.png">
```

5. Open **`service-worker.js`**
6. Update paths:

```javascript
const urlsToCache = [
  '/',
  '/dashboard.php',
  '/index.php',
  ...
];
```

---

### Step 10: Test Your Website

1. Open browser on your computer
2. Go to: `http://your-subdomain.infinityfreeapp.com`
3. You should see the login page
4. Login with existing credentials:
   - Email: johndoe@helpdesk.com
   - Password: (check your database or create new user)

---

### Step 11: Install PWA on iPhone

1. Open **Safari** on your iPhone (must use Safari)
2. Go to: `https://your-subdomain.infinityfreeapp.com`
3. Tap the **Share** button (square with arrow)
4. Scroll and tap **"Add to Home Screen"**
5. Tap **"Add"**
6. App icon appears on home screen!

---

## 🔧 Troubleshooting

### Database Connection Error
- Check database credentials in `src/Database.php`
- Ensure database was imported successfully
- Verify database user has all privileges

### 404 Error / Page Not Found
- Ensure files are in `htdocs` or `public_html` (not in subfolder)
- Check `.htaccess` file exists
- Clear browser cache

### Login Not Working
- Check if database was imported
- Verify users table has data
- Try creating a new user via phpMyAdmin

### PWA Not Installing
- Must use Safari on iOS
- Ensure using HTTPS (free hosting provides this)
- Check manifest.json paths are correct

---

## 📱 Default Login Credentials

Check your database users table or create new admin:

```sql
INSERT INTO users (name, email, password, role, phone, last_password, created_at)
VALUES (
  'Admin User',
  'admin@helpdesk.com',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
  'admin',
  '0712345678',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  NOW()
);
```

---

## ✅ Post-Deployment Checklist

- [ ] Database imported successfully
- [ ] Can access login page
- [ ] Can login successfully
- [ ] Dashboard loads correctly
- [ ] Can create tickets
- [ ] Can create users
- [ ] PWA installs on iPhone
- [ ] Notifications working

---

## 🎉 Success!

Your helpdesk system is now:
- ✅ Hosted online for FREE
- ✅ Accessible from anywhere
- ✅ Installable as app on iPhone
- ✅ No MacBook dependency

---

## 💡 Tips

1. **Backup regularly**: Export database weekly via phpMyAdmin
2. **Keep credentials safe**: Don't share database passwords
3. **Update regularly**: Check for updates to keep system secure
4. **Monitor usage**: Free hosting has limits, monitor your usage

---

## 📞 Need Help?

- InfinityFree Forum: https://forum.infinityfree.net
- 000webhost Support: https://www.000webhost.com/forum

---

**Created with ❤️ for free deployment**
