<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use \Illuminate\Support\Facades\Session;
class ApiService
{
     

    public function sendApiRequest($params)
    {
        

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://192.168.157.6:5700/grab/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'image_url' => $params['image_url'],
                'private_key' => 'PoGpApBq455wuwZkTE7jMzLw39I8Ria7',
                'category' => $params['category'],
            ),
        ));

        
        $response = curl_exec($curl);
        
        if ($response === false) {
            return false;
            echo 'cURL Error: ' . curl_error($curl);
        } else {
            $decodedResponse = json_decode($response, true);
            if ($decodedResponse === null) {
                return false;
                echo 'JSON Decoding Error: ' . json_last_error_msg();
            } else {
                // $decodedResponse['id'] = rand(1,100);
                // $decodedResponse['status'] = 200;
                return $decodedResponse;
            }
        }
        curl_close($curl);
 
    }


    public function sendApiResult($ai_id)
    {
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://192.168.157.6:5700/detection/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'pk' => $ai_id,
                'private_key' => 'PoGpApBq455wuwZkTE7jMzLw39I8Ria7',
            ),
        ));

        
        $response = curl_exec($curl);
        if ($response === false) {
            return false;
            echo 'cURL Error: ' . curl_error($curl);
        } else {
            $decodedResponse = json_decode($response, true);
            if ($decodedResponse === null) {
                return false;
                echo 'JSON Decoding Error: ' . json_last_error_msg();
            } else {
                return $decodedResponse;
            }
        }
        curl_close($curl);
 
    }


}

