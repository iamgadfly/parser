<?php

namespace App\Services;

class TranslateService
{
    public function translate(array $text)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            "Authorization: AApi-Key " . env('TRANSLATE_KEY'), 
        ]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode([
            "targetLanguageCode" => 'ru',
            "texts"               => $text,
        ]));
        curl_setopt($curl, CURLOPT_URL, 'https://translate.api.cloud.yandex.net/translate/v2/translate');
        curl_setopt($curl, CURLOPT_POST, true);
        $result = curl_exec($curl);
        curl_close($curl);
	$data = json_decode($result)->translations ?? null;
	if(!is_null($data)){
		$result = collect($data);
		$result_data = $result->pluck('text');
		return [
						//'name' => $result_data->pull(0),
						'color' => $result_data->pull(0),
						'materials' => $result_data->all(),
		];	
	} else {
		return null;
	}
    }
}
