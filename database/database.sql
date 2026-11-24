-- ============================================================
-- NSBM NewsDesk - MySQL database schema
-- Compatible with MySQL 8.0.16+ (enforces CHECK constraints)
-- ============================================================

CREATE DATABASE IF NOT EXISTS nsbm_newsdesk
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nsbm_newsdesk;

-- For a fresh installation, run in an empty database.
-- This script does not drop existing tables or data.

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(255) NOT NULL,
  password VARCHAR(255) NOT NULL COMMENT 'Store password hash only',
  role ENUM('student','moderator','admin') NOT NULL DEFAULT 'student',
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categories (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  description TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_categories_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS articles (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  content TEXT NOT NULL,
  author_id INT UNSIGNED NOT NULL,
  category_id INT UNSIGNED NOT NULL,
  status ENUM('draft','pending','approved','published','rejected','archived')
    NOT NULL DEFAULT 'pending',
  rejection_reason TEXT NULL,
  reviewed_by INT UNSIGNED NULL,
  reviewed_at TIMESTAMP NULL DEFAULT NULL,
  is_featured BOOLEAN NOT NULL DEFAULT FALSE,
  is_breaking BOOLEAN NOT NULL DEFAULT FALSE,
  image_path VARCHAR(255) NULL,
  published_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_articles_status_created (status, created_at),
  KEY idx_articles_category_status (category_id, status),
  KEY idx_articles_author_created (author_id, created_at),
  KEY idx_articles_reviewed_by (reviewed_by),
  CONSTRAINT fk_articles_author FOREIGN KEY (author_id) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_articles_category FOREIGN KEY (category_id) REFERENCES categories(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_articles_reviewer FOREIGN KEY (reviewed_by) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comments (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  article_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  content TEXT NOT NULL,
  status ENUM('pending','approved','hidden','removed') NOT NULL DEFAULT 'pending',
  moderated_by INT UNSIGNED NULL,
  moderated_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_comments_article_status (article_id, status),
  KEY idx_comments_user_created (user_id, created_at),
  KEY idx_comments_moderated_by (moderated_by),
  CONSTRAINT fk_comments_article FOREIGN KEY (article_id) REFERENCES articles(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_comments_user FOREIGN KEY (user_id) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_comments_moderator FOREIGN KEY (moderated_by) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS bookmarks (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  article_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_bookmarks_user_article (user_id, article_id),
  CONSTRAINT fk_bookmarks_user FOREIGN KEY (user_id) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_bookmarks_article FOREIGN KEY (article_id) REFERENCES articles(id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS subscriptions (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  category_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_subscriptions_user_category (user_id, category_id),
  CONSTRAINT fk_subscriptions_user FOREIGN KEY (user_id) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_subscriptions_category FOREIGN KEY (category_id) REFERENCES categories(id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS announcements (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  content TEXT NOT NULL,
  created_by INT UNSIGNED NOT NULL,
  status ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
  published_at TIMESTAMP NULL DEFAULT NULL,
  expires_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_announcements_status_published (status, published_at),
  CONSTRAINT fk_announcements_creator FOREIGN KEY (created_by) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS article_views (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  article_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NULL,
  viewed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_article_views_article_time (article_id, viewed_at),
  KEY idx_article_views_user_time (user_id, viewed_at),
  CONSTRAINT fk_article_views_article FOREIGN KEY (article_id) REFERENCES articles(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_article_views_user FOREIGN KEY (user_id) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS content_reports (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  reporter_id INT UNSIGNED NOT NULL,
  target_type ENUM('article','comment') NOT NULL,
  article_id INT UNSIGNED NULL,
  comment_id INT UNSIGNED NULL,
  reason VARCHAR(100) NOT NULL,
  description TEXT NULL,
  status ENUM('pending','reviewing','resolved','dismissed') NOT NULL DEFAULT 'pending',
  reviewed_by INT UNSIGNED NULL,
  reviewed_at TIMESTAMP NULL DEFAULT NULL,
  resolution_note TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_content_reports_status_created (status, created_at),
  KEY idx_content_reports_reporter_created (reporter_id, created_at),
  KEY idx_content_reports_reviewer (reviewed_by),
  KEY idx_content_reports_article (article_id),
  KEY idx_content_reports_comment (comment_id),
  CONSTRAINT chk_content_reports_single_target CHECK (
    (target_type = 'article' AND article_id IS NOT NULL AND comment_id IS NULL)
    OR
    (target_type = 'comment' AND comment_id IS NOT NULL AND article_id IS NULL)
  ),
  CONSTRAINT fk_content_reports_reporter FOREIGN KEY (reporter_id) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_content_reports_reviewer FOREIGN KEY (reviewed_by) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_content_reports_article FOREIGN KEY (article_id) REFERENCES articles(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_content_reports_comment FOREIGN KEY (comment_id) REFERENCES comments(id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS moderation_logs (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  moderator_id INT UNSIGNED NOT NULL,
  action VARCHAR(50) NOT NULL,
  article_id INT UNSIGNED NULL,
  comment_id INT UNSIGNED NULL,
  report_id INT UNSIGNED NULL,
  reason TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_moderation_logs_moderator_created (moderator_id, created_at),
  KEY idx_moderation_logs_article (article_id),
  KEY idx_moderation_logs_comment (comment_id),
  KEY idx_moderation_logs_report (report_id),
  CONSTRAINT fk_moderation_logs_moderator FOREIGN KEY (moderator_id) REFERENCES users(id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_moderation_logs_article FOREIGN KEY (article_id) REFERENCES articles(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_moderation_logs_comment FOREIGN KEY (comment_id) REFERENCES comments(id)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT fk_moderation_logs_report FOREIGN KEY (report_id) REFERENCES content_reports(id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional initial categories; uncomment if desired.
-- INSERT IGNORE INTO categories (name, description) VALUES
-- ('Academic', 'Academic news and updates'),
-- ('Events', 'Campus events and activities'),
-- ('Sports', 'Sports news and results'),
-- ('General', 'General campus news');



