-- One Sound clean development schema
-- MariaDB 10.4+ / MySQL 8+
-- Contains fictional demo accounts only. Commercial music/video files are intentionally excluded.

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS request_limits;
DROP TABLE IF EXISTS review_audio;
DROP TABLE IF EXISTS videos;
DROP TABLE IF EXISTS audio;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS roles;

CREATE TABLE roles (
  role_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  role_type VARCHAR(50) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (role_id),
  UNIQUE KEY uq_roles_type (role_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
  user_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_name VARCHAR(100) NOT NULL,
  user_phone VARCHAR(30) NOT NULL,
  user_address VARCHAR(255) NOT NULL,
  user_email VARCHAR(254) NOT NULL,
  user_password VARCHAR(255) NOT NULL,
  session_version INT UNSIGNED NOT NULL DEFAULT 1,
  user_image VARCHAR(255) NOT NULL DEFAULT 'vector.jpg',
  role_id INT UNSIGNED NOT NULL,
  account_status ENUM('active','disabled') NOT NULL DEFAULT 'active',
  disabled_at DATETIME NULL,
  last_login_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id),
  UNIQUE KEY uq_users_email (user_email),
  KEY idx_users_role (role_id),
  KEY idx_users_status (account_status),
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles (role_id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- "categories" is intentionally a compact catalog-metadata table for this educational project.
-- It combines artist, album, genre, language, year and cover image to avoid over-engineering.
CREATE TABLE categories (
  category_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  category_artist VARCHAR(100) NOT NULL,
  category_album VARCHAR(150) NOT NULL,
  category_genre VARCHAR(100) NOT NULL,
  category_language VARCHAR(100) NOT NULL,
  category_year SMALLINT UNSIGNED NOT NULL,
  category_image VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (category_id),
  KEY idx_categories_artist (category_artist),
  KEY idx_categories_album (category_album),
  KEY idx_categories_genre (category_genre),
  KEY idx_categories_language (category_language),
  KEY idx_categories_year (category_year),
  CONSTRAINT chk_category_year CHECK (category_year BETWEEN 1900 AND 2100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE audio (
  audio_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  audio_title VARCHAR(180) NOT NULL,
  audio_name VARCHAR(80) NOT NULL COMMENT 'Random server-side storage key',
  audio_original_name VARCHAR(180) NOT NULL,
  audio_mime_type VARCHAR(100) NOT NULL DEFAULT 'audio/mpeg',
  audio_size_bytes INT UNSIGNED NOT NULL DEFAULT 0,
  category_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (audio_id),
  UNIQUE KEY uq_audio_name (audio_name),
  KEY idx_audio_category (category_id),
  KEY idx_audio_title (audio_title),
  CONSTRAINT fk_audio_category FOREIGN KEY (category_id) REFERENCES categories (category_id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE videos (
  video_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  video_title VARCHAR(180) NOT NULL,
  video_name VARCHAR(80) NOT NULL COMMENT 'Random server-side storage key',
  video_original_name VARCHAR(180) NOT NULL,
  video_mime_type VARCHAR(100) NOT NULL DEFAULT 'video/mp4',
  video_size_bytes INT UNSIGNED NOT NULL DEFAULT 0,
  category_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (video_id),
  UNIQUE KEY uq_video_name (video_name),
  KEY idx_video_category (category_id),
  KEY idx_video_title (video_title),
  CONSTRAINT fk_video_category FOREIGN KEY (category_id) REFERENCES categories (category_id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE messages (
  message_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  message_name VARCHAR(100) NOT NULL,
  message_email VARCHAR(254) NOT NULL,
  message_subject VARCHAR(150) NOT NULL,
  message_detail TEXT NOT NULL,
  message_status ENUM('pending','seen') NOT NULL DEFAULT 'pending',
  read_at DATETIME NULL,
  read_by INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (message_id),
  KEY idx_messages_status_created (message_status, created_at),
  KEY idx_messages_read_by (read_by),
  CONSTRAINT fk_messages_read_by FOREIGN KEY (read_by) REFERENCES users (user_id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE review_audio (
  review_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  review_text VARCHAR(255) NOT NULL,
  review_rating TINYINT UNSIGNED NOT NULL,
  audio_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  review_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (review_id),
  UNIQUE KEY uq_review_audio_user (audio_id, user_id),
  KEY idx_review_user (user_id),
  KEY idx_review_date (review_date),
  CONSTRAINT fk_review_audio FOREIGN KEY (audio_id) REFERENCES audio (audio_id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_review_user FOREIGN KEY (user_id) REFERENCES users (user_id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT chk_review_rating CHECK (review_rating BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE request_limits (
  limit_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  action_name VARCHAR(40) NOT NULL,
  rate_key CHAR(64) NOT NULL,
  attempt_count SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  window_started_at DATETIME NOT NULL,
  last_attempt_at DATETIME NOT NULL,
  blocked_until DATETIME NULL,
  PRIMARY KEY (limit_id),
  UNIQUE KEY uq_request_limit_action_key (action_name, rate_key),
  KEY idx_request_limits_cleanup (blocked_until, last_attempt_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO roles (role_id, role_type) VALUES
  (1, 'Administrator'),
  (2, 'User');

-- Demo passwords:
-- admin.demo@example.test = AdminDemo123!
-- user.demo@example.test  = UserDemo123!
INSERT INTO users
  (user_id, user_name, user_phone, user_address, user_email, user_password, user_image, role_id)
VALUES
  (1, 'Demo Administrator', '+92 300 0000000', 'Fictional portfolio data', 'admin.demo@example.test', '$2y$12$7k1xu6GpI3PramIGZzZiieceuAEMXpBiE5q0Y6.fid50GjyhvgJL6', 'vector.jpg', 1),
  (2, 'Demo User', '+92 300 0000001', 'Fictional portfolio data', 'user.demo@example.test', '$2y$12$tsXrVLhi27xJ.jD.dvkUA.EXoNWUgWfBk2h.9gpEMrYNpOE3e.pHW', 'vector.jpg', 2);

SET FOREIGN_KEY_CHECKS = 1;
