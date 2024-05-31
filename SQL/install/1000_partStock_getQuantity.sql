CREATE DEFINER=`root`@`%` FUNCTION `partStock_getQuantity`(
	`StockNumber` CHAR(4)
)
RETURNS int(11)
LANGUAGE SQL
DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT ''
BEGIN

DECLARE Quantity  INT;

DECLARE StockId INT;

SET StockId = (SELECT Id FROM partStock WHERE partStock.StockNumber = StockNumber);

SET Quantity = (SELECT SUM(partStock_history.Quantity)
FROM partStock_history
JOIN (

	SELECT StockId, CreationDate AS LastCountDate, Quantity
	FROM partStock_history
	WHERE (ChangeType = 'Absolute' OR ChangeType = 'Create') AND partStock_history.StockId = StockId 
	ORDER BY CreationDate DESC LIMIT 1

) t ON partStock_history.StockId = t.StockId
WHERE partStock_history.CreationDate >= t.LastCountDate);


RETURN Quantity;
END