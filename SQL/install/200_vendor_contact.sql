CREATE TABLE `vendor_contact` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`VendorId` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	`VendorAddressId` INT(11) UNSIGNED NOT NULL DEFAULT '0',
	`Gender` ENUM('Male','Female') NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`FirstName` TINYTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`LastName` TINYTEXT NOT NULL COLLATE 'utf8_general_ci',
	`JobTitle` TINYTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`Language` ENUM('German','English') NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`Phone` TINYTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`E-Mail` TINYTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	PRIMARY KEY (`Id`) USING BTREE,
	INDEX `FK_business_contacts_business` (`VendorId`) USING BTREE,
	CONSTRAINT `FK_business_contacts_business` FOREIGN KEY (`VendorId`) REFERENCES `vendor` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=6
;
