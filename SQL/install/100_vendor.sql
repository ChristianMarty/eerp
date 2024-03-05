CREATE TABLE `vendor` (
	`Id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`ParentId` INT(11) UNSIGNED NULL DEFAULT NULL,
	`FullName` CHAR(50) NOT NULL COLLATE 'utf8_general_ci',
	`CustomerNumber` CHAR(50) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`ShortName` CHAR(50) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`AbbreviatedName` CHAR(10) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`IsSupplier` BIT(1) NOT NULL DEFAULT b'0',
	`IsManufacturer` BIT(1) NOT NULL DEFAULT b'0',
	`IsContractor` BIT(1) NOT NULL DEFAULT b'0',
	`IsCarrier` BIT(1) NOT NULL DEFAULT b'0',
	`IsCustomer` BIT(1) NOT NULL DEFAULT b'0',
	`Note` TEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`API` TINYTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`ApiData` LONGTEXT NULL DEFAULT NULL COLLATE 'utf8mb4_bin',
	`PartNumberPreprocessor` TINYTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`) USING BTREE,
	UNIQUE INDEX `Name` (`FullName`) USING BTREE,
	UNIQUE INDEX `AbbreviatedName` (`AbbreviatedName`) USING BTREE,
	UNIQUE INDEX `ShortName` (`ShortName`) USING BTREE,
	INDEX `FK_vendor_user` (`CreationUserId`) USING BTREE,
	CONSTRAINT `FK_vendor_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;