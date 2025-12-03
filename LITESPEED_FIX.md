# 🚀 LiteSpeed Server - 403 Fix Guide

## 🔍 Your Server Setup

Based on [your test results](https://web.jippymart.in/test-https.php):
- **Server:** LiteSpeed (not Apache!)
- **PHP Version:** 8.2.27
- **HTTPS:** Working correctly
- **Direct PHP:** ✅ Works
- **Laravel APIs:** ❌ 403 Forbidden

---

## 🎯 The Issue

**LiteSpeed Web Application Firewall (LSWAF)** is blocking your API requests because they come from:
- Postman
- Mobile apps  
- curl/API clients
- Non-browser user agents

---

## ✅ Fixes Applied

I've added **LiteSpeed-specific directives** to both `.htaccess` files:

### 1. Root `.htaccess` (/)
```apache
# Disable LiteSpeed WAF
<IfModule LiteSpeed>
    php_value modsecurity.enabled Off
</IfModule>

<IfModule mod_security.c>
    SecRuleEngine Off
</IfModule>

# In rewrite section
SetEnvIf Request_URI ".*" MODSEC_ENABLE=Off
```

### 2. Public `.htaccess` (/public)
Same directives added for completeness.

---

## 🚀 Deploy Now

```bash
git add .
git commit -m "Fix LiteSpeed WAF blocking API requests"
git push origin main
```

On your server:
```bash
git pull origin main
```

---

## 🧪 Test After Deploy

### Test 1: Browser (should still work)
```
https://web.jippymart.in/api/test-server
```

### Test 2: Postman (should now work!)
```
GET https://web.jippymart.in/api/test-server
```

### Test 3: Your Stories API
```
GET https://web.jippymart.in/api/stories?zone_id=BmSTwRFzmP13PnVNFJZJ
```

---

## 🛡️ If Still Getting 403 - Check LiteSpeed Admin Panel

Since you're on LiteSpeed, you may have access to **LiteSpeed Admin Console** or **cPanel with LiteSpeed**.

### Option 1: LiteSpeed WebAdmin Console

If you have access to LiteSpeed WebAdmin (usually port 7080):

1. Access: `https://your-server-ip:7080`
2. Login with admin credentials
3. Go to: **Actions** → **Security** → **ModSecurity**
4. Set **Enable ModSecurity** to: **No**
5. Click **Save**
6. **Graceful Restart** the server

### Option 2: cPanel with LiteSpeed

1. Login to **cPanel**
2. Look for **"ModSecurity"** or **"WAF"**
3. **Disable** for your domain `web.jippymart.in`
4. Save changes

### Option 3: WHM (if you have access)

1. Login to **WHM**
2. Search for **"ModSecurity"**
3. Go to **ConfigServer Security & Firewall** or **ModSecurity™ Tools**
4. **Disable ModSecurity** or add domain to whitelist

---

## 📞 Contact Your Hosting Provider

If `.htaccess` changes don't work, send this to your host:

```
Subject: Disable LiteSpeed WAF for web.jippymart.in

Hi,

I'm using a LiteSpeed server and my Laravel API is being blocked 
by the Web Application Firewall.

Server: LiteSpeed (confirmed via test-https.php)
Domain: web.jippymart.in
PHP Version: 8.2.27

Issue:
- Browser requests: ✅ Work fine
- Postman/API clients: ❌ 403 Forbidden
- Mobile app: ❌ 403 Forbidden

Working URL (browser): https://web.jippymart.in/api/test-server
Blocked URL (Postman): Same URL returns 403

Please disable LiteSpeed WAF / ModSecurity for this domain or 
whitelist these user-agents:
- PostmanRuntime
- okhttp
- CFNetwork
- curl
- python-requests

Thank you!
```

---

## 🔧 Alternative: Create .modsec_vendor.conf

Some LiteSpeed hosts allow custom ModSecurity rules:

Create `/home/username/.modsec_vendor.conf`:
```apache
<Location /api>
    SecRuleEngine Off
</Location>

<Location />
    SecRuleRemoveById 990002
    SecRuleRemoveById 990012
    SecRuleRemoveById 990011
</Location>
```

Then restart LiteSpeed or contact host to apply it.

---

## 🎯 LiteSpeed-Specific Commands

### Check if LiteSpeed is running:
```bash
ps aux | grep lsphp
ps aux | grep litespeed
```

### Check LiteSpeed version:
```bash
/usr/local/lsws/bin/lshttpd -v
```

### Restart LiteSpeed (if you have SSH access):
```bash
sudo systemctl restart lsws
# OR
sudo /usr/local/lsws/bin/lswsctrl restart
```

---

## 💡 Temporary Workaround for Testing

While waiting for server changes, test in Postman with browser headers:

**Add to Postman Headers:**
```
User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36
Accept: application/json
Accept-Language: en-US,en;q=0.9
```

This makes Postman look like Chrome, bypassing the WAF.

---

## 📊 Common LiteSpeed Hosting Providers

If you're on one of these, here's how to disable WAF:

### **Namecheap** (with LiteSpeed):
- cPanel → ModSecurity → Toggle OFF

### **HostGator** (LiteSpeed plans):
- cPanel → Security → ModSecurity → Disable

### **A2 Hosting** (Turbo servers use LiteSpeed):
- cPanel → ModSecurity → Turn Off

### **Cloudways** (LiteSpeed):
- Cloudways Platform → Application → Settings → Disable ModSecurity

### **SiteGround** (uses LiteSpeed):
- Site Tools → Security → ModSecurity → Disable

### **Other hosts**:
- Contact support with template above

---

## ✅ Success Checklist

- [ ] Deployed updated .htaccess files with LiteSpeed directives
- [ ] Cleared browser cache
- [ ] Tested API in browser (should still work)
- [ ] Tested API in Postman (should now work)
- [ ] If still blocked, disabled ModSecurity in cPanel
- [ ] If no cPanel access, contacted hosting provider
- [ ] Mobile app will work once Postman works

---

## 🆘 Still Not Working?

If `.htaccess` changes don't help, it means:

1. **LiteSpeed is ignoring .htaccess ModSecurity rules** (common)
2. **WAF must be disabled at server level** (requires host/admin)
3. **Your host has server-level security** (contact them)

**The solution is to have your hosting provider disable LiteSpeed WAF for your domain.**

---

## 🎉 Expected Result

After fix:
- ✅ Browser → Works
- ✅ Postman → Works
- ✅ Mobile apps → Work
- ✅ curl/API clients → Work

All user-agents should be treated equally!

---

**Deploy the changes and test in Postman immediately!** 🚀


