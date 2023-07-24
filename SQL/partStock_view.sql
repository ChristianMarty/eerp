SELECT 
	supplier.Name AS SupplierName, 
	supplierPart.SupplierPartNumber, 
	partStock.OrderReference, 
	partStock.StockNo, 
	vendor_displayName(manufacturer.Id) AS ManufacturerName, 
	manufacturer.Id AS ManufacturerId, 
	manufacturerPart_partNumber.Number AS ManufacturerPartNumber, 
	partStock.ManufacturerPartNumberId, 
	partStock.Date,
	partStock.LocationId, 
	location_getHomeLocationId_stock(partStock.Id) AS HomeLocationId, 
	hc.CreateQuantity,  
	h.HistoryQuantity AS Quantity, 
	r.ReservedQuantity AS ReservedQuantity, 
	h.LastCountDate AS LastCountDate, 
	hc.CreateData
FROM partStock

LEFT JOIN (SELECT SupplierPartId, purchaseOrder_itemReceive.Id FROM purchaseOrder_itemOrder LEFT JOIN purchaseOrder_itemReceive ON purchaseOrder_itemOrder.Id = purchaseOrder_itemReceive.ItemOrderId)poLine ON poLine.Id = partStock.ReceivalId

LEFT JOIN supplierPart ON (supplierPart.Id = partStock.SupplierPartId AND partStock.ReceivalId IS NULL) OR (supplierPart.Id = poLine.SupplierPartId)

LEFT JOIN manufacturerPart_partNumber ON (manufacturerPart_partNumber.Id = partStock.ManufacturerPartNumberId AND supplierPart.ManufacturerPartNumberId IS NULL) OR manufacturerPart_partNumber.Id = supplierPart.ManufacturerPartNumberId
LEFT JOIN manufacturerPart_item ON manufacturerPart_item.Id = manufacturerPart_partNumber.ItemId
LEFT JOIN manufacturerPart_series ON manufacturerPart_series.Id = manufacturerPart_item.SeriesId

LEFT JOIN (SELECT Id, Name FROM vendor)manufacturer ON manufacturer.Id = manufacturerPart_item.VendorId
LEFT JOIN (SELECT Id, Name FROM vendor)supplier ON supplier.Id = supplierPart.VendorId

LEFT JOIN (SELECT SUM(Quantity) AS ReservedQuantity, StockId FROM partStock_reservation GROUP BY StockId)r ON r.StockId = partStock.Id
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