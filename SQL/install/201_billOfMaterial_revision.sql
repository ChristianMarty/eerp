CREATE TABLE `billOfMaterial_revision` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`BillOfMaterialId` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`Type` ENUM('Revision','Deviation') NOT NULL COLLATE 'utf8_general_ci',
	`Status` ENUM('Editing','Review','Approved') NOT NULL COLLATE 'utf8_general_ci',
	`VersionNumber` DOUBLE(3,2) NULL DEFAULT NULL,
	`ParentId` INT(10) UNSIGNED NULL DEFAULT '0',
	`Title` TEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`Description` TEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`) USING BTREE,
	INDEX `FK_billOfMaterial_version_billOfMaterial` (`BillOfMaterialId`) USING BTREE,
	INDEX `FK_billOfMaterial_revision_user` (`CreationUserId`) USING BTREE,
	CONSTRAINT `FK_billOfMaterial_revision_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_billOfMaterial_version_billOfMaterial` FOREIGN KEY (`BillOfMaterialId`) REFERENCES `billOfMaterial` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
