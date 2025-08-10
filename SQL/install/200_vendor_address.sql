CREATE TABLE `vendor_address` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`VendorId` INT(10) UNSIGNED NOT NULL,
	`CountryId` INT(10) UNSIGNED NOT NULL,
	`Street` TINYTEXT NOT NULL,
	`PostalCode` TINYTEXT NOT NULL,
	`City` TINYTEXT NOT NULL,
	`VatTaxNumber` TINYTEXT NULL DEFAULT NULL,
	`CustomsAccountNumber` TINYTEXT NULL DEFAULT NULL,
	PRIMARY KEY (`Id`),
	INDEX `FK_business_address_country` (`CountryId`),
	INDEX `FK_business_address_business` (`VendorId`),
	CONSTRAINT `FK_business_address_business` FOREIGN KEY (`VendorId`) REFERENCES `vendor` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_business_address_country` FOREIGN KEY (`CountryId`) REFERENCES `country` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
);

