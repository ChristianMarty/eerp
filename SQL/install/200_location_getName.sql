CREATE FUNCTION `location_getName`(`LocationId` INT)
RETURNS tinytext CHARSET utf8
LANGUAGE SQL
DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT ''
BEGIN
	
	DECLARE ReturnName TINYTEXT; 
	SET @RecursionDepthLimit = (SELECT RecursionDepth FROM location WHERE Id = LocationId)+1;
	
	WITH RECURSIVE locationName AS 
	(
		SELECT 1 AS idx,  Id, LocationId, ParentId,  RecursionDepth,  Name
		FROM location AS parent
		WHERE Id = LocationId
		
		UNION ALL 
		
		SELECT idx+1, child.Id, child.LocationId, child.ParentId, child.RecursionDepth,  child.Name
		FROM location AS child
		JOIN locationName AS parent ON parent.LocationId <=> child.Id  OR parent.ParentId <=> child.Id
		WHERE parent.ParentId = child.Id AND idx < @RecursionDepthLimit
	
	)
	SELECT GROUP_CONCAT( DISTINCT Name ORDER BY idx DESC SEPARATOR " ") INTO ReturnName   FROM locationName;
	
	RETURN ReturnName;

END