CREATE TABLE `vendor_contact` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`VendorId` INT(11) UNSIGNED NOT NULL,
	`VendorAddressId` INT(11) UNSIGNED NOT NULL,
	`Gender` ENUM('Male','Female') NULL DEFAULT NULL,
	`FirstName` TINYTEXT NULL DEFAULT NULL,
	`LastName` TINYTEXT NOT NULL,
	`JobTitle` TINYTEXT NULL DEFAULT NULL,
	`Language` ENUM('German','English') NULL DEFAULT NULL,
	`Phone` TINYTEXT NULL DEFAULT NULL,
	`E-Mail` TINYTEXT NULL DEFAULT NULL,
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),	
	PRIMARY KEY (`Id`),
	INDEX `FK_business_contacts_business` (`VendorId`),
	INDEX `FK_vendor_contacts_user` (`CreationUserId`),
	CONSTRAINT `FK_business_contacts_business` FOREIGN KEY (`VendorId`) REFERENCES `vendor` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_vendor_contacts_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
);
