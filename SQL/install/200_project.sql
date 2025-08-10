CREATE TABLE `project` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`ProjectNumber` INT(5) UNSIGNED ZEROFILL NOT NULL DEFAULT (cast(rand() * 100000 as unsigned)),
	`Name` TINYTEXT NOT NULL,
	`Description` MEDIUMTEXT NULL DEFAULT NULL,
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`),
	UNIQUE KEY `ProjectNumber` (`ProjectNumber`),
	UNIQUE KEY `Name` (`Name`),
	INDEX `FK_project_user` (`CreationUserId`),
	CONSTRAINT `FK_project_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
);
