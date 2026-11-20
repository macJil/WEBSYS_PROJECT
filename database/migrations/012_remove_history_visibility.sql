-- Apply once after 011. All existing history rows become visible.
ALTER TABLE response_updates DROP COLUMN history_hidden_at;
ALTER TABLE evacuation_records DROP COLUMN history_hidden_at;
ALTER TABLE relief_stock_movements DROP COLUMN history_hidden_at;
ALTER TABLE relief_distributions DROP COLUMN history_hidden_at;
