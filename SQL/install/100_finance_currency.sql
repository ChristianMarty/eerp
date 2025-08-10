CREATE TABLE `finance_currency` (
  `Id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `CurrencyCode` char(3) NOT NULL DEFAULT '0',
  `Symbol` char(5) NOT NULL,
  `FractionalUnit` char(10) NOT NULL,
  `Name` tinytext NOT NULL,
  `Digits` int(11) NOT NULL DEFAULT 2,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `CurrencyCode` (`CurrencyCode`)
);

INSERT INTO `finance_currency` (`Id`, `CurrencyCode`, `Symbol`, `FractionalUnit`, `Name`, `Digits`) VALUES
	(1, 'CHF', 'Fr', 'Rappen', 'Swiss Franc', 2),
	(2, 'USD', '$', 'Cent', 'United States Dollar', 2),
	(3, 'EUR', '€', 'Cent', 'Euro', 2),
	(4, 'GBP', '£', 'Penny', 'Pound Sterling', 2)
;