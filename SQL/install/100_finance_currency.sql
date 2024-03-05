CREATE TABLE IF NOT EXISTS `finance_currency` (
  `Id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `CurrencyCode` char(3) NOT NULL DEFAULT '0',
  `Symbol` char(5) DEFAULT NULL,
  `FractionalUnit` char(10) DEFAULT NULL,
  `Name` tinytext NOT NULL,
  `Digits` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `CurrencyCode` (`CurrencyCode`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- Exportiere Daten aus Tabelle BlueNova.finance_currency: ~4 rows (ungefähr)
INSERT INTO `finance_currency` (`Id`, `CurrencyCode`, `Symbol`, `FractionalUnit`, `Name`, `Digits`) VALUES
	(1, 'CHF', 'Fr', 'Rappen', 'Swiss Franc', 2),
	(2, 'USD', '$', 'Cent', 'United States Dollar', 2),
	(3, 'EUR', '€', 'Cent', 'Euro', 2),
	(4, 'GBP', '£', 'Penny', 'Pound Sterling', 2)
;