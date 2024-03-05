CREATE DEFINER=`root`@`%` FUNCTION `vendor_name_recursive`(
	`VendorId` INT
)
RETURNS tinytext CHARSET utf8
LANGUAGE SQL
DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT ''
BEGIN
 	DECLARE temp CHAR(50);
 	DECLARE temp2 CHAR(50);
 	DECLARE parent INT DEFAULT 0;

	SELECT NAME, ParentId INTO temp, parent FROM vendor WHERE Id = VendorId;
	
	IF (parent != 0)
	THEN 
		SELECT NAME INTO temp2 FROM vendor WHERE Id = parent;
		SET temp = CONCAT(temp2, " - ",temp);
	END IF;

	RETURN temp;
END