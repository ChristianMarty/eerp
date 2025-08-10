CREATE TABLE `document` (
	`Id` INT(11) NOT NULL AUTO_INCREMENT,
	`DocumentNumber` INT(5) UNSIGNED ZEROFILL NOT NULL DEFAULT (cast(rand() * 100000 as unsigned)),
	`Path` MEDIUMTEXT NOT NULL,
	`Name` TEXT NOT NULL,
	`Note` TEXT NULL DEFAULT NULL,
	`Type` ENUM('Manual','Datasheet','Invoice','Receipt','Calibration','Unknown','Certificate','Software','Confirmation','DeliveryNote','Quote') NOT NULL DEFAULT 'Unknown',
	`LinkType` ENUM('Internal','External') NOT NULL DEFAULT 'Internal',
	`Hash` CHAR(32) NULL DEFAULT NULL,
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`),
	UNIQUE KEY `DocumentNumber` (`DocumentNumber`),
	UNIQUE KEY `Hash` (`Hash`),
	INDEX `FK_document_user` (`CreationUserId`),
	CONSTRAINT `FK_document_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
);
