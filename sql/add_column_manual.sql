-- SQL script to add remember_token column to users table
-- This script can be run directly with phpMyAdmin or MySQL command line client

-- Step 1: Select your database
USE tree_smoker;

-- Step 2: Run this statement to add the column
ALTER TABLE `users` ADD COLUMN `remember_token` VARCHAR(255) NULL;

-- Note: If the column already exists, you'll get an error, which you can ignore.
-- To manually check if the column exists before running the above statement,
-- you can run this query first:
-- SHOW COLUMNS FROM `users` LIKE 'remember_token';

-- If no results are returned, the column doesn't exist and needs to be added. 