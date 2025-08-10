CREATE TABLE `specificationPart_revision` (
	`Id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`SpecificationPartId` INT(11) UNSIGNED NOT NULL,
	`Revision` TINYTEXT NOT NULL,
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`),
	INDEX `FK_specificationPart_revision_specificationPart` (`SpecificationPartId`),
	INDEX `FK_specificationPart_revision_user` (`CreationUserId`),
	CONSTRAINT `FK_specificationPart_revision_specificationPart` FOREIGN KEY (`SpecificationPartId`) REFERENCES `specificationPart` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_specificationPart_revision_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
);
