<?php
/**
 * OAuth2Client.php
 */

namespace Kooyara\RecommenderSystem;

/**
 * Class OAuth2Client
 * @package Kooyara\RecommenderSystem
 */
class OAuth2Client
{
    /**
     * @var string
     */
    private $client_id;

    /**
     * @var string
     */
    private $client_secret;

    /**
     * @var CurlClient
     */
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
     * Fetch an access token from the OAuth2 server.
     *
     * @param string $tokenURL
     * @param string $grantType
     *
     * @return Token
     */
    public function fetchToken(string $tokenURL, string $grantType)
    {
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