CREATE TABLE `specificationPart` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`Number` INT(5) UNSIGNED ZEROFILL NOT NULL DEFAULT (cast(rand() * 100000 as unsigned)),
	`Name` CHAR(50) NOT NULL,
	`Type` ENUM('PCB','PCB Stencil','Acrylic Plate') NOT NULL DEFAULT 'PCB',
	`Description` TEXT NULL DEFAULT NULL,
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`),
	UNIQUE KEY `Number` (`Number`),
	UNIQUE KEY `Name` (`Name`),
	INDEX `FK_specificationPart_user` (`CreationUserId`),
	CONSTRAINT `FK_specificationPart_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
);
