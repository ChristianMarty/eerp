CREATE TABLE `productionPart` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`NumberingPrefixId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`Number` CHAR(10) NOT NULL COLLATE 'utf8_general_ci',
	`Description` CHAR(100) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`StockMinimum` INT(11) UNSIGNED NULL DEFAULT NULL,
	`StockMaximum` INT(11) UNSIGNED NULL DEFAULT NULL,
	`StockWarning` INT(11) UNSIGNED NULL DEFAULT NULL,
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	`Cache_ReferencePrice_WeightedAverage` DECIMAL(20,10) UNSIGNED NULL DEFAULT NULL,
	`Cache_ReferencePrice_Minimum` DECIMAL(20,10) UNSIGNED NULL DEFAULT NULL,
	`Cache_ReferencePrice_Maximum` DECIMAL(20,10) UNSIGNED NULL DEFAULT NULL,
	`Cache_ReferenceLeadTime_WeightedAverage` INT(10) UNSIGNED NULL DEFAULT NULL,
	`Cache_PurchasePrice_WeightedAverage` DECIMAL(20,10) UNSIGNED NULL DEFAULT NULL,
	`Cache_Sourcing_NumberOfManufacturers` INT(11) UNSIGNED NULL DEFAULT NULL,
	`Cache_Sourcing_NumberOfParts` INT(11) UNSIGNED NULL DEFAULT NULL,
	`Cache_BillOfMaterial_TotalQuantityUsed` INT(11) UNSIGNED NULL DEFAULT NULL,
	`Cache_BillOfMaterial_NumberOfOccurrence` INT(11) UNSIGNED NULL DEFAULT NULL,
	PRIMARY KEY (`Id`) USING BTREE,
	UNIQUE INDEX `NumberingPrefixId_neu` (`NumberingPrefixId`, `Number`) USING BTREE,
	CONSTRAINT `FK_productionPart_numbering` FOREIGN KEY (`NumberingPrefixId`) REFERENCES `numbering` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
