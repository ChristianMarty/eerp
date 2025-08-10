CREATE TABLE `manufacturerPart_attribute` (
	`Id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`ParentId` INT(11) UNSIGNED NULL DEFAULT NULL,
	`Name` MEDIUMTEXT NOT NULL,
	`UnitOfMeasurementId` INT(11) UNSIGNED NULL DEFAULT NULL,
	`Symbol` MEDIUMTEXT NULL DEFAULT NULL COMMENT 'Overrides the symbol from UnitOfMeasurement',
	`Type` ENUM('string','integer','bool','float') NULL DEFAULT NULL,
	`Options` MEDIUMTEXT NULL DEFAULT NULL COMMENT 'JSON Array of options to restrict value',
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`),
	UNIQUE KEY `Name` (`Name`),
	INDEX `UnitId` (`UnitOfMeasurementId`),
	INDEX `FK_manufacturerPart_attribute_user` (`CreationUserId`),
	INDEX `FK_manufacturerPart_attribute_manufacturerPart_attribute` (`ParentId`),
	CONSTRAINT `FK_manufacturerPart_attribute_manufacturerPart_attribute` FOREIGN KEY (`ParentId`) REFERENCES `manufacturerPart_attribute` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_manufacturerPart_attribute_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_partAttribute_unitOfMeasurement` FOREIGN KEY (`UnitOfMeasurementId`) REFERENCES `unitOfMeasurement` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
);
