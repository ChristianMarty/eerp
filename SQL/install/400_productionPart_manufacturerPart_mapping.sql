CREATE TABLE `productionPart_manufacturerPart_mapping` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`ManufacturerPartNumberId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`ProductionPartId` INT(10) UNSIGNED NOT NULL,
	PRIMARY KEY (`Id`) USING BTREE,
	UNIQUE INDEX `ManufacturerPartNumberId_ProductionPartId` (`ManufacturerPartNumberId`, `ProductionPartId`) USING BTREE,
	INDEX `FK_productionPartMapping_productionPart` (`ProductionPartId`) USING BTREE,
	CONSTRAINT `FK_productionPartMapping_manufacturerPart_partNumber` FOREIGN KEY (`ManufacturerPartNumberId`) REFERENCES `manufacturerPart_partNumber` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_productionPartMapping_productionPart` FOREIGN KEY (`ProductionPartId`) REFERENCES `productionPart` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
