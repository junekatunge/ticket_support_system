-- Migration script to clean up requester table
-- Run this SQL command in phpMyAdmin or MySQL console

USE helpdesk_core_php;

-- REQUIRED: Remove email and phone fields to fix the error
-- The database requires these columns to be removed since they're not being used

ALTER TABLE `requester` DROP COLUMN `email`;
ALTER TABLE `requester` DROP COLUMN `phone`;

-- Verification query to check the table structure
DESCRIBE `requester`;

-- The table should now only have: id, name, created_at, updated_at