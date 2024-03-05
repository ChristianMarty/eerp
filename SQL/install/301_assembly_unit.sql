CREATE TABLE `assembly_unit` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`ParentId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`AssemblyUnitNumber` INT(5) UNSIGNED ZEROFILL NOT NULL,
	`AssemblyId` INT(10) UNSIGNED NOT NULL,
	`LocationId` INT(10) UNSIGNED NOT NULL DEFAULT '1',
	`SerialNumber` TINYTEXT NOT NULL COLLATE 'utf8_general_ci',
	`Note` TEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`WorkOrderId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`) USING BTREE,
	UNIQUE INDEX `AssemblyItemNo` (`AssemblyUnitNumber`) USING BTREE,
	INDEX `FK_assembly_item_location` (`LocationId`) USING BTREE,
	INDEX `FK_assembly_item_workOrder` (`WorkOrderId`) USING BTREE,
	INDEX `FK_assembly_item_assembly` (`AssemblyId`) USING BTREE,
	INDEX `FK_assembly_unit_assembly_unit` (`ParentId`) USING BTREE,
	INDEX `FK_assembly_unit_user` (`CreationUserId`) USING BTREE,
	CONSTRAINT `FK_assembly_item_assembly` FOREIGN KEY (`AssemblyId`) REFERENCES `assembly` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_assembly_item_location` FOREIGN KEY (`LocationId`) REFERENCES `location` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_assembly_item_workOrder` FOREIGN KEY (`WorkOrderId`) REFERENCES `workOrder` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_assembly_unit_assembly_unit` FOREIGN KEY (`ParentId`) REFERENCES `assembly_unit` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_assembly_unit_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;