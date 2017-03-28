<?php

namespace Kooyara\RecommenderSystem;

class OAuth2Client
{
    private $client_id;
    private $client_secret;

    private $curl_client;

    /**
     * OAuth2Client constructor.
     * @param string $client_id
     * @param string $client_secret
     */
    function __construct(string $client_id, string $client_secret)
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->curl_client = new CurlClient();
    }

    /**
     * @param string $tokenURL
     * @param string $grantType
     * @return mixed
     */
    public function fetchToken(string $tokenURL, string $grantType) {
        $data = array(
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => $grantType
        );
        $result = $this->curl_client->call('POST', $tokenURL, null, $data);

        if (isset($result->error)) {
            return json_decode($result);
        }

        $tokenData = json_decode($result);
        return new Token($tokenData);
    }
}