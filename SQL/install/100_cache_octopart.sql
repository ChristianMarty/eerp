CREATE TABLE `cache_octopart` (
	`Id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`OctopartId` INT(10) UNSIGNED NOT NULL,
	`Timestamp` DATETIME NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
	`Data` LONGTEXT NULL DEFAULT NULL COLLATE 'utf8mb4_bin',
	PRIMARY KEY (`Id`) USING BTREE,
	UNIQUE INDEX `OctopartId` (`OctopartId`) USING BTREE
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1
;
