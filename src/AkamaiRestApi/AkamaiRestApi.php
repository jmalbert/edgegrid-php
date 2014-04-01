<?php 
/**
 * Abstraction class to invoke Akamai {OPEN} API
 * 
 * @author Julien Malbert <julien.malbert@gmail.com>
 */
require(__DIR__ . '/ApiCredentials.php');
require(__DIR__ . '/EdgeGridV1Signer.php');
require(__DIR__ . '/conf.php');
require(__DIR__ . '/httpful.phar');

class AkamaiRestApi
{
    private $protocol = 'https';
    private $signer;
    private $serviceConsumerDomain;
    
    public function __construct()
    {
        $this->signer = new EdgeGridV1Signer(
                new ApiCredentials(
                        $GLOBALS['AKAMAI_REST_API_CLIENT']['CLIENT_TOKEN'],
                        $GLOBALS['AKAMAI_REST_API_CLIENT']['ACCESS_TOKEN'],
                        $GLOBALS['AKAMAI_REST_API_CLIENT']['SECRET']));
        $this->serviceConsumerDomain = 
                $GLOBALS['AKAMAI_REST_API_CLIENT']['SERVICE_CONSUMER_DOMAIN'];
    }
    
    public function call($method,$endPoint,$params=NULL)
    {
        $payload = $this->convertParams($params);
        
        $authHeader = $this->signer->getAuthorizationHeader(
                $method,
                $this->protocol,
                $this->serviceConsumerDomain,
                $endPoint,
                $payload);
        
        $response = \Httpful\Request::$method(
                $this->protocol.'://'.
                $this->serviceConsumerDomain . 
                $endPoint)
                ->addHeader('Host', $this->serviceConsumerDomain) 
                ->addHeader('Authorization',$authHeader) 
                ->body($payload)
                ->sendsForm() 
                ->send();        
        
        return $response;
   
    }
    
    public function setProtocol($p)
    {
        $this->protocol = $p;
    }
    
    
    private function convertParams($params)
    {
        
        if(isset($params) && is_array($params)){
            $payload = '';
            $first = true;
            foreach($params as $key=>$value){
                $payload .= ($first?'':'&').
                        $key.'='.
                        urlencode(json_encode($value));
                $first = false;
            }
            return $payload;
        }else{
            return NULL;
        }
    }
}

