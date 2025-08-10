CREATE FUNCTION `location_getIdPath`(`LocationId` INT)
RETURNS text CHARSET utf8
LANGUAGE SQL
NOT DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT ''
BEGIN

	DECLARE ReturnIdPath TEXT;
	
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
	SELECT GROUP_CONCAT( DISTINCT Id ORDER BY idx DESC SEPARATOR ",") INTO ReturnIdPath FROM locationName;
	
	RETURN ReturnIdPath;
END