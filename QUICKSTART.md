# 🚀 Quick Start - 5 Minutes to Running

## Prerequisites
- PHP 8.0+ and MySQL 8.0+ installed
- Web server running (Apache/Nginx/XAMPP/WAMP)

## Setup in 5 Steps

### 1️⃣ Import Database (1 min)
```bash
mysql -u root -p < schema.sql
```

### 2️⃣ Configure Environment (1 min)
```bash
cd public/
cp .env.example .env
```

Edit `.env` - minimum required:
```env
DB_PASS=your_mysql_password
HASH_SALT=change_to_random_string
```

### 3️⃣ Set Permissions (30 sec)
```bash
chmod 600 public/.env
```

### 4️⃣ Test Database Connection (30 sec)
```bash
php -r "require 'public/lib/db.php'; echo get_db_connection() ? '✓ Connected' : '✗ Failed';"
```

### 5️⃣ Open Browser (1 min)
```
http://localhost/public/index.php
```

Click **"Start Test"** - done! 🎉

---

## Full Setup (with cron jobs)

### Add Cron Jobs
```bash
crontab -e
```

Add:
```cron
0 0 * * * /usr/bin/php /path/to/public/cron/rollups_daily.php >> /var/log/speedtracker-rollups.log 2>&1
*/15 * * * * /usr/bin/php /path/to/public/cron/detect_anomalies.php >> /var/log/speedtracker-anomalies.log 2>&1
```

---

## Verify Everything Works

✅ Homepage loads: `http://localhost/public/`  
✅ ISP detected: `http://localhost/public/api/whoami.php`  
✅ Leaderboard: `http://localhost/public/api/leaderboard.php?date=today`  
✅ Speed test completes (click "Start Test")  
✅ Language toggle works (EN/UR)  
✅ Rate limit activates (10 min cooldown after test)

---

## Common Issues

**"Database connection failed"**
- Check MySQL is running: `systemctl status mysql`
- Verify password in `.env`

**"ISP shows Unknown"**
- Normal for localhost
- Get free IPInfo token: https://ipinfo.io/signup
- Add to `.env`: `IPINFO_TOKEN=your_token`

**"PHP Parse error"**
- Check PHP version: `php -v` (need 8.0+)

---

## Next Steps

1. **Customize:** Edit `components/header.php` for branding
2. **Deploy:** See `INSTALL.md` for production setup
3. **Monitor:** Check `README.md` for maintenance guide

---

## File Structure Overview
```
public/
├── index.php          ← Main app (start here)
├── .env               ← Your config
├── components/        ← HTML pieces
├── api/              ← REST endpoints
├── speed/            ← Test endpoints
├── lib/              ← Core logic
└── cron/             ← Background jobs
```

---

## Tech Stack
- **Backend:** Pure PHP 8+ (no frameworks)
- **Frontend:** Bootstrap 5 + Vanilla JS
- **Database:** MySQL 8+
- **Dependencies:** Zero (just CDN links)

---

## Production Checklist
- [ ] Change `HASH_SALT` to random value
- [ ] Set strong database password
- [ ] Configure SSL certificate
- [ ] Update `ALLOWED_ORIGIN` in `.env`
- [ ] Set up cron jobs
- [ ] Enable error logging
- [ ] Test on mobile devices

---

## Documentation

📖 **Full docs:**
- `README.md` - Complete documentation
- `INSTALL.md` - Detailed installation
- `PROJECT_SUMMARY.md` - Technical overview
- `DEVELOPER_NOTES.md` - Code insights

💬 **Need help?** Check troubleshooting sections in docs above

---

**Built for Pakistan** 🇵🇰 | **Made with** ❤️

*Estimated setup time: 5 minutes (basic) | 30 minutes (full with cron)*
