<?php 
/**
 * Akamai {OPEN} API Client Authentication Protocol
 * EdgeGrid version 1 
 * HTTP Authorization Header generator
 * https://developer.akamai.com/stuff/Getting_Started_with_OPEN_APIs/Client_Auth.html
 * PHP version of https://github.com/akamai-open
 * 
 * @author Julien Malbert <julien.malbert@gmail.com>
 */
class EdgeGridV1Signer
{
    private $credentials;               // ApiCredentials object

    private $method;                    // method (GET, POST, ...)
    private $protocol;                  // protocol (http, https, ...)
    private $serviceConsumerDomain;     // api domain
    private $endPoint;                  // api method to call
    private $postbody;                  // raw body (only for post method)
    
    private $customHeaders = [];        // cutom headers
    private $timestamp;                 // timestamp yyyyMMddTHH:mm:ss+0000

    public function __construct(ApiCredentials $credentials)
    {	
        $this->credentials = $credentials;		
    }

    public function setCredentials(ApiCredentials $credentials)
    {	
        $this->credentials = $credentials;
    }    
    
    public function addCustomHeaders(array $headers)
    {
        //$headers=['Host'=>'exemple.com','headerName'=>'HeaderValue'];      
        $this->customHeaders = $headers;
    }
    
    public function getAuthorizationHeader($method,$protocol,$serviceConsumerDomain,$endPoint,$postbody=NULL)
    {	
        $this->method=strtoupper($method);
        $this->protocol=strtolower($protocol);
        $this->serviceConsumerDomain=$serviceConsumerDomain;
        $this->endPoint=$endPoint;			
        $this->postbody=$postbody;	

        // create timestamp
        $this->timestamp = date('Ymd\TH:i:s+0100');  

        $authorizationHeader = 'EG1-HMAC-SHA256 client_token='.
                $this->credentials->client_token.
                ';access_token='.$this->credentials->access_token.
                ';timestamp='.$this->timestamp.
                ';nonce='.md5($this->timestamp).';';

        $authorizationHeader .= 'signature='.
                $this->getEdgeGridV1Signature($authorizationHeader);
        
        return $authorizationHeader;	

    }

    private function getEdgeGridV1Signature($authorizationHeader)
    {
        //data to sign include the information in the request that are deemed relevant. 
        //It includes the Request Data concatenated with the Authorization header value 
        //(excluding the signature field, but including the ‘;’ right before the signature field).
        $data_to_sign = $this->method."\t".
                $this->protocol."\t".
                $this->serviceConsumerDomain."\t".
                $this->endPoint."\t".
                $this->getCanonicalizedRequestHeaders()."\t".
                $this->getContentHash()."\t".
                $authorizationHeader;

        // The Signing Key is computed as the base64 encoding of the SHA–256 HMAC 
        // of the timestamp string with the Client Secret as the key.
        $sigining_key = base64_encode( hash_hmac(
                'sha256' , $this->timestamp , 
                $this->credentials->secret , true ));
        
         //The Signature is the base64-encoding of the SHA–256 HMAC 
        //of the Data to Sign with the Signing Key.
        return base64_encode( hash_hmac(
                'sha256' , $data_to_sign , 
                $sigining_key , true ));	       
        
    }    

    private function getContentHash()
    {
        //Content Hash of the request body for POST requests
        if('POST'==$this->method){
            return base64_encode(hash('sha256',$this->postbody , true ));
        }else{
            return '';
        }          
    }

    private function getCanonicalizedRequestHeaders()
    {
        // create the canonicalized query
        $canonicalized_query = '';
        foreach ($this->customHeaders as $param=>$value){
            $param = $this->normalize(strtolower($param));
            $value  = $this->normalize($value);
            $canonicalized_query = $param.":".$value."\t";
        }
        return $canonicalized_query;
    }

    private function normalize($in)
    {
        // trim + remplacer multiples espaces par un seul + delete tabs
        return preg_replace("#( +)#"," ",str_replace(array("\t", "\r", "\n"), " ", trim($in))); 
    }

}
