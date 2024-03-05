CREATE VIEW `vendor_names` AS

SELECT 
	`vendor`.`Id` AS `Id`,
	`vendor`.`FullName` AS `Name`
	from `vendor` 
UNION 
SELECT 
	`vendor`.`Id` AS `Id`,
	`vendor`.`ShortName` AS `NAME`
	from `vendor` 
	where `vendor`.`ShortName` is not NULL
UNION
SELECT 
	`vendor`.`Id` AS `Id`,
	`vendor`.`AbbreviatedName` AS `NAME` 
	from `vendor` 
	where `vendor`.`AbbreviatedName` is not NULL 
UNION 
SELECT 
	`vendor_alias`.`VendorId` AS `Id`,
	`vendor_alias`.`Name` AS `NAME` 
	from `vendor_alias`;