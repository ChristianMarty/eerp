CREATE TABLE `supplierPart` (
	`Id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`ManufacturerPartNumberId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`VendorId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`SupplierPartNumber` CHAR(50) NULL DEFAULT NULL,
	`SupplierPartLink` MEDIUMTEXT NULL DEFAULT NULL,
	`Note` TINYTEXT NULL DEFAULT NULL,
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`),
	UNIQUE KEY `VendorId_SupplierPartNumber` (`VendorId`, `SupplierPartNumber`),
	INDEX `FK_supplierPart_vendor` (`VendorId`),
	INDEX `FK_supplierPart_manufacturerPart_partNumber` (`ManufacturerPartNumberId`),
	INDEX `FK_supplierPart_user` (`CreationUserId`),
	CONSTRAINT `FK_supplierPart_manufacturerPart_partNumber` FOREIGN KEY (`ManufacturerPartNumberId`) REFERENCES `manufacturerPart_partNumber` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_supplierPart_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_supplierPart_vendor` FOREIGN KEY (`VendorId`) REFERENCES `vendor` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
);
