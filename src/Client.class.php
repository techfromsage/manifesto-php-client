<?php

namespace Manifesto;

require_once dirname(__FILE__) . '/common.inc.php';

class Client {

    protected $clientId;
    protected $clientSecret;

    /**
     * @var \personaclient\PersonaClient
     */
    protected $personaClient;

    /**
     * @var array
     */
    protected $personaConnectValues = array();

    /**
     * @var string
     */
    protected $manifestoBaseUrl;

    /**
     * @var \Guzzle\Http\Client
     */
    protected $httpClient;
    /**
     * @param string $manifestoBaseUrl
     * @param array $personaConnectValues
     */
    public function __construct($manifestoBaseUrl, $personaConnectValues = array())
    {
        $this->manifestoBaseUrl = $manifestoBaseUrl;
        $this->personaConnectValues = $personaConnectValues;
    }

    /**
     * @param array $personaConnectValues
     */
    public function setPersonaConnectValues($personaConnectValues)
    {
        $this->personaConnectValues = $personaConnectValues;
    }

    /**
     * @return string
     */
    public function getManifestoBaseUrl()
    {
        return $this->manifestoBaseUrl;
    }

    /**
     * @param string $manifestoBaseUrl
     */
    public function setManifestoBaseUrl($manifestoBaseUrl)
    {
        $this->manifestoBaseUrl = $manifestoBaseUrl;
    }

    /**
     * @return \personaclient\PersonaClient
     */
    protected function getPersonaClient()
    {
        if(!isset($this->personaClient))
        {
            $this->personaClient = new \personaclient\PersonaClient($this->personaConnectValues);
        }
        return $this->personaClient;
    }

    /**
     * @param \personaclient\PersonaClient $personaClient
     */
    public function setPersonaClient(\personaclient\PersonaClient $personaClient)
    {
        $this->personaClient = $personaClient;
    }

    /**
     * @return \Guzzle\Http\Client
     */
    protected function getHTTPClient()
    {
        if(!$this->httpClient)
        {
            $this->httpClient = new \Guzzle\Http\Client();
        }
        return $this->httpClient;
    }

    /**
     * @param Manifest $manifest
     * @param string $clientId
     * @param string $clientSecret
     */
    public function requestArchive(Manifest $manifest, $clientId, $clientSecret)
    {
        $archiveLocation = $this->manifestoBaseUrl . '/1/archives';
        $manifestDocument = json_encode($manifest->generateManifest());

//        try
//        {
            $client = $this->getHTTPClient();
            $headers = $this->getHeaders($clientId, $clientSecret);
            $headers['Content-Type'] = 'application/json';
            $request = $client->post($archiveLocation, $headers, $manifestDocument);

            $response = $request->send();
return $response;
//            switch($response->getStatusCode())
//            {
//                case 202:
//
//            }

//        }
//        catch(\Exception $e)
//        {
//
//        }
    }

    /**
     * Setup the header array for any request to Manifesto
     * @param string clientId
     * @param string $clientSecret
     * @return array
     */
    protected function getHeaders($clientId, $clientSecret)
    {
        $arrPersonaToken = $this->getPersonaClient()->obtainNewToken($clientId, $clientSecret);
        $personaToken = $arrPersonaToken['access_token'];
        $headers = array(
            'Content-Type'=>'application/json',
            'Authorization'=>'Bearer '.$personaToken
        );
        return $headers;
    }
}