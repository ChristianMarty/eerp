SELECT partStock_history.Id, partStock_history.StockId, partStock_history.ChangeType, partStock_history.Quantity, partStock_history.Date
FROM partStock_history
JOIN (
	SELECT StockId, MAX(DATE) AS LastCountDate
	FROM partStock_history
	WHERE (ChangeType = 'Absolute' OR ChangeType = 'Create')
	GROUP BY StockId
) t ON partStock_history.StockId = t.StockId
WHERE partStock_history.Date >= t.LastCountDate ORDER BY DATE ASC;