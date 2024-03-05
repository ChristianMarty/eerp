CREATE TABLE `numbering` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`Prefix` CHAR(4) NOT NULL DEFAULT '0' COLLATE 'utf8_general_ci',
	`Category` ENUM('Undefined','Inventory','PurchaseOrder','ProductionPart','WorkOrder','Document','Location','Stock','Assembly','AssemblyUnit','ManufacturerPartNumber','AssemblyUnitHistory','Shipment','CostCenter','Project','SpecificationPart') NOT NULL DEFAULT 'Undefined' COLLATE 'utf8_general_ci',
	`Name` TINYTEXT NOT NULL COLLATE 'utf8_general_ci',
	PRIMARY KEY (`Id`) USING BTREE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
