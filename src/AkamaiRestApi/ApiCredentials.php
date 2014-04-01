<?php 
/**
 * Class storing credentials
 * 
 * @author Julien Malbert <julien.malbert@gmail.com>
 */
class ApiCredentials
{
    public $client_token;
    public $access_token;
    public $secret;

    public function __construct($client_token,$access_token,$secret)
    {
        $this->client_token = $client_token;
        $this->access_token = $access_token;
        $this->secret = $secret; 
    }	
}
