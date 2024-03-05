CREATE TABLE `purchaseOrder_itemOrder` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`PurchaseOrderId` INT(11) UNSIGNED NOT NULL,
	`LineNumber` INT(11) UNSIGNED NOT NULL,
	`OrderReference` CHAR(50) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`SupplierPartId` INT(11) UNSIGNED NULL DEFAULT NULL,
	`SpecificationPartId` INT(11) UNSIGNED NULL DEFAULT NULL,
	`Sku` CHAR(50) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`Type` ENUM('Part','Generic','Specification Part') NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`PartNo` CHAR(10) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`ManufacturerName` TINYTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`ManufacturerPartNumber` TINYTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`Description` TINYTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`Note` TEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`Quantity` INT(10) UNSIGNED NOT NULL,
	`Price` DECIMAL(12,6) UNSIGNED NOT NULL DEFAULT '0.000000',
	`ExpectedReceiptDate` DATE NULL DEFAULT NULL,
	`VatTaxId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`Discount` FLOAT NULL DEFAULT NULL,
	`UnitOfMeasurementId` INT(10) UNSIGNED NOT NULL DEFAULT '29',
	`UnitOfMeasurementPrefixId` INT(10) UNSIGNED NOT NULL DEFAULT '21',
	`StockPart` BIT(1) NULL DEFAULT NULL,
	`CreationUserId` INT(10) UNSIGNED NOT NULL DEFAULT '1',
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`) USING BTREE,
	INDEX `FK_purchasOrder_itemOrder_supplierPart` (`SupplierPartId`) USING BTREE,
	INDEX `FK_purchasOrder_itemOrder_finance_tax` (`VatTaxId`) USING BTREE,
	INDEX `FK_purchasOrder_itemOrder_unitsOfMeasure` (`UnitOfMeasurementId`) USING BTREE,
	INDEX `FK_purchasOrder_itemOrder_unitOfMeasurement_prefix` (`UnitOfMeasurementPrefixId`) USING BTREE,
	INDEX `FK_purchasOrder_item_purchasOrder` (`PurchaseOrderId`) USING BTREE,
	INDEX `FK_purchaseOrder_itemOrder_specificationPart` (`SpecificationPartId`) USING BTREE,
	INDEX `FK_purchaseOrder_itemOrder_user` (`CreationUserId`) USING BTREE,
	CONSTRAINT `FK_purchasOrder_itemOrder_finance_tax` FOREIGN KEY (`VatTaxId`) REFERENCES `finance_tax` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_purchasOrder_itemOrder_supplierPart` FOREIGN KEY (`SupplierPartId`) REFERENCES `supplierPart` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_purchasOrder_itemOrder_unitOfMeasurement_prefix` FOREIGN KEY (`UnitOfMeasurementPrefixId`) REFERENCES `unitOfMeasurement_prefix` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_purchasOrder_itemOrder_unitsOfMeasure` FOREIGN KEY (`UnitOfMeasurementId`) REFERENCES `unitOfMeasurement` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_purchasOrder_item_purchasOrder` FOREIGN KEY (`PurchaseOrderId`) REFERENCES `purchaseOrder` (`Id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
	CONSTRAINT `FK_purchaseOrder_itemOrder_specificationPart` FOREIGN KEY (`SpecificationPartId`) REFERENCES `specificationPart` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_purchaseOrder_itemOrder_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;