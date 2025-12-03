# 🔒 HTTPS 403 Forbidden Fix Guide

## Problem
Some API endpoints return 403 Forbidden on HTTPS but work fine on HTTP.

## Root Cause
The web server is blocking HTTPS requests before they reach Laravel. This is typically due to:
1. Proxy/Load Balancer configuration
2. Server security rules
3. ModSecurity or hosting panel restrictions

---

## ✅ Fixes Already Applied (Deploy These Files)

### 1. **TrustProxies.php** - Trust all proxies
```php
protected $proxies = '*';
```

### 2. **.htaccess** - Force HTTPS header detection
```apache
# Force HTTPS header detection for proxied requests
RewriteCond %{HTTP:X-Forwarded-Proto} =https
RewriteRule .* - [E=HTTPS:on]

# Handle Authorization Header
RewriteCond %{HTTP:Authorization} .
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
```

### 3. **AppServiceProvider.php** - Force HTTPS scheme
```php
// Force HTTPS detection when behind a proxy/load balancer
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $this->app['request']->server->set('HTTPS', 'on');
    \URL::forceScheme('https');
}
```

---

## 🚀 Deployment Steps

1. **Commit and push these changes:**
   ```bash
   git add .
   git commit -m "Fix HTTPS 403 errors - Trust proxies and force HTTPS detection"
   git push origin main
   ```

2. **Deploy to your server**

3. **Clear caches on server:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   php artisan view:clear
   ```

4. **Test the API:**
   ```
   https://yourdomain.com/api/stories?zone_id=BmSTwRFzmP13PnVNFJZJ
   ```

---

## 🔧 Additional Server-Side Fixes (If Still Not Working)

### A. **Check Server SSL Configuration**

If using **Apache**, ensure mod_ssl is enabled:
```bash
sudo a2enmod ssl
sudo a2enmod headers
sudo a2enmod rewrite
sudo systemctl restart apache2
```

If using **Nginx**, check your SSL configuration in `/etc/nginx/sites-available/your-site`:
```nginx
server {
    listen 443 ssl http2;
    server_name yourdomain.com;
    
    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Important: Pass HTTPS headers
        fastcgi_param HTTPS on;
        fastcgi_param HTTP_X_FORWARDED_PROTO $scheme;
    }
}
```

### B. **Disable ModSecurity (If Present)**

Check if ModSecurity is blocking requests:
```bash
# Check ModSecurity logs
sudo tail -f /var/log/apache2/modsec_audit.log
# OR
sudo tail -f /var/log/nginx/modsec_audit.log
```

To disable ModSecurity for your API:
```apache
# In .htaccess or Apache config
<IfModule mod_security.c>
    SecRuleEngine Off
</IfModule>
```

### C. **Check cPanel/Plesk Security Settings**

If using cPanel:
1. Go to **Security** → **ModSecurity**
2. Disable ModSecurity for your domain OR
3. Add rule exception for `/api/*` paths

If using Plesk:
1. Go to **Websites & Domains** → **Security**
2. Check Web Application Firewall settings
3. Add exception for API endpoints

### D. **Check File/Directory Permissions**
```bash
# Set correct permissions
cd /path/to/your/project
sudo chown -R www-data:www-data .
sudo chmod -R 755 storage bootstrap/cache
sudo chmod -R 775 storage/logs
```

### E. **Check .htaccess in Public Directory**

Ensure `/public/.htaccess` has:
```apache
<IfModule mod_rewrite.c>
    Options -MultiViews -Indexes
    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Route to index.php
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

### F. **Check PHP-FPM Configuration** (If applicable)

Ensure PHP-FPM is not restricting HTTPS:
```ini
# /etc/php/8.1/fpm/pool.d/www.conf
; Security settings
security.limit_extensions = .php
```

---

## 🧪 Testing

1. **Test with curl:**
   ```bash
   curl -X GET "https://yourdomain.com/api/stories?zone_id=BmSTwRFzmP13PnVNFJZJ" \
        -H "Accept: application/json" \
        -H "Content-Type: application/json" \
        -v
   ```

2. **Check server logs:**
   ```bash
   # Apache
   sudo tail -f /var/log/apache2/error.log
   sudo tail -f /var/log/apache2/access.log
   
   # Nginx
   sudo tail -f /var/log/nginx/error.log
   sudo tail -f /var/log/nginx/access.log
   
   # Laravel
   tail -f storage/logs/laravel.log
   ```

---

## 📝 Common Issues Checklist

- [ ] TrustProxies middleware updated to trust all proxies (`'*'`)
- [ ] .htaccess has X-Forwarded-Proto rewrite rule
- [ ] AppServiceProvider forces HTTPS scheme
- [ ] Caches cleared on server
- [ ] mod_rewrite enabled (Apache)
- [ ] mod_ssl enabled (Apache)
- [ ] mod_headers enabled (Apache)
- [ ] File permissions correct (755/775)
- [ ] ModSecurity disabled or has exceptions
- [ ] cPanel/Plesk WAF exceptions added
- [ ] SSL certificate valid and not expired
- [ ] Server has correct PHP version (8.0+)
- [ ] No IP restrictions in server config

---

## 🆘 Still Not Working?

If the issue persists after trying all the above:

1. **Check if it's a CDN/Firewall issue:**
   - CloudFlare, Sucuri, or similar services might be blocking
   - Check their security logs and add exceptions

2. **Check Load Balancer settings:**
   - If using AWS ELB, Azure Load Balancer, or similar
   - Ensure health checks are passing
   - Verify target group settings

3. **Contact your hosting provider:**
   - They may have server-level security rules
   - Ask them to check why HTTPS requests to `/api/stories` are returning 403

4. **Enable Laravel debug mode temporarily:**
   ```env
   APP_DEBUG=true
   ```
   Then check if you get more detailed error messages.

---

## 📞 Need Help?

Provide your hosting provider with:
- Web server type (Apache/Nginx)
- PHP version
- Control panel (cPanel/Plesk/None)
- CDN/Firewall (CloudFlare/None)
- This error: "403 Forbidden on HTTPS for /api/stories, works on HTTP"


