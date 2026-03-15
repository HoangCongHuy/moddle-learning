-- Moodle database initialization
-- This runs on first container startup

CREATE DATABASE IF NOT EXISTS moodle
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'moodle'@'%' IDENTIFIED BY 'moodlepass';
GRANT ALL PRIVILEGES ON moodle.* TO 'moodle'@'%';
FLUSH PRIVILEGES;
