<?php

namespace core\lib\cloud;


use core\lib\cloud\http\HasHttpRequests;
use GuzzleHttp\Client;

/**
 * cloud云服务类
 * Class CloudService
 * @package core\lib\cloud
 */
class CloudService
{
    use HasHttpRequests;

    private $baseUri = 'http://127.0.0.1:8000/';

    public function httpPost(string $url, array $options = []) {
        return $this->toRequest($url, 'POST', $options);
    }

    public function httpGet(string $url, array $options = []) {
        return $this->toRequest($url, 'GET', $options);
    }

    public function request(string $method, string $url, array $options = []) {
        return (new Client(['base_uri' => $this->baseUri ]))->request($method, $url, $options);
    }

    public function getUrl(string $url) {
        return $this->baseUri . $url;
    }
}
