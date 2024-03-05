CREATE TABLE `purchaseOrder_additionalCharges` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`LineNumber` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`Quantity` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	`PurchaseOrderId` INT(10) UNSIGNED NOT NULL,
	`Type` ENUM('Shipping','Packaging','Handling','Other','Discount','Rounding','Import Tax') NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`VatTaxId` INT(10) UNSIGNED NOT NULL,
	`Price` FLOAT(10,4) NOT NULL DEFAULT '0.0000',
	`Description` TINYTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`) USING BTREE,
	INDEX `FK_purchasOrder_additionalCharges_finance_tax` (`VatTaxId`) USING BTREE,
	INDEX `FK_purchasOrder_additionalCharges_purchasOrder` (`PurchaseOrderId`) USING BTREE,
	INDEX `FK_purchaseOrder_additionalCharges_user` (`CreationUserId`) USING BTREE,
	CONSTRAINT `FK_purchasOrder_additionalCharges_finance_tax` FOREIGN KEY (`VatTaxId`) REFERENCES `finance_tax` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_purchaseOrder_additionalCharges_purchaseOrder` FOREIGN KEY (`PurchaseOrderId`) REFERENCES `purchaseOrder` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_purchaseOrder_additionalCharges_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
