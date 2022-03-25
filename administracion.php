<?php

    class Administracion extends ApiTwitter{
        const TWITTER_API_DOMAIN = 'https://api.twitter.com/';
        const TWITTER_API_VERSION = '1.1';

        private $_consumerKey;
        private $_consumerSecret;
        private $_tokenSecret;

        public function __construct($consumerKey, $consumerSecret, $tokenSecret = ''){
            $this ->_consumerKey = $consumerKey;
            $this ->_consumerSecret = $consumerSecret;
            $this ->_tokenSecret = $tokenSecret;
        }

        public function getSignature( $method, $endpoint, $authorizationParams, $urlParams = array() ) {
			$authorizationParams = array_merge( $authorizationParams, $urlParams );
			uksort( $authorizationParams, 'strcmp' );

			foreach ( $authorizationParams as $key => $value ) { // encode keys and params
				$authorizationParams[$key] = rawurlencode( $key ) . '=' . rawurlencode( $value );
			}

			$signatureBase = array( // signature base array
				rawurlencode( $method ), // encoded method
				rawurlencode( $endpoint ), // encoded endpoint
				rawurlencode( implode( '&', $authorizationParams ) ), // authorization params delimited by '&'
			);

			// create the signature base string delimited by '&'
			$signatureBaseString = implode( '&', $signatureBase );

			$signatureKey = array( // signature key
				rawurlencode( $this->_consumerSecret ), // encoded consumer secret
				$this->_tokenSecret ? rawurlencode( $this->_tokenSecret ) : '' // endocded access token if we have one
			);

			// create the signature key string delimited by '&'
			$signatureKeyString = implode( '&', $signatureKey );

			// return base64 encoded hmac as required by twitter
			return base64_encode( hash_hmac( 'sha1', $signatureBaseString, $signatureKeyString, true ) );
		}

        public function getAuthorizationString( $authorizationParams ) {
            $authorizationString = 'Authorization: OAuth';
            $count = 0;
            foreach ( $authorizationParams as $key => $value ) { 
                $authorizationString .= !$count ? ' ' : ', ';
                $authorizationString .= rawurlencode( $key ) . '="' . rawurlencode( $value ) . '"';
                $count++;
            }
            return $authorizationString;
        }

        public function getRequestToken($callBack){
            $method = 'POST';
            $endpoint = self::TWITTER_API_DOMAIN .'oauth/request_token';
    
            $authorizationParams = array( 
                'oauth_consumer_key' => $this->_consumerKey, 
                'oauth_nonce' => md5( microtime() . mt_rand() ), 
                'oauth_signature_method' => 'HMAC-SHA1', 
                'oauth_timestamp' => time(), 
                'oauth_version' => '1.0' 
            );
            $authorizationParams['oauth_signature'] = $this->getSignature( $method, $endpoint, $authorizationParams );
            
            $apiParams = array( 
                'method' => $method,
                'endpoint' => $endpoint, 
                'authorization' => $this->getAuthorizationString( $authorizationParams ), 
                'url_params' => array() 
            );
            
			
            $apiResponse = $this->makeApiCall( $apiParams );
            if($apiResponse['status'] == 'ok'){
                $responseParts = explode("\r\n\r\n",$apiResponse['api_data']);
                $responseBody = array_pop( $responseParts );
                parse_str( $responseBody, $response );
            }
            else{
                $response = $apiResponse;
            }
            return $response;
        }

        public function makeAuthorize($data){
            if($data['oauth_callback_confirmed']){
                $oauth_token = $data['oauth_token'];
                $oauth_token_secret = $data['oauth_token_secret'];
            }
            $method = 'GET';
            $endpoint = self::TWITTER_API_DOMAIN .'oauth/authorize';
            $apiParams = array( 
                'method' => $method,
                'endpoint' => $endpoint, 
                'auth' => '', 
                'url_params' => array(
                    'oauth_token' => $oauth_token
                ) 
            );

            $data = $this->makeApiCall( $apiParams );
            return $data['api_data'];
        }

        public function getAccessToken($oauth_token,$oauth_verifier){
            $method = 'POST';
            $endpoint = self::TWITTER_API_DOMAIN .'oauth/access_token' ;
            $apiParams = array( 
                'method' => $method,
                'endpoint' => $endpoint,
                'url_params' => array(
                    'oauth_verifier' =>$oauth_verifier,
                    'oauth_token' => $oauth_token,
                    ) 
                );
                $data = $this->makeApiCall($apiParams);
                parse_str( $data['api_data'], $tokens );
                return $tokens;
        }

        /* public function invalidateToken(){
            $method = 'POST';
            $endpoint = 'https://api.twitter.com/oauth/invalidate_token';
    
            $authorizationParams = array( 
                'oauth_consumer_key' => $this->_consumerKey, 
                'oauth_nonce' => md5( microtime() . mt_rand() ), 
                'oauth_signature_method' => 'HMAC-SHA1', 
                'oauth_timestamp' => time(), 
                'oauth_version' => '1.0' 
            );
            $authorizationParams['oauth_signature'] = $this->getSignature( $method, $endpoint, $authorizationParams );
            
            $apiParams = array( 
                'method' => $method,
                'endpoint' => $endpoint, 
                'authorization' => $this->getAuthorizationString( $authorizationParams ), 
                'url_params' => array() 
            );
            $data = $this->makeApiCall( $apiParams );
            echo "<pre>";
            print_r($data);
            die();
        } */



    }




?>