<?php
    require 'apiTwitter.php';

    
    //$apiTwitter = new ApiTwitter(BEARER_TOKEN,CONSUMER_KEY,CONSUMER_SECRET);
    //$apiTwitter->getAcces('http://localhost/APIsRedesSociales/Twitter');

    $conexion = new mysqli('localhost', 'root', '', 'twitter');
    $sql = 'SELECT * FROM credenciales';
    $resultado = $conexion->query($sql);

    if($resultado->num_rows){
        $fila = $resultado->fetch_assoc();
    }
    $token = $fila['Token'];
    $tokenSecret = $fila['TokenSecret'];
    $usuario = $fila['Usuario'];
    $id_Usuario = $fila['Id_Usuario'];   
    
    $apiTwitter = new ApiTwitter(BEARER_TOKEN,CONSUMER_KEY,CONSUMER_SECRET,$token,$tokenSecret);
    $apiTwitter->getTweetsLastDay('2329495967');
    echo "<pre>";
    print_r($apiTwitter->getPublicMetrics('rubiu5'));

      
?>  