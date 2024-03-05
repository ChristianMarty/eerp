CREATE TABLE `renderer` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`Name` TINYTEXT NOT NULL COLLATE 'utf8_general_ci',
	`Description` TEXT NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`Render` ENUM('Template','PHP') NOT NULL COLLATE 'utf8_general_ci',
	`Language` ENUM('ZPL','ESCPOS','HTML') NOT NULL COLLATE 'utf8_general_ci',
	`Tag` TINYTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`Variables` MEDIUMTEXT NULL DEFAULT NULL COLLATE 'ascii_bin',
	`Code` MEDIUMTEXT NULL DEFAULT NULL COLLATE 'ascii_bin',
	`Hight` DECIMAL(6,2) NOT NULL DEFAULT '0.00',
	`Width` DECIMAL(6,2) NOT NULL DEFAULT '0.00',
	`Rotation` ENUM('0','90','180','270') NOT NULL DEFAULT '0' COLLATE 'utf8_general_ci',
	`Resolution` ENUM('6dpmm','8dpmm','12dpmm','24dpmm') NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	PRIMARY KEY (`Id`) USING BTREE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
