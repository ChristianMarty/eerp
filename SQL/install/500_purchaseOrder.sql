CREATE TABLE `purchaseOrder` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`PurchaseOrderNumber` INT(10) UNSIGNED NOT NULL DEFAULT (cast(rand() * 100000 as signed)),
	`VendorId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`VendorAddressId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`VendorContactId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`ShippingContactId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`BillingContactId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`PurchaseContactId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`Carrier` TEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`PaymentTerms` TEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`InternationalCommercialTerms` TEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`HeadNote` TEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`FootNote` TEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`Spalte 26` TINYTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`OrderNumber` TINYTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`AcknowledgementNumber` TINYTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`QuotationNumber` TINYTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`Title` CHAR(150) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`PurchaseDate` DATE NOT NULL,
	`Description` MEDIUMTEXT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`Status` ENUM('Editing','Placed','Confirmed','Closed') NOT NULL DEFAULT 'Editing' COLLATE 'utf8_general_ci',
	`CurrencyId` INT(10) UNSIGNED NOT NULL DEFAULT '1',
	`ExchangeRate` FLOAT NOT NULL DEFAULT '1',
	`DocumentIds` TINYTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`CreationUserId` INT(11) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`) USING BTREE,
	UNIQUE INDEX `PurchaseOrderNumber` (`PurchaseOrderNumber`) USING BTREE,
	INDEX `FK_purchasOrder_finance_currency` (`CurrencyId`) USING BTREE,
	INDEX `FK_purchasOrder_vendor` (`VendorId`) USING BTREE,
	INDEX `FK_purchaseOrder_user` (`CreationUserId`) USING BTREE,
	CONSTRAINT `FK_purchasOrder_finance_currency` FOREIGN KEY (`CurrencyId`) REFERENCES `finance_currency` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_purchasOrder_vendor` FOREIGN KEY (`VendorId`) REFERENCES `vendor` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_purchaseOrder_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;