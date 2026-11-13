-- Back up first. Apply once after migration 005 on existing databases.
ALTER TABLE relief_items DROP COLUMN minimum_stock;
