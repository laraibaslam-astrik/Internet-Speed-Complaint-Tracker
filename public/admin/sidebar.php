<!-- Sidebar Component -->
<div class="sidebar text-white p-4" style="width: 250px;">
    <h4 class="mb-4"><i class="bi bi-speedometer2 me-2"></i>Speed Tracker</h4>
    
    <nav class="nav flex-column">
        <a class="nav-link text-white" href="dashboard.php">
            <i class="bi bi-house-door me-2"></i>Dashboard
        </a>
        <a class="nav-link text-white" href="visitors.php">
            <i class="bi bi-people me-2"></i>Visitors
        </a>
        <a class="nav-link text-white" href="realtime.php">
            <i class="bi bi-broadcast me-2"></i>Real-time
        </a>
        <a class="nav-link text-white" href="tests.php">
            <i class="bi bi-speedometer me-2"></i>Speed Tests
        </a>
        <a class="nav-link text-white" href="analytics.php">
            <i class="bi bi-graph-up me-2"></i>Analytics
        </a>
        <a class="nav-link text-white" href="settings.php">
            <i class="bi bi-gear me-2"></i>Settings
        </a>
    </nav>
    
    <div class="position-absolute bottom-0 mb-4">
        <div class="text-white-50 small">Logged in as:</div>
        <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></div>
        <a href="logout.php" class="btn btn-sm btn-outline-light mt-2">
            <i class="bi bi-box-arrow-right me-1"></i>Logout
        </a>
    </div>
</div>
