CREATE TABLE `manufacturerPart_item` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`SeriesId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`VendorId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`PackageId` INT(11) UNSIGNED NULL DEFAULT NULL,
	`PartClassId` INT(11) UNSIGNED NULL DEFAULT NULL,
	`Attribute` TEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`Number` CHAR(100) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`Temp_NumberTemplate` CHAR(100) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`MarkingCode` CHAR(50) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`Description` TINYTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`VerifiedByUserId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`DocumentIds` TINYTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`Parameter` TEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`ToDelete` BIT(1) NULL DEFAULT NULL,
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`) USING BTREE,
	INDEX `FK_manufacturerPart_item_manufacturerPart_series` (`SeriesId`) USING BTREE,
	INDEX `FK_manufacturerPart_item_partPackage` (`PackageId`) USING BTREE,
	INDEX `FK_manufacturerPart_item_user` (`VerifiedByUserId`) USING BTREE,
	INDEX `FK_manufacturerPart_item_vendor` (`VendorId`) USING BTREE,
	INDEX `FK_manufacturerPart_item_manufacturerPart_class` (`PartClassId`) USING BTREE,
	INDEX `FK_manufacturerPart_item_user_2` (`CreationUserId`) USING BTREE,
	CONSTRAINT `FK_manufacturerPart_item_manufacturerPart_class` FOREIGN KEY (`PartClassId`) REFERENCES `manufacturerPart_class` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_manufacturerPart_item_manufacturerPart_series` FOREIGN KEY (`SeriesId`) REFERENCES `manufacturerPart_series` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_manufacturerPart_item_partPackage` FOREIGN KEY (`PackageId`) REFERENCES `manufacturerPart_partPackage` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_manufacturerPart_item_user` FOREIGN KEY (`VerifiedByUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_manufacturerPart_item_user_2` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_manufacturerPart_item_vendor` FOREIGN KEY (`VendorId`) REFERENCES `vendor` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
