CREATE TABLE `productionPart_manufacturerPart_mapping` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`ManufacturerPartNumberId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`ProductionPartId` INT(10) UNSIGNED NOT NULL,
	`ApprovedUsage` ENUM('Unclassified','Engineering','Production') NOT NULL DEFAULT 'Unclassified',
	`CreationUserId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`),
	UNIQUE KEY `ManufacturerPartNumberId_ProductionPartId` (`ManufacturerPartNumberId`, `ProductionPartId`),
	INDEX `FK_productionPartMapping_productionPart` (`ProductionPartId`),
	INDEX `FK_productionPart_manufacturerPart_mapping_user` (`CreationUserId`),
	CONSTRAINT `FK_productionPartMapping_manufacturerPart_partNumber` FOREIGN KEY (`ManufacturerPartNumberId`) REFERENCES `manufacturerPart_partNumber` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_productionPartMapping_manufacturerPart_partNumber_2` FOREIGN KEY (`ManufacturerPartNumberId`) REFERENCES `manufacturerPart_partNumber` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_productionPartMapping_productionPart` FOREIGN KEY (`ProductionPartId`) REFERENCES `productionPart` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_productionPart_manufacturerPart_mapping_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
);

