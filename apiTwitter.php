<?php 
    define('BEARER_TOKEN','AAAAAAAAAAAAAAAAAAAAABf4aAEAAAAALlOOS9ZTCuaxyu49wfkpgLFjzJc%3DiliUyQtS4BwNLWoSh4ZXOAvdYBhiQfIVcwNLoaeePX9hjH43XY');
    define('CONSUMER_KEY', 'm9lAdcNELnLhGykxP3cbF81JZ');
    define('CONSUMER_SECRET','TCbwtPT9bzoKnPCw0d3uyOcd3HH6JnMMaHHvDjzKEDblu4ts2k');
    require 'administracion.php';
    require 'funciones.php';
    


    class ApiTwitter{
        const TWITER_API_DOMAIN = 'https://api.twitter.com/';
        const AUTH1 = 'OAuth 1.0a'; //user context 
        const AUTH2 = 'OAuth 2.0';  //app-only
        
        private $_bearerToken;
        private $_consumerKey;
        private $_consumerSecret;
        private $_tokenSecret;
        private $_token;

        public function __construct($bearerToken, $consumerKey, $consumerSecret, $token='', $tokenSecret=''){
            $this->_bearerToken = $bearerToken;
            $this->_consumerKey = $consumerKey;
            $this->_consumerSecret = $consumerSecret;
            $this->_tokenSecret = $tokenSecret;
            $this->_token = $token;
        }

        public function getAcces($callbackUrl){
            $admin = new Administracion($this->_consumerKey, $this->_consumerSecret, $this->_tokenSecret);
            $data = $admin->getRequestToken($callbackUrl);
            echo $admin->makeAuthorize($data);
            return;
        }
        
        protected function makeApiCall( $apiParams ) {
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

        private function doRequest($method,$endpoint,$authorization,$urlParams = array()){
            $admin = new Administracion($this->_bearerToken, $this->_consumerKey, $this->_consumerSecret, $this->_token, $this->_tokenSecret);
            $authorization = $admin->doAuthorization($method,$endpoint,$urlParams,$authorization);
            $apiParams = $admin->getApiParams($method,$endpoint,$urlParams,$authorization);
            $apiResponse = $this->makeApiCall($apiParams);
            return $apiResponse;
        }

        public function errorApi($apiResponse){
            if(isset($apiResponse['errors'])){
                if(isset($apiResponse['data']))
                    array_shift($apiResponse);
                return $apiResponse;
            }
            else
                return $apiResponse['data'];
        }

        /**
        *Get the id of an user name 
        *@param user_name
        *@return user_id
        */
        public function getIdUser($userName){ //TIENE ERRORS
            $endpoint = self::TWITER_API_DOMAIN . '2/users/by/username/' . $userName;
            $dataUser = $this->doRequest('GET',$endpoint,self::AUTH2); 
            $apiResponse = removeHeader($dataUser);
            return $this->errorApi($apiResponse);
            return $apiResponse;
            if(isset($apiResponse['data']))
                return $apiResponse['data']['id'];
            else
                return $apiResponse['errors'][0]['detail'];
        }

        /**
        *Get the publics metrics of a user
        *@param user_name
        *@return followers_count
        *@return following_count
        *@return tweet_count
        *@return listed_count
        */
        public function getPublicMetrics($userNameList){ //TIENE ERRORS
            $endpoint = self::TWITER_API_DOMAIN .'2/users/by' ;
            $urlParams = array(
                'usernames' => commaSeparated($userNameList),
                'user.fields' => 'public_metrics'
            );
            $dataUser = $this->doRequest('GET',$endpoint,self::AUTH2,$urlParams); 
            $apiResponse = removeHeader($dataUser);
            return $this->errorApi($apiResponse);
            return $apiResponse;
            if(isset($apiResponse['errors']))
                return $apiResponse['errors'];
            $usersMetrics = array();
            foreach($apiResponse['data'] as $dato){
                array_push($usersMetrics,array(
                    'id' => $dato['id'],
                    'username' => $dato['username'],
                    'public_metrics' => $dato['public_metrics'])
                );
            }
            return $usersMetrics;
        }

        /**
        *Get tweets for an user in a time interval
        *@param user_id
        *@return tweets_ids
        */
        public function getIdTweets($id_user,$interval){ //TIENE ERRORS
            $endpoint = self::TWITER_API_DOMAIN .'2/users/'.$id_user.'/tweets' ;
            $urlParams = array(
                'exclude' => 'retweets,replies',
                'start_time' => $interval['start'],
                'end_time' => $interval['end']
            );
            $dataUser = $this->doRequest('GET',$endpoint,self::AUTH2,$urlParams);
            $apiResponse = removeHeader($dataUser);
            return $apiResponse;
            if(isset($apiResponse['data'])){
                $tweetsIds = array();
                foreach($apiResponse['data'] as $dato){
                    array_push($tweetsIds,$dato['id']);
                }
                return $tweetsIds;     
            }
            return array(
                'result_count' => $apiResponse['meta']['result_count']
            );
        }

        /**
        *Get public metrics of a list of tweets 
        *@param tweets_ids
        *@return public_metrics
        */
        public function getTweetMetrics($listTweetsIds){ //TIENE ERRORS
            $endpoint = self::TWITER_API_DOMAIN .'2/tweets';
            $urlParams = array(
                'ids' => commaSeparated($listTweetsIds),
                'tweet.fields' => 'public_metrics'
            );
            $dataUser = $this->doRequest('GET',$endpoint,self::AUTH2,$urlParams);
            $apiResponse = removeHeader($dataUser);
            return $apiResponse;
            $tweetsMetrics = array();
            foreach($apiResponse['data'] as $dato){
                array_push($tweetsMetrics,array(
                    'id' => $dato['id'],
                    'public_metrics' => $dato['public_metrics'])
                );
            }
            return $tweetsMetrics;
        }

        /**
        *Get organic metrics of a list of tweets 
        *Require OAUTH1.0
        *@param tweets_ids
        *@return organic_metrics
        */
        public function getOrganicMetrics($listTweetsIds){
            $endpoint = self::TWITER_API_DOMAIN .'2/tweets';
            $urlParams = array(
                'ids' => commaSeparated($listTweetsIds),
                'tweet.fields' => 'organic_metrics'
            );
            $dataUser = $this->doRequest('GET',$endpoint,self::AUTH1,$urlParams);
            $apiResponse = removeHeader($dataUser);
            $tweetsMetrics = array();
            foreach($apiResponse['data'] as $dato){
                array_push($tweetsMetrics,array(
                    'id' => $dato['id'],
                    'organic_metrics' => $dato['organic_metrics'])
                );
            }
            return $tweetsMetrics;   
        }

        /**
        *Get the tweets in a interval of time 
        *@param query
        *@param interval array(start,end) 
        *@return total_tweet_count
        */
       
        public function getCountTweets($query,$interval){ //tiene errors
            $endpoint = self::TWITER_API_DOMAIN .'2/tweets/counts/recent';
            $urlParams = array(
                'start_time' => $interval['start'],
                'end_time' => $interval['end'],
                'query' => $query
            );
            $dataUser = $this->doRequest('GET',$endpoint,self::AUTH2,$urlParams);
            $apiResponse = removeHeader($dataUser);
            return $apiResponse;
            return $apiResponse['meta']['total_tweet_count'];
        }
        

        
    }

    



    
?>

