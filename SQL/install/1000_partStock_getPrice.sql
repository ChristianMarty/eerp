CREATE DEFINER=`root`@`%` FUNCTION `partStock_getPrice`(
	`PartStockId` INT
)
RETURNS float
LANGUAGE SQL
DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT ''
BEGIN

RETURN (	SELECT purchaseOrder_itemOrder.Price FROM  partStock
			LEFT JOIN purchaseOrder_itemReceive ON purchaseOrder_itemReceive.Id = partStock.ReceivalId
			LEFT JOIN purchaseOrder_itemOrder ON purchaseOrder_itemOrder.Id = purchaseOrder_itemReceive.ItemOrderId
			WHERE partStock.Id = PartStockId);
END