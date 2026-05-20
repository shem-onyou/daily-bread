USE bakery_db;

-- Widen image column from VARCHAR(500) to MEDIUMTEXT (holds up to 16MB)
ALTER TABLE products MODIFY COLUMN image MEDIUMTEXT;
