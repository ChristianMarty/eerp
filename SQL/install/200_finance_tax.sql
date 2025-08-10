CREATE TABLE `finance_tax` (
  `Id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Type` enum('VAT') NOT NULL,
  `CountryId` int(10) unsigned DEFAULT NULL,
  `Value` float NOT NULL DEFAULT 0,
  `Description` tinytext DEFAULT NULL,
  `Active` bit(1) DEFAULT b'1',
  `Note` text DEFAULT NULL,
  `CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`Id`),
  INDEX `FK_finance_tax_country` (`CountryId`),
  INDEX `FK_finance_tax_user` (`CreationUserId`),
  CONSTRAINT `FK_finance_tax_country` FOREIGN KEY (`CountryId`) REFERENCES `country` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
	CONSTRAINT `FK_finance_tax_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
);

INSERT INTO `finance_tax` (`Id`, `Type`, `CountryId`, `Value`, `Description`, `Active`, `Note`, `CreationUserId`) VALUES
	(1, 'VAT', 219, 7.7, 'Normal rate', b'0', NULL, '1'),
	(2, 'VAT', 219, 3.7, 'Special rate', b'1', NULL, '1'),
	(3, 'VAT', 219, 2.5, 'Reduced rate', b'1', NULL, '1'),
	(4, 'VAT', 219, 0, 'None', b'1', NULL, '1'),
	(5, 'VAT', 219, 8.1, 'Normal rate', b'1', 'From 2024', '1')
;