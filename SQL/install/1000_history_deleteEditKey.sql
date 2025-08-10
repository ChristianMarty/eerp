CREATE EVENT `history_deleteEditKey`
	ON SCHEDULE
		EVERY 1 DAY
	ON COMPLETION NOT PRESERVE
	ENABLE
	COMMENT ''
	DO BEGIN
	UPDATE assembly_unit_history SET EditToken = NULL WHERE (NOW() - assembly_unit_history.Date > (60*60*24));
	UPDATE partStock_history SET EditToken = NULL WHERE (NOW() - partStock_history.Date > (7*60*60*24));
END