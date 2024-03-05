CREATE TABLE `inventory_accessory` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`InventoryId` INT(10) UNSIGNED NOT NULL,
	`AccessoryNumber` INT(10) UNSIGNED NOT NULL,
	`Description` TEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`Note` TEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`Labeled` BIT(1) NOT NULL DEFAULT b'0',
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`) USING BTREE,
	INDEX `FK_inventory_accessory_inventory` (`InventoryId`) USING BTREE,
	INDEX `FK_inventory_accessory_user` (`CreationUserId`) USING BTREE,
	CONSTRAINT `FK_inventory_accessory_inventory` FOREIGN KEY (`InventoryId`) REFERENCES `inventory` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_inventory_accessory_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
