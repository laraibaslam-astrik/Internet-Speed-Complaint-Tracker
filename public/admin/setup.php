<?php
/**
 * Admin Setup & Debug Tool
 * Automatically fixes admin login issues
 * DELETE THIS FILE AFTER SETUP!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../lib/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 2rem; }
        .setup-card { background: white; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-width: 700px; margin: 0 auto; }
        .success { color: #10b981; }
        .error { color: #ef4444; }
        pre { background: #f8f9fa; padding: 1rem; border-radius: 8px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="setup-card p-5">
        <h2 class="mb-4">🔧 Admin Setup & Debug</h2>
        
        <?php
        $conn = get_db_connection();
        $issues = [];
        $fixes_applied = [];
        
        // Check 1: Database Connection
        echo "<div class='mb-3'>";
        echo "<h5>1. Database Connection</h5>";
        if (!$conn) {
            echo "<div class='error'>❌ Database connection FAILED!</div>";
            echo "<p class='small text-muted'>Check your .env file credentials</p>";
            $issues[] = "database_connection";
        } else {
            echo "<div class='success'>✓ Database connected</div>";
        }
        echo "</div>";
        
        if ($conn) {
            // Check 2: admin_users table exists
            echo "<div class='mb-3'>";
            echo "<h5>2. Admin Users Table</h5>";
            $result = $conn->query("SHOW TABLES LIKE 'admin_users'");
            
            if ($result->num_rows === 0) {
                echo "<div class='error'>❌ Table 'admin_users' does NOT exist</div>";
                $issues[] = "missing_table";
                
                // Auto-create table
                if (isset($_POST['create_table'])) {
                    $sql = "
                    CREATE TABLE admin_users (
                        id INT PRIMARY KEY AUTO_INCREMENT,
                        username VARCHAR(64) UNIQUE NOT NULL,
                        password_hash VARCHAR(255) NOT NULL,
                        email VARCHAR(255),
                        role ENUM('admin', 'viewer') DEFAULT 'viewer',
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        last_login TIMESTAMP NULL,
                        is_active BOOLEAN DEFAULT TRUE,
                        INDEX idx_username (username)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                    ";
                    
                    if ($conn->query($sql)) {
                        echo "<div class='alert alert-success mt-2'>✓ Table created successfully!</div>";
                        $fixes_applied[] = "table_created";
                    } else {
                        echo "<div class='alert alert-danger mt-2'>Failed to create table: " . $conn->error . "</div>";
                    }
                } else {
                    echo "<form method='post' class='mt-2'>";
                    echo "<button type='submit' name='create_table' class='btn btn-primary'>Create Table Now</button>";
                    echo "</form>";
                }
            } else {
                echo "<div class='success'>✓ Table 'admin_users' exists</div>";
                
                // Check 3: Admin user exists
                echo "</div><div class='mb-3'>";
                echo "<h5>3. Admin User</h5>";
                
                $result = $conn->query("SELECT id, username, role, is_active FROM admin_users WHERE username = 'admin'");
                
                if ($result->num_rows === 0) {
                    echo "<div class='error'>❌ Admin user NOT found</div>";
                    $issues[] = "missing_admin";
                    
                    // Auto-create admin
                    if (isset($_POST['create_admin'])) {
                        $hash = password_hash('admin123', PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("INSERT INTO admin_users (username, password_hash, role) VALUES ('admin', ?, 'admin')");
                        $stmt->bind_param('s', $hash);
                        
                        if ($stmt->execute()) {
                            echo "<div class='alert alert-success mt-2'>";
                            echo "<strong>✓ Admin user created!</strong><br>";
                            echo "Username: <code>admin</code><br>";
                            echo "Password: <code>admin123</code><br>";
                            echo "<span class='text-danger'>⚠️ Change password immediately!</span>";
                            echo "</div>";
                            $fixes_applied[] = "admin_created";
                        } else {
                            echo "<div class='alert alert-danger mt-2'>Failed: " . $stmt->error . "</div>";
                        }
                        $stmt->close();
                    } else {
                        echo "<form method='post' class='mt-2'>";
                        echo "<button type='submit' name='create_admin' class='btn btn-success'>Create Admin User</button>";
                        echo "</form>";
                    }
                } else {
                    $admin = $result->fetch_assoc();
                    echo "<div class='success'>✓ Admin user exists</div>";
                    echo "<div class='mt-2'>";
                    echo "<strong>Username:</strong> " . htmlspecialchars($admin['username']) . "<br>";
                    echo "<strong>Role:</strong> " . htmlspecialchars($admin['role']) . "<br>";
                    echo "<strong>Status:</strong> " . ($admin['is_active'] ? '<span class="text-success">Active</span>' : '<span class="text-danger">Inactive</span>');
                    echo "</div>";
                    
                    // Password reset option
                    if (isset($_POST['reset_password'])) {
                        $new_password = $_POST['new_password'] ?? 'admin123';
                        $hash = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("UPDATE admin_users SET password_hash = ? WHERE username = 'admin'");
                        $stmt->bind_param('s', $hash);
                        
                        if ($stmt->execute()) {
                            echo "<div class='alert alert-success mt-3'>";
                            echo "✓ Password reset successful!<br>";
                            echo "New password: <code>" . htmlspecialchars($new_password) . "</code>";
                            echo "</div>";
                        }
                        $stmt->close();
                    }
                    
                    echo "<div class='mt-3'>";
                    echo "<button class='btn btn-warning btn-sm' onclick='document.getElementById(\"resetForm\").style.display=\"block\"'>";
                    echo "Reset Password";
                    echo "</button>";
                    echo "<form method='post' id='resetForm' style='display:none;' class='mt-2'>";
                    echo "<input type='text' name='new_password' class='form-control mb-2' placeholder='New password' value='admin123'>";
                    echo "<button type='submit' name='reset_password' class='btn btn-warning'>Confirm Reset</button>";
                    echo "</form>";
                    echo "</div>";
                }
            }
            echo "</div>";
        }
        
        // Summary
        echo "<hr>";
        echo "<div class='mb-3'>";
        echo "<h5>Summary</h5>";
        
        if (empty($issues)) {
            echo "<div class='alert alert-success'>";
            echo "<strong>✓ All checks passed!</strong><br>";
            echo "You can now login with:<br>";
            echo "<code>Username: admin</code><br>";
            echo "<code>Password: admin123</code><br><br>";
            echo "<a href='login.php' class='btn btn-primary'>Go to Login Page</a>";
            echo "</div>";
        } else {
            echo "<div class='alert alert-warning'>";
            echo "<strong>Issues found:</strong> " . count($issues) . "<br>";
            echo "Click the buttons above to fix them automatically.";
            echo "</div>";
        }
        
        if (!empty($fixes_applied)) {
            echo "<div class='alert alert-info mt-2'>";
            echo "<strong>Fixes applied:</strong><br>";
            foreach ($fixes_applied as $fix) {
                echo "• " . str_replace('_', ' ', ucfirst($fix)) . "<br>";
            }
            echo "<br><a href='setup.php' class='btn btn-sm btn-secondary'>Refresh Page</a>";
            echo "</div>";
        }
        echo "</div>";
        
        // Quick import option
        echo "<hr>";
        echo "<div class='mb-3'>";
        echo "<h5>Quick Import (Alternative)</h5>";
        echo "<p class='small text-muted'>If you prefer to import the full analytics schema:</p>";
        echo "<pre>mysql -u root -p speedtracker < analytics_schema.sql</pre>";
        echo "</div>";
        ?>
        
        <div class="alert alert-danger mt-4">
            <strong>⚠️ Security Warning:</strong> DELETE this file after setup!<br>
            <code>rm public/admin/setup.php</code>
        </div>
        
        <div class="text-center mt-4">
            <a href="/" class="btn btn-outline-secondary">← Back to Website</a>
            <a href="login.php" class="btn btn-primary ms-2">Go to Login</a>
        </div>
    </div>
</body>
</html>
