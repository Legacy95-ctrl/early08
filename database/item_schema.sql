-- Item page support: favorites + comments (matches the de-facto early-2008 revival layout)
USE roblox08;

CREATE TABLE IF NOT EXISTS catalog_favorites (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  item_id INT UNSIGNED NOT NULL,
  created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_user_item (user_id, item_id),
  KEY idx_item (item_id),
  CONSTRAINT fk_fav_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_fav_item FOREIGN KEY (item_id) REFERENCES catalog_items(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS catalog_comments (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  item_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  body TEXT NOT NULL,
  created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_item (item_id, created),
  CONSTRAINT fk_cc_item FOREIGN KEY (item_id) REFERENCES catalog_items(id) ON DELETE CASCADE,
  CONSTRAINT fk_cc_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;