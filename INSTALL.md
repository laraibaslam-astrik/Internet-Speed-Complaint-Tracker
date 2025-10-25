# Installation Guide - Pakistan Internet Speed Tracker

## Prerequisites Checklist

- [ ] PHP 8.0+ installed
- [ ] MySQL 8.0+ installed
- [ ] Web server (Apache/Nginx) configured
- [ ] Command-line access for cron setup

## Step-by-Step Installation

### 1. Import Database Schema (5 minutes)

```bash
# Login to MySQL
mysql -u root -p

# Import schema
mysql -u root -p < schema.sql

# Verify tables created
mysql -u root -p -e "USE speedtracker; SHOW TABLES;"
```

**Expected output:**
```
+-------------------------+
| Tables_in_speedtracker  |
+-------------------------+
| outages                 |
| rollups_daily           |
| tests                   |
+-------------------------+
```

### 2. Configure Environment (3 minutes)

```bash
cd public/
cp .env.example .env
nano .env  # or use your preferred editor
```

**Minimum required changes:**
```env
DB_PASS=your_secure_password_here
HASH_SALT=generate_random_string_here
ALLOWED_ORIGIN=http://localhost  # Change to your domain in production
```

**Generate random salt:**
```bash
# Linux/Mac
openssl rand -hex 32

# Windows PowerShell
-join ((48..57) + (65..90) + (97..122) | Get-Random -Count 32 | % {[char]$_})
```

### 3. Test Database Connection (2 minutes)

Create `public/test_db.php`:
```php
<?php
require_once 'lib/db.php';
$conn = get_db_connection();
if ($conn) {
    echo "✓ Database connection successful!\n";
    echo "Server info: " . $conn->server_info . "\n";
} else {
    echo "✗ Database connection failed!\n";
}
```

Run test:
```bash
php public/test_db.php
```

### 4. Set Permissions (1 minute)

**Linux/Mac:**
```bash
# Set ownership (Apache)
sudo chown -R www-data:www-data public/

# Set permissions
find public/ -type f -exec chmod 644 {} \;
find public/ -type d -exec chmod 755 {} \;

# Protect .env
chmod 600 public/.env
```

**Windows (XAMPP/WAMP):**
```powershell
# Just ensure the web server user has read access to public/
icacls public /grant "Everyone:(OI)(CI)R" /T
```

### 5. Configure Web Server (5 minutes)

#### Apache (with .htaccess)

**.htaccess is already created**, just ensure mod_rewrite is enabled:

```bash
# Enable mod_rewrite
sudo a2enmod rewrite

# Restart Apache
sudo systemctl restart apache2
```

**Test:** Visit `http://localhost/` - should load index.php

#### Nginx

Create `/etc/nginx/sites-available/speedtracker`:
```nginx
server {
    listen 80;
    server_name localhost;
    root /path/to/project/public;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ ^/r/([a-zA-Z0-9]+)$ {
        try_files $uri /result.php?id=$1;
    }
    
    location ~ /\.(env|htaccess) {
        deny all;
    }
}
```

Enable and restart:
```bash
sudo ln -s /etc/nginx/sites-available/speedtracker /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 6. Test API Endpoints (3 minutes)

```bash
# Test WHO AM I
curl http://localhost/api/whoami.php

# Expected: {"asn":null,"isp_name":"Unknown","city":"Unknown","ip":"127.0.0.1"}

# Test PING
curl http://localhost/api/ping.php

# Expected: {"server_now":1234567890.123,"timestamp":1234567890}

# Test Leaderboard (will be empty initially)
curl http://localhost/api/leaderboard.php?date=today

# Expected: {"date":"2024-01-01","city":null,"spike":false,"leaderboard":[]}
```

### 7. Configure Cron Jobs (3 minutes)

```bash
# Edit crontab
crontab -e

