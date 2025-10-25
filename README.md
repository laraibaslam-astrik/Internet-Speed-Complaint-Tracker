# Pakistan Internet Speed Complaint Tracker

A comprehensive, production-ready web application for testing and tracking internet speeds across Pakistan. Built with pure PHP (no frameworks), Bootstrap 5, and vanilla JavaScript.

## 🌟 Features

- **Real-time Speed Testing**: Download, upload, ping, and jitter measurements
- **Multi-connection Testing**: 4 parallel connections for accurate bandwidth measurement
- **ISP Detection**: Automatic detection of ISP, city, and connection type
- **Interactive Heatmap**: City-level speed visualization across Pakistan
- **ISP Leaderboard**: Compare ISP performance in real-time
- **Anomaly Detection**: Automatic detection of latency spikes and speed drops
- **Bilingual Support**: English and Urdu (اردو) interface
- **Rate Limiting**: 10-minute cooldown between tests
- **Social Sharing**: Share results on Twitter, WhatsApp, and Facebook
- **Privacy-Focused**: No personal data stored, only anonymized network stats
- **Dark Mode**: Respects system preference
- **Accessibility**: WCAG 2.1 compliant, keyboard navigable

## 📋 Requirements

- PHP 8.0 or higher
- MySQL 8.0 or higher
- Apache or Nginx web server
- Optional: APCu extension for caching
- Optional: IPInfo.io API token for accurate ISP detection

## 🚀 Quick Start

### 1. Database Setup

```bash
# Import the database schema
mysql -u root -p < schema.sql

# Create database user
mysql -u root -p
CREATE USER 'speeduser'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON speedtracker.* TO 'speeduser'@'localhost';
FLUSH PRIVILEGES;
exit;
```

### 2. Configuration

```bash
# Copy environment template
cd public
cp .env.example .env

# Edit configuration
nano .env
```

Update `.env` with your settings:

```env
DB_HOST=localhost
DB_NAME=speedtracker
DB_USER=speeduser
DB_PASS=your_secure_password
HASH_SALT=change_this_to_random_string
IPINFO_TOKEN=your_ipinfo_token_optional
ALLOWED_ORIGIN=https://your-domain.com
RATE_LIMIT_WINDOW_SEC=600
RATE_LIMIT_MAX=2
```

**Important**: Change `HASH_SALT` to a random string for security.

### 3. Web Server Configuration

#### Apache

Create a virtual host or `.htaccess`:

```apache
<VirtualHost *:80>
    ServerName speedtest.yourdomain.com
    DocumentRoot /path/to/project/public
    
    <Directory /path/to/project/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        
        # Enable PHP
        <FilesMatch \.php$>
            SetHandler application/x-httpd-php
        </FilesMatch>
        
        # Rewrite rules for clean URLs
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^r/([a-zA-Z0-9]+)$ /result.php?id=$1 [L,QSA]
    </Directory>
</VirtualHost>
```

#### Nginx

```nginx
server {
    listen 80;
    server_name speedtest.yourdomain.com;
    root /path/to/project/public;
    index index.php index.html;
    
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
    
    location ~ /\. {
        deny all;
    }
}
```

### 4. Set Up Cron Jobs

```bash
# Edit crontab
crontab -e

# Add these lines:

# Daily rollups at midnight PKT (UTC+5)
0 0 * * * /usr/bin/php /path/to/project/public/cron/rollups_daily.php >> /var/log/speedtracker-rollups.log 2>&1

# Anomaly detection every 15 minutes
*/15 * * * * /usr/bin/php /path/to/project/public/cron/detect_anomalies.php >> /var/log/speedtracker-anomalies.log 2>&1
```

### 5. Permissions

```bash
# Set proper ownership
chown -R www-data:www-data /path/to/project/public

# Set secure permissions
find /path/to/project/public -type f -exec chmod 644 {} \;
find /path/to/project/public -type d -exec chmod 755 {} \;

# Protect sensitive files
chmod 600 /path/to/project/public/.env
```

### 6. Verify Installation

1. Visit your domain: `http://speedtest.yourdomain.com`
2. Check ISP detection: `http://speedtest.yourdomain.com/api/whoami.php`
3. Run a speed test
4. Check logs: `tail -f /var/log/speedtracker-*.log`

## 📁 Project Structure

```
public/
├── index.php              # Main application (component-based)
├── .env.example           # Environment configuration template
├── .env                   # Your configuration (not in git)
├── components/            # HTML components
│   ├── header.php         # Navigation and language toggle
│   ├── metrics.php        # Live metrics cards
│   ├── map.php            # Pakistan heatmap
│   ├── leaderboard.php    # ISP leaderboard
│   ├── share.php          # Social sharing bar
│   └── footer.php         # Footer and privacy notice
├── api/                   # API endpoints
│   ├── whoami.php         # ISP/location detection
│   ├── ping.php           # Ping test endpoint
│   ├── submit.php         # Test results submission
│   ├── leaderboard.php    # ISP rankings
│   ├── heatmap.php        # City-level aggregates
│   └── outages.php        # Anomaly reports
├── speed/                 # Speed test endpoints
│   ├── down.php           # Download test
│   └── up.php             # Upload test
├── lib/                   # Core libraries
│   ├── db.php             # Database connection
│   ├── util.php           # Utility functions
│   ├── rate_limit.php     # Rate limiting
│   └── geoip.php          # GeoIP lookup
└── cron/                  # Scheduled tasks
    ├── rollups_daily.php  # Daily aggregation
    └── detect_anomalies.php # Anomaly detection
```

