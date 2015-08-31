<?php

namespace Nticaric\Majestic;

use GuzzleHttp\Client;

class MajesticAPIService
{

    private $endpoint;
    private $apiKey;
    private $client;
    private $configurations = [];

    public function __construct($apiKey, $domain)
    {
        $this->setupEndpoint($domain);

        $this->responseType = "json";
        $this->apiKey = $apiKey;
        $this->client = new Client;
    }

    protected function setUpEndpoint($domain)
    {
        $this->endpoint = sprintf("http://%s/api/", $domain);
    }

    public function setResponseType($type)
    {
        $this->responseType = $type;
    }

    public function executeCommand($command, $params = array())
    {

        $params["cmd"]         = $command;
        $params["app_api_key"] = $this->apiKey;

        if (isset($this->configs[$command])) {
            $params = array_merge($this->configs[$command], $params);
        }

        return $this->client->get($this->endpoint . $this->responseType, [
            'query' => $params
        ]);
    }

    public function configure($fnName, array $params)
    {
        $fnName = ucfirst($fnName);
        $this->configs[$fnName] = $params;
    }

    public function __call($name, array $arguments)
    {
        $command = ucfirst($name);

        if(isset($arguments[1])) {
            $params  = $arguments[1];
        } else {
            $params = array();
        }

        if(is_string($arguments[0])) {
            $params['item'] = $arguments[0];
        } elseif(is_array($arguments[0])) {
            $counter = 0;
            foreach ($arguments[0] as $url) {
                $params['item' . $counter] = $url;
                $counter++;
            }
            $params['items'] = $counter;
        }

        return $this->executeCommand($command, $params);
    }
}
