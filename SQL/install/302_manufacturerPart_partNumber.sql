CREATE TABLE `manufacturerPart_partNumber` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`VendorId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`ItemId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`Number` CHAR(50) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`MarkingCode` CHAR(50) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`PackageId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`VerifiedByUserId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`OctopartId` INT(11) UNSIGNED NULL DEFAULT NULL,
	`Description` TINYTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`ToDelete` BIT(1) NULL DEFAULT NULL,
	`Weight` FLOAT NULL DEFAULT NULL,
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	`Temp_OctopartData` TEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	PRIMARY KEY (`Id`) USING BTREE,
	UNIQUE INDEX `VendorId_ItemId_Number` (`VendorId`, `ItemId`, `Number`) USING BTREE,
	INDEX `FK_manufacturerPart_partNumber_user` (`VerifiedByUserId`) USING BTREE,
	INDEX `VendorId` (`VendorId`) USING BTREE,
	INDEX `ItemId` (`ItemId`) USING BTREE,
	INDEX `FK_manufacturerPart_partNumber_manufacturerPart_partPackage` (`PackageId`) USING BTREE,
	INDEX `FK_manufacturerPart_partNumber_user_2` (`CreationUserId`) USING BTREE,
	CONSTRAINT `FK_manufacturerPart_partNumber_manufacturerPart_item` FOREIGN KEY (`ItemId`) REFERENCES `manufacturerPart_item` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_manufacturerPart_partNumber_manufacturerPart_partPackage` FOREIGN KEY (`PackageId`) REFERENCES `manufacturerPart_partPackage` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_manufacturerPart_partNumber_user` FOREIGN KEY (`VerifiedByUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_manufacturerPart_partNumber_user_2` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_manufacturerPart_partNumber_vendor` FOREIGN KEY (`VendorId`) REFERENCES `vendor` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
