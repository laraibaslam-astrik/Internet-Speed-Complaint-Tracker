-- Pakistan Internet Speed Tracker Database Schema
-- MySQL 8.0+

CREATE DATABASE IF NOT EXISTS speedtracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE speedtracker;

-- Main tests table: stores individual speed test results
CREATE TABLE IF NOT EXISTS tests (
    id CHAR(26) PRIMARY KEY COMMENT 'ULID identifier',
    ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Test timestamp',
    asn INT NULL COMMENT 'Autonomous System Number',
    isp_name VARCHAR(128) NOT NULL COMMENT 'ISP name',
    city VARCHAR(128) NOT NULL COMMENT 'City name',
    dl_mbps DECIMAL(8,2) NOT NULL COMMENT 'Download speed in Mbps',
    ul_mbps DECIMAL(8,2) NOT NULL COMMENT 'Upload speed in Mbps',
    ping_ms DECIMAL(8,2) NOT NULL COMMENT 'Ping latency in milliseconds',
    jitter_ms DECIMAL(8,2) NOT NULL COMMENT 'Jitter in milliseconds',
    tech VARCHAR(32) DEFAULT 'Unknown' COMMENT 'Connection technology (4G, Wi-Fi, etc)',
    device_type VARCHAR(16) DEFAULT 'unknown' COMMENT 'Device type (mobile, desktop)',
    hash_ip CHAR(64) NOT NULL COMMENT 'SHA256 hash of IP+salt',
    sample_ms INT DEFAULT 0 COMMENT 'Test duration in milliseconds',
    INDEX idx_tests_date_city_isp (ts, city, isp_name),
    INDEX idx_tests_hash_ip_ts (hash_ip, ts),
    INDEX idx_tests_city (city),
    INDEX idx_tests_isp (isp_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Individual speed test results';

-- Daily rollups table: aggregated statistics per day/city/ISP
CREATE TABLE IF NOT EXISTS rollups_daily (
    dt DATE NOT NULL COMMENT 'Date',
    city VARCHAR(128) NOT NULL COMMENT 'City name',
    isp_name VARCHAR(128) NOT NULL COMMENT 'ISP name',
    avg_dl DECIMAL(8,2) NOT NULL COMMENT 'Average download speed',
    avg_ul DECIMAL(8,2) NOT NULL COMMENT 'Average upload speed',
    avg_ping DECIMAL(8,2) NOT NULL COMMENT 'Average ping',
    p95_ping DECIMAL(8,2) DEFAULT 0 COMMENT '95th percentile ping',
    tests_count INT NOT NULL DEFAULT 0 COMMENT 'Number of tests',
    PRIMARY KEY (dt, city, isp_name),
    INDEX idx_rollups_date (dt),
    INDEX idx_rollups_city (city),
    INDEX idx_rollups_isp (isp_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Daily aggregated statistics';

-- Outages/anomalies table: detected network issues
CREATE TABLE IF NOT EXISTS outages (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    window_start DATETIME NOT NULL COMMENT 'Anomaly detection window start',
    city VARCHAR(128) NOT NULL COMMENT 'Affected city',
    isp_name VARCHAR(128) NOT NULL COMMENT 'Affected ISP',
    anomaly_type ENUM('latency_spike','dl_drop') NOT NULL COMMENT 'Type of anomaly',
    severity TINYINT NOT NULL DEFAULT 0 COMMENT 'Severity level (0-3)',
    evidence JSON NULL COMMENT 'Anomaly details and metrics',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_outages_window (window_start),
    INDEX idx_outages_city (city),
    INDEX idx_outages_isp (isp_name),
    INDEX idx_outages_type (anomaly_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Detected network anomalies and outages';

-- Create database user (adjust password as needed)
-- CREATE USER IF NOT EXISTS 'speeduser'@'localhost' IDENTIFIED BY 'supersecret';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON speedtracker.* TO 'speeduser'@'localhost';
-- FLUSH PRIVILEGES;
