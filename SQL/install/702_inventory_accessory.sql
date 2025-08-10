CREATE TABLE `inventory_accessory` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`InventoryId` INT(10) UNSIGNED NOT NULL,
	`AccessoryNumber` INT(10) UNSIGNED NOT NULL,
	`Description` TEXT NOT NULL,
	`Note` TEXT NULL DEFAULT NULL,
	`Labeled` BIT(1) NOT NULL DEFAULT b'0',
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`),
	INDEX `FK_inventory_accessory_inventory` (`InventoryId`),
	INDEX `FK_inventory_accessory_user` (`CreationUserId`),
	CONSTRAINT `FK_inventory_accessory_inventory` FOREIGN KEY (`InventoryId`) REFERENCES `inventory` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_inventory_accessory_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
);
