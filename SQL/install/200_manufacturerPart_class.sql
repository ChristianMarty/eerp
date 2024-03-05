CREATE TABLE `manufacturerPart_class` (
	`Id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`ParentId` INT(11) UNSIGNED NULL DEFAULT NULL,
	`Name` CHAR(50) NOT NULL DEFAULT '' COLLATE 'utf8_bin',
	`ShortName` CHAR(50) NULL DEFAULT NULL COLLATE 'utf8_bin',
	`NoParts` BIT(1) NULL DEFAULT b'0',
	`Hidden` BIT(1) NULL DEFAULT b'0',
	`SymbolPath` CHAR(50) NULL DEFAULT NULL COLLATE 'utf8_bin',
	`Prefix` CHAR(3) NULL DEFAULT NULL COLLATE 'utf8_bin',
	`AttributeList` TEXT NULL DEFAULT NULL COLLATE 'utf8_bin',
	`DescriptionTemplate` MEDIUMTEXT NULL DEFAULT NULL COLLATE 'utf8_bin',
	`MainLabelTemplate` TEXT NULL DEFAULT NULL COLLATE 'utf8_bin',
	`SmallLabelTemplate` MEDIUMTEXT NULL DEFAULT NULL COLLATE 'utf8_bin',
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`) USING BTREE,
	INDEX `FK_manufacturerPart_class_user` (`CreationUserId`) USING BTREE,
	INDEX `FK_manufacturerPart_class_manufacturerPart_class` (`ParentId`) USING BTREE,
	CONSTRAINT `FK_manufacturerPart_class_manufacturerPart_class` FOREIGN KEY (`ParentId`) REFERENCES `manufacturerPart_class` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_manufacturerPart_class_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8_bin'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
