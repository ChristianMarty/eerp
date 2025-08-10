CREATE TABLE `pickingOrder` (
	`Id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`PickingOrderNumber` INT(5) UNSIGNED ZEROFILL NOT NULL DEFAULT (cast(rand() * 100000 as unsigned)),
	`Name` CHAR(50) NOT NULL,
	`Status` ENUM('Preparing','InProgress','Complete') NOT NULL DEFAULT 'Preparing',
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`),
	UNIQUE KEY `PickingOrderNumber` (`PickingOrderNumber`),
	INDEX `FK_pickingOrder_user` (`CreationUserId`),
	CONSTRAINT `FK_pickingOrder_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
);
