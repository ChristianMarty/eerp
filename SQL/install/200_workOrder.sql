CREATE TABLE `workOrder` (
	`Id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`WorkOrderNumber` INT(5) UNSIGNED ZEROFILL NOT NULL DEFAULT (cast(rand() * 100000 as unsigned)),
	`Name` CHAR(50) NOT NULL,
	`Status` ENUM('Preparing','InProgress','Complete') NOT NULL DEFAULT 'Preparing',
	`Quantity` INT(10) UNSIGNED NOT NULL,
	`ProjectId` INT(11) UNSIGNED NULL DEFAULT NULL,
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`),
	UNIQUE KEY `WorkOrderNumber` (`WorkOrderNumber`),
	INDEX `FK_workOrder_project` (`ProjectId`),
	INDEX `FK_workOrder_user` (`CreationUserId`),
	CONSTRAINT `FK_workOrder_project` FOREIGN KEY (`ProjectId`) REFERENCES `project` (`Id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
	CONSTRAINT `FK_workOrder_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
);
