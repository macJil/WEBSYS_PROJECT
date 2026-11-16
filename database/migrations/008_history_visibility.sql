-- Apply once to an existing database; fresh installs already include these columns.
-- Hiding an entry changes display only. No rows, balances or relationships are deleted.
ALTER TABLE response_updates ADD COLUMN history_hidden_at DATETIME NULL;
ALTER TABLE evacuation_records ADD COLUMN history_hidden_at DATETIME NULL;
ALTER TABLE relief_stock_movements ADD COLUMN history_hidden_at DATETIME NULL;
ALTER TABLE relief_distributions ADD COLUMN history_hidden_at DATETIME NULL;
