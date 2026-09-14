SET NAMES utf8mb4;

-- ROBLOX staff user accounts so the blog shows authentic author names.
-- Passwords are all "test1234" (same bcrypt hash as the other test users).

INSERT INTO users (username, password, email, email_verified, gender, avatar_id, is_bc, robux, tickets, chat_mode, is_admin, is_banned, is_online, about)
SELECT 'Telamon', '$2y$10$c5Lj1ua8r0YjbwiEiY10GuHHdGJmQ3kXjw0Ip44MfWm5yCCILcbBO', 'telamon@roblox.com', 1, 'male', 2, 1, 100000, 100000, 'moderate', 1, 0, 0, 'Co-founder of ROBLOX. Host of "Scripting with Telamon".'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'Telamon');

INSERT INTO users (username, password, email, email_verified, gender, avatar_id, is_bc, robux, tickets, chat_mode, is_admin, is_banned, is_online, about)
SELECT 'ReeseMcBlox', '$2y$10$c5Lj1ua8r0YjbwiEiY10GuHHdGJmQ3kXjw0Ip44MfWm5yCCILcbBO', 'reese@roblox.com', 1, 'male', 3, 1, 100000, 100000, 'moderate', 1, 0, 0, 'ROBLOX staff. Runs contests and writes the community updates.'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'ReeseMcBlox');

INSERT INTO users (username, password, email, email_verified, gender, avatar_id, is_bc, robux, tickets, chat_mode, is_admin, is_banned, is_online, about)
SELECT 'BrightEyes', '$2y$10$c5Lj1ua8r0YjbwiEiY10GuHHdGJmQ3kXjw0Ip44MfWm5yCCILcbBO', 'brighteyes@roblox.com', 1, 'female', 4, 1, 100000, 100000, 'moderate', 1, 0, 0, 'ROBLOX staff. Community and jobs.'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'BrightEyes');