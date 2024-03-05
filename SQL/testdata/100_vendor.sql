INSERT INTO `vendor` (`Id`, `ParentId`, `FullName`, `CustomerNumber`, `ShortName`, `AbbreviatedName`, `IsSupplier`, `IsManufacturer`, `IsContractor`, `IsCarrier`, `IsCustomer`, `Note`, `API`, `ApiData`, `PartNumberPreprocessor`, `CreationUserId`, `CreationDate`) 
VALUES 
(1, NULL, 'Mouser', '1234567890', NULL, NULL, b'1', b'0', b'0', b'0', b'0', NULL, 'mouser', NULL, NULL, 1, '2024-01-24 23:41:30'),
(2, NULL, 'Aliexpress', NULL, NULL, NULL, b'1', b'0', b'0', b'0', b'0', NULL, NULL, NULL, NULL, 1, '2024-01-24 23:41:30'),
(3, NULL, 'Distrelec', '1234567890', NULL, NULL, b'1', b'0', b'0', b'0', b'0', NULL, 'distrelec', NULL, 'distrelec', 1, '2024-01-24 23:41:30'),
(4, NULL, 'Digikey', NULL, NULL, NULL, b'1', b'0', b'0', b'0', b'0', NULL, 'digikey', NULL, NULL, 1, '2024-01-24 23:41:30')
;
