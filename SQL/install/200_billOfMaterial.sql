CREATE TABLE `billOfMaterial` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`BillOfMaterialNumber` INT(5) UNSIGNED ZEROFILL NULL DEFAULT NULL,
	`Name` TEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`Description` TEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`) USING BTREE,
	INDEX `FK_billOfMaterial_user` (`CreationUserId`) USING BTREE,
	CONSTRAINT `FK_billOfMaterial_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
