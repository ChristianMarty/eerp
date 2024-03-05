CREATE TABLE `vendor_address` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`VendorId` INT(10) UNSIGNED NOT NULL,
	`CountryId` INT(10) UNSIGNED NOT NULL,
	`Street` TINYTEXT NOT NULL COLLATE 'utf8_general_ci',
	`PostalCode` TINYTEXT NOT NULL COLLATE 'utf8_general_ci',
	`City` TINYTEXT NOT NULL COLLATE 'utf8_general_ci',
	`VatTaxNumber` TINYTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`CustomsAccountNumber` TINYTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	PRIMARY KEY (`Id`) USING BTREE,
	INDEX `FK_business_address_country` (`CountryId`) USING BTREE,
	INDEX `FK_business_address_business` (`VendorId`) USING BTREE,
	CONSTRAINT `FK_business_address_business` FOREIGN KEY (`VendorId`) REFERENCES `vendor` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_business_address_country` FOREIGN KEY (`CountryId`) REFERENCES `country` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=5
;

