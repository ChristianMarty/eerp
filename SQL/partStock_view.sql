SELECT supplier.Name AS SupplierName, supplierPart.SupplierPartNumber, partStock.OrderReference, partStock.StockNo, partManufacturer.Name AS ManufacturerName, manufacturerPart.ManufacturerId, manufacturerPart.ManufacturerPartNumber, partStock.ManufacturerPartId, partStock.Date,location.Id AS LocationId, hc.CreateQuantity,  h.HistoryQuantity AS Quantity, h.LastCountDate AS LastCountDate, hc.CreateData
FROM partStock
LEFT JOIN manufacturerPart ON manufacturerPart.Id = partStock.ManufacturerPartId
LEFT JOIN partManufacturer ON partManufacturer.Id = manufacturerPart.ManufacturerId
LEFT JOIN supplierPart ON supplierPart.Id = partStock.SupplierPartId
LEFT JOIN supplier ON supplier.Id = supplierPart.SupplierId
LEFT JOIN location ON location.Id = partStock.LocationId
LEFT JOIN (SELECT StockId, Quantity AS CreateQuantity, Date AS CreateData FROM partStock_history WHERE ChangeType = 'Create')hc ON  hc.StockId = partStock.Id
LEFT JOIN (
	SELECT partStock_history.Id, partStock_history.StockId, partStock_history.ChangeType, partStock_history.Quantity, partStock_history.Date, q.HistoryQuantity, q.LastCountDate
	FROM partStock_history
	LEFT JOIN (
		SELECT partStock_history.StockId, SUM(partStock_history.Quantity) AS HistoryQuantity, t.LastCountDate
		FROM partStock_history
		JOIN (
			SELECT StockId, MAX(Date) AS LastCountDate
			FROM partStock_history
			WHERE ChangeType = 'Absolute' OR ChangeType = 'Create'
			GROUP BY StockId
		) t ON partStock_history.StockId = t.StockId AND partStock_history.Date >= t.LastCountDate
		WHERE partStock_history.Date >= t.LastCountDate
		GROUP BY partStock_history.StockId) q ON partStock_history.StockId = q.StockId 
	WHERE partStock_history.ChangeType = 'Absolute' OR partStock_history.ChangeType = 'Create')h ON h.StockId = partStock.Id
GROUP BY h.StockId