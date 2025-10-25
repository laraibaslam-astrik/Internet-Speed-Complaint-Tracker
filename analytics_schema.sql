-- Admin Dashboard Analytics Schema
-- Complete tracking system with detailed visitor information

-- Visitor sessions table
CREATE TABLE IF NOT EXISTS visitor_sessions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    session_id VARCHAR(64) UNIQUE NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    device_type VARCHAR(32),
    browser VARCHAR(64),
    browser_version VARCHAR(32),
    os VARCHAR(64),
    os_version VARCHAR(32),
    
    -- Geographic data
    country VARCHAR(64),
    country_code CHAR(2),
    region VARCHAR(128),
    city VARCHAR(128),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    timezone VARCHAR(64),
    postal_code VARCHAR(16),
    
    -- ISP data
    isp_name VARCHAR(128),
    asn INT,
    connection_type VARCHAR(32),
    
    -- Session data
    first_visit TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    total_pageviews INT DEFAULT 1,
    total_duration_seconds INT DEFAULT 0,
    
    -- Referrer
    referrer_url TEXT,
    referrer_domain VARCHAR(255),
    landing_page VARCHAR(512),
    
    -- Device details
    screen_resolution VARCHAR(32),
    screen_color_depth TINYINT,
    language VARCHAR(16),
    
    INDEX idx_session_id (session_id),
    INDEX idx_ip (ip_address),
    INDEX idx_timestamp (first_visit),
    INDEX idx_city (city),
    INDEX idx_country (country_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Page views tracking
CREATE TABLE IF NOT EXISTS pageviews (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    session_id VARCHAR(64) NOT NULL,
    page_url VARCHAR(512) NOT NULL,
    page_title VARCHAR(255),
    referrer_url TEXT,
    view_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    time_on_page INT DEFAULT 0,
    scroll_depth TINYINT DEFAULT 0,
    
    INDEX idx_session (session_id),
    INDEX idx_timestamp (view_timestamp),
    INDEX idx_page (page_url(255)),
    FOREIGN KEY (session_id) REFERENCES visitor_sessions(session_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Click/event tracking
CREATE TABLE IF NOT EXISTS click_events (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    session_id VARCHAR(64) NOT NULL,
    event_type VARCHAR(64) NOT NULL,
    element_id VARCHAR(128),
    element_class VARCHAR(128),
    element_text TEXT,
    page_url VARCHAR(512),
    event_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_session (session_id),
    INDEX idx_event_type (event_type),
    INDEX idx_timestamp (event_timestamp),
    FOREIGN KEY (session_id) REFERENCES visitor_sessions(session_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Speed test results (link to existing tests table)
-- Already exists in main schema

-- Real-time online users
CREATE TABLE IF NOT EXISTS online_users (
    session_id VARCHAR(64) PRIMARY KEY,
    last_ping TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    current_page VARCHAR(512),
    
    INDEX idx_last_ping (last_ping),
    FOREIGN KEY (session_id) REFERENCES visitor_sessions(session_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin users
CREATE TABLE IF NOT EXISTS admin_users (
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

-- Insert default admin (password: admin123 - CHANGE THIS!)
INSERT INTO admin_users (username, password_hash, role) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE id=id;
