CREATE DEFINER=`root`@`%` FUNCTION `history_generateEditToken`()
RETURNS char(32) CHARSET utf8
LANGUAGE SQL
NOT DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT ''
BEGIN
SET @CharPool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
SET @PoolLength = LENGTH(@CharPool)-1;

SET @LoopCount = 0;
SET @RandomString = '';

label1: WHILE @LoopCount < 32 DO
	SET @RandomString = CONCAT(@RandomString, SUBSTRING(@CharPool,CAST(RAND() * @PoolLength AS INT)+1,1));
	SET @LoopCount = @LoopCount + 1;
END WHILE label1;


RETURN @RandomString;

END