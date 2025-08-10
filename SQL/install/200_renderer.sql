CREATE TABLE `renderer` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`Name` TINYTEXT NOT NULL,
	`Description` TEXT NULL DEFAULT NULL,
	`DatasetId` INT(10) UNSIGNED NOT NULL,
	`MediumId` INT(10) UNSIGNED NULL,
	`Render` ENUM('Template','PHP') NOT NULL,
	`Language` ENUM('ZPL','ESCPOS','HTML') NOT NULL,
	`Code` MEDIUMTEXT NULL DEFAULT NULL COLLATE 'ascii_bin',
	PRIMARY KEY (`Id`) USING BTREE,
	UNIQUE KEY `Name` (`Name`),
	INDEX `FK_renderer_renderer_dataset` (`DatasetId`) USING BTREE,
	INDEX `FK_renderer_renderer_medium` (`MediumId`) USING BTREE,
	CONSTRAINT `FK_renderer_renderer_dataset` FOREIGN KEY (`DatasetId`) REFERENCES `renderer_dataset` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_renderer_renderer_medium` FOREIGN KEY (`MediumId`) REFERENCES `renderer_medium` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
);
