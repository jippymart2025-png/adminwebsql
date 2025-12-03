# 🔧 Server-Level 403 Debugging Steps

## The Problem
You're still getting 403 Forbidden on HTTPS after code fixes. This means **the web server is blocking the request before it reaches Laravel**.

---

## 🧪 Step 1: Test Direct PHP Access

First, verify PHP and HTTPS are working:

1. **Upload the test file** `public/test-https.php` to your server

2. **Access it directly via HTTPS:**
   ```
   https://yourdomain.com/test-https.php
   ```

3. **Expected Results:**
   - ✅ If you see JSON with "SUCCESS - PHP is working!" → PHP and HTTPS work, problem is with Laravel routing
   - ❌ If you get 403 → Server is blocking ALL HTTPS requests (go to Step 2)
   - ❌ If you get different error → Note the error and continue

---

## 🔍 Step 2: Check Server Error Logs

### For cPanel Hosting:
```bash
# Access via SSH or File Manager
tail -100 /home/yourusername/logs/error_log
# OR
tail -100 /home/yourusername/public_html/error_log
```

### For VPS/Dedicated Server:

**Apache:**
```bash
sudo tail -100 /var/log/apache2/error.log
sudo tail -100 /var/log/apache2/ssl_error.log
```

**Nginx:**
```bash
sudo tail -100 /var/log/nginx/error.log
```

**Look for:**
- "ModSecurity: Access denied"
- "client denied by server configuration"
- "AH01630: client denied by server configuration"
- Any mentions of your API endpoint

---

## 🛡️ Step 3: Check ModSecurity / Web Application Firewall

### cPanel:
1. Log into cPanel
2. Go to **Security** → **ModSecurity**
3. Check if it's enabled
4. Look for recent hits/blocks
5. **Temporarily disable it** or add exception:

### Add Exception in .htaccess (try this):
```apache
<IfModule mod_security2.c>
    SecRuleEngine Off
</IfModule>

# OR add exception for API only
<IfModule mod_security2.c>
    <LocationMatch "^/api/">
        SecRuleEngine Off
    </LocationMatch>
</IfModule>
```

### Plesk:
1. Go to **Websites & Domains**
2. Click your domain → **Web Application Firewall**
3. Either disable or add exception for `/api/*`

---

## 🔐 Step 4: Check IP/Country Blocking

Some servers block certain IPs or countries by default:

### In .htaccess, add this at the top:
```apache
# Allow all IPs (temporary test)
<RequireAll>
    Require all granted
</RequireAll>
```

---

## 🌐 Step 5: Check Apache Virtual Host Configuration

If you have SSH access:

```bash
# Find your Apache config
sudo apachectl -S

# Check your site's config file
sudo nano /etc/apache2/sites-available/yourdomain.conf
# OR
sudo nano /etc/httpd/conf.d/yourdomain.conf
```

**Look for SSL VirtualHost section:**
```apache
<VirtualHost *:443>
    ServerName yourdomain.com
    DocumentRoot /path/to/public
    
    # MAKE SURE THESE ARE PRESENT:
    <Directory /path/to/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # Check these modules are loaded
    # SSLEngine on
    # SSLCertificateFile ...
    # SSLCertificateKeyFile ...
</VirtualHost>
```

**Key things to check:**
- `AllowOverride All` must be present (allows .htaccess to work)
- `Require all granted` allows access
- DocumentRoot points to `/public` folder

---

## 📦 Step 6: Check Nginx Configuration

If using Nginx:

```bash
sudo nano /etc/nginx/sites-available/yourdomain.com
```

**Your SSL server block should look like:**
```nginx
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    root /path/to/your/project/public;

    # SSL Configuration
    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;

    # Important: Add these headers for Laravel
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    # CRITICAL: Laravel routing
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Don't block API endpoints
    location ~ ^/api/ {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM configuration
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Force HTTPS detection
        fastcgi_param HTTPS on;
        fastcgi_param HTTP_X_FORWARDED_PROTO $scheme;
    }

    # Deny access to hidden files
    location ~ /\. {
        deny all;
    }
}
```

**After changes:**
```bash
sudo nginx -t
sudo systemctl restart nginx
```

---

## ☁️ Step 7: Check CDN/Firewall (Cloudflare, Sucuri, etc.)

