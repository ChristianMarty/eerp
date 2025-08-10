CREATE DEFINER=`root`@`%` FUNCTION `partStock_generateStockNumber`()
RETURNS char(4) CHARSET utf8
LANGUAGE SQL
NOT DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT ''
BEGIN
SET @CharPool = 'ABCDEFGHIJKLMNPQRSTUVWXYZ23456789'; -- Remove 0 and 1 to prevent confusion with O and I -> 1’185’921 combinations
SET @PoolLength = LENGTH(@CharPool)-1;

SET @LoopCount = 0;
SET @RandomString = '';

label1: WHILE @LoopCount < 4 DO
	SET @RandomString = CONCAT(@RandomString, SUBSTRING(@CharPool,CAST(RAND() * @PoolLength AS INT)+1,1));
	SET @LoopCount = @LoopCount + 1;
END WHILE label1;


RETURN @RandomString;

END