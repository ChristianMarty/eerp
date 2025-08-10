CREATE TABLE `peripheral` (
	`Id` INT(11) NOT NULL AUTO_INCREMENT,
	`Name` CHAR(50) NOT NULL,
	`DeviceType` ENUM('printer','scale') NOT NULL,
	`Ip` CHAR(50) NOT NULL,
	`Port` INT(11) NOT NULL,
	`Language` ENUM('ZPL','ESCPOS') NULL DEFAULT NULL,
	`Type` ENUM('Bon','Label') NULL DEFAULT NULL,
	`Description` TINYTEXT NULL DEFAULT NULL,
	`Driver` TINYTEXT NULL DEFAULT NULL,
	PRIMARY KEY (`Id`),
	UNIQUE KEY `Name` (`Name`)
);
