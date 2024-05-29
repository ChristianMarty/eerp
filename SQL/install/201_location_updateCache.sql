CREATE DEFINER=`root`@`%` PROCEDURE `location_updateCache`()
LANGUAGE SQL
NOT DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT ''
BEGIN

	UPDATE location SET location.Cache_DisplayName = location_getName(location.Id);
	UPDATE location SET location.Cache_DisplayName = NULL WHERE location.`Virtual` = 1;
	
	UPDATE location SET location.Cache_DisplayLocation = CONCAT(location.Title, COALESCE( CONCAT(" (",NULLIF(location.Description,""),")"),""));
	UPDATE location SET location.Cache_DisplayPath = CONCAT(location_getPath(Id), COALESCE( CONCAT(" : ",NULLIF(TRIM(Cache_DisplayLocation),"")), ""));
	UPDATE location SET location.Cache_IdPath = location_getIdPath(Id);

END