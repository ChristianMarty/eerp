CREATE TABLE `numbering` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`Prefix` CHAR(4) NOT NULL,
	`Category` ENUM('Undefined','Inventory','PurchaseOrder','ProductionPart','WorkOrder','PickingOrder','Document','Location','Stock','Assembly','AssemblyUnit','ManufacturerPartNumber','AssemblyUnitHistory','Shipment','CostCenter','Project','SpecificationPart') NOT NULL,
	`Name` TINYTEXT NOT NULL,
	PRIMARY KEY (`Id`)
);

INSERT INTO `numbering` (`Id`, `Prefix`, `Category`, `Name`) VALUES 
(1, 'Inv', 'Inventory', 'Inventory'),
(2, 'PO', 'PurchaseOrder', 'Purchase Order'),
(3, 'WO', 'WorkOrder', 'Work Order'),
(4, 'Pick', 'PickingOrder', 'Picking Order'),
(5, 'Doc', 'Document', 'Document'),
(6, 'Loc', 'Location', 'Location'),
(7, 'Stk', 'Stock', 'Stock'),
(8, 'ASM', 'Assembly', 'Assembly'),
(9, 'ASU', 'AssemblyUnit', 'Assembly Unit'),
(10, 'MPN', 'ManufacturerPartNumber', 'Manufacturer Part Number'),
(11, 'ASH', 'AssemblyUnitHistory', 'Assembly Unit History'),
(12, 'Shp', 'Shipment', 'Shipment'),
(13, 'CC', 'CostCenter', 'Cost Center'),
(14, 'Pjct', 'Project', 'Project'),
(15, 'Spec', 'SpecificationPart', 'Specification Part');

