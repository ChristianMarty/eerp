CREATE TABLE `location` (
	`Id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`LocationNumber` INT(5) UNSIGNED ZEROFILL NOT NULL DEFAULT (cast(rand() * 100000 as signed)),
	`ParentId` INT(11) UNSIGNED NULL DEFAULT NULL,
	`LocationId` INT(11) UNSIGNED NULL DEFAULT NULL,
	`Name` TINYTEXT NOT NULL,
	`Title` TINYTEXT NULL DEFAULT NULL,
	`Description` TINYTEXT NULL DEFAULT NULL,
	`Movable` BIT(1) NOT NULL DEFAULT b'0',
	`Virtual` BIT(1) NOT NULL DEFAULT b'0',
	`RecursionDepth` INT(11) NOT NULL DEFAULT '0',
	`ESD` BIT(1) NOT NULL DEFAULT b'0',
	`HoldingStock` INT(10) UNSIGNED NULL DEFAULT NULL,
	`EmptyWeight` FLOAT NULL DEFAULT NULL,
	`Cache_DisplayName` TEXT NULL DEFAULT NULL,
	`Cache_DisplayLocation` TEXT NULL DEFAULT NULL,
	`Cache_DisplayPath` TEXT NULL DEFAULT NULL,
	`Cache_IdPath` TEXT NULL DEFAULT NULL,
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`),
	UNIQUE KEY `LocationNumber` (`LocationNumber`),
	INDEX `FK_locations_locations` (`LocationId`),
	INDEX `FK_location_user` (`CreationUserId`),
	INDEX `FK_location_location` (`ParentId`),
	INDEX `FK_location_partStock` (`HoldingStock`),
	CONSTRAINT `FK_location_location` FOREIGN KEY (`ParentId`) REFERENCES `location` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_location_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_locations_locations` FOREIGN KEY (`LocationId`) REFERENCES `location` (`Id`) ON UPDATE RESTRICT ON DELETE RESTRICT
);

INSERT INTO `location` (`Id`, `LocationNumber`, `Name`, `Movable`, `Virtual`, `CreationUserId`) VALUES 
(1, 00000, 'Unassigned', b'0', b'0', 1);


