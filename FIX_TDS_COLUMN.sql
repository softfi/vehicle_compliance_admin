-- Fix TDS Percentage Column
-- Run this SQL query directly in your database if migration doesn't work

-- Check if column exists
SELECT COUNT(*) as column_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'do_registration' 
AND COLUMN_NAME = 'tds_percentage';

-- Add column if it doesn't exist
ALTER TABLE `do_registration` 
ADD COLUMN IF NOT EXISTS `tds_percentage` DECIMAL(5,2) NULL DEFAULT 2.00 COMMENT 'TDS percentage (default 2%)' 
AFTER `rate`;

-- Update existing records with default value if NULL
UPDATE `do_registration` 
SET `tds_percentage` = 2.00 
WHERE `tds_percentage` IS NULL;
