<?php

namespace Fugikzl\MoodleWrapper;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request as Psr7Request;

class Request
{
    public function __construct(
        string $wsToken,
        string $wsFunction,
        private string $webservicesUrl,
        private array $params = [],
    ){
        $this->params['wsfunction'] = $wsFunction;
        $this->params['wstoken'] = $wsToken;
        $this->params['moodlewsrestformat'] = 'json';
    }

    public function send() : array
    {
        $client = new Client();    
        $res = $client->request("POST", $this->webservicesUrl, [
            'query' => $this->params,
        ]);

        $data = json_decode($res->getBody()->getContents(),1);

        if(array_key_exists("exception",$data)){
            throw new Exception($data["message"]);
        }else{
            return $data;
        }
    }    
}