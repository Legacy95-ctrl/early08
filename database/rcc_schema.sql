-- RCC avatar-rendering schema (port of zyphie render pipeline)
-- Adds per-user avatar colors + RCC-rendered thumbnail blobs.

ALTER TABLE users
  ADD COLUMN headcolor       INT NOT NULL DEFAULT 24,
  ADD COLUMN torsocolor      INT NOT NULL DEFAULT 23,
  ADD COLUMN leftarmcolor    INT NOT NULL DEFAULT 21,
  ADD COLUMN rightarmcolor   INT NOT NULL DEFAULT 21,
  ADD COLUMN leftlegcolor    INT NOT NULL DEFAULT 103,
  ADD COLUMN rightlegcolor   INT NOT NULL DEFAULT 103,
  ADD COLUMN thumbnail              LONGTEXT NULL,
  ADD COLUMN thumbnailsmall         LONGTEXT NULL,
  ADD COLUMN thumbnailfriends       LONGTEXT NULL,
  ADD COLUMN thumbnailcharacter     LONGTEXT NULL;