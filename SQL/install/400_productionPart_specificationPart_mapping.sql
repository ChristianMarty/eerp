CREATE TABLE `productionPart_specificationPart_mapping` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`ProductionPartId` INT(10) UNSIGNED NOT NULL,
	`SpecificationPartRevisionId` INT(10) UNSIGNED NOT NULL,
	`ApprovedUsage` ENUM('Unclassified','Engineering','Production') NOT NULL DEFAULT 'Unclassified' COLLATE 'utf8_general_ci',
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`) USING BTREE,
	INDEX `ProductionPartId` (`ProductionPartId`) USING BTREE,
	INDEX `FK_productionPart_specificationPart_mapping_specificationPart` (`SpecificationPartRevisionId`) USING BTREE,
	INDEX `FK_productionPart_specificationPart_mapping_user` (`CreationUserId`) USING BTREE,
	CONSTRAINT `FK_productionPart_specificationPart_mapping_productionPart` FOREIGN KEY (`ProductionPartId`) REFERENCES `productionPart` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_productionPart_specificationPart_mapping_specPart_revision` FOREIGN KEY (`SpecificationPartRevisionId`) REFERENCES `specificationPart_revision` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_productionPart_specificationPart_mapping_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
