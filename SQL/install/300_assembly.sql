CREATE TABLE `assembly` (
	`Id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`AssemblyNumber` INT(5) UNSIGNED ZEROFILL NOT NULL,
	`Name` TINYTEXT NOT NULL COLLATE 'utf8_general_ci',
	`Description` TINYTEXT NOT NULL COLLATE 'utf8_general_ci',
	`ProductionPartId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`) USING BTREE,
	UNIQUE INDEX `AssemblyNo` (`AssemblyNumber`) USING BTREE,
	INDEX `FK_assembly_user` (`CreationUserId`) USING BTREE,
	INDEX `FK_assembly_productionPart` (`ProductionPartId`) USING BTREE,
	CONSTRAINT `FK_assembly_productionPart` FOREIGN KEY (`ProductionPartId`) REFERENCES `productionPart` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_assembly_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
