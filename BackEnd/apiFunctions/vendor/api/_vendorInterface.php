<?php
//*************************************************************************************************
// FileName : _vendorInterface.php
// FilePath : apiFunctions/vendor
// Author   : Christian Marty
// Date		: 11.12.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);

namespace vendorInterface;

class vendorInterface
{
    protected bool $orderImportSupported = false;
    protected bool $orderUploadSupported = false;
    protected bool $skuSearchSupported = false;

    protected \stdClass|null  $apiData = null;

    public function __construct(\stdClass|null $apiData)
    {
        $this->apiData = $apiData;
    }

    public function information(): array
    {
        $output = array();
        $output['Authentication']= array();
        $output['Authentication']['Authenticated'] = false;
        $output['Authentication']['AuthenticationUrl'] = '';

        $output['Capability']= array();
        $output['Capability']['OrderImportSupported'] = $this->orderImportSupported;
        $output['Capability']['OrderUploadSupported'] = $this->orderUploadSupported;
        $output['Capability']['SkuSearchSupported'] = $this->skuSearchSupported;
        return $output;
    }

    public function defaultApiData(): array
    {
        return [];
    }

    public function getOrderHistory(): array|null
    {
        return null;
    }

    public function parseOrderInformation(string $data): array|null
    {
        return null;
    }

    public function getOrderInformation(string $orderId): array|null
    {
        return null;
    }

    public function skuSearch(string $sku): array|null
    {
        return null;
    }

    public function getPartData(string $mouserPartNumber): array|null
    {
        return null;
    }
}
