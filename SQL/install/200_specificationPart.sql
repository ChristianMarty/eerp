CREATE TABLE `specificationPart` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`Number` INT(5) UNSIGNED ZEROFILL NOT NULL DEFAULT (cast(rand() * 100000 as unsigned)),
	`Name` CHAR(50) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`Type` ENUM('PCB','PCB Stencil','Acrylic Plate') NOT NULL DEFAULT 'PCB' COLLATE 'utf8_general_ci',
	`Description` TEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`) USING BTREE,
	INDEX `FK_specificationPart_user` (`CreationUserId`) USING BTREE,
	CONSTRAINT `FK_specificationPart_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
