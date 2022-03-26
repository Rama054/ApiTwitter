<?php
    define('TIME_ZONE','Europe/Madrid');

    function getYesterday(){
        $time = time() - (1*24*60*60);
        date_default_timezone_set(TIME_ZONE); 
        $pastTime = date('Y-m-d', $time);
        return $pastTime . 'T00:00:00.000Z';    
    }

    function commaSeparated($list){
        return implode(",", $list);
    }

    function removeHeader($apiResponse){
        $responseParts = explode("\r\n\r\n",$apiResponse['api_data']);
        $responseBody = array_pop( $responseParts );
        $apiResponse = json_decode($responseBody,true);
        return $apiResponse;
        
    }

    function imprimirArray($array){
        echo "<pre>";
        print_r($array);
        return;
    }





?>