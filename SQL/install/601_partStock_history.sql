CREATE TABLE `partStock_history` (
	`Id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`StockId` INT(11) UNSIGNED NOT NULL,
	`ChangeType` ENUM('Relative','Absolute','Create') NOT NULL COLLATE 'utf8_general_ci',
	`Quantity` INT(11) NOT NULL,
	`WorkOrderId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`EditToken` CHAR(32) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`Note` TEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`) USING BTREE,
	UNIQUE INDEX `EditToken` (`EditToken`) USING BTREE,
	INDEX `FK_partStock_history_partStock` (`StockId`) USING BTREE,
	INDEX `FK_partStock_history_workOrder` (`WorkOrderId`) USING BTREE,
	INDEX `FK_partStock_history_user` (`CreationUserId`) USING BTREE,
	CONSTRAINT `FK_partStock_history_partStock` FOREIGN KEY (`StockId`) REFERENCES `partStock` (`Id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
	CONSTRAINT `FK_partStock_history_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_partStock_history_workOrder` FOREIGN KEY (`WorkOrderId`) REFERENCES `workOrder` (`Id`) ON UPDATE RESTRICT ON DELETE RESTRICT
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
