CREATE TABLE `billOfMaterial` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`BillOfMaterialNumber` INT(5) UNSIGNED ZEROFILL NOT NULL DEFAULT (cast(rand() * 100000 as unsigned)),
	`Name` TEXT NULL DEFAULT NULL,
	`Description` TEXT NULL DEFAULT NULL,
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`),
	UNIQUE KEY `BillOfMaterialNumber` (`BillOfMaterialNumber`),
	UNIQUE KEY `Name` (`Name`),
	INDEX `FK_billOfMaterial_user` (`CreationUserId`),
	CONSTRAINT `FK_billOfMaterial_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
);
