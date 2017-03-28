<?php

namespace Kooyara\RecommenderSystem;

class API
{

    private $protocol;
    private $host;
    private $version;
    private $base_url;
    private $token_url;

    private $curl_client;

    /**
     * API constructor.
     * Configures the API object based on the specified environment name.
     *
     * @param string $env
     * @param string $version
     * @param string $client_id
     * @param string $client_secret
     */
    function __construct(
        string $env,
        string $version,
        string $client_id,
        string $client_secret
    ) {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;

        switch ($env) {
            case 'development':
                $this->protocol = Config::development_protocol;
                $this->host = Config::development_host;
                $this->version = Config::development_version;
                break;

            case 'testing':
                $this->protocol = Config::test_protocol;
                $this->host = Config::test_host;
                $this->version = Config::test_version;
                break;

            case 'production':
                $this->protocol = Config::production_protocol;
                $this->host = Config::production_host;
                $this->version = Config::production_version;
                break;

            default:
                return NULL;
                break;
        }

        $this->base_url = sprintf(
            "%s://%s/",
            $this->protocol,
            $this->host
        );

        $this->token_url = $this->base_url . Config::access_token_url;

        $this->curl_client = new CurlClient();
    }

    public function index(string $auth) {
        $url = $this->getAPIBase();
        $headers = array('Authorization: ' . $auth);
        $result = $this->curl_client->call('GET', $url, $headers);
        return json_decode($result);
    }

    public function getMatches(
        string $auth,
        string $profile,
        array $data = NULL
    ) {
        $url = $this->getAPIBase() . '/matches/' . $profile;
        $method = 'GET';
        $headers = array('Authorization: ' . $auth);
        $encode = FALSE;
        if ($data) {
            $method = 'POST';
            $headers[] = 'Content-Type: application/json';
            $encode = TRUE;
        }
        $result = $this->curl_client->call(
            $method,
            $url,
            $headers,
            $data,
            $encode
        );
        return json_decode($result);
    }

    public function getStatements(string $auth) {
        $url = $this->getAPIBase() . '/statement';
        $headers = array('Authorization: ' . $auth);
        $result = $this->curl_client->call('GET', $url, $headers);
        return json_decode($result);
    }

    public function getInactionStatement(
        string $auth,
        string $profile,
        array $data = NULL
    ) {
        $url = $this->getAPIBase() . '/statement/inaction/' . $profile;
        $method = 'GET';
        $headers = array('Authorization: ' . $auth);
        $encode = FALSE;
        if ($data) {
            $method = 'POST';
            $headers[] = 'Content-Type: application/json';
            $encode = TRUE;
        }
        $result = $this->curl_client->call(
            $method,
            $url,
            $headers,
            $data,
            $encode
        );
        return json_decode($result);
    }

    public function postReaction(string $auth, array $reaction) {
        $url = $this->getAPIBase() . '/reaction';
        $headers = array(
            'Authorization: ' . $auth,
            'Content-Type: application/json'
        );
        $result = $this->curl_client->call(
            'POST',
            $url,
            $headers,
            $reaction,
            TRUE
        );
        return json_decode($result);
    }

    public function getReactions(string $auth, string $profile) {
        $url = $this->getAPIBase() . '/reaction/profile/' . $profile;
        $headers = array('Authorization: ' . $auth);
        $result = $this->curl_client->call('GET', $url, $headers);
        return json_decode($result);
    }

    public function getReaction(string $auth, string $reaction) {
        $url = $this->getAPIBase() . '/reaction/' . $reaction;
        $headers = array('Authorization: ' . $auth);
        $result = $this->curl_client->call('GET', $url, $headers);
        return json_decode($result);
    }

    public function putReaction(string $auth, string $reaction, array $data) {
        $url = $this->getAPIBase() . '/reaction/' . $reaction;
        $headers = array(
            'Content-Type: application/json',
            'Authorization: ' . $auth
        );
        $result = $this->curl_client->call('PUT', $url, $headers, $data, TRUE);
        return json_decode($result);
    }

    public function deleteReaction(string $auth, string $reaction) {
        $url = $this->getAPIBase() . '/reaction/' . $reaction;
        $headers = array('Authorization: ' . $auth);
        $result = $this->curl_client->call('DELETE', $url, $headers);
        return json_decode($result);
    }

    public function getProfiles(string $auth) {
        $url = $this->getAPIBase() . '/profile';
        $headers = array('Authorization: ' . $auth);
        $result = $this->curl_client->call('GET', $url, $headers);
        return json_decode($result);
    }

    public function getProfile(string $auth, string $profile) {
        $url = $this->getAPIBase() . '/profile/' . $profile;
        $headers = array('Authorization: ' . $auth);
        $result = $this->curl_client->call('GET', $url, $headers);
        return json_decode($result);
    }

    public function postProfile(string $auth, array $profile) {
        $url = $this->getAPIBase() . '/profile';
        $headers = array(
            'Content-Type: application/json',
            'Authorization: ' . $auth
        );
        $result = $this->curl_client->call('POST', $url, $headers, $profile, TRUE);
        return json_decode($result);
    }

    public function putProfile(string $auth, string $profile, array $data) {
        $url = $this->getAPIBase() . '/profile/' . $profile;
        $headers = array(
            'Content-Type: application/json',
            'Authorization: ' . $auth
        );
        $result = $this->curl_client->call('PUT', $url, $headers, $data, TRUE);
        return json_decode($result);
    }

    public function deleteProfile(string $auth, string $profile) {
        $url = $this->getAPIBase() . '/profile/' . $profile;
        $headers = array('Authorization: ' . $auth);
        $result = $this->curl_client->call('DELETE', $url, $headers);
        return json_decode($result);
    }

    /**
     * @return string
     */
    public function getTokenURL() {
        return $this->token_url;
    }

    /**
     * @return string
     */
    public function getAPIBase() {
        return $this->base_url . $this->version;
    }
}