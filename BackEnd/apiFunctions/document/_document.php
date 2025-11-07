<?php
//*************************************************************************************************
// FileName : _document.php
// FilePath : apiFunctions/document/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);

namespace Document {

    use Numbering\Category;

    require_once __DIR__ . "/../../config.php";
    require_once __DIR__ . "/../../core/error.php";
    require_once __DIR__ . "/../../core/numbering.php";

    function _formatDocumentOutput(\stdClass $item): \stdClass
    {
        $output = new \stdClass();
        if (isset($item->Id)) {
            $output->Id = $item->Id;
        }
        $output->DocumentNumber = intval($item->DocumentNumber);
        $output->DocumentRevision = intval($item->RevisionNumber);
        $output->ItemCode = \Numbering\format(\Numbering\Category::Document, $item->DocumentNumber);
        $output->Name = $item->Name;
        $output->Description = $item->Description ?? '';
        $output->Category = $item->Category;
        $output->CreationDate = $item->CreationDate;

        if (isset($item->CreatedByInitials)) {
            $output->CreatedByInitials = $item->CreatedByInitials;
        }
        if (isset($item->CreatedByName)) {
            $output->CreatedByName = $item->CreatedByName;
        }

        global $dataRootPath;
        global $documentPath;
        if ($item->LinkType === "Internal") {
            $output->Path =  $dataRootPath . $documentPath . "/" . \Numbering\format(\Numbering\Category::Document,$item->DocumentNumber, $item->RevisionNumber) . ".".$item->Extension;
        } else if ($item->LinkType === "External") {
            $output->Path =  $item->Path;
        }

        return $output;
    }

    function getDocumentsFromIds(string|null $documentIds): array
    {
        if ($documentIds === null) return [];
        $documentIds = trim($documentIds);
        if (strlen($documentIds) === 0) return [];

        global $database;

        $docIds = explode(",", $documentIds);
        if (empty($docIds)) return [];
        $idList = implode(", ", $docIds);

        $query = <<< QUERY
            SELECT
                document.Id,
                DocumentNumber,
                document_revision.RevisionNumber,
                document_revision.Path,
                document.Description,
                Name,
                Category,
                document_revision.LinkType,
                document_revision.Hash,
                document_revision.Extension,
                document_revision.Description AS RevisionDescription,
                user.Initials AS CreatedByInitials,
                user.UserId AS CreatedByName,
                document_revision.CreationDate
            FROM document
            LEFT JOIN document_revision on document.Id = document_revision.DocumentNumberId 
            LEFT JOIN user on document_revision.CreationUserId = user.Id
            WHERE document.Id IN($idList)
            ORDER BY document.Id DESC;
        QUERY;

        $result = $database->query($query);

        foreach ($result as &$item) {
            $item = _formatDocumentOutput($item);
        }
        return $result;
    }

    enum LinkType implements \JsonSerializable
    {
        case Undefined;
        case Internal;
        case External;

        public function jsonSerialize(): string
        {
            return match ($this) {
                LinkType::Undefined => '',
                LinkType::Internal => 'Internal',
                LinkType::External => 'External'
            };
        }
    }

    function DocumentLinkType(string $input): LinkType
    {
        if (strtolower($input) === 'internal') return LinkType::Internal;
        if (strtolower($input) === 'external') return LinkType::External;
        return LinkType::Undefined;
    }

    class DocumentMetaData implements \JsonSerializable
    {
        public int $documentId;
        public int $documentNumber;
        public string $name;
        public string $category;
        public string $description;
        public \UserInformation $createdBy;
        public string $creationDate;

        public function jsonSerialize(): \stdClass
        {
            $output = new \stdClass();
            $output->Name = $this->name;
            $output->DocumentNumber = $this->documentNumber;
            $output->ItemCode = \Numbering\format(\Numbering\Category::Document, $this->documentNumber);
            $output->Category = $this->category;
            $output->Description = $this->description;
            $output->CreatedBy = $this->createdBy;
            $output->CreationDate = $this->creationDate;
            return $output;
        }
    }

    class Document implements \JsonSerializable
    {
        public DocumentMetaData $meta;
        public int $revision;
        public string $description;
        public LinkType $type;
        public string|null $path;
        public string|null $extension;
        public string|null $hash;
        public \UserInformation $createdBy;
        public string $creationDate;

