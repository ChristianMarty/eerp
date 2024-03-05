CREATE TABLE `finance_costCenter` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`CostCenterNumber` INT(5) UNSIGNED ZEROFILL NOT NULL,
	`ParentId` INT(11) UNSIGNED NULL DEFAULT NULL,
	`Name` TINYTEXT NOT NULL COLLATE 'utf8_general_ci',
	`Description` TINYTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`ProjectId` INT(11) UNSIGNED NULL DEFAULT NULL,
	`Color` CHAR(7) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`) USING BTREE,
	INDEX `FK_finance_costCenter_project` (`ProjectId`) USING BTREE,
	INDEX `FK_finance_costCenter_user` (`CreationUserId`) USING BTREE,
	CONSTRAINT `FK_finance_costCenter_project` FOREIGN KEY (`ProjectId`) REFERENCES `project` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_finance_costCenter_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
