-- SQL script to add remember_token column to users table

-- Simplified approach to add column (will fail silently if column already exists)
ALTER TABLE `users` ADD COLUMN `remember_token` VARCHAR(255) NULL; 