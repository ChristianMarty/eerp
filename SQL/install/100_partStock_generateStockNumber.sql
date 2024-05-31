CREATE DEFINER=`root`@`%` FUNCTION `partStock_generateStockNumber`()
RETURNS char(4) CHARSET utf8
LANGUAGE SQL
NOT DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT ''
BEGIN
SET @CharPool = 'ABCDEFGHIJKLMNPQRSTUVWXYZ0123456789'; 
SET @PoolLength = LENGTH(@CharPool)-1;

SET @LoopCount = 0;
SET @RandomString = '';

label1: WHILE @LoopCount < 4 DO
	SET @RandomString = CONCAT(@RandomString, SUBSTRING(@CharPool,CAST(RAND() * @PoolLength AS INT)+1,1));
	SET @LoopCount = @LoopCount + 1;
END WHILE label1;


RETURN @RandomString;

END