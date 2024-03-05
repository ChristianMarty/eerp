CREATE TABLE `inventory_purchaseOrderReference` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`InventoryId` INT(10) UNSIGNED NOT NULL,
	`ReceivalId` INT(10) UNSIGNED NOT NULL,
	`Quantity` DECIMAL(4,2) UNSIGNED NOT NULL DEFAULT '1.00',
	`Type` ENUM('Purchase','Maintenance') NOT NULL DEFAULT 'Purchase' COLLATE 'utf8_general_ci',
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`) USING BTREE,
	UNIQUE INDEX `InventoryId_ReceivalId` (`InventoryId`, `ReceivalId`) USING BTREE,
	INDEX `FK_inventory_purchasOrderReference_purchasOrder_itemReceive` (`ReceivalId`) USING BTREE,
	INDEX `FK_inventory_purchaseOrderReference_user` (`CreationUserId`) USING BTREE,
	CONSTRAINT `FK_inventory_purchasOrderReference_inventory` FOREIGN KEY (`InventoryId`) REFERENCES `inventory` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_inventory_purchasOrderReference_purchasOrder_itemReceive` FOREIGN KEY (`ReceivalId`) REFERENCES `purchaseOrder_itemReceive` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_inventory_purchaseOrderReference_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
