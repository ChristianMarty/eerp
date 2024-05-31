CREATE TABLE `location` (
	`Id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`LocationNumber` INT(5) UNSIGNED ZEROFILL NOT NULL DEFAULT (cast(rand() * 100000 as signed)),
	`ParentId` INT(11) UNSIGNED NULL DEFAULT NULL,
	`LocationId` INT(11) UNSIGNED NULL DEFAULT NULL,
	`Name` TINYTEXT NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`Title` TINYTEXT NULL DEFAULT NULL COLLATE 'utf8_bin',
	`Description` TINYTEXT NULL DEFAULT NULL COLLATE 'utf8_bin',
	`Movable` BIT(1) NOT NULL DEFAULT b'0',
	`Virtual` BIT(1) NOT NULL DEFAULT b'0',
	`RecursionDepth` INT(11) NOT NULL DEFAULT '0',
	`ESD` BIT(1) NOT NULL DEFAULT b'0',
	`HoldingStock` INT(10) UNSIGNED NULL DEFAULT NULL,
	`EmptyWeight` FLOAT NULL DEFAULT NULL,
	`Cache_DisplayName` TEXT NULL DEFAULT NULL COLLATE 'utf8_bin',
	`Cache_DisplayLocation` TEXT NULL DEFAULT NULL COLLATE 'utf8_bin',
	`Cache_DisplayPath` TEXT NULL DEFAULT NULL COLLATE 'utf8_bin',
	`Cache_IdPath` TEXT NULL DEFAULT NULL COLLATE 'utf8_bin',
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`) USING BTREE,
	UNIQUE INDEX `LocNr` (`LocationNumber`) USING BTREE,
	INDEX `FK_locations_locations` (`LocationId`) USING BTREE,
	INDEX `FK_location_user` (`CreationUserId`) USING BTREE,
	INDEX `FK_location_location` (`ParentId`) USING BTREE,
	INDEX `FK_location_partStock` (`HoldingStock`) USING BTREE,
	CONSTRAINT `FK_location_location` FOREIGN KEY (`ParentId`) REFERENCES `location` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_location_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_locations_locations` FOREIGN KEY (`LocationId`) REFERENCES `location` (`Id`) ON UPDATE RESTRICT ON DELETE RESTRICT
)
COLLATE='utf8_bin'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
