CREATE TABLE `finance_costCenter` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`CostCenterNumber` INT(5) UNSIGNED ZEROFILL NOT NULL DEFAULT (cast(rand() * 100000 as unsigned)),
	`ParentId` INT(11) UNSIGNED NULL DEFAULT NULL,
	`Name` TINYTEXT NOT NULL,
	`Description` TINYTEXT NULL DEFAULT NULL,
	`ProjectId` INT(11) UNSIGNED NULL DEFAULT NULL,
	`Color` CHAR(7) NULL DEFAULT NULL,
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`),
	UNIQUE KEY `Name` (`Name`),
	INDEX `FK_finance_costCenter_project` (`ProjectId`),
	INDEX `FK_finance_costCenter_user` (`CreationUserId`),
	CONSTRAINT `FK_finance_costCenter_project` FOREIGN KEY (`ProjectId`) REFERENCES `project` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_finance_costCenter_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
);
