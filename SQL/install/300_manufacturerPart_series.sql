CREATE TABLE `manufacturerPart_series` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`Title` TINYTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`Description` TINYTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`VendorId` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`NumberTemplate` TINYTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`ClassId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`DocumentIds` TEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`SeriesNameMatch` TEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`Parameter` TEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`VerifiedByUserId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`) USING BTREE,
	INDEX `FK_manufacturerPartSeries_vendor` (`VendorId`) USING BTREE,
	INDEX `FK_manufacturerPartSeries_partClass` (`ClassId`) USING BTREE,
	INDEX `FK_manufacturerPart_series_user` (`VerifiedByUserId`) USING BTREE,
	INDEX `FK_manufacturerPart_series_user_2` (`CreationUserId`) USING BTREE,
	CONSTRAINT `FK_manufacturerPartSeries_partClass` FOREIGN KEY (`ClassId`) REFERENCES `manufacturerPart_class` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_manufacturerPartSeries_vendor` FOREIGN KEY (`VendorId`) REFERENCES `vendor` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_manufacturerPart_series_user` FOREIGN KEY (`VerifiedByUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_manufacturerPart_series_user_2` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
