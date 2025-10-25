# Admin Dashboard Setup Guide

## 🚀 Quick Setup (5 Minutes)

### Step 1: Import Analytics Schema

```bash
# Import the analytics schema
mysql -u root -p speedtracker < analytics_schema.sql
```

This creates tables:
- `visitor_sessions` - Detailed visitor tracking
- `pageviews` - Page view tracking
- `click_events` - User interaction tracking
- `online_users` - Real-time online users
- `admin_users` - Admin authentication

### Step 2: Access Admin Panel

```
https://your-domain.com/admin/login.php
```

**Default Credentials:**
- Username: `admin`
- Password: `admin123`

⚠️ **IMPORTANT:** Change password immediately after first login!

### Step 3: Enable Tracking

Add this to your `index.php` (bottom of file, before `</body>`):

```html
<script>
// Advanced Analytics Tracking
(function() {
    let sessionId = null;
    
    // Initialize tracking
    fetch('/track.php?action=init', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `page_url=${encodeURIComponent(window.location.pathname)}&page_title=${encodeURIComponent(document.title)}`
    })
    .then(r => r.json())
    .then(data => {
        sessionId = data.session_id;
    });
    
    // Track clicks
    document.addEventListener('click', (e) => {
        if (!sessionId) return;
        
        fetch('/track.php?action=event', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `session_id=${sessionId}&event_type=click&element_id=${e.target.id || ''}&element_class=${e.target.className || ''}&element_text=${encodeURIComponent(e.target.innerText?.substring(0, 100) || '')}`
        });
    });
    
    // Heartbeat every 30 seconds
    setInterval(() => {
        if (sessionId) {
            fetch('/track.php?action=heartbeat', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `session_id=${sessionId}`
            });
        }
    }, 30000);
})();
</script>
```

## 📊 Dashboard Features

### Main Dashboard
- **Real-time stats** - Total visitors, today's visitors, online users
- **Recent visitors list** - IP, location, ISP, device, status
- **Traffic charts** - Hourly visitor trends
- **Geographic breakdown** - Top countries

### Detailed Tracking Includes:
✅ IP Address (exact)  
✅ Geographic Location (city, region, country, lat/long, postal code)  
✅ ISP Information (name, ASN, connection type)  
✅ Device Details (type, OS, browser, screen resolution)  
✅ User Behavior (pageviews, time on site, click tracking)  
✅ Referrer tracking  
✅ Real-time online status  

## 🔐 Security

### Change Default Password

```sql
-- Generate new password hash
-- In PHP: password_hash('your_new_password', PASSWORD_DEFAULT)

UPDATE admin_users 
SET password_hash = '$2y$10$YOUR_NEW_HASH_HERE'
WHERE username = 'admin';
```

### Create New Admin User

```sql
INSERT INTO admin_users (username, password_hash, role, email)
VALUES ('newadmin', '$2y$10$HASH_HERE', 'admin', 'email@example.com');
```

## 🎯 Privacy Compliance

⚠️ **CRITICAL:** This system tracks detailed user information. You MUST:

1. **Update Privacy Policy**
   - Disclose what data you collect
   - Explain why you collect it
   - State data retention period

2. **Add Cookie Consent**
   - Implement cookie banner
   - Get user consent before tracking
   - Provide opt-out mechanism

3. **GDPR Compliance** (if EU visitors)
   - Allow users to request their data
   - Implement data deletion on request
   - Keep data processing records

4. **Data Retention**
   - Default: 30 days (set in analytics.php)
   - Adjust based on your needs
   - Run cleanup regularly

### Disable Tracking for Specific IPs

```sql
-- Add to your code
$excluded_ips = ['127.0.0.1', 'YOUR_IP_HERE'];
if (!in_array(client_ip(), $excluded_ips)) {
    track_visitor();
}
```

## 📈 Advanced Features

### Export Data

```sql
-- Export visitor data as CSV
SELECT 
    ip_address,
    city,
    country,
    isp_name,
    first_visit,
    total_pageviews
FROM visitor_sessions
INTO OUTFILE '/tmp/visitors.csv'
FIELDS TERMINATED BY ','
ENCLOSED BY '"'
LINES TERMINATED BY '\n';
```

### Real-time Dashboard

Access: `/admin/realtime.php` (create this for live visitor feed)

### Custom Reports

Dashboard menu includes:
- Visitors (detailed list)
- Real-time (live activity)
- Speed Tests (all test results)
- Analytics (advanced charts)
- Settings (configuration)

## 🔧 Troubleshooting

### Issue: No visitors tracked

**Check:**
1. Analytics schema imported? `SHOW TABLES LIKE 'visitor_sessions'`
2. Tracking script added to index.php?
3. `/track.php` accessible?
4. Check browser console for errors

### Issue: Geographic data shows "Unknown"

**Fix:**
1. Check `allow_url_fopen` enabled
2. Verify external API access
3. Check firewall rules

### Issue: Can't login

**Reset password:**
```bash
php -r "echo password_hash('newpassword', PASSWORD_DEFAULT);"
# Copy hash and run:
mysql -u root -p speedtracker -e "UPDATE admin_users SET password_hash='HASH_HERE' WHERE username='admin';"
```

## 🗑️ Cleanup & Maintenance

### Auto-cleanup (add to cron)

```bash
# Add to crontab -e
0 2 * * * php /path/to/public/cleanup_analytics.php
```

Create `cleanup_analytics.php`:
```php
<?php
require_once __DIR__ . '/lib/analytics.php';
cleanup_old_sessions();
echo "Cleanup completed: " . date('Y-m-d H:i:s') . "\n";
```

### Manual cleanup

```sql
-- Remove old sessions (>30 days)
DELETE FROM visitor_sessions 
WHERE first_visit < DATE_SUB(NOW(), INTERVAL 30 DAY);

-- Remove offline users
DELETE FROM online_users 
WHERE last_ping < DATE_SUB(NOW(), INTERVAL 5 MINUTE);
```

## 📞 Support

Dashboard shows real-time metrics updated every 30 seconds.

For production use:
1. Enable HTTPS
2. Change default credentials
3. Update privacy policy
4. Implement cookie consent
5. Regular backups

---

**Dashboard URL:** `/admin/login.php`  
**Default Login:** admin / admin123  
**⚠️ Change password immediately!**
