<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/specificationPart
// Author   : Christian Marty
// Date		: 01.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
global $database;
global $api;
global $user;

enum SpecificationPartType implements \JsonSerializable
{
    case Undefined;
    case Pcb;
    case PcbStencil;
    case AcrylicPlate;

    public function jsonSerialize(): string
    {
        return match ($this) {
            SpecificationPartType::Undefined => '',
            SpecificationPartType::Pcb => 'PCB',
            SpecificationPartType::PcbStencil => 'PCB Stencil',
            SpecificationPartType::AcrylicPlate => 'Acrylic Plate'
        };
    }
}

function specificationPartType(string $input): SpecificationPartType
{
    $input = strtolower($input);
    if ($input === 'pcb') return SpecificationPartType::Pcb;
    if ($input === 'pcb stencil') return SpecificationPartType::PcbStencil;
    if ($input === 'acrylic plate') return SpecificationPartType::AcrylicPlate;
    return SpecificationPartType::Undefined;
}

class SpecificationPart extends stdClass implements \JsonSerializable
{
    public int $specificationPartNumber;
    public string $name;
    public SpecificationPartType $type;
    public string|null $description;
    public \UserInformation $createdBy;
    public string $creationDate;
    public array $revision = [];

    public function jsonSerialize(): \stdClass
    {
        $output = new \stdClass();
        $output->Name = $this->name;
        $output->SpecificationPartNumber = $this->specificationPartNumber;
        $output->ItemCode = \Numbering\format(\Numbering\Category::SpecificationPart, $this->specificationPartNumber);
        $output->Type = $this->type;
        $output->Description = $this->description??"";
        $output->CreatedBy = $this->createdBy;
        $output->CreationDate = $this->creationDate;
        $output->Revision = $this->revision;
        return $output;
    }
}

class SpecificationPartRevision implements \JsonSerializable
{
    public string $revision;
    public \UserInformation $createdBy;
    public string $creationDate;
    public array $productionPart = [];

    public function jsonSerialize(): \stdClass
    {
        $output = new \stdClass();
        $output->RevisionCode = $this->revision;
        $output->CreatedBy = $this->createdBy;
        $output->CreationDate = $this->creationDate;
        $output->ProductionPart = $this->productionPart;
        return $output;
    }
}

if($api->isGet())
{
    $parameters = $api->getGetData();
    if(!isset($parameters->SpecificationPartBarcode)) $api->returnParameterMissingError('SpecificationPartBarcode');
    $specificationPartNumber = \Numbering\parser(\Numbering\Category::SpecificationPart, $parameters->SpecificationPartBarcode);

    $query = <<<STR
        SELECT 
            specificationPart.SpecificationPartNumber,
            specificationPart.Type,
            specificationPart.Name,
            specificationPart.Description,
            
            specificationPartUser.UserId AS SpecificationPartCreatedByName,
            specificationPartUser.Initials AS SpecificationPartCreatedByInitials,
            specificationPart.CreationDate AS SpecificationPartCreationDate,
            
            specificationPart_revision.Revision AS Revision, 
            revisionPartUser.UserId AS RevisionCreatedByName,
            revisionPartUser.Initials AS RevisionCreatedByInitials,
            specificationPart_revision.CreationDate AS RevisionCreationDate,
            
            productionPart.Number AS ProductionPartNumber,
            numbering.Prefix AS ProductionPartNumberPrefix,
            productionPart.Description AS ProductionPartDescription 
        FROM specificationPart
        LEFT JOIN specificationPart_revision ON specificationPart_revision.SpecificationPartId = specificationPart.Id
        LEFT JOIN productionPart_specificationPart_mapping ON productionPart_specificationPart_mapping.SpecificationPartRevisionId = specificationPart_revision.Id
        LEFT JOIN productionPart ON productionPart.Id = productionPart_specificationPart_mapping.ProductionPartId
        LEFT JOIN numbering ON numbering.Id = productionPart.NumberingPrefixId
        LEFT JOIN user as specificationPartUser on specificationPart.CreationUserId = specificationPartUser.Id
        LEFT JOIN user as revisionPartUser on specificationPart_revision.CreationUserId = revisionPartUser.Id
        WHERE SpecificationPartNumber = '$specificationPartNumber'
    STR;
    $result = $database->query($query);
    \Error\checkErrorAndExit($result);
    \Error\checkNoResultAndExit($result, $parameters->SpecificationPartBarcode);

    $item = $result[0];

    $output = new SpecificationPart();
    $output->name = $item->Name;
    $output->type = specificationPartType($item->Type);
    $output->specificationPartNumber = $item->SpecificationPartNumber;
    $output->description = $item->Description;
    $output->createdBy = new \UserInformation($item->SpecificationPartCreatedByName, $item->SpecificationPartCreatedByInitials);
    $output->creationDate = $item->SpecificationPartCreationDate;


    $revisions = [];
    foreach ($result as $line)
    {
        if($line->Revision === null) continue;

        if(!array_key_exists($line->Revision, $revisions)){
            $revision = new SpecificationPartRevision();
            $revision->revision = $line->Revision;
            $revision->createdBy = new \UserInformation($line->RevisionCreatedByName, $line->RevisionCreatedByInitials);
            $revision->creationDate = $line->RevisionCreationDate;

            $revisions[$revision->revision] = $revision;
        }

        if($line->ProductionPartNumber === null) continue;

        $productionPart = [];
        $productionPart['ItemCode'] = $line->ProductionPartNumberPrefix."-".$line->ProductionPartNumber;
        $productionPart['ProductionPartNumber'] = $line->ProductionPartNumber;
        $productionPart['Description'] = $line->ProductionPartDescription;

        $revisions[$line->Revision]->productionPart[] = $productionPart;
    }
    $output->revision = array_values($revisions);

    $api->returnData($output);
}
if($api->isPost())
{
    $data = $api->getPostData();

    if(!isset($data->Type)) $api->returnParameterMissingError('Type');
    if(!isset($data->Name)) $api->returnParameterMissingError('Name');

    $sqlData = array();
    $sqlData['Type'] = $data->Type;
    $sqlData['Name'] = $data->Name;
    $sqlData['CreationUserId'] = $user->userId();

    $id = $database->insert("specificationPart", $sqlData);
    $query = <<< QUERY
        SELECT SpecificationPartNumber FROM specificationPart WHERE Id = $id;
    QUERY;

    $output = array();
    $output['SpecificationPartNumber'] = $database->query($query)[0]->SpecificationPartNumber;
    $output['ItemCode'] = \Numbering\format(\Numbering\Category::SpecificationPart, $output['SpecificationPartNumber']);
    $api->returnData($output);
}
