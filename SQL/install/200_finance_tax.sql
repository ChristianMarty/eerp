CREATE TABLE IF NOT EXISTS `finance_tax` (
  `Id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Type` enum('VAT') NOT NULL,
  `CountryId` int(10) unsigned DEFAULT NULL,
  `Value` float NOT NULL DEFAULT 0,
  `Description` tinytext DEFAULT NULL,
  `Active` bit(1) DEFAULT b'1',
  `Note` text DEFAULT NULL,
  PRIMARY KEY (`Id`),
  KEY `FK_finance_tax_country` (`CountryId`),
  CONSTRAINT `FK_finance_tax_country` FOREIGN KEY (`CountryId`) REFERENCES `country` (`Id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

INSERT INTO `finance_tax` (`Id`, `Type`, `CountryId`, `Value`, `Description`, `Active`, `Note`) VALUES
	(1, 'VAT', 219, 7.7, 'Normal rate', b'0', NULL),
	(2, 'VAT', 219, 3.7, 'Special rate', b'1', NULL),
	(3, 'VAT', 219, 2.5, 'Reduced rate', b'1', NULL),
	(4, 'VAT', 219, 0, 'None', b'1', NULL),
	(5, 'VAT', 219, 8.1, 'Normal rate', b'1', 'From 2024')
;