<?php

declare(strict_types=1);

namespace Fugikzl\Moodle\Client;

use Exception;
use GuzzleHttp\Client;

class UploadFileRequest
{
    public function __construct(
        string $wsToken,
        private string $webservicesUrl,
        private array $body,
        private array $params = []
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

        $data = json_decode($res->getBody()->getContents(), true);

        if(array_key_exists("exception", $data)) {
            throw new Exception($data["message"]);
        }

        return $data;
    }
}
