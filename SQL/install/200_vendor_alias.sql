CREATE TABLE `vendor_alias` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`VendorId` INT(10) UNSIGNED NOT NULL,
	`Name` CHAR(50) NOT NULL,
	`Note` TEXT NULL DEFAULT NULL,
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),	
	PRIMARY KEY (`Id`),
	UNIQUE KEY `Name` (`Name`),
	INDEX `VendorId` (`VendorId`),
	INDEX `FK_vendor_alias_user` (`CreationUserId`),
	CONSTRAINT `FK_vendor_alias_vendor` FOREIGN KEY (`VendorId`) REFERENCES `vendor` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_vendor_alias_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
);
