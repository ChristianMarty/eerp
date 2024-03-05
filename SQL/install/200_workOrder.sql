CREATE TABLE `workOrder` (
	`Id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`WorkOrderNumber` INT(11) UNSIGNED NOT NULL DEFAULT (cast(rand() * 100000 as signed)),
	`Name` CHAR(50) NOT NULL COLLATE 'utf8_general_ci',
	`Status` ENUM('Preparing','InProgress','Complete') NOT NULL DEFAULT 'Preparing' COLLATE 'utf8_general_ci',
	`Quantity` INT(10) UNSIGNED NOT NULL,
	`ProjectId` INT(11) UNSIGNED NOT NULL,
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`) USING BTREE,
	UNIQUE INDEX `WorkOrderNumber` (`WorkOrderNumber`) USING BTREE,
	INDEX `FK_workOrder_project` (`ProjectId`) USING BTREE,
	INDEX `FK_workOrder_user` (`CreationUserId`) USING BTREE,
	CONSTRAINT `FK_workOrder_project` FOREIGN KEY (`ProjectId`) REFERENCES `project` (`Id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
	CONSTRAINT `FK_workOrder_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
