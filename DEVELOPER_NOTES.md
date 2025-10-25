# Developer Notes - Pakistan Internet Speed Tracker

## About APCu Lint Warnings

**Status:** ✅ **Safe to ignore**

The IDE shows warnings for `apcu_fetch()` and `apcu_store()` in `public/lib/geoip.php`. This is expected and intentional.

### Why?
APCu is an **optional** PHP extension for in-memory caching. The code is written to work both WITH and WITHOUT it:

```php
// Safe pattern - checks existence before use
if (function_exists('apcu_fetch')) {
    $cached = apcu_fetch($cache_key);
    // ... use cache
}
```

### Should I install APCu?
**Recommended but not required:**
- ✅ **With APCu:** GeoIP lookups cached for 5 minutes → faster responses
- ✅ **Without APCu:** Direct API calls every time → still works fine

### Install APCu (Optional)
```bash
# Ubuntu/Debian
sudo apt install php-apcu
sudo systemctl restart apache2

# Verify
php -m | grep apcu
```

---

## Code Quality Standards

### PHP
- ✅ PHP 8.0+ syntax
- ✅ Strict type declarations where appropriate
- ✅ Prepared statements for all SQL queries
- ✅ Input validation and sanitization
- ✅ Error logging (not displayed to users)
- ✅ Consistent naming conventions (snake_case for functions)

### JavaScript
- ✅ ES6+ syntax (const/let, arrow functions, async/await)
- ✅ No jQuery dependency
- ✅ Vanilla JS only
- ✅ Progressive enhancement
- ✅ Error handling with try/catch
- ✅ Console logging for debugging

### HTML/CSS
- ✅ Semantic HTML5
- ✅ Bootstrap 5 utility classes
- ✅ Mobile-first responsive design
- ✅ Accessibility attributes (ARIA)
- ✅ Valid markup
- ✅ Clean separation of concerns

---

## Architecture Decisions

### 1. Why Component-Based PHP (not a Framework)?
**Pros:**
- Simple to understand
- No learning curve
- Easy deployment
- Low resource usage
- Single-file components

**Cons:**
- No built-in routing
- Manual dependency management
- No ORM

**Decision:** For this project, simplicity wins. The component approach gives structure without framework overhead.

### 2. Why No JavaScript Framework?
**Pros:**
- Faster page loads
- No build step
- Easier debugging
- Lower barrier to contribution

**Cons:**
- More verbose code
- Manual DOM manipulation
- No reactive state management

**Decision:** Vanilla JS is sufficient for this use case. The app is primarily about running tests and displaying results, not complex state management.

### 3. Why Canvas (not SVG or Leaflet)?
**Pros:**
- Lightweight (~50 lines of code)
- Fast rendering
- Full control
- No external dependencies

