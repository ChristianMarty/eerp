CREATE DEFINER=`root`@`%` FUNCTION `location_getPath`(
	`LocationId` INT
)
RETURNS tinytext CHARSET utf8
LANGUAGE SQL
DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT ''
BEGIN

	DECLARE ReturnPath TINYTEXT;
	
	WITH RECURSIVE locationName AS 
	(
	SELECT 1 AS idx,  Id, LocationId, ParentId,  RecursionDepth, Cache_DisplayName, Name
	FROM location 
	WHERE Id = LocationId
	
	UNION ALL 
	
	SELECT idx+1, child.Id, child.LocationId, child.ParentId, child.RecursionDepth, child.Cache_DisplayName, child.Name
	FROM location AS child
	JOIN locationName AS parent ON parent.LocationId <=> child.Id  OR parent.ParentId <=> child.Id
	WHERE parent.Id != child.ParentId
	
	)
	SELECT GROUP_CONCAT( DISTINCT Cache_DisplayName ORDER BY idx DESC SEPARATOR " -> ") INTO ReturnPath FROM locationName;
	
	RETURN ReturnPath;
END