        public function jsonSerialize(): \stdClass
        {
            $output = new \stdClass();
            $output->ItemCode = \Numbering\format(\Numbering\Category::Document, $this->meta->documentNumber, $this->revision);
            $output->Type = $this->type;
            $output->Description = $this->description;
            $output->Hash = $this->hash;
            $output->CreatedBy = $this->createdBy;
            $output->CreationDate = $this->creationDate;
            $output->Extension = $this->extension;

            global $dataRootPath;
            global $documentPath;

            $output->Path = match ($this->type) {
                LinkType::Internal => $dataRootPath . $documentPath . "/" . \Numbering\format(\Numbering\Category::Document, $this->meta->documentNumber, $this->revision) . "." . $this->extension,
                LinkType::External => $this->path,
                LinkType::Undefined => ""
            };

            return $output;
        }
    }

    class DocumentCitation implements \JsonSerializable
    {
        public string $category;
        public string $itemCode;
        public string $description;

        public function jsonSerialize(): \stdClass
        {
            $output = new \stdClass();
            $output->Category = $this->category;
            $output->ItemCode = $this->itemCode;
            $output->Description = $this->description;
            return $output;
        }
    }

    function getDocuments(): array
    {
        global $database;
        $query = <<< QUERY
            SELECT 
               DocumentNumber,
               Category,
               Name,
               document_revision.RevisionNumber,
               document_revision.Path,
               document_revision.LinkType,
               document_revision.Extension,
               document.Description AS Description,
               document_revision.Description,
               document_revision.CreationDate AS CreationDate
            FROM document
            
            LEFT JOIN (
                SELECT a.Id, a.RevisionNumber, a.DocumentNumberId
                FROM document_revision a
                INNER JOIN (
                    SELECT Id, MAX(RevisionNumber) RevisionNumber
                    FROM document_revision
                    GROUP BY Id
                ) b ON a.Id = b.Id AND a.RevisionNumber = b.RevisionNumber
            )revision ON document.Id = revision.DocumentNumberId
            
            LEFT JOIN document_revision ON document_revision.Id = revision.Id
            ORDER BY document_revision.CreationDate DESC
        QUERY;
        $result = $database->query($query);

        foreach ($result as &$item) {
            $item = _formatDocumentOutput($item);
        }
        return $result;
    }

    function getDocumentCodeByHash(string $documentHash ): string|null
    {
        global $database;

        $documentHash = $database->escape($documentHash);

        $query = <<< QUERY
        SELECT
            CONCAT("Doc-",document.DocumentNumber,"-",document_revision.RevisionNumber) AS ItemCode
        FROM document_revision
        RIGHT JOIN document ON document.Id = document_revision.DocumentNumberId
        WHERE Hash = $documentHash;
        QUERY;
        $result = $database->query($query);

        if (count($result) === 0) return null;
        else return $result[0]->ItemCode;
    }

    function getDocumentMetaData(int $documentNumber): DocumentMetaData|null
    {
        global $database;

        $query = <<< QUERY
        SELECT
            document.Id,
            document.DocumentNumber,
            document.Category,
            document.Name,
            document.Description,
            user.Initials AS CreatedByInitials,
            user.UserId AS CreatedByName,
            document.CreationDate
        FROM document
        LEFT JOIN user on document.CreationUserId = user.Id
        WHERE DocumentNumber = '$documentNumber';
        QUERY;

        $result = $database->query($query);
        if (count($result) === 0) return null;
        $item = $result[0];

        $userData = new \UserInformation();
        $userData->name = $item->CreatedByName;
        $userData->initials = $item->CreatedByInitials;

        $output = new DocumentMetaData();
        $output->documentId = $item->Id;
        $output->name = $item->Name;
        $output->documentNumber = $item->DocumentNumber;
        $output->category = $item->Category;
        $output->description = $item->Description ?? "";
        $output->createdBy = $userData;
        $output->creationDate = $item->CreationDate;

        return $output;
    }

    function getRevisions(DocumentMetaData $documentMetaData): array // type: Document
    {
        global $database;
        $query = <<< QUERY
            SELECT 
                RevisionNumber,
                Description,
                LinkType,
                Path,
                Hash,
                Extension,
                CreationUserId,
                CreationDate,
                user.Initials AS CreatedByInitials,
                user.UserId AS CreatedByName
            FROM document_revision
            LEFT JOIN user on CreationUserId = user.Id
            WHERE DocumentNumberId = $documentMetaData->documentId
            ORDER BY RevisionNumber DESC
        QUERY;
        $result = $database->query($query);

        $output = [];
        foreach ($result as $item) {
            $userData = new \UserInformation();
            $userData->name = $item->CreatedByName;
            $userData->initials = $item->CreatedByInitials;

            $document = new Document();
            $document->meta = $documentMetaData;
            $document->revision = $item->RevisionNumber;
            $document->description = $item->Description ?? "";
            $document->path = $item->Path;
            $document->hash = $item->Hash;
            $document->extension = $item->Extension;
            $document->type = DocumentLinkType($item->LinkType);
            $document->creationDate = $item->CreationDate;

            $document->createdBy = $userData;

            $output[] = $document;
        }
        return $output;
    }

