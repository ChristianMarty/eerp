<?php
//*************************************************************************************************
// FileName : digikey.php
// FilePath : apiFunctions/externalApi/
// Author   : Christian Marty
// Date		: 06.04.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
use vendorInterface\vendorInterface;

require_once __DIR__ . "/../../../config.php";

class digikey extends vendorInterface {

    public function defaultApiData(): array
    {
        global $domainRootPath;

        $output = [];
        $output["ApiPath"] = "";
        $output["ClientId"] = "";
        $output["ClientSecret"] = "";
        $output["CallbackPath"] = $domainRootPath."/pushHandler/Digikey/oAuthCallback.php";

        return $output;
    }

    public function information(): array
    {
        $authentication = array();
        if($this->isAuthenticated())
        {
            $authentication['Authenticated'] = true;
            $authentication['AuthenticationUrl'] = '';
        }
        else
        {
            $authentication['Authenticated'] = false;
            $authentication['AuthenticationUrl'] = $this->authenticate();
        }

        $data = array();
        $data['Authentication'] = $authentication;

        $capability = array();
        $capability['OrderImportSupported'] = true;
        $capability['SkuSearchSupported'] = true;

        $data['Capability'] = $capability;

        return $data ;
    }

    private function authenticate(): string|null
    {
        global $digikeyApiPath;
        global $digikeyClientId;
        global $digikeyClientSecret;
        global $digikeyCallbackPath;

        if(isset($_SESSION['digikeyAccessTokenExpire'])) $digikeyAccessTokenExpire = $_SESSION['digikeyAccessTokenExpire'];
        else $digikeyAccessTokenExpire = 0;

        if(isset($_SESSION['digikeyAccessToken'])) $digikeyAccessToken = $_SESSION['digikeyAccessToken'];
        else $digikeyAccessToken = null;


        if($digikeyAccessToken == null || $digikeyAccessTokenExpire <= time() )
        {
            $url  = $digikeyApiPath.'v1/oauth2/authorize?response_type=code';
            $url .= '&client_id='.$digikeyClientId;
            $url .= '&redirect_uri='.urlencode($digikeyCallbackPath);

            return $url;

            /*header('Location: '.$url );
            header('Access-Control-Allow-Origin: https://auth.digikey.com');
            header('Access-Control-Allow-Origin: https://api.digikey.com');
            exit;*/
        }

        return null;
    }

    private function isAuthenticated(): bool
    {
        if(isset($_SESSION['digikeyAccessToken']) && isset($_SESSION['digikeyAccessTokenExpire']))
        {
            return!($_SESSION['digikeyAccessToken'] == null || $_SESSION['digikeyAccessTokenExpire'] <= time());
        }

        return false;
    }

    function getPartData(string $digikeyPartNumber): array|null
    {
        global $digikeyApiPath;
        global $digikeyClientId;

        $url = $digikeyApiPath.'Search/v3/Products/'.$digikeyPartNumber;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('X-DIGIKEY-Client-Id: '.$digikeyClientId, 'Authorization: Bearer '.$_SESSION['digikeyAccessToken']));

        $result = curl_exec($curl);

        curl_close($curl);

        $digikeyData = json_decode($result,true);

        return $digikeyData;
    }

    public function getOrderHistory(): array|null
    {
        global $digikeyApiPath;
        global $digikeyClientId;

        $url = $digikeyApiPath.'OrderDetails/v3/History';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('X-DIGIKEY-Client-Id: '.$digikeyClientId, 'Authorization: Bearer '.$_SESSION['digikeyAccessToken']));

        $result = curl_exec($curl);

        curl_close($curl);

        $digikeyData = json_decode($result,true);

        $data = Array();
        $data['NumberOfOrders'] = count($digikeyData);

        $orders = array();
        foreach($digikeyData as $order)
        {
            $dateTime = explode("T", $order["DateEntered"]);

            $temp = array();
            $temp['OrderDate'] = $dateTime[0];
            $temp['OrderTime'] = $dateTime[1];
            $temp['OrderNumber'] = $order["SalesorderId"];

            $orders[$temp['OrderNumber']] = $temp;
        }

        $data['Orders'] = $orders;

        return $data;
    }

    function getOrderInformation(string $salesOrderId): array
    {
        digikey_auth();

        global $digikeyApiPath;
        global $digikeyClientId;
        global $accountingCurrency;

        $salesOrderId = trim($salesOrderId, " \n\r");

        $url = $digikeyApiPath.'OrderDetails/v3/Status/'.$salesOrderId;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('X-DIGIKEY-Client-Id: '.$digikeyClientId, 'Authorization: Bearer '.$_SESSION['digikeyAccessToken']));

        $result = curl_exec($curl);

        $_SESSION['digikeyLastError'] = $result;

        curl_close($curl);

        $digikeyData = json_decode($result,true);

        $data = Array();

        $data['VatPrice'] = 0;
        $data['TotalPrice'] = 0;
        $data['ShippingPrice'] = 0;
        $data['MerchandisePrice'] = 0;
        $data['CurrencyCode'] = $digikeyData["Currency"];
        $data['OrderDate'] = digikey_getOrderHistory()['Orders'][$salesOrderId]['OrderDate'];

        $lineIndex = 1;
        $lines = array();
        foreach($digikeyData["LineItems"] as $line)
        {
            $temp = array();
            $temp['ManufacturerPartNumber'] = $line["ManufacturerPartNumber"];
            $temp['ManufacturerName'] = $line["Manufacturer"]; //digikey_getProductData($line["ManufacturerPartNumber"])['Supplier'];
            $temp['SupplierPartNumber'] = $line["DigiKeyPartNumber"];
            $temp['SupplierDescription'] = $line["ProductDescription"];
            $temp['OrderReference'] = $line["CustomerReference"];
            $temp['Quantity'] = $line["Quantity"];
            $temp['Price'] = $line["UnitPrice"];
            $temp['TotalPrice'] = $line["TotalPrice"];
            if($line["PoLineItemNumber"] == "") $temp['LineNumber'] = $lineIndex;
            else $temp['LineNumber'] = $line["PoLineItemNumber"];

            $lineIndex++;

            $data['MerchandisePrice'] += $temp['Price'];

            $lines[] = $temp;
        }

        $data['Lines'] = $lines;


        return $data;
    }
}