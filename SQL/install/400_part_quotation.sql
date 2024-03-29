CREATE TABLE `part_quotation` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`Price` DECIMAL(12,6) UNSIGNED NOT NULL DEFAULT '0.000000',
	`MinimumOrderQuantity` INT(11) NULL DEFAULT NULL,
	`IncrementalOrderQuantity` INT(11) NULL DEFAULT NULL,
	`LeadTime` INT(11) NULL DEFAULT NULL,
	`Weight` DECIMAL(4,3) UNSIGNED NULL DEFAULT '1.000',
	`CurrencyId` INT(10) UNSIGNED NOT NULL DEFAULT '1',
	`ProductionPartId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`ManufacturerPartNumberId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`SupplierId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`InformationSource` TEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`InformationDate` DATE NULL DEFAULT NULL,
	`Note` TEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` DATETIME NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`) USING BTREE,
	INDEX `FK_part_referencePrice_finance_currency` (`CurrencyId`) USING BTREE,
	INDEX `FK_part_referencePrice_productionPart` (`ProductionPartId`) USING BTREE,
	INDEX `FK_part_referencePrice_vendor` (`SupplierId`) USING BTREE,
	INDEX `FK_part_quotation_user` (`CreationUserId`) USING BTREE,
	CONSTRAINT `FK_part_quotation_finance_currency` FOREIGN KEY (`CurrencyId`) REFERENCES `finance_currency` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_part_quotation_productionPart` FOREIGN KEY (`ProductionPartId`) REFERENCES `productionPart` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_part_quotation_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_part_quotation_vendor` FOREIGN KEY (`SupplierId`) REFERENCES `vendor` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
