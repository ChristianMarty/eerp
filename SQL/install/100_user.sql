CREATE TABLE IF NOT EXISTS `user` (
  `Id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UserId` char(50) NOT NULL DEFAULT '',
  `Roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `Settings` longtext NOT NULL DEFAULT '',
  `Token` char(255) DEFAULT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `UserId` (`UserId`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

INSERT INTO `user` (`Id`, `UserId`, `Roles`, `Settings`, `Token`) VALUES
	(1, 'admin', '{ \r\n"error":{"php": true},\r\n"assembly":{"view": true, "create": true, "unit" : {"add": true, "history" : {"add": true, "edit": true}}},\r\n"inventory":{"print": true,"create": true, "history" : {"add": true, "edit": true}, "accessory": {"add": true, "edit": true}, "purchase": {"edit": true}},\r\n"metrology":{"view": true, "create": true},\r\n"purchasing":{"create": true, "edit": true, "confirm": true},\r\n"vendor":{"view": true, "create": true, "edit": true},\r\n"supplierPart":{"create": true},\r\n"process":{"run": true},\r\n"document":{"upload": true, "create": true, "ingest": true},\r\n"manufacturerPartSeries":{"create": true,"edit": true},\r\n"manufacturerPart":{"create": true,"edit": true},\r\n"manufacturerPartNumber":{"create": true,"edit": true},\r\n"productionPart":{"create": true,"edit": true},\r\n"stock":{"view":true, "create": true, "add": true, "remove":true, "count":true, "delete":true}, \r\n"location":{"transfer":true, "bulkTransfer":true, "print": true},\r\n"finance":{"view":true, "costCenter":true},\r\n"bom":{"print":true},\r\n"workOrder":{"create": true, "edit": true}\r\n}', '{\r\n        "Default": {\r\n        	"StockLabelPrinter": 3,\r\n        	"StockLabel": 4,\r\n        	"BomPrinter": 2,\r\n                "AssemblyReportPrinter": 2,\r\n                "AssemblyReportTemplate": 3,\r\n                "PurchaseOrder": {"UoM": 29, "VAT": 1}\r\n        }\r\n}', 'Bia4k46rBYYDJAFAciSPFymuD8jYVmUEBYwtRnTVJCzzSyfFNtYjaWni7EwgUGiicAS270QBcPUfFKqCESrzvmWJJaL70iZZnUD0')
;
