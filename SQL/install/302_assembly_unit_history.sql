CREATE TABLE `assembly_unit_history` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`AssemblyUnitHistoryNumber` INT(6) UNSIGNED ZEROFILL NOT NULL,
	`AssemblyUnitId` INT(10) UNSIGNED NOT NULL,
	`EditToken` CHAR(32) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`Title` TINYTEXT NOT NULL COLLATE 'utf8_general_ci',
	`Description` TINYTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`Data` LONGTEXT NULL DEFAULT NULL COLLATE 'utf8mb4_bin',
	`Type` ENUM('Unknown','Note','Production','Repair','Modification','Inspection Fail','Inspection Pass','Characterisation Test','Test Fail','Test Pass','Shipped Out','Firmware Change') NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`Date` DATETIME NOT NULL,
	`ShippingProhibited` BIT(1) NOT NULL DEFAULT b'0',
	`ShippingClearance` BIT(1) NOT NULL DEFAULT b'0',
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`) USING BTREE,
	UNIQUE INDEX `EditToken` (`EditToken`) USING BTREE,
	INDEX `FK_assembly_item_history_assembly_item` (`AssemblyUnitId`) USING BTREE,
	INDEX `FK_assembly_unit_history_user` (`CreationUserId`) USING BTREE,
	CONSTRAINT `FK_assembly_unit_history_assembly_unit` FOREIGN KEY (`AssemblyUnitId`) REFERENCES `assembly_unit` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_assembly_unit_history_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;