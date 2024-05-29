CREATE DEFINER=`root`@`%` TRIGGER `location_beforeUpdate` BEFORE UPDATE ON `location` FOR EACH ROW BEGIN

	IF NEW.Virtual = 1
	THEN
		SET NEW.Cache_DisplayName = NULL;
	ELSE
		SET NEW.Cache_DisplayName = location_getName(new.Id);
	END IF;
	
	SET NEW.Cache_DisplayLocation = CONCAT(NEW.Title, COALESCE( CONCAT(" (",NULLIF(NEW.Description,""),")"),""));
	SET NEW.Cache_DisplayPath = CONCAT(location_getPath(NEW.LocationId), " -> ", NEW.Cache_DisplayName, COALESCE( CONCAT(" : ",NULLIF(TRIM(NEW.Cache_DisplayLocation),"")), ""));
	SET NEW.Cache_IdPath = location_getIdPath(NEW.LocationId);
END