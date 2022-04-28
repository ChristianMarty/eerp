SELECT supplier.Name AS SupplierName, supplierPart.SupplierPartNumber, partStock.OrderReference, partStock.StockNo, manufacturer.Name AS ManufacturerName, manufacturerPart.VendorId AS ManufacturerId, manufacturerPart.ManufacturerPartNumber, partStock.ManufacturerPartId, partStock.Date,
partStock.LocationId, location_getHomeLocationId_stock(partStock.Id) AS HomeLocationId, hc.CreateQuantity,  h.HistoryQuantity AS Quantity, r.ReservedQuantity AS ReservedQuantity, h.LastCountDate AS LastCountDate, hc.CreateData
FROM partStock
LEFT JOIN manufacturerPart ON manufacturerPart.Id = partStock.ManufacturerPartId
LEFT JOIN supplierPart ON supplierPart.Id = partStock.SupplierPartId
LEFT JOIN (SELECT Id, Name FROM vendor)manufacturer ON manufacturer.Id = manufacturerPart.VendorId
LEFT JOIN (SELECT Id, Name FROM vendor)supplier ON supplier.Id = supplierPart.VendorId
LEFT JOIN (
	SELECT SUM(Quantity) AS ReservedQuantity, StockId FROM partStock_reservation GROUP BY StockId
)r ON r.StockId = partStock.Id
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