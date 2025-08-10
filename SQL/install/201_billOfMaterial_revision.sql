CREATE TABLE `billOfMaterial_revision` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`BillOfMaterialId` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`Type` ENUM('Revision','Deviation') NOT NULL DEFAULT 'Revision',
	`Status` ENUM('Editing','Review','Approved') NOT NULL DEFAULT 'Editing',
	`VersionNumber` DOUBLE(3,2) NOT NULL,
	`ParentId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`Name` TEXT NULL DEFAULT NULL,
	`Description` TEXT NULL DEFAULT NULL,
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`),
	INDEX `FK_billOfMaterial_version_billOfMaterial` (`BillOfMaterialId`),
	INDEX `FK_billOfMaterial_revision_user` (`CreationUserId`),
	CONSTRAINT `FK_billOfMaterial_revision_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_billOfMaterial_version_billOfMaterial` FOREIGN KEY (`BillOfMaterialId`) REFERENCES `billOfMaterial` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
);
