CREATE TABLE `manufacturerPart_class` (
	`Id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`ParentId` INT(11) UNSIGNED NULL DEFAULT NULL,
	`Name` CHAR(50) NOT NULL,
	`ShortName` CHAR(50) NULL DEFAULT NULL,
	`NoParts` BIT(1) NULL DEFAULT b'0',
	`Hidden` BIT(1) NULL DEFAULT b'0',
	`AttributeList` TEXT NULL DEFAULT NULL COMMENT 'JSON Array of Attribute IDs',
	`DescriptionTemplate` MEDIUMTEXT NULL DEFAULT NULL,
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`),
	UNIQUE KEY `Name` (`Name`),
	INDEX `FK_manufacturerPart_class_user` (`CreationUserId`),
	INDEX `FK_manufacturerPart_class_manufacturerPart_class` (`ParentId`),
	CONSTRAINT `FK_manufacturerPart_class_manufacturerPart_class` FOREIGN KEY (`ParentId`) REFERENCES `manufacturerPart_class` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_manufacturerPart_class_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
);
