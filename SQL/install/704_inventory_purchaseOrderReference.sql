CREATE TABLE `inventory_purchaseOrderReference` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`InventoryId` INT(10) UNSIGNED NOT NULL,
	`ReceivalId` INT(10) UNSIGNED NOT NULL,
	`Quantity` DECIMAL(4,2) UNSIGNED NOT NULL DEFAULT '1.00',
	`Type` ENUM('Purchase','Maintenance') NOT NULL DEFAULT 'Purchase',
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`),
	UNIQUE KEY `InventoryId_ReceivalId` (`InventoryId`, `ReceivalId`),
	INDEX `FK_inventory_purchasOrderReference_purchasOrder_itemReceive` (`ReceivalId`),
	INDEX `FK_inventory_purchaseOrderReference_user` (`CreationUserId`),
	CONSTRAINT `FK_inventory_purchasOrderReference_inventory` FOREIGN KEY (`InventoryId`) REFERENCES `inventory` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_inventory_purchasOrderReference_purchasOrder_itemReceive` FOREIGN KEY (`ReceivalId`) REFERENCES `purchaseOrder_itemReceive` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_inventory_purchaseOrderReference_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
);
