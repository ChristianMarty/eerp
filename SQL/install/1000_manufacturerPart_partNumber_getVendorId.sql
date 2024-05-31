CREATE DEFINER=`root`@`%` FUNCTION `manufacturerPart_partNumber_getVendorId`(
	`ManufacturerPartNumberId` INT
)
RETURNS int(11)
LANGUAGE SQL
DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT ''
BEGIN

DECLARE ReturnVendorId INT; 

SELECT 
CASE 
	WHEN manufacturerPart_series.VendorId IS NOT NULL THEN manufacturerPart_series.VendorId
	WHEN manufacturerPart_item.VendorId IS NOT NULL THEN manufacturerPart_item.VendorId 
	WHEN manufacturerPart_partNumber.VendorId IS NOT NULL THEN manufacturerPart_partNumber.VendorId 
END AS VendorId INTO ReturnVendorId
FROM manufacturerPart_partNumber 
LEFT JOIN manufacturerPart_item ON manufacturerPart_item.Id = manufacturerPart_partNumber.ItemId
LEFT JOIN manufacturerPart_series ON manufacturerPart_series.Id = manufacturerPart_item.SeriesId
WHERE manufacturerPart_partNumber.Id = ManufacturerPartNumberId;
RETURN ReturnVendorId;
END