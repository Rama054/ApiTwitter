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
    //imprimirArray($apiTwitter->getPublicMetrics(array('Ramiro_lopez005','jmilei')));
    
    $intervalo = array(
        'end' => getToday(),
        'start' => getNow()
    );
    //echo $apiTwitter->getRecentTweets('jmilei',$intervalo);
    //'2329495967' 4020276615  
    //1508626483593449480
    //1508602840670883841
    //1508602744260624384
    //imprimirArray($apiTwitter->getPublicMetrics('Ramiro_lopez005'));
    imprimirArray($apiTwitter->getIdUser('milei55,ramiro_lopez005'));
    
?>  