CREATE TABLE IF NOT EXISTS `unitOfMeasurement_prefix` (
  `Id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Name` char(10) DEFAULT NULL,
  `Symbol` char(10) DEFAULT NULL,
  `Strict` bit(1) NOT NULL DEFAULT b'0',
  `Multiplier` float DEFAULT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;

INSERT INTO `unitOfMeasurement_prefix` (`Id`, `Name`, `Symbol`, `Strict`, `Multiplier`) VALUES
	(1, 'Kilo', 'k', b'1', 1000),
	(2, 'Mega', 'M', b'1', 1000000),
	(3, 'Giga', 'G', b'1', 1000000000),
	(4, 'Tera', 'T', b'1', 1000000000000),
	(5, 'Deci', 'd', b'0', 0.1),
	(6, 'Centi', 'c', b'0', 0.01),
	(7, 'Milli', 'm', b'1', 0.001),
	(8, 'Micro', 'μ', b'1', 0.000001),
	(9, 'Nano', 'n', b'1', 0.000000001),
	(10, 'Pico', 'p', b'1', 0.000000000001),
	(11, 'Femto', 'f', b'1', 0.000000000000001),
	(12, 'Atto', 'a', b'1', 1e-18),
	(13, 'Zepto', 'z', b'1', 1e-21),
	(14, 'Yocto', 'y', b'1', 1e-24),
	(15, 'Deca', 'da', b'0', 10),
	(16, 'Hecto', 'h', b'0', 100),
	(17, 'Peta', 'P', b'1', 1e15),
	(18, 'Exa', 'E', b'1', 1e18),
	(19, 'Zetta', 'Z', b'1', 1e21),
	(20, 'Yotta', 'Y', b'1', 1e24),
	(21, '', '', b'1', 1)
;
