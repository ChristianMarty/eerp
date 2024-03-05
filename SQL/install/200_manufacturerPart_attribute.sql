CREATE TABLE `manufacturerPart_attribute` (
	`Id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`ParentId` INT(11) UNSIGNED NULL DEFAULT NULL,
	`Name` MEDIUMTEXT NOT NULL COLLATE 'utf8_bin',
	`UnitOfMeasurementId` INT(11) UNSIGNED NULL DEFAULT NULL,
	`Symbol` MEDIUMTEXT NULL DEFAULT NULL COLLATE 'utf8_bin',
	`Type` ENUM('string','integer','bool','float') NULL DEFAULT NULL COLLATE 'utf8_bin',
	`Scale` INT(11) NOT NULL DEFAULT '1',
	`UseMinTypMax` BIT(1) NOT NULL DEFAULT b'0',
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`) USING BTREE,
	INDEX `UnitId` (`UnitOfMeasurementId`) USING BTREE,
	INDEX `FK_manufacturerPart_attribute_user` (`CreationUserId`) USING BTREE,
	INDEX `FK_manufacturerPart_attribute_manufacturerPart_attribute` (`ParentId`) USING BTREE,
	CONSTRAINT `FK_manufacturerPart_attribute_manufacturerPart_attribute` FOREIGN KEY (`ParentId`) REFERENCES `manufacturerPart_attribute` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_manufacturerPart_attribute_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_partAttribute_unitOfMeasurement` FOREIGN KEY (`UnitOfMeasurementId`) REFERENCES `unitOfMeasurement` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8_bin'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
