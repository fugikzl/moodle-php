<?php

declare(strict_types=1);

namespace Fugikzl\Moodle\Client;

use Exception;
use GuzzleHttp\Client;

class Request
{
    public function __construct(
        string $wsToken,
        string $wsFunction,
        private string $webservicesUrl,
        private array $params = [],
    ) {
        $this->params['wsfunction'] = $wsFunction;
        $this->params['wstoken'] = $wsToken;
        $this->params['moodlewsrestformat'] = 'json';
    }

    public function send(): array
    {
        $client = new Client();
        $res = $client->request("POST", $this->webservicesUrl, [
            'query' => $this->params,
        ]);

        $data = json_decode($res->getBody()->getContents(), true);

        if(array_key_exists("exception", $data)) {
            throw new Exception($data["message"]);
        }

        return $data;
    }
}