# Add these lines (adjust paths):
0 0 * * * /usr/bin/php /var/www/html/public/cron/rollups_daily.php >> /var/log/speedtracker-rollups.log 2>&1
*/15 * * * * /usr/bin/php /var/www/html/public/cron/detect_anomalies.php >> /var/log/speedtracker-anomalies.log 2>&1

# Create log directory
sudo mkdir -p /var/log/speedtracker
sudo touch /var/log/speedtracker-rollups.log
sudo touch /var/log/speedtracker-anomalies.log
sudo chown www-data:www-data /var/log/speedtracker-*.log
```

**Test cron scripts manually:**
```bash
php public/cron/rollups_daily.php
php public/cron/detect_anomalies.php
```

### 8. Optional: IPInfo.io Integration (5 minutes)

For accurate ISP detection:

1. Get free API token: https://ipinfo.io/signup
2. Add to `.env`: `IPINFO_TOKEN=your_token_here`
3. Test: `curl http://localhost/api/whoami.php`

### 9. Run First Speed Test (2 minutes)

1. Open browser: `http://localhost/`
2. Click "Start Test"
3. Wait for completion (~30 seconds)
4. Check database:

```bash
mysql -u root -p speedtracker -e "SELECT id, city, isp_name, dl_mbps, ul_mbps, ping_ms FROM tests LIMIT 1;"
```

### 10. Verify Complete Installation

**Checklist:**
- [ ] Homepage loads without errors
- [ ] API endpoints return JSON
- [ ] Speed test completes successfully
- [ ] Test result saved in database
- [ ] Leaderboard displays data
- [ ] Share buttons work
- [ ] Language toggle (EN/UR) works
- [ ] Mobile responsive layout works
- [ ] Rate limiting activates after test

## Troubleshooting

### "Parse error" in PHP files
- **Cause:** PHP version < 8.0
- **Fix:** Upgrade PHP or use compatibility mode

### "Access denied for user"
- **Cause:** Wrong database credentials
- **Fix:** Check `.env` file and MySQL grants

### "Call to undefined function mysqli_connect"
- **Cause:** PHP MySQL extension not installed
- **Fix:** `sudo apt install php-mysql` (Ubuntu/Debian)

### Speed test stuck at "Initializing"
- **Cause:** JavaScript error or API unreachable
- **Fix:** Check browser console (F12) for errors

### ISP always shows "Unknown"
- **Cause:** No IPInfo token configured
- **Fix:** Add token to `.env` or use localhost testing

### Upload test fails immediately
- **Cause:** PHP upload limits too low
- **Fix:** Edit `php.ini`:
  ```ini
  upload_max_filesize = 20M
  post_max_size = 20M
  ```

### Cron jobs not running
- **Cause:** Wrong PHP path or permissions
- **Fix:** Use `which php` to find correct path

## Production Deployment

### Before going live:

1. **SSL Certificate:**
   ```bash
   sudo certbot --nginx -d yourdomain.com
   ```

2. **Update `.env`:**
   ```env
   ALLOWED_ORIGIN=https://yourdomain.com
   ```

3. **Security:**
   - Change `HASH_SALT` to production value
   - Use strong database password
   - Disable directory listing
   - Enable firewall (UFW/firewalld)

4. **Performance:**
   - Enable PHP opcache
   - Configure MySQL query cache
   - Use CDN for Bootstrap/icons (optional)

5. **Monitoring:**
   - Set up uptime monitoring
   - Configure error logging
   - Monitor disk space for logs

## Estimated Total Installation Time

**Development:** 20-30 minutes  
**Production:** 45-60 minutes (includes SSL, security hardening)

## Next Steps

- Read `README.md` for detailed documentation
- Customize branding in `components/header.php`
- Add more Pakistan cities to map coordinates
- Configure backup strategy for database

## Support

Check logs for errors:
```bash
# PHP errors
tail -f /var/log/apache2/error.log  # Apache
tail -f /var/log/nginx/error.log    # Nginx

# Application logs
tail -f /var/log/speedtracker-*.log

# MySQL errors
tail -f /var/log/mysql/error.log
```

Good luck! 🚀🇵🇰
