-- 2008 ROBLOX recreation database schema
SET NAMES utf8mb4;

USE roblox08;

-- ------------------------------------------------------------
-- Users
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(20) NOT NULL,
  password VARCHAR(255) NOT NULL,
  email VARCHAR(100) NOT NULL DEFAULT '',
  gender ENUM('male','female') NOT NULL DEFAULT 'male',
  birthday DATE DEFAULT NULL,
  created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_login DATETIME DEFAULT NULL,
  is_bc TINYINT(1) NOT NULL DEFAULT 0,
  bc_expires DATE DEFAULT NULL,
  is_admin TINYINT(1) NOT NULL DEFAULT 0,
  is_banned TINYINT(1) NOT NULL DEFAULT 0,
  ban_reason VARCHAR(255) DEFAULT NULL,
  is_online TINYINT(1) NOT NULL DEFAULT 0,
  about TEXT,
  PRIMARY KEY (id),
  UNIQUE KEY username (username),
  KEY idx_banned (is_banned)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Sessions (remember-me tokens)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS user_sessions (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  token CHAR(64) NOT NULL,
  user_agent VARCHAR(255) DEFAULT NULL,
  ip_address VARCHAR(46) DEFAULT NULL,
  created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  expires DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY token (token),
  KEY idx_user (user_id),
  CONSTRAINT fk_sess_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Site alerts / maintenance notices
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS site_alerts (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(200) NOT NULL,
  message TEXT NOT NULL,
  alert_type ENUM('info','warning','maintenance','error') NOT NULL DEFAULT 'info',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- News posts
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS site_news (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  author_id INT UNSIGNED NOT NULL,
  title VARCHAR(200) NOT NULL,
  body TEXT NOT NULL,
  created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  published TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  KEY idx_author (author_id),
  CONSTRAINT fk_news_author FOREIGN KEY (author_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Abuse reports
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS abuse_reports (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  reporter_id INT UNSIGNED DEFAULT NULL,
  reported_user_id INT UNSIGNED DEFAULT NULL,
  report_type VARCHAR(50) NOT NULL DEFAULT 'user',
  target_id INT UNSIGNED DEFAULT NULL,
  abuse_category VARCHAR(50) NOT NULL DEFAULT 'other',
  comments TEXT,
  status ENUM('pending','reviewed','actioned','dismissed') NOT NULL DEFAULT 'pending',
  created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_reporter (reporter_id),
  KEY idx_reported (reported_user_id),
  KEY idx_status (status),
  CONSTRAINT fk_report_reporter FOREIGN KEY (reporter_id) REFERENCES users(id),
  CONSTRAINT fk_report_reported FOREIGN KEY (reported_user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Private messages
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS messages (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  sender_id INT UNSIGNED NOT NULL,
  recipient_id INT UNSIGNED NOT NULL,
  subject VARCHAR(200) NOT NULL DEFAULT '(no subject)',
  body TEXT NOT NULL,
  sent DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  sender_deleted TINYINT(1) NOT NULL DEFAULT 0,
  recipient_deleted TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_recipient (recipient_id, recipient_deleted, sent),
  KEY idx_sender (sender_id, sender_deleted, sent),
  CONSTRAINT fk_msg_sender FOREIGN KEY (sender_id) REFERENCES users(id),
  CONSTRAINT fk_msg_recipient FOREIGN KEY (recipient_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Friendships
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS friendships (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  requester_id INT UNSIGNED NOT NULL,
  addressee_id INT UNSIGNED NOT NULL,
  accepted TINYINT(1) NOT NULL DEFAULT 0,
  created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  responded DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uk_pair (requester_id, addressee_id),
  KEY idx_addressee (addressee_id, accepted),
  CONSTRAINT fk_fr_requester FOREIGN KEY (requester_id) REFERENCES users(id),
  CONSTRAINT fk_fr_addressee FOREIGN KEY (addressee_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Forums
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS forum_categories (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  sort_order INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS forum_threads (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  category_id INT UNSIGNED NOT NULL,
  author_id INT UNSIGNED NOT NULL,
  title VARCHAR(200) NOT NULL,
  pinned TINYINT(1) NOT NULL DEFAULT 0,
  locked TINYINT(1) NOT NULL DEFAULT 0,
  views INT UNSIGNED NOT NULL DEFAULT 0,
  created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_category (category_id, created),
  KEY idx_author (author_id),
  CONSTRAINT fk_thread_cat FOREIGN KEY (category_id) REFERENCES forum_categories(id),
  CONSTRAINT fk_thread_author FOREIGN KEY (author_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS forum_posts (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  thread_id INT UNSIGNED NOT NULL,
  author_id INT UNSIGNED NOT NULL,
  body TEXT NOT NULL,
  created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_thread (thread_id, created),
  KEY idx_author (author_id),
  CONSTRAINT fk_post_thread FOREIGN KEY (thread_id) REFERENCES forum_threads(id) ON DELETE CASCADE,
  CONSTRAINT fk_post_author FOREIGN KEY (author_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Catalogue
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS asset_types (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(50) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY name (name)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS catalog_items (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  asset_type_id INT UNSIGNED NOT NULL,
  creator_id INT UNSIGNED DEFAULT NULL,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  price INT UNSIGNED NOT NULL DEFAULT 0,
  offer_price INT UNSIGNED NOT NULL DEFAULT 0,
  is_for_sale TINYINT(1) NOT NULL DEFAULT 1,
  sales_count INT UNSIGNED NOT NULL DEFAULT 0,
  status ENUM('approved','pending','rejected') NOT NULL DEFAULT 'approved',
  thumbnail VARCHAR(255) DEFAULT NULL,
  created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated DATETIME DEFAULT NULL,
  data LONGBLOB,
  PRIMARY KEY (id),
  KEY idx_asset_type (asset_type_id, is_for_sale),
  KEY idx_creator (creator_id),
  KEY idx_status (status),
  CONSTRAINT fk_item_type FOREIGN KEY (asset_type_id) REFERENCES asset_types(id),
  CONSTRAINT fk_item_creator FOREIGN KEY (creator_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS user_inventory (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  item_id INT UNSIGNED NOT NULL,
  acquired DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  equipped TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uk_owner_item (user_id, item_id),
  KEY idx_item (item_id),
  CONSTRAINT fk_inv_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_inv_item FOREIGN KEY (item_id) REFERENCES catalog_items(id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Places / games
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS places (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  owner_id INT UNSIGNED NOT NULL,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  visits INT UNSIGNED NOT NULL DEFAULT 0,
  thumbnail VARCHAR(255) DEFAULT NULL,
  is_public TINYINT(1) NOT NULL DEFAULT 1,
  created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_owner (owner_id),
  KEY idx_public (is_public, visits),
  CONSTRAINT fk_place_owner FOREIGN KEY (owner_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Dashboard "cool places" featured spots
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS featured_places (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  place_id INT UNSIGNED NOT NULL,
  blurb VARCHAR(255) DEFAULT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  CONSTRAINT fk_featured_place FOREIGN KEY (place_id) REFERENCES places(id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Builder's Club / Transactions
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS transactions (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  amount INT NOT NULL DEFAULT 0,
  reason VARCHAR(200) DEFAULT NULL,
  created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_user (user_id, created),
  CONSTRAINT fk_tx_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Admin settings
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS site_settings (
  setting_key VARCHAR(50) NOT NULL,
  setting_value TEXT,
  PRIMARY KEY (setting_key)
) ENGINE=InnoDB;

-- Seed data
INSERT IGNORE INTO asset_types (id, name) VALUES
  (1,'Hat'), (2,'Face'), (3,'Shirt'), (4,'Pants'), (5,'TShirt'),
  (6,'Package'), (7,'Gear'), (8,'Model'), (9,'Place'), (10,'Mesh');

INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES
  ('site_name','ROBLOX'),
  ('maintenance_mode','0'),
  ('admin_username','Robloxian');

INSERT IGNORE INTO forum_categories (id, name, description, sort_order) VALUES
  (1,'News & Announcements','Public announcements about the site.',1),
  (2,'General Discussion','Talk about anything ROBLOX.',2),
  (3,'Trading & Buying','Buy and sell items with other players.',3),
  (4,'Help','Questions and support.',4);