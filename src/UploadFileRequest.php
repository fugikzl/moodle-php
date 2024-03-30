<?php

namespace Fugikzl\MoodleWrapper;

use Exception;
use GuzzleHttp\Client;

class UploadFileRequest
{
    public function __construct(
        string $wsToken,
        private string $webservicesUrl,
        private array $params = [],
        private array $body
    ) {
        $this->params['token'] = $wsToken;
        $this->params['moodlewsrestformat'] = 'json';
    }

    public function send(): array
    {
        $client = new Client();
        $res = $client->request("POST", $this->webservicesUrl, [
            'query' => $this->params,
            "multipart" => $this->body
        ]);

        $data = json_decode($res->getBody()->getContents(), 1);

        if(array_key_exists("exception", $data)) {
            throw new Exception($data["message"]);
        } else {
            return $data;
        }
    }
}
