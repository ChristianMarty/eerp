CREATE PROCEDURE `manufacturerPart_class_getChildrenRecursive`(IN `partClassId` INT)
LANGUAGE SQL
NOT DETERMINISTIC
CONTAINS SQL
SQL SECURITY INVOKER
COMMENT ''
BEGIN
	 IF partClassId = 0
	 THEN 
	 	select * FROM manufacturerPart_class;
	 ELSE
		 WITH recursive temp AS 
		 (
			select Id, ParentId, Name, AttributeList from manufacturerPart_class where Id = partClassId
	 		union all 
	 		select child.Id,child.ParentId, child.Name, child.AttributeList from manufacturerPart_class as child
	 		join temp as parent on parent.Id = child.ParentId
	 	) 
	 	select * FROM temp;	
	 END IF;
END