<?php
//*************************************************************************************************
// FileName : split.php
// FilePath : apiFunctions/stock/item/
// Author   : Christian Marty
// Date		: 09.11.2025
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;
global $user;

if ($api->isPost(\Permission::Stock_Split)) {

    $data = $api->getPostData();

    if(!isset($data->StockCode)) $api->returnData(\Error\parameterMissing("StockCode"));
    $stockNumber = \Numbering\parser(\Numbering\Category::Stock, $data->StockCode);
    if($stockNumber === null) $api->returnData(\Error\parameter("StockCode"));

    if(!isset($data->Quantity)) $api->returnData(\Error\parameterMissing("Quantity"));
    $quantity = intval($data->Quantity);
    if($quantity === 0) $api->returnData(\Error\parameter("Quantity"));

    $stockNumberQuoted = $database->escape($stockNumber);

    $query = <<<STR
		SELECT 
		    partStock_history.Id,
			partStock_history.Quantity
		FROM partStock_history 
		LEFT JOIN partStock ON partStock_history.StockId = partStock.Id
		WHERE partStock.StockNumber =  $stockNumberQuoted AND (ChangeType = 'Absolute' OR ChangeType = 'Create')
	STR;
    $historyResult = $database->query($query);
    \Error\checkErrorAndExit($historyResult);
    \Error\checkNoResultAndExit($historyResult, $data->StockCode);

    foreach($historyResult as $item){
        if($item->Quantity < $quantity){
            $api->returnData(\Error\generic("Split quantity bigger as stock quantity"));
        }
    }

    $database->beginTransaction();

    $query = <<<STR
		SELECT 
			ManufacturerPartNumberId,
			SpecificationPartRevisionId,
			AssemblyId,
			Date,
			CountryOfOriginCountryId,
			LocationId,
			HomeLocationId,
			SupplierPartId,
			ReceivalId,
			LotNumber
		FROM partStock 
		WHERE partStock.StockNumber =  $stockNumberQuoted 
	STR;
    $result = $database->query($query);
    \Error\checkErrorAndExit($result);
    \Error\checkNoResultAndExit($result, $data->StockCode);

    $oldItem = $result[0];

    $insertData = [];
    $insertData['StockNumber']['raw'] = "partStock_generateStockNumber()";
    $insertData['ManufacturerPartNumberId'] = $oldItem->ManufacturerPartNumberId;
    $insertData['SpecificationPartRevisionId'] = $oldItem->SpecificationPartRevisionId;
    $insertData['AssemblyId'] = $oldItem->AssemblyId;
    $insertData['Date'] = $oldItem->Date;
    $insertData['CountryOfOriginCountryId'] = $oldItem->CountryOfOriginCountryId;
    $insertData['LocationId'] = $oldItem->LocationId;
    $insertData['HomeLocationId'] = $oldItem->HomeLocationId;
    $insertData['SupplierPartId'] = $oldItem->SupplierPartId;
    $insertData['ReceivalId'] = $oldItem->ReceivalId;
    $insertData['LotNumber'] = $oldItem->LotNumber;
    $insertData['CreationUserId'] = $user->userId();
    $newItemId = $database->insert('partStock', $insertData);
    if($newItemId instanceof \Error\Data) {
        $database->rollBackTransaction();
        $api->returnData($newItemId);
    }

    $insertData = [];
    $insertData['StockId'] = $newItemId;
    $insertData['Quantity'] = $quantity;
    $insertData['ChangeType'] = "Create";
    $insertData['CreationUserId'] = $user->userId();
    $newHistoryId = $database->insert("partStock_history", $insertData);
    if($newHistoryId instanceof \Error\Data) {
        $database->rollBackTransaction();
        $api->returnData($newHistoryId);
    }

    foreach($historyResult as $item){
        $id = $item->Id;
        $updateData = [];
        $updateData['Quantity'] = $item->Quantity - $quantity;
        $updateResult = $database->update('partStock_history', $updateData, "Id = $id");
        if($updateResult instanceof \Error\Data) {
            $database->rollBackTransaction();
            $api->returnData($updateResult);
        }
    }

    $query = <<<STR
		SELECT 
		    StockNumber
		FROM partStock 
		WHERE partStock.Id = $newItemId
	STR;
    $itemResult = $database->query($query);
    if($itemResult instanceof \Error\Data) {
        $database->rollBackTransaction();
        $api->returnData($itemResult);
    }

    if(count($itemResult) !== 1) {
        $database->rollBackTransaction();
        $api->returnData($itemResult);
    }

    $database->commitTransaction();

    $output = $itemResult[0];
    $output->ItemCode = \Numbering\format(\Numbering\Category::Stock, $output->StockNumber);
    $api->returnData($output);
}
