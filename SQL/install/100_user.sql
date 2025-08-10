CREATE TABLE `user` (
  `Id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserId` char(50) NOT NULL,
  `Initials` CHAR(3) NOT NULL,
  `Roles` longtext NOT NULL DEFAULT '{}',
  `Settings` longtext NOT NULL DEFAULT '{}',
  `Token` char(255) DEFAULT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `UserId` (`UserId`),
  UNIQUE KEY `Initials` (`Initials`)
);

INSERT INTO `user` (`Id`, `UserId`, `Initials`) VALUES
  (1, 'System', 'SYS');


