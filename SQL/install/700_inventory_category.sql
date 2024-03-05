CREATE TABLE `inventory_category` (
	`Id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`ParentId` INT(11) UNSIGNED NOT NULL,
	`Name` MEDIUMTEXT NOT NULL COLLATE 'utf8_general_ci',
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`) USING BTREE,
	INDEX `FK_inventory_category_user` (`CreationUserId`) USING BTREE,
	CONSTRAINT `FK_inventory_category_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8_bin'
ENGINE=InnoDB
AUTO_INCREMENT=1
;