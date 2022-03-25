<?php
    require 'apiTwitter.php';
    if( isset( $_GET['oauth_token'] ) && isset( $_GET['oauth_verifier'] )){
        $admin = new Administracion(CONSUMER_KEY,CONSUMER_SECRET);
        $tokens = $admin->getAccessToken($_GET['oauth_token'],$_GET['oauth_verifier']);
        /* $_SESSION['oauth_token'] = $tokens['oauth_token'];		
        $_SESSION['oauth_token_secret'] = $tokens['oauth_token_secret'];
        $_SESSION['screen_name'] = $tokens['screen_name'];
        $_SESSION['user_id'] = $tokens['user_id']; */

    }


   /*  $conexion = new PDO('mysql:host=localhost; dbname=twitter', 'root', '');
    $statement = $conexion->prepare("INSERT INTO credenciales VALUES(null,$tokens['oauth_token'],$tokens['oauth_token_secret'],$tokens['screen_name'],$tokens['user_id'])");
    $statement->execute();
    echo 'claves guardadas'; */

    $conexion = new mysqli('localhost', 'root', '', 'twitter');

    $statement = $conexion->prepare("INSERT INTO credenciales(ID, Token,TokenSecret,Usuario,Id_Usuario) VALUES(?,?,?,?,?)");
    $statement->bind_param('ssssi',$id,$token,$tokenSecret,$usuario,$id_usuario);
    $id = null;
    $token = $tokens['oauth_token'];
    $tokenSecret = $tokens['oauth_token_secret'];
    $usuario = $tokens['screen_name'];
    $id_usuario = $tokens['user_id'];

    $statement->execute();
    echo 'filas aniadidas: '.$conexion->affected_rows;


?>