## 🔧 API Endpoints

### Public Endpoints

- `GET /api/whoami.php` - Get client's ISP, city, ASN
- `GET /api/ping.php` - Server timestamp for ping calculation
- `GET /api/leaderboard.php?date=today&city=Karachi` - ISP rankings
- `GET /api/heatmap.php?date=today` - City-level speed data
- `GET /api/outages.php?date=today&city=Karachi` - Network anomalies
- `GET /speed/down.php?b=4` - Download test (4MB)
- `POST /speed/up.php` - Upload test (accepts binary data)
- `POST /api/submit.php` - Submit test results (JSON)

### Request Examples

**Submit Test Results:**
```bash
curl -X POST https://your-domain.com/api/submit.php \
  -H "Content-Type: application/json" \
  -d '{
    "dl_mbps": 45.5,
    "ul_mbps": 10.2,
    "ping_ms": 35.5,
    "jitter_ms": 5.2,
    "sample_ms": 30000,
    "isp_name": "PTCL",
    "asn": 23674,
    "city": "Karachi",
    "tech": "4G",
    "device_type": "mobile"
  }'
```

**Get Leaderboard:**
```bash
curl https://your-domain.com/api/leaderboard.php?date=today
```

## 🔐 Security Features

- **IP Hashing**: IP addresses are salted and hashed (SHA-256)
- **Rate Limiting**: 2 tests per 10 minutes per IP
- **Input Validation**: All inputs sanitized and validated
- **Prepared Statements**: SQL injection protection
- **CORS Protection**: Same-origin policy
- **No PII Storage**: Zero personal data collection

### Rotating Hash Salt (Recommended)

```bash
# Generate new salt daily
echo "HASH_SALT=$(openssl rand -hex 32)" >> /path/to/.env.new

# Keep old salt for 24 hours for overlap
# Then replace .env with .env.new
```

## 📊 Data Retention

- **Raw Tests**: 90 days (configurable in `rollups_daily.php`)
- **Daily Rollups**: Indefinite
- **Anomalies**: 7 days

## 🎨 Customization

### Branding

Edit `public/components/header.php`:
```php
<a class="navbar-brand fw-bold" href="/">
    <i class="bi bi-speedometer2 me-2"></i>
    Your Brand Name
</a>
```

### Colors

Edit `public/index.php` CSS variables:
```css
:root {
    --primary-color: #0d6efd;
    --success-color: #198754;
    /* ... */
}
```

### City Coordinates

Edit `public/index.php`, search for `const cities` and update coordinates.

## 🐛 Troubleshooting

### Issue: "Database connection failed"

- Check MySQL is running: `systemctl status mysql`
- Verify credentials in `.env`
- Test connection: `mysql -u speeduser -p speedtracker`

### Issue: "Rate limited" immediately

- Clear localStorage in browser dev tools
- Check `tests` table for duplicate `hash_ip`
- Adjust `RATE_LIMIT_MAX` in `.env`

### Issue: ISP shows "Unknown"

- Get IPInfo.io token (free tier available)
- Add to `.env`: `IPINFO_TOKEN=your_token`
- Test: `curl https://your-domain.com/api/whoami.php`

### Issue: Anomalies not detected

- Verify cron job is running: `grep CRON /var/log/syslog`
- Run manually: `php /path/to/cron/detect_anomalies.php`
- Check logs: `tail -f /var/log/speedtracker-anomalies.log`

### Issue: Upload test fails

- Increase PHP `upload_max_filesize` and `post_max_size`
- Check `php.ini`: `upload_max_filesize = 20M`
- Restart PHP-FPM: `systemctl restart php8.1-fpm`

## 🌐 Production Checklist

- [ ] Change `HASH_SALT` to random value
- [ ] Set strong database password
- [ ] Configure HTTPS/SSL certificate
- [ ] Set `ALLOWED_ORIGIN` to your domain
- [ ] Enable PHP opcache
- [ ] Set up monitoring (e.g., UptimeRobot)
- [ ] Configure backup for database
- [ ] Test on mobile devices
- [ ] Validate WCAG compliance
- [ ] Set up error logging
- [ ] Add rate limiting at web server level
- [ ] Configure CDN (optional)

## 📝 License

This project is open source and available for free use. No warranty provided.

## 🤝 Contributing

Contributions welcome! Areas for improvement:

- More accurate Pakistan city coordinates
- Additional ISP detection patterns
- Performance optimizations
- Translations for other languages
- Better mobile UI

## 📞 Support

For issues and questions, please check:

1. This README
2. Troubleshooting section
3. PHP/MySQL error logs
4. Browser console for JavaScript errors

## 🙏 Credits

Built for Pakistan 🇵🇰 with ❤️

Technologies:
- Bootstrap 5
- PHP 8+
- MySQL 8+
- Canvas API for visualizations
