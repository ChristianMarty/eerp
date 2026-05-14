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

    enum ApiEndpoint
    {
        case Chat;
        case Embedding;
        case Load;
        case Models;
    }

    function apiUrl(ApiEndpoint $endpoint): string
    {
        global $lmStudioUrl;
        return match ($endpoint) {
            ApiEndpoint::Chat => $lmStudioUrl.'api/v1/chat',
            ApiEndpoint::Embedding => $lmStudioUrl.'v1/embeddings',
            ApiEndpoint::Load => $lmStudioUrl.'api/v1/models/load',
            ApiEndpoint::Models => $lmStudioUrl.'api/v1/models'
        };
    }

    enum Model: string
    {
        case OlmOcr = 'allenai/olmocr-2-7b';
        case NomicEmbed = 'text-embedding-nomic-embed-text-v1.5';
    }

    function modelName(Model $model) : string
    {
        return $model->value;
    }

    function embedding(string $input): \stdClass |array | \Error\Data
    {
        $data = [];
        $data['model'] = modelName(Model::NomicEmbed);
        $data['input'] = $input;

        return apiPostRequest($data, ApiEndpoint::Embedding);
    }

    function ocr(string $input): \Document\DocumentOcrData | \Error\Data
    {
        $models = models();
        if(\Error\checkError($models)){
            return $models;
        }

        if(!in_array(Model::OlmOcr, $models)) {
            $load = loadOcr();
            if (\Error\checkError($load)) {
                return $load;
            }
        }

        $text = [];
        $image = [];

        $text['type'] = "text";
        $text['content'] = "";

        $image['type'] = "image";
        $imageData = base64_encode($input);
        $image['data_url'] = "data:image/PDF;base64,$imageData";

        $data = chat(modelName(Model::OlmOcr) ,[$text, $image]);
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

    function loadOcr(): \stdClass | \Error\Data
    {
        $data = [];
        $data['model'] = modelName(Model::OlmOcr);
        $data['context_length'] = 8196;

        return apiPostRequest($data, ApiEndpoint::Load);
    }

    function chat(string $model, string|array $input): \stdClass | \Error\Data
    {
        $data = [];
        $data['model'] = $model;
        $data['input'] = $input;

        return apiPostRequest($data, ApiEndpoint::Chat);
    }

    function models(): array | \Error\Data
    {
        $data = apiGetRequest(ApiEndpoint::Models);
        if(\Error\checkError($data)){
            return $data;
        }
        if(!property_exists($data, 'models')){
            return \Error\generic('Api parser Error -> no "models"');
        }

        $output = [];
        foreach($data->models as $model)
        {
            foreach($model->loaded_instances as $instances) {
                $model = Model::tryFrom($instances->id);
                if($model) {
                    $output[] = $model;
                }
            }
        }
        return $output;
    }

    function apiPostRequest(array $data, ApiEndpoint $endpoint): \stdClass | \Error\Data
    {
        global $lmStudioToken;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, apiUrl($endpoint));
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

    function apiGetRequest( ApiEndpoint $endpoint): \stdClass | \Error\Data
    {
        global $lmStudioToken;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, apiUrl($endpoint));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Bearer $lmStudioToken","Content-Type: application/json"));
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $return = json_decode(curl_exec($curl));
        if($return === null){
            return \Error\generic("LM Studio Output JSON parser error");
        }

        return $return;
    }
}
