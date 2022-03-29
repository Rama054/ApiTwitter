<?php
    define('TIME_ZONE','Europe/Madrid');
    date_default_timezone_set('UTC'); 
    

    function getYesterday(){
        //date_default_timezone_set(TIME_ZONE); 
        $time = time() - (1*24*60*60);
        $pastTime = date('Y-m-d', $time);
        return $pastTime . 'T00:00:00.000Z';    
    }

    function getToday(){
        return date('Y-m-d', time()) . 'T00:00:00.000Z';
    }

    function getNow(){
        $fecha = date('Y-m-d',time());
        $hora = date('H:i:s',time()-60);
        return $fecha . 'T' . $hora . '.000Z';
    }

    
    
    function commaSeparated($list){
        return (is_string($list)) ? $list : implode(",", $list);
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