**Cons:**
- Not interactive (can't click bubbles)
- Lower quality on zoom
- Manual coordinate mapping

**Decision:** For a simple heatmap visualization, Canvas provides the best performance-to-complexity ratio.

### 4. Why MySQL (not PostgreSQL)?
**Pros:**
- Widely available on shared hosting
- Familiar to most developers
- Good performance for this workload
- JSON column support

**Cons:**
- Less advanced features than PostgreSQL

**Decision:** MySQL availability and familiarity make it the pragmatic choice.

---

## Performance Considerations

### Database Indexes
```sql
-- Critical indexes for query performance
INDEX idx_tests_date_city_isp (ts, city, isp_name)  -- Leaderboard queries
INDEX idx_tests_hash_ip_ts (hash_ip, ts)            -- Rate limiting
```

**Why these indexes?**
- `leaderboard.php` filters by date, groups by city/ISP
- `rate_limit.php` checks recent tests by IP hash
- Without indexes, queries would scan entire table (slow)

### Caching Strategy
1. **APCu:** GeoIP lookups (5 min TTL)
2. **Browser:** Static assets (1 month via .htaccess)
3. **None:** Speed test results (must be real-time)

### Query Optimization
- Rollups table reduces leaderboard query complexity
- P95 calculation done once daily (not per request)
- LIMIT clauses on all unbounded queries

---

## Security Deep Dive

### 1. Rate Limiting
**Implementation:** Database-backed counter per IP hash

**Why not session-based?**
- Sessions can be cleared
- Shared IPs need unified limits
- Survives page refresh

**Attack surface:**
- User could rotate IPs via VPN
- Mitigation: 10-minute window is reasonable tradeoff

### 2. IP Hashing
**Current:** SHA-256(IP + SALT)

**Why hash (not encrypt)?**
- One-way function (can't reverse)
- Same IP always produces same hash (for rate limiting)
- Salt prevents rainbow table attacks

**Salt rotation strategy:**
```bash
# Daily cron (optional)
0 0 * * * /usr/local/bin/rotate_salt.sh
```

```bash
#!/bin/bash
# rotate_salt.sh
NEW_SALT=$(openssl rand -hex 32)
echo "HASH_SALT=$NEW_SALT" >> /path/to/.env.new
# Keep old salt for 24h for overlap, then swap
```

### 3. SQL Injection Prevention
**Method:** Prepared statements with bound parameters

```php
// ✅ SAFE
$stmt->prepare("SELECT * FROM tests WHERE city = ?");
$stmt->bind_param('s', $city);

// ❌ VULNERABLE (not used in this project)
$query = "SELECT * FROM tests WHERE city = '$city'";
```

### 4. XSS Prevention
**Methods:**
- `htmlspecialchars()` on all output
- `strip_tags()` on input
- Content-Security-Policy header

---

## Testing Strategy

### Manual Testing Checklist
- [ ] Fresh installation on clean server
- [ ] All API endpoints return valid JSON
- [ ] Speed test completes without errors
- [ ] Rate limiting activates correctly
- [ ] Language toggle works
- [ ] Social sharing buttons generate correct URLs
- [ ] Mobile responsive on iPhone/Android
- [ ] Dark mode respects system preference
- [ ] Keyboard navigation works
- [ ] Screen reader announces state changes

### Load Testing (Recommended Tools)
```bash
# Apache Bench - simple load test
ab -n 1000 -c 10 http://localhost/api/leaderboard.php

# Expected: <100ms average response time
```

### Browser Compatibility
**Tested:**
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

**Required Features:**
- ES6 (const/let, arrow functions)
- Fetch API
- Canvas 2D
- localStorage
- async/await

**Fallbacks:**
- No Fetch → show error message
- No Canvas → map section hidden
- No localStorage → language defaults to EN

---

## Deployment Environments

### Local Development (XAMPP/WAMP)
```
htdocs/speedtracker/public/
- No virtual host needed
- Access via http://localhost/speedtracker/
- MySQL via phpMyAdmin
```

### Shared Hosting (cPanel)
```
public_html/
- Upload via FTP
- Database via phpMyAdmin
- Cron via cPanel interface
- SSL via AutoSSL
```

### VPS (Ubuntu 22.04)
```
/var/www/speedtracker/public/
- Nginx + PHP-FPM
- MySQL 8+
- Let's Encrypt SSL
- Cron via crontab
```

---

## Monitoring & Maintenance

### Log Files to Watch
```bash
# Application logs
tail -f /var/log/speedtracker-*.log

# Web server logs
tail -f /var/log/nginx/error.log
tail -f /var/log/apache2/error.log

# PHP logs
tail -f /var/log/php8.1-fpm.log

# MySQL slow queries
tail -f /var/log/mysql/slow.log
```

### Database Maintenance
```sql
-- Weekly: check table sizes
SELECT 
    table_name, 
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
FROM information_schema.TABLES
WHERE table_schema = 'speedtracker';

-- Monthly: optimize tables
OPTIMIZE TABLE tests;
OPTIMIZE TABLE rollups_daily;
OPTIMIZE TABLE outages;

-- Check for anomalies in data
SELECT DATE(ts), COUNT(*) FROM tests GROUP BY DATE(ts) ORDER BY ts DESC LIMIT 30;
```

### Backup Strategy
```bash
# Daily automated backup
0 2 * * * mysqldump -u root -p speedtracker | gzip > /backups/speedtracker-$(date +\%Y\%m\%d).sql.gz

# Keep last 30 days
0 3 * * * find /backups/ -name "speedtracker-*.sql.gz" -mtime +30 -delete
```

---

## Common Customizations

### 1. Add New City to Map
Edit `public/index.php`, find `const cities`:
```javascript
const cities = {
    'Karachi': { x: 0.35, y: 0.85 },
    'YourCity': { x: 0.40, y: 0.60 },  // Add here
    // ...
};
```

### 2. Change Rate Limit
Edit `public/.env`:
```env
RATE_LIMIT_WINDOW_SEC=600  # Change to 300 for 5 minutes
RATE_LIMIT_MAX=2           # Change to 5 for 5 tests
```

### 3. Modify Speed Thresholds
Edit `public/index.php`, find `hashToColor`:
```javascript
function hashToColor(str, speed = 0) {
    if (speed < 5) return '#dc3545';   // Change 5 to new threshold
    if (speed < 20) return '#ffc107';  // Change 20 to new threshold
    return '#198754';
}
```

### 4. Add New ISP Detection Pattern
Edit `public/lib/geoip.php`, find `guess_pakistan_isp`:
```php
$pak_isps = [
    'PTCL' => [23674],
    'YourISP' => [12345],  // Add ASN here
];
```

### 5. Change Data Retention
Edit `public/cron/rollups_daily.php`:
```php
$cutoff_date = date('Y-m-d', strtotime('-90 days'));  // Change 90 to desired days
```

---

## Troubleshooting Reference

### Problem: High Memory Usage
**Cause:** Large result sets in queries  
**Fix:** Add pagination, reduce LIMIT

### Problem: Slow Leaderboard Load
**Cause:** Missing indexes  
**Fix:** Run `SHOW INDEX FROM tests;` and compare with schema.sql

### Problem: Anomalies Not Detected
**Cause:** Insufficient baseline data  
**Fix:** Requires 20+ tests in 7-day window

### Problem: Upload Test Timeout
**Cause:** PHP execution time too low  
**Fix:** Increase `max_execution_time` in php.ini

### Problem: CORS Errors
**Cause:** Mismatched ALLOWED_ORIGIN  
**Fix:** Update .env with correct domain

---

## Code Metrics

### Complexity Analysis
- **Cyclomatic Complexity:** Low (mostly linear code paths)
- **Coupling:** Low (components are independent)
- **Cohesion:** High (functions have single responsibility)

### File Sizes
- Largest PHP file: `index.php` (~900 lines) - Main app logic
- Largest component: `metrics.php` (~150 lines) - Metrics cards
- Total codebase: ~3,500 lines

### Performance Targets
- API response time: <100ms
- Page load time: <2s on 3G
- Speed test duration: 25-35s
- Database query time: <50ms average

---

## Contributing Guidelines

### Code Style
- PHP: PSR-12 standard
- JavaScript: Airbnb style guide (simplified)
- Indentation: 4 spaces
- Line length: 120 characters max

### Commit Message Format
```
type(scope): subject

body (optional)

footer (optional)
```

**Types:** feat, fix, docs, style, refactor, test, chore

**Example:**
```
feat(api): add IPv6 detection to whoami endpoint

- Detect IPv6 addresses
- Return ip_version field in response
- Update tests for IPv6 support

Closes #42
```

### Pull Request Process
1. Fork repository
2. Create feature branch
3. Make changes with tests
4. Update documentation
5. Submit PR with description

---

## Future Roadmap Ideas

### Phase 2 (Next Quarter)
- [ ] User accounts (optional, for history)
- [ ] Test scheduling (run test daily)
- [ ] Email notifications for outages
- [ ] Advanced filtering on leaderboard
- [ ] Export data as CSV/JSON

### Phase 3 (6-12 Months)
- [ ] Mobile app (PWA or native)
- [ ] Public API with rate limiting
- [ ] GraphQL endpoint
- [ ] Real-time dashboard with WebSockets
- [ ] Machine learning for anomaly detection

### Nice to Have
- [ ] ISP comparison tool
- [ ] Historical trend charts
- [ ] City-to-city latency matrix
- [ ] CDN performance testing
- [ ] DNS resolution time tracking

---

## License & Credits

**License:** Open source (MIT/GPL/Apache - choose one)

**Built With:**
- Bootstrap 5 by Twitter
- Bootstrap Icons
- PHP MySQL driver
- Canvas API

**Inspiration:**
- Speedtest.net (Ookla)
- Fast.com (Netflix)
- M-Lab Speed Test

**Made for Pakistan** 🇵🇰 with ❤️

---

## Contact & Support

**Issues:** Check troubleshooting sections in README and INSTALL docs first

**Documentation:** All docs in project root
- `README.md` - Overview
- `INSTALL.md` - Installation
- `PROJECT_SUMMARY.md` - Technical details
- `DEVELOPER_NOTES.md` - This file

**Community:** (Add your community links here)
- GitHub: (your repo)
- Discord: (optional)
- Forum: (optional)

---

*Last Updated: 2024-01-26*