    function getCitations(DocumentMetaData $meta): array  // type: DocumentCitation
    {
        $documentId = $meta->documentId;

        global $database;
        $output = [];

// Get documents from inventory
        $query = <<< STR
        SELECT 
            inventory.InventoryNumber,
            inventory.Title,
            inventory.Type,
            inventory.Manufacturer
        FROM inventory 
        WHERE replace(json_array(DocumentIds), ',', '","') LIKE '%"$documentId"%'
        STR;

        $result = $database->query($query);
        foreach ($result as $r) {
            $temp = new DocumentCitation();
            $temp->category = 'Inventory';
            $temp->itemCode = \Numbering\format(\Numbering\Category::Inventory, $r->InventoryNumber);
            $temp->description = $r->Title . " - " . $r->Manufacturer . " " . $r->Type;
            $output[] = $temp;
        }

// Get documents from inventory_history
        $query = <<< STR
        SELECT 
            inventory.InventoryNumber,
            inventory.Title,
            inventory.Type,
            inventory.Manufacturer,
            inventory_history.Description,
            inventory_history.Type AS HistoryType
        FROM inventory_history 
        LEFT JOIN inventory ON inventory.Id = inventory_history.InventoryId
        WHERE replace(json_array(inventory_history.DocumentIds), ',', '","') LIKE '%"$documentId"%'
        STR;

        $result = $database->query($query);
        foreach ($result as $r) {
            $temp = new DocumentCitation();
            $temp->category = 'Inventory History';
            $temp->itemCode = \Numbering\format(\Numbering\Category::Inventory, $r->InventoryNumber);
            $temp->description = $r->HistoryType . " - " . $r->Description . " - " . $r->Manufacturer . " " . $r->Type;
            $output[] = $temp;
        }

// Get documents from manufacturerPart_series
        $query = <<< STR
        SELECT 
            manufacturerPart_series.Title,
            manufacturerPart_series.Description,
            vendor_displayName(vendor.Id) AS VendorName
        FROM manufacturerPart_series 
        LEFT JOIN vendor ON vendor.Id = manufacturerPart_series.VendorId
        WHERE replace(json_array(manufacturerPart_series.DocumentIds), ',', '","') LIKE '%"$documentId"%'
        STR;

        $result = $database->query($query);
        foreach ($result as $r) {
            $temp = new DocumentCitation();
            $temp->category = 'Manufacturer Part Series';
            $temp->itemCode = '';
            $temp->description = $r->VendorName . " " . $r->Title . " - " . $r->Description;
            $output[] = $temp;
        }

// Get documents from manufacturerPart_Item
        $query = <<< STR
        SELECT 
            manufacturerPart_item.Number,
            manufacturerPart_item.Description,
            vendor_displayName(vendor.Id) AS VendorName
        FROM manufacturerPart_item
        LEFT JOIN vendor ON vendor.Id = manufacturerPart_item.VendorId
        WHERE replace(json_array(manufacturerPart_item.DocumentIds), ',', '","') LIKE '%"$documentId"%'
        STR;

        $result = $database->query($query);
        foreach ($result as $r) {
            $temp = new DocumentCitation();
            $temp->category = 'Manufacturer Part Item';
            $temp->itemCode = '';
            $temp->description = $r->VendorName . " " . $r->Number . " - " . $r->Description;
            $output[] = $temp;
        }

// Get documents from purchaseOrder
        $query = <<< STR
        SELECT 
            purchaseOrder.PurchaseOrderNumber,
            purchaseOrder.Title,
            vendor_displayName(vendor.Id) AS VendorName
        FROM purchaseOrder 
        LEFT JOIN vendor ON vendor.Id = purchaseOrder.VendorId
        WHERE replace(json_array(purchaseOrder.DocumentIds), ',', '","') LIKE '%"$documentId"%'
        STR;

        $result = $database->query($query);
        foreach ($result as $r) {
            $temp = new DocumentCitation();
            $temp->category = 'Purchase Order';
            $temp->itemCode = \Numbering\format(\Numbering\Category::PurchaseOrder, $r->PurchaseOrderNumber);
            $temp->description = $r->VendorName . " - " . $r->Title;
            $output[] = $temp;
        }

// Get documents from shipment
        $query = <<< STR
        SELECT 
            shipment.ShipmentNumber,
            shipment.Direction,
            shipment.Description
        FROM shipment 
        WHERE replace(json_array(DocumentIds), ',', '","') LIKE '%"$documentId"%'
        STR;

        $result = $database->query($query);
        foreach ($result as $r) {
            $temp = new DocumentCitation();
            $temp->category = 'Shipment';
            $temp->itemCode = \Numbering\format(\Numbering\Category::Shipment, $r->ShipmentNumber);
            $temp->description = $r->Direction . " - " . $r->Description;
            $output[] = $temp;
        }

        return $output;
    }
}

