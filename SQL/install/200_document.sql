CREATE TABLE `document` (
	`Id` INT(11) NOT NULL AUTO_INCREMENT,
	`DocumentNumber` INT(5) UNSIGNED ZEROFILL NOT NULL DEFAULT (cast(rand() * 100000 as unsigned)),
	`Path` MEDIUMTEXT NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`Name` TEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`Note` TEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`Type` ENUM('Manual','Datasheet','Invoice','Receipt','Calibration','Unknown','Certificate','Software','Confirmation','DeliveryNote','Quote') NOT NULL DEFAULT 'Unknown' COLLATE 'utf8_general_ci',
	`LinkType` ENUM('Internal','External') NOT NULL DEFAULT 'Internal' COLLATE 'utf8_general_ci',
	`Hash` CHAR(32) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`) USING BTREE,
	UNIQUE INDEX `DocNo` (`DocumentNumber`) USING BTREE,
	UNIQUE INDEX `Hash` (`Hash`) USING BTREE,
	INDEX `FK_document_user` (`CreationUserId`) USING BTREE,
	CONSTRAINT `FK_document_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
