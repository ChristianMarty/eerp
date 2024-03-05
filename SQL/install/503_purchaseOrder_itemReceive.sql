CREATE TABLE `purchaseOrder_itemReceive` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`ItemOrderId` INT(10) UNSIGNED NOT NULL,
	`QuantityReceived` INT(11) UNSIGNED NOT NULL,
	`ReceivalDate` DATE NOT NULL,
	`CreationUserId` INT(10) UNSIGNED NOT NULL,
	`CreationDate` DATETIME NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`Id`) USING BTREE,
	INDEX `FK_purchasOrder_itemReceive_purchasOrder_itemOrder` (`ItemOrderId`) USING BTREE,
	INDEX `FK_purchasOrder_itemReceive_user` (`CreationUserId`) USING BTREE,
	CONSTRAINT `FK_purchasOrder_itemReceive_purchasOrder_itemOrder` FOREIGN KEY (`ItemOrderId`) REFERENCES `purchaseOrder_itemOrder` (`Id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
	CONSTRAINT `FK_purchasOrder_itemReceive_user` FOREIGN KEY (`CreationUserId`) REFERENCES `user` (`Id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