namespace Document\Ingest
{

    function download(string $url): \stdClass | \Error\Data
    {
        global $database;
        global $serverDataPath;
        global $ingestPath;

        $fileName = basename($url);
        $file = file_get_contents($url);

        if (!$file){
            return \Error\generic("File download failed!");
        }

        // Check if file already exists
        $documentHash = md5($file);
        $preexisting = \Document\getDocumentCodeByHash($documentHash);

        if($preexisting !== null) {
            return \Error\generic("This file already exists as ".$preexisting);
        }

        file_put_contents($serverDataPath.$ingestPath."/".$fileName, $file);

        $output = new \stdClass();
        $output->message = "File downloaded successfully.";
        return $output;
    }

    // input: $_FILES["file"]
    function upload($file): \stdClass | \Error\Data
    {
        global $database;
        global $serverDataPath;
        global $ingestPath;

        $output = new \stdClass();

        $fileName = basename($file["name"]);
        $file = $file["tmp_name"];

        // Check if file already exists
        $documentHash = md5_file($file);
        $preexisting = \Document\getDocumentCodeByHash($documentHash);

        if($preexisting !== null) {
            return \Error\generic("This file already exists as ".$preexisting);
        }

        move_uploaded_file($file, $serverDataPath.$ingestPath."/".$fileName);

        $output->message = "File uploaded successfully.";
        return $output;
    }

    function delete(string $fileName): null | \Error\Data
    {
        global $serverDataPath;
        global $ingestPath;

        $filePath = $serverDataPath.$ingestPath."/".$fileName;

        if (!unlink($filePath)) {
            return \Error\generic("File delete failed.");
        }

        return null;
    }

    class Data
    {
        public int|null $documentNumber = null;
        public string $ingestName; // path for external / file name for internal
        public string|null $name;
        public string|null $category;
        public string|null $documentDescription = null;
        public string|null $revisionDescription = null;
        public \Document\LinkType $linkType = \Document\LinkType::Undefined;
    }

    function validateRequest(\stdClass|null|\Error\Data $data): Data | \Error\Data
    {
        if($data === null) return \Error\postDataMissing();
        if($data instanceof \Error\Data) return $data;

        $output = new Data();

        if(!isset($data->IngestName)) return \Error\parameterMissing('IngestName');
        if(!isset($data->LinkType)) return \Error\parameterMissing('LinkType');

        if(isset($data->DocumentNumber)){
            $output->documentNumber = \Numbering\parser(\Numbering\Category::Document, $data->DocumentNumber);
        }else{
            if(!isset($data->Name)) return \Error\parameterMissing('Name');
            if(!isset($data->Category)) return \Error\parameterMissing('Category');

            $output->name = $data->Name;
            $output->category = $data->Category;
            $output->documentDescription = $data->DocumentDescription??"";
        }

        $output->ingestName = $data->IngestName;
        $output->revisionDescription = $data->RevisionDescription??"";

        if(strtolower($data->LinkType) == "internal") $output->linkType = \Document\LinkType::Internal;
        if(strtolower($data->LinkType) == "external") $output->linkType = \Document\LinkType::External;

        return $output;
    }

