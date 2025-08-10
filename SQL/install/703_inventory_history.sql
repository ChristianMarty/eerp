CREATE TABLE `inventory_history` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`InventoryId` INT(10) UNSIGNED NOT NULL,
	`Description` MEDIUMTEXT NOT NULL,
	`Type` ENUM('Calibration','Repair','Replacement','Planned Maintenance','Firmware Change','Unknown') NOT NULL DEFAULT 'Unknown',
	`DocumentIds` TINYTEXT NULL DEFAULT NULL,
	`Date` DATE NOT NULL,
	`NextDate` DATE NULL DEFAULT NULL,
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	`EditToken` CHAR(32) NULL DEFAULT NULL,
	PRIMARY KEY (`Id`),
	INDEX `FK_inventory_history_inventory` (`InventoryId`),
	INDEX `FK_inventory_history_user` (`CreationUserId`),
	CONSTRAINT `FK_inventory_history_inventory` FOREIGN KEY (`InventoryId`) REFERENCES `inventory` (`Id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
	CONSTRAINT `FK_inventory_history_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
);
