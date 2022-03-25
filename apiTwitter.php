<?php 
    define('BEARER_TOKEN','AAAAAAAAAAAAAAAAAAAAABf4aAEAAAAALlOOS9ZTCuaxyu49wfkpgLFjzJc%3DiliUyQtS4BwNLWoSh4ZXOAvdYBhiQfIVcwNLoaeePX9hjH43XY');
    define('CONSUMER_KEY', 'm9lAdcNELnLhGykxP3cbF81JZ');
    define('CONSUMER_SECRET','TCbwtPT9bzoKnPCw0d3uyOcd3HH6JnMMaHHvDjzKEDblu4ts2k');
    require 'administracion.php';
    require 'funciones.php';

    class ApiTwitter{
        //const TWITER_ID_DOMAIN = 'https://id.twitch.tv/';
        const TWITER_API_DOMAIN = 'https://api.twitter.com/';

        private $_bearerToken;
        private $_consumerKey;
        private $_consumerSecret;
        private $_token;

        public function __construct($bearerToken, $consumerKey, $consumerSecret, $tokenSecret='',$token=''){
            $this ->_bearerToken = $bearerToken;
            $this ->_consumerKey = $consumerKey;
            $this ->_consumerSecret = $consumerSecret;
            $this ->_tokenSecret = $tokenSecret;
            $this ->_token = $token;
        }

        public function getAcces($callbackUrl){
            $admin = new Administracion($this->_consumerKey, $this->_consumerSecret, $this->_tokenSecret);
            $data = $admin->getRequestToken($callbackUrl);
            echo $admin->makeAuthorize($data);
            return;


        }
        
        public function makeApiCall( $apiParams ) {
            $curlOptions = array( // curl options
                CURLOPT_URL => $apiParams['endpoint'], // endpoint
                CURLOPT_RETURNTRANSFER => TRUE, // return stuff!
            );

            if( isset($apiParams['authorization'])){
                $curlOptions[CURLOPT_HEADER] = TRUE;
                $curlOptions[CURLOPT_HTTPHEADER] = array(
                    $apiParams['authorization']
                );
            }

            if ( 'POST' == $apiParams['method'] && isset($apiParams['url_params'])) { 
				$curlOptions[CURLOPT_POST] = TRUE;
				$curlOptions[CURLOPT_POSTFIELDS] = http_build_query( $apiParams['url_params'] );
			} elseif ( 'GET' == $apiParams['method'] && isset($apiParams['url_params'])) { 
				$curlOptions[CURLOPT_URL] .= '?' . http_build_query( $apiParams['url_params'] );
			}
                           
            
    
            $ch = curl_init();
            curl_setopt_array( $ch, $curlOptions );
            $apiResponse = curl_exec( $ch );
                       
            //200 OK
            //400 BAD REQUEST
            //401 UNAUTHORIZE
            if ( 200 == curl_getinfo( $ch, CURLINFO_HTTP_CODE ) ) { 
                $status = 'ok';
                $message = '';
            } else {
                $status = 'fail';
                $message = 'HTTP Error Code: '.curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            }

            curl_close( $ch );
                
            return array( // return array
                'status' => $status, // status
                'message' => $message,  // message
                'api_data' => $apiResponse, // api response
                'endpoint' => $curlOptions[CURLOPT_URL], // endpoint hit
                //'authorization' => $apiParams['authorization'] // authorrization headers
            );
        }


        /**
        *Take the publics metrics of a user
        *@return followers_count
        *@return following_count
        *@return tweet_count
        *@return listed_count
        */
        public function getPublicMetrics($usuario){
            $method = 'GET';
            $endpoint = self::TWITER_API_DOMAIN .'2/users/by' ;
            $apiParams = array( 
                'method' => $method,
                'endpoint' => $endpoint,
                'authorization' => "Authorization: Bearer ".$this->_bearerToken,
                'url_params' => array(
                    'usernames' => $usuario,
                    'user.fields' => 'public_metrics',
                    ) 
                );
            
            $dataUser = $this->makeApiCall($apiParams);    
            $responseParts = explode("\r\n\r\n",$dataUser['api_data']);
            $responseBody = array_pop( $responseParts );
            $apiResponse = json_decode($responseBody,true);
            return $apiResponse;//['data'][0]['public_metrics'];
        }

        public function getTweetsLastDay($id_usuario){
            $method = 'GET';
            $endpoint = self::TWITER_API_DOMAIN .'2/users/'.$id_usuario.'/tweets' ;
            $apiParams = array( 
                'method' => $method,
                'endpoint' => $endpoint,
                'authorization' => "Authorization: Bearer ".$this->_bearerToken,
                'url_params' => array(
                    'exclude' => 'retweets,replies',
                    'start_time' => '2015-01-01T00:00:00.000Z',//getYesterday(),
                    'end_time' => date('Y-m-d', time()) . 'T00:00:00.000Z'
                    ) 
                );
            $dataUser = $this->makeApiCall($apiParams);   
            $apiResponse = removeHeader($dataUser);
            
            $pepe = array();
            foreach($apiResponse['data'] as $dato){
                array_push($pepe,$dato['id']);
                
            }
            echo '<pre>';
            print_r($pepe);
            $this->getTweetMetrics($pepe);
            
            
        }

        public function getTweetMetrics($list){
            //funcion(metodo,endpoint,params,authoritation)
            $method = 'GET';
            $endpoint = self::TWITER_API_DOMAIN .'2/tweets';
            $apiParams = array( 
                'method' => $method,
                'endpoint' => $endpoint,
                'authorization' => "Authorization: Bearer ".$this->_bearerToken,
                'url_params' => array(
                    'ids' => commaSeparated($list),
                    'tweet.fields' => 'public_metrics'
                    )
                ); 
            $dataUser = $this->makeApiCall($apiParams);  
            $apiResponse = removeHeader($dataUser);
            
            $pepe = array();
            foreach($apiResponse['data'] as $dato){
                array_push($pepe,array(
                    'id' => $dato['id'],
                    'public_metrics' => $dato['public_metrics'])
                );
                
            }


            echo '<pre>';
            print_r($pepe);
            die();
        }

       

        public function consultarVistasTwitter($id_usuario){
            $method = 'GET';
            $endpoint = self::TWITER_API_DOMAIN .'2/users/'.$id_usuario.'tweets' ;
            $apiParams = array( 
                'method' => $method,
                'endpoint' => $endpoint,
                'authorization' => "Authorization: Bearer ".$this->_bearerToken,
                'url_params' => array(
                    'usernames' => $usuario,
                    'expansions' => 'pinned_tweet_id',
                    ) 
                );
            
            $dataUser = $this->makeApiCall($apiParams);    
            $responseParts = explode("\r\n\r\n",$dataUser['api_data']);
            $responseBody = array_pop( $responseParts );
            $apiResponse = json_decode($responseBody,true);
            echo "<pre>";
            print_r($apiResponse);
            $idTweet = $apiResponse['includes']['tweets'][0]['id'];
            $endpoint = self::TWITER_API_DOMAIN .'2/tweets/'.$idTweet;
            
            $urlParams = array( // url params for endpoint
                'tweet.fields' => 'organic_metrics,non_public_metrics',
			);
            
            $authorizationParams = array( 
                'oauth_consumer_key' => $this->_consumerKey, 
                'oauth_nonce' => md5( microtime() . mt_rand() ), 
                'oauth_signature_method' => 'HMAC-SHA1', 
                'oauth_timestamp' => time(),
                'oauth_token' => $this->_token,
                'oauth_version' => '1.0' 
            );
            $admin = new Administracion($this->_consumerKey, $this->_consumerSecret, $this->_tokenSecret);
            $authorizationParams['oauth_signature'] = $admin->getSignature( $method, $endpoint, $authorizationParams, $urlParams);


            $apiParams = array( 
                'method' => $method,
                'endpoint' => $endpoint,
                'authorization' => $admin->getAuthorizationString( $authorizationParams ),
                'url_params' => $urlParams
            );
            $dataUser = $this->makeApiCall($apiParams);
            echo "<pre>";
            print_r($dataUser);
           
            die();
            return $dataUser;
        }

        
    }

    





    /* if( isset( $_GET['oauth_token'] ) && isset( $_GET['oauth_verifier'] )){
        $admin = new Administracion(CONSUMER_KEY,CONSUMER_SECRET);
        $tokens = $admin->getAccessToken($_GET['oauth_token'],$_GET['oauth_verifier']);
        $_SESSION['oauth_token'] = $tokens['oauth_token'];		
		$_SESSION['oauth_token_secret'] = $tokens['oauth_token_secret'];
        $_SESSION['screen_name'] = $tokens['screen_name'];
        $_SESSION['user_id'] = $tokens['user_id'];
        $apiTwitter = new ApiTwitter(BEARER_TOKEN,CONSUMER_KEY,CONSUMER_SECRET,$_SESSION['oauth_token_secret'],$_SESSION['oauth_token']);
        $apiTwitter->consultarVistasTwitter($_SESSION['screen_name']);


    } */

    /* $usuario = 'JMilei';

    $apiTwitter = new ApiTwitter(BEARER_TOKEN,CONSUMER_KEY,CONSUMER_SECRET);
    $apiTwitter->getAcces('http://localhost/APIsRedesSociales/Twitter');

    $apiTwitter = new ApiTwitter(BEARER_TOKEN,CONSUMER_KEY,CONSUMER_SECRET);
    //$data = $apiTwitter->consultarVistasTwitter($usuario);
    $data = $apiTwitter->followsTwitter('Ramiro_lopez005');
    
    echo "<pre>";
    print_r($data);
    die();










    
    

    $params = array(
        'bearerToken' => 'AAAAAAAAAAAAAAAAAAAAABf4aAEAAAAALlOOS9ZTCuaxyu49wfkpgLFjzJc%3DiliUyQtS4BwNLWoSh4ZXOAvdYBhiQfIVcwNLoaeePX9hjH43XY',
        'endPointURL' => 'https://api.twitter.com/2/users/by?usernames='.$usuario .'&user.fields=public_metrics'
    );


    function realizarPeticion($params){
        $ch = curl_init();
        $curlOptions = array(
            CURLOPT_URL => $params['endPointURL'],
            CURLOPT_RETURNTRANSFER=> true,
            CURLOPT_HEADER => TRUE,    
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $params['bearerToken']
                //'Authorization: OAuth 2329495967-pi35JNkiaqR6OLC6HOOFEv0MO1gIQcF52rX1nyB'
            )
        );

        curl_setopt_array($ch,$curlOptions);
        $apiResponse = curl_exec($ch);
        
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $apiResponseBody = substr($apiResponse, $headerSize);
        $apiResponse = json_decode($apiResponseBody,true);
        echo '<pre>';
        print_r($apiResponse);
        //echo curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        die();
        if ( 200 == curl_getinfo( $ch, CURLINFO_HTTP_CODE ) ) { 
            $status = 'ok';
            $message = '';
        } else {
            $status = 'fail';
            $message = isset( $response['errors'][0]['message'] ) ? $response['errors'][0]['message'] : 'Unauthorized';
        }

        curl_close($ch);
        //return $apiResponse;
        return array(
            'status' => isset($apiResponse['status']) ? 'fail' : 'ok',
            'message' => isset($apiResponse['message']) ? $apiResponse['message'] : '',
            'api_data' => $apiResponse,
            'endpoint' => $curlOptions[CURLOPT_URL]
        ); 
    }

    function consultarFollowsTwitter($usuario){
        $params = array(
            'bearerToken' => 'AAAAAAAAAAAAAAAAAAAAABf4aAEAAAAALlOOS9ZTCuaxyu49wfkpgLFjzJc%3DiliUyQtS4BwNLWoSh4ZXOAvdYBhiQfIVcwNLoaeePX9hjH43XY',
            'endPointURL' => 'https://api.twitter.com/2/users/by?usernames='.$usuario .'&tweet.fields=public_metrics'
        );
        $dataUser = realizarPeticion($params);   
        return $dataUser['data'][0]['public_metrics']['followers_count'];
    }

    function consultarVistasTwitter($usuario){
        $params = array(
            'bearerToken' => 'AAAAAAAAAAAAAAAAAAAAABf4aAEAAAAALlOOS9ZTCuaxyu49wfkpgLFjzJc%3DiliUyQtS4BwNLWoSh4ZXOAvdYBhiQfIVcwNLoaeePX9hjH43XY',
            'endPointURL' => 'https://api.twitter.com/2/users/by?usernames='.$usuario .'&expansions=pinned_tweet_id'
        );
         /* $dataUser = realizarPeticion($params);   
        $idPinnedTweet = $dataUser['includes']['tweets']['0']['id'];
        echo  $idPinnedTweet;  */
        /* $params['endPointURL'] = 'https://api.twitter.com/2/tweets/1504605814769692676';
        $dataPinnedTweet = realizarPeticion($params);  
        
        echo '<pre>';
        print_r($dataPinnedTweet);
        die(); */

    //}
   
    //consultarVistasTwitter($usuario);

    

 
        

























    
?>

