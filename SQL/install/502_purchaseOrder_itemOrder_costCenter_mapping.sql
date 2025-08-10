CREATE TABLE `purchaseOrder_itemOrder_costCenter_mapping` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`CostCenterId` INT(10) UNSIGNED NOT NULL,
	`ItemOrderId` INT(10) UNSIGNED NOT NULL,
	`Quota` DECIMAL(4,3) NOT NULL DEFAULT '1.000',
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`),
	UNIQUE INDEX `CostCenterId_ItemOrderId` (`CostCenterId`, `ItemOrderId`),
	INDEX `FK_purchasOrder_itemOrder_costCenter_mapping` (`ItemOrderId`),
	INDEX `FK_purchaseOrder_itemOrder_costCenter_mapping_user` (`CreationUserId`),
	CONSTRAINT `FK_purchasOrder_itemOrder_costCenter_mapping` FOREIGN KEY (`ItemOrderId`) REFERENCES `purchaseOrder_itemOrder` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_purchasOrder_itemOrder_costCenter_mapping_finance_costCenter` FOREIGN KEY (`CostCenterId`) REFERENCES `finance_costCenter` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_purchaseOrder_itemOrder_costCenter_mapping_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
);