### If using Cloudflare:
1. Log into Cloudflare dashboard
2. Go to **Security** → **WAF**
3. Check **Security Events** for blocked requests
4. Add **Firewall Rule** to allow API:
   ```
   (http.request.uri.path contains "/api/") → Action: Allow
   ```

### If using other CDN:
- Check their security/firewall logs
- Temporarily disable WAF to test

---

## 🔑 Step 8: Check File Permissions

Wrong permissions can cause 403:

```bash
cd /path/to/your/project

# Fix ownership
sudo chown -R www-data:www-data .
# OR for cPanel
sudo chown -R username:username .

# Fix permissions
sudo find . -type f -exec chmod 644 {} \;
sudo find . -type d -exec chmod 755 {} \;
sudo chmod -R 775 storage bootstrap/cache
```

---

## 🚨 Step 9: Enable Apache Modules

Make sure required modules are enabled:

```bash
sudo a2enmod rewrite
sudo a2enmod ssl
sudo a2enmod headers
sudo a2enmod proxy_fcgi
sudo systemctl restart apache2
```

---

## 🧹 Step 10: Clear Server Caches

```bash
# Clear Laravel caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Clear OPcache (if enabled)
sudo systemctl restart php8.1-fpm
# OR
sudo systemctl restart apache2

# For cPanel
# Go to: Select PHP Version → Extensions → Opcache → Reset
```

---

## 🆘 Step 11: Contact Hosting Provider

If nothing works, contact your hosting provider with this info:

```
Subject: 403 Forbidden on HTTPS for Laravel API endpoints

Description:
- API endpoint /api/stories returns 403 on HTTPS but works on HTTP
- Direct PHP files work fine (test-https.php returns 200)
- Laravel application code is correct
- Suspecting server-level security blocking HTTPS API requests

Please check:
1. ModSecurity rules blocking /api/* on HTTPS
2. Apache/Nginx virtual host configuration for SSL
3. Any IP or geographical restrictions
4. WAF or firewall rules
5. AllowOverride settings in Apache config

Error: 403 Forbidden (HTML response, not Laravel error)
URL: https://yourdomain.com/api/stories?zone_id=BmSTwRFzmP13PnVNFJZJ
```

---

## 📊 Step 12: Quick Command Reference

**Check what web server you're using:**
```bash
ps aux | grep -E 'apache|nginx|httpd'
```

**Check if .htaccess is being read:**
```bash
# Add intentional error to .htaccess:
XXXINVALID

# Reload page - if you get 500 error, .htaccess is working
# If still 403, .htaccess is being ignored (check AllowOverride)
```

**Check SSL certificate:**
```bash
curl -vI https://yourdomain.com 2>&1 | grep -E 'SSL|TLS'
```

**Test API with full debugging:**
```bash
curl -X GET "https://yourdomain.com/api/stories?zone_id=BmSTwRFzmP13PnVNFJZJ" \
     -H "Accept: application/json" \
     -H "User-Agent: Mozilla/5.0" \
     -v 2>&1 | tee debug.log
```

---

## ✅ Checklist

Run through this checklist:

- [ ] test-https.php works on HTTPS
- [ ] Checked error logs (Apache/Nginx)
- [ ] ModSecurity disabled or exception added
- [ ] IP/Country blocking checked
- [ ] Apache/Nginx config allows API access
- [ ] AllowOverride All is set
- [ ] File permissions correct (755/644)
- [ ] Apache modules enabled (rewrite, ssl, headers)
- [ ] CDN/Firewall exceptions added
- [ ] Server caches cleared
- [ ] Contacted hosting provider (if needed)

---

## 💡 Most Common Solution

**90% of the time, it's one of these:**

1. **ModSecurity blocking the request** → Disable or add exception
2. **AllowOverride not set** → Apache ignoring .htaccess
3. **Wrong DocumentRoot** → Not pointing to /public folder
4. **CDN/Firewall blocking** → Cloudflare or similar blocking API
5. **cPanel Security rules** → Check Security Center in cPanel

---

## 🎯 Next Steps

1. Start with Step 1 (test-https.php)
2. Check your hosting provider's control panel security settings
3. Review error logs
4. Try disabling ModSecurity temporarily
5. Report back what you find!


