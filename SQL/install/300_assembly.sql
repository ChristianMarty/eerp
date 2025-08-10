CREATE TABLE `assembly` (
	`Id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`AssemblyNumber` INT(5) UNSIGNED ZEROFILL NOT NULL DEFAULT (cast(rand() * 100000 as unsigned)),
	`Name` TINYTEXT NOT NULL,
	`Description` TINYTEXT NULL DEFAULT NULL,
	`ProductionPartId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`),
	UNIQUE KEY `AssemblyNumber` (`AssemblyNumber`),
	UNIQUE KEY `Name` (`Name`),
	INDEX `FK_assembly_user` (`CreationUserId`),
	INDEX `FK_assembly_productionPart` (`ProductionPartId`),
	CONSTRAINT `FK_assembly_productionPart` FOREIGN KEY (`ProductionPartId`) REFERENCES `productionPart` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_assembly_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
);
