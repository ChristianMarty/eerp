CREATE TABLE `vendor_address` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`VendorId` INT(10) UNSIGNED NOT NULL,
	`CountryId` INT(10) UNSIGNED NOT NULL,
	`Street` TINYTEXT NOT NULL,
	`PostalCode` TINYTEXT NOT NULL,
	`City` TINYTEXT NOT NULL,
	`VatTaxNumber` TINYTEXT NULL DEFAULT NULL,
	`CustomsAccountNumber` TINYTEXT NULL DEFAULT NULL,
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),	
	PRIMARY KEY (`Id`),
	INDEX `FK_vendors_address_country` (`CountryId`),
	INDEX `FK_vendor_business` (`VendorId`),
	INDEX `FK_vendor_address_user` (`CreationUserId`),
	CONSTRAINT `FK_vendor_address_business` FOREIGN KEY (`VendorId`) REFERENCES `vendor` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_vendor_address_country` FOREIGN KEY (`CountryId`) REFERENCES `country` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_vendor_address_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
);
