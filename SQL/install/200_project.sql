CREATE TABLE `project` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`ProjectNumber` INT(5) UNSIGNED NOT NULL DEFAULT (cast(rand() * 100000 as signed)),
	`Name` TINYTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`Description` MEDIUMTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`) USING BTREE,
	INDEX `FK_project_user` (`CreationUserId`) USING BTREE,
	CONSTRAINT `FK_project_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
