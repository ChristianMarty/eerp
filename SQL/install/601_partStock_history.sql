CREATE TABLE `partStock_history` (
	`Id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`StockId` INT(11) UNSIGNED NOT NULL,
	`ChangeType` ENUM('Relative','Absolute','Create') NOT NULL,
	`Quantity` INT(11) NOT NULL,
	`WorkOrderId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`EditToken` CHAR(32) NULL DEFAULT NULL,
	`Note` TEXT NULL DEFAULT NULL,
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`),
	UNIQUE INDEX `EditToken` (`EditToken`),
	INDEX `FK_partStock_history_partStock` (`StockId`),
	INDEX `FK_partStock_history_workOrder` (`WorkOrderId`),
	INDEX `FK_partStock_history_user` (`CreationUserId`),
	CONSTRAINT `FK_partStock_history_partStock` FOREIGN KEY (`StockId`) REFERENCES `partStock` (`Id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
	CONSTRAINT `FK_partStock_history_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_partStock_history_workOrder` FOREIGN KEY (`WorkOrderId`) REFERENCES `workOrder` (`Id`) ON UPDATE RESTRICT ON DELETE RESTRICT
);
