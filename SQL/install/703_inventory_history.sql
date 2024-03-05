CREATE TABLE `inventory_history` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`InventoryId` INT(10) UNSIGNED NOT NULL,
	`Description` MEDIUMTEXT NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`Type` ENUM('Calibration','Repair','Replacement','Planned Maintenance','Firmware Change','Unknown') NOT NULL DEFAULT 'Unknown' COLLATE 'utf8_general_ci',
	`DocumentIds` TINYTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`Date` DATE NOT NULL,
	`NextDate` DATE NULL DEFAULT NULL,
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	`EditToken` CHAR(32) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	PRIMARY KEY (`Id`) USING BTREE,
	INDEX `FK_inventory_history_inventory` (`InventoryId`) USING BTREE,
	INDEX `FK_inventory_history_user` (`CreationUserId`) USING BTREE,
	CONSTRAINT `FK_inventory_history_inventory` FOREIGN KEY (`InventoryId`) REFERENCES `inventory` (`Id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
	CONSTRAINT `FK_inventory_history_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
