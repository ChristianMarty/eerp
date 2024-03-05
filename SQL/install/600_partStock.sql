CREATE TABLE `partStock` (
	`Id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`StockNo` CHAR(4) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`ManufacturerPartNumberId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`SpecificationPartRevisionId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`AssemblyId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`Date` DATE NULL DEFAULT NULL,
	`LocationId` INT(11) UNSIGNED NOT NULL DEFAULT '1' COMMENT '118 -> "Unassigned"',
	`HomeLocationId` INT(11) NULL DEFAULT NULL,
	`CreationUserId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	`OrderReference` CHAR(50) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`SupplierPartId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`ReceivalId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`LotNumber` CHAR(50) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`DeleteRequestUserId` INT(10) UNSIGNED NULL DEFAULT NULL,
	`DeleteRequestDate` TIMESTAMP NULL DEFAULT NULL,
	`DeleteRequestNote` TEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`Cache_Quantity` INT(11) NULL DEFAULT NULL,
	PRIMARY KEY (`Id`) USING BTREE,
	UNIQUE INDEX `Id` (`StockNo`) USING BTREE,
	INDEX `FK_partStock_location` (`LocationId`) USING BTREE,
	INDEX `FK_partStock_purchasOrder_itemReceive` (`ReceivalId`) USING BTREE,
	INDEX `FK_partStock_user` (`DeleteRequestUserId`) USING BTREE,
	INDEX `FK_partStock_manufacturerPart_partNumber` (`ManufacturerPartNumberId`) USING BTREE,
	INDEX `FK_partStock_specificationPart_revision` (`SpecificationPartRevisionId`) USING BTREE,
	INDEX `FK_partStock_user_2` (`CreationUserId`) USING BTREE,
	INDEX `FK_partStock_assembly` (`AssemblyId`) USING BTREE,
	CONSTRAINT `FK_partStock_assembly` FOREIGN KEY (`AssemblyId`) REFERENCES `assembly` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_partStock_manufacturerPart_partNumber` FOREIGN KEY (`ManufacturerPartNumberId`) REFERENCES `manufacturerPart_partNumber` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_partStock_purchasOrder_itemReceive` FOREIGN KEY (`ReceivalId`) REFERENCES `purchaseOrder_itemReceive` (`Id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
	CONSTRAINT `FK_partStock_specificationPart_revision` FOREIGN KEY (`SpecificationPartRevisionId`) REFERENCES `specificationPart_revision` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_partStock_user` FOREIGN KEY (`DeleteRequestUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT `FK_partStock_user_2` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
