CREATE TABLE `billOfMaterial_item` (
	`Id` INT(11) NOT NULL AUTO_INCREMENT,
	`BillOfMaterialRevisionId` INT(10) UNSIGNED NOT NULL,
	`ProductionPartId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`ReferenceDesignator` TINYTEXT NOT NULL COLLATE 'utf8_general_ci',
	`Layer` ENUM('Top','Bottom','Other') NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`PositionX` FLOAT NULL DEFAULT NULL,
	`PositionY` FLOAT NULL DEFAULT NULL,
	`Rotation` FLOAT NULL DEFAULT NULL,
	`Description` TINYTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`) USING BTREE,
	INDEX `FK_bom_item_productionPart` (`ProductionPartId`) USING BTREE,
	INDEX `FK_billOfMaterial_item_billOfMaterial_revision` (`BillOfMaterialRevisionId`) USING BTREE,
	INDEX `FK_billOfMaterial_item_user` (`CreationUserId`) USING BTREE,
	CONSTRAINT `FK_billOfMaterial_item_billOfMaterial_revision` FOREIGN KEY (`BillOfMaterialRevisionId`) REFERENCES `billOfMaterial_revision` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_billOfMaterial_item_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_bom_item_productionPart` FOREIGN KEY (`ProductionPartId`) REFERENCES `productionPart` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
