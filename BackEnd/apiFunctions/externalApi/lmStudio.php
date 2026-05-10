<?php
//*************************************************************************************************
// FileName : lmStudio.php
// FilePath : apiFunctions/externalApi/
// Author   : Christian Marty
// Date		: 10.05.2026
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
namespace LmStudio
{
    require_once __DIR__ . '/../document/_document.php';

    function ocr(string $input): \Document\DocumentOcrData | \Error\Data
    {
        $text = [];
        $image = [];

        $text['type'] = "text";
        $text['content'] = "";

        $image['type'] = "image";
        $imageData = base64_encode($input);
        $image['data_url'] = "data:image/PDF;base64,$imageData";

        $data = apiRequest("allenai/olmocr-2-7b",[$text, $image]);
        if(\Error\checkError($data)){
            return $data;
        }
        if(!property_exists($data, 'output')){
            return \Error\generic('OCR parser Error -> no "output"');
        }

        if(!property_exists( $data->output[0], 'content')){
            return \Error\generic('OCR parser Error -> no "content"');
        }

        $dataParts = explode('---', $data->output[0]->content);

        if(count($dataParts) < 3){
            return \Error\generic("OCR parser Error -> Formatting Error");
        }

        if(strlen(array_shift($dataParts)) !== 0){
            return \Error\generic("OCR parser Error -> Unexpected data at beginning");
        }

        $output = new \Document\DocumentOcrData();
        $output->meta = array_shift($dataParts);
        $output->data = implode('---', $dataParts);
        return $output;
    }

    function apiRequest(string $model, string|array $input): \stdClass | \Error\Data
    {
        global $lmStudioUrl;
        global $lmStudioToken;

        $data = [];
        $data['model'] = $model;
        $data['input'] = $input;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $lmStudioUrl.'api/v1/chat');
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Bearer $lmStudioToken","Content-Type: application/json"));
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));

        $return = json_decode(curl_exec($curl));

        if($return === null){
            return \Error\generic("LM Studio Output JSON parser error");
        }

        return $return;
    }
}
