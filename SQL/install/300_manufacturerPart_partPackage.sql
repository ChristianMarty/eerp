CREATE TABLE `manufacturerPart_partPackage` (
	`Id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`ParentId` INT(11) UNSIGNED NULL DEFAULT NULL,
	`Name` CHAR(50) NOT NULL DEFAULT '0' COLLATE 'utf8_bin',
	`Alias` CHAR(50) NULL DEFAULT NULL COLLATE 'utf8_bin',
	`SMD` BIT(1) NULL DEFAULT NULL,
	`PinCount` INT(11) UNSIGNED NULL DEFAULT NULL,
	`CreationUserId` INT(11) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`) USING BTREE,
	UNIQUE INDEX `Name_Alias` (`Name`, `Alias`) USING BTREE,
	INDEX `FK_manufacturerPart_partPackage_user` (`CreationUserId`) USING BTREE,
	INDEX `FK_manufacturerPart_partPackage_manufacturerPart_partPackage` (`ParentId`) USING BTREE,
	CONSTRAINT `FK_manufacturerPart_partPackage_manufacturerPart_partPackage` FOREIGN KEY (`ParentId`) REFERENCES `manufacturerPart_partPackage` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_manufacturerPart_partPackage_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8_bin'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
