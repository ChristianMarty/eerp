CREATE TABLE `peripheral` (
	`Id` INT(11) NOT NULL AUTO_INCREMENT,
	`Name` CHAR(50) NOT NULL COLLATE 'utf8_general_ci',
	`DeviceType` ENUM('printer','scale') NOT NULL DEFAULT 'printer' COLLATE 'utf8_general_ci',
	`Ip` CHAR(50) NOT NULL COLLATE 'utf8_general_ci',
	`Port` INT(11) NOT NULL,
	`Language` ENUM('ZPL','ESCPOS') NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`Type` ENUM('Bon','Label') NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`Description` TINYTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`Driver` TINYTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	PRIMARY KEY (`Id`) USING BTREE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