    function save(Data $data): \Document\DocumentMetaData | \Error\Data
    {
        global $serverDataPath;
        global $documentPath;

        if($data->linkType === \Document\LinkType::Undefined ) return \Error\generic("Link type is undefined.");

        if($data->linkType === \Document\LinkType::Internal){
            global $ingestPath;
            $sourcePath = $serverDataPath . $ingestPath . "/" . $data->ingestName;
            if (!file_exists($sourcePath)) return \Error\generic("File path invalid.");

            $documentHash = md5_file($sourcePath);
            $preexisting = \Document\getDocumentCodeByHash($documentHash);

            if($preexisting !== null) {
                return \Error\generic("This file already exists as ".$preexisting);
            }
            $fileExtension = pathinfo($sourcePath, PATHINFO_EXTENSION);
            $fileExtension = strtolower($fileExtension);
        }

        global $database;
        global $user;
        $database->beginTransaction();

        $documentId = null;
        if($data->documentNumber === null) {
            if($data->name == "" or $data->name == null) return \Error\parameterMissing("Name");
            if($data->category == "" or $data->category == null) return \Error\parameterMissing("Category");

            $fileNameIllegalCharactersRegex = '/[%:"*?<>|\\/]+/';
            if(preg_match($fileNameIllegalCharactersRegex, $data->name) != 0) return \Error\generic("Name contains illegal character.");

            $sqlData = [];
            $sqlData['Name'] = $data->name;
            $sqlData['Description'] = $data->documentDescription;
            $sqlData['Category'] = $data->category;
            $sqlData['CreationUserId'] = $user->userId();;
            $sqlData['DocumentNumber']['raw'] = "(SELECT generateItemNumber())";

            try {
                $documentId = $database->insert("document", $sqlData);
            } catch (\PDOException $e) {
                $database->rollBackTransaction();
                return \Error\database($e->getMessage());
            }
        }else{
            $documentNumber = $database->escape($data->documentNumber);
            $query = "SELECT Id FROM document WHERE DocumentNumber = $documentNumber;";
            $result = $database->query($query);
            if(count($result) == 0){
                $database->rollBackTransaction();
                return \Error\database("Document number doesn't exist");
            }
            $documentId = $result[0]->Id;
        }

        $query = <<< QUERY
            SELECT 
                MAX(COALESCE(document_revision.RevisionNumber,0))+1 AS NextRevisionNumber
            FROM document 
            LEFT JOIN document_revision ON document.Id = document_revision.DocumentNumberId 
            WHERE document.Id = $documentId
        QUERY;
        $result = $database->query($query);
        if(count($result) == 0) {
            $database->rollBackTransaction();
            return \Error\database("Document number doesn't exist");
        }
        $nextRevisionNumber = $result[0]->NextRevisionNumber;

        $sqlData = [];
        if($data->linkType === \Document\LinkType::Internal){
            $sqlData['LinkType'] = "Internal";
            $sqlData['Extension'] = $fileExtension;
            $sqlData['Path'] = null;
            $sqlData['Hash'] = $documentHash;
        }elseif($data->linkType === \Document\LinkType::External){
            $sqlData['LinkType'] = "External";
            $sqlData['Extension'] = null;
            $sqlData['Path'] = $data->ingestName;
            $sqlData['Hash'] = null;
        }
        $sqlData['Description'] = $data->revisionDescription;
        $sqlData['DocumentNumberId'] = $documentId;
        $sqlData['RevisionNumber'] = $nextRevisionNumber;
        $sqlData['CreationUserId'] = $user->userId();;

        try {
            $documentRevisionId = $database->insert("document_revision", $sqlData);
        } catch (\PDOException $e) {
            $database->rollBackTransaction();
            return \Error\database($e->getMessage());
        }

        $query = <<< QUERY
            SELECT CONCAT("Doc-",document.DocumentNumber,"-",document_revision.RevisionNumber) AS ItemCode, document.DocumentNumber
            FROM document_revision 
            LEFT JOIN document ON document.Id = document_revision.DocumentNumberId
            WHERE document_revision.Id = $documentRevisionId
        QUERY;

        $result = $database->query($query);
        if(count($result) == 0){
            $database->rollBackTransaction();
            return \Error\parameter("Document number doesn't exist");
        }
        $documentItemCode = $result[0]->ItemCode;
        $documentNumber = $result[0]->DocumentNumber;

        if($data->linkType === \Document\LinkType::Internal){
            $destinationPath = $serverDataPath.$documentPath. "/" .$documentItemCode.".".$fileExtension;
            if (!rename($sourcePath, $destinationPath)) {
                $database->rollBackTransaction();
                return \Error\generic("File copy failed.");
            }
        }

        $database->commitTransaction();

        return \Document\getDocumentMetaData($documentNumber);;
    }
}
