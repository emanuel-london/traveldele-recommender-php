<?php
/**
 * API.php
 */

namespace Kooyara\RecommenderSystem;

/**
 * Class API
 * @package Kooyara\RecommenderSystem
 */
class API
{

    /**
     * @var string
     */
    private $protocol;

    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $base_url;

    /**
     * @var string
     */
    private $token_url;

    /**
     * @var CurlClient
     */
    private $curl_client;

    /**
     * API constructor.
     * Configures the API object based on the specified environment name.
     *
     * @param string $env Environment name. One of: testing, development, production.
     * @param string $version API version.
     * @param string $client_id OAuth2 client_id.
     * @param string $client_secret OAuth2 client_secret.
     */
    function __construct(
        string $env,
        string $version,
        string $client_id,
        string $client_secret
    ) {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;

        // Set protocol, host and API version based on the environment name.
        switch ($env) {
            case 'development':
                $this->protocol = Config::developmentProtocol();
                $this->host = Config::developmentHost();
                $this->version = Config::developmentVersion();
                break;

            case 'testing':
                $this->protocol = Config::testingProtocol();
                $this->host = Config::testingHost();
                $this->version = Config::testingVersion();
                break;

            case 'production':
                $this->protocol = Config::productionProtocol();
                $this->host = Config::productionHost();
                $this->version = Config::productionVersion();
                break;

            default:
                return NULL;
                break;
        }

        $this->base_url = $this->protocol . '://' . $this->host . '/';
        $this->token_url = $this->base_url . Config::$access_token_url;
        $this->curl_client = new CurlClient();
    }

    /**
     * Get the data returned by the base URL of the API.
     *
     * @param string $auth OAuth2 access token.
     *
     * @return mixed
     */
    public function index(string $auth)
    {
        $url = $this->getAPIBase();
        $headers = array('Authorization: ' . $auth);
        $result = $this->curl_client->call('GET', $url, $headers);
        return json_decode($result);
    }

    /**
     * Get profile matches from the recommender system.
     *
     * @param string $auth OAuth2 access token.
     * @param string $profile Recommender system profile id.
     * @param array|NULL $data
     *      $data = [
     *          'sort_similarity'   =>  (integer) ascending = 1, descending = -1. Optional.
     *          'limit'             =>  (integer) max number of results. Optional.
     *      ]
     *
     * @return mixed $result
     *      $result = [
     *          'status'    =>  (integer) HTTP status. Always returned.
     *          'result'    =>  [ (array of stdClass) Profile matches. Returned on success.
     *              '_id'   ->  (string) Profile id of the match.
     *              'external_id'   ->  (string) Local id of the match.
     *              'similarity'    ->  (float) Similarity score of match [0, 1].
     *          ]
     *      ]
     */
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

    /**
     * Get all statements in the recommender system.
     *
     * @param string $auth OAuth2 access token.
     *
     * @return mixed
     */
    public function getStatements(string $auth)
    {
        $url = $this->getAPIBase() . '/statement';
        $headers = array('Authorization: ' . $auth);
        $result = $this->curl_client->call('GET', $url, $headers);
        return json_decode($result);
    }

    /**
     * Get a random statement from the set of statements not reacted to by the
     * user.
     *
     * @param string $auth OAuth2 access token.
     * @param string $profile Recommender system profile id.
     * @param array|NULL $data
     *      $data = [
     *          'tags' => (array) Tags to filter by. Optional.
     *      ]
     *
     * @return mixed $result
     *      $result = [
     *          'status'    =>  (integer) HTTP status. Always returned.
     *          'result'    =>  [ (stdClass) Statement without reaction. Returned on success.
     *              '_id'   -> (string) Recommender system id of statement.
     *              'statement' -> (string) The statement.
     *          ]
     *      ]
     */
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

    /**
     * Add a new reaction to the recommender system.
     *
     * @param string $auth OAuth2 access token.
     * @param array $reaction
     *      $reaction = [
     *          'profile'   =>  (string) Recommender system profile id. Required.
     *          'statement' =>  (string) Recommender system statement id. Required.
     *          'reaction'  =>  (integer) User reaction to statement [1,5]. Required.
     *      ]
     *
     * @return mixed $result
     *      $result = [
     *          'status'    =>  (integer) HTTP status. Always returned.
     *          'result'    =>  [ (stdClass) Post result. Returned on success.
     *              'inserted_id'   ->  (string) Recommender system reaction id.
     *          ]
     *      ]
     */
    public function postReaction(string $auth, array $reaction)
    {
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

    /**
     * Get all reactions in the recommender system.
     *
     * @param string $auth OAuth2 access token.
     * @param string $profile Recommender system profile id.
     *
     * @return mixed $result
     *      $result = [
     *          'status'    =>  (integer) HTTP status. Always returned.
     *          'result'    =>  [ (array of stdClass) Profile reactions. Returned on success.
     *              'statement' -> (string) Recommender system statement id.
     *              'reaction'  -> (integer) User reaction to statement.
     *          ]
     *      ]
     */
    public function getReactions(string $auth, string $profile)
    {
        $url = $this->getAPIBase() . '/reaction/profile/' . $profile;
        $headers = array('Authorization: ' . $auth);
        $result = $this->curl_client->call('GET', $url, $headers);
        return json_decode($result);
    }

    /**
     * Get a reaction from the recommender system.
     *
     * @param string $auth OAuth2 access token.
     * @param string $reaction Recommender system reaction id.
     *
     * @return mixed $result
     *      $result = [
     *          'status'    =>  (integer) HTTP status. Always returned.
     *          'result'    =>  [ (stdClass) A reaction. Returned on success.
     *              '_id'   ->  (string) Recommender system reaction id.
     *              'profile'   ->  (string) Recommender system profile id.
     *              'statement' ->  (string) Recommender system statement id.
     *              'reaction'  -> (integer) User reaction to statement.
     *          ]
     *      ]
     */
    public function getReaction(string $auth, string $reaction)
    {
        $url = $this->getAPIBase() . '/reaction/' . $reaction;
        $headers = array('Authorization: ' . $auth);
        $result = $this->curl_client->call('GET', $url, $headers);
        return json_decode($result);
    }

    /**
     * Update an existing reaction in the recommender system.
     *
     * @param string $auth OAuth2 access token.
     * @param string $reaction Recommender system reaction id.
     * @param array $data The fields to be updated together with the new values.
     *
     * @return mixed $result
     *      $result = [
     *          'status'    =>  (integer) HTTP status. Always returned.
     *          'result'    =>  [ (stdClass) Result of action. Returned on success.
     *              'updated_id'    ->  (string) Recommender system reaction id.
     *          ]
     *      ]
     */
    public function putReaction(string $auth, string $reaction, array $data)
    {
        $url = $this->getAPIBase() . '/reaction/' . $reaction;
        $headers = array(
            'Content-Type: application/json',
            'Authorization: ' . $auth
        );
        $result = $this->curl_client->call('PUT', $url, $headers, $data, TRUE);
        return json_decode($result);
    }

    /**
     * Delete a reaction from the recommender system.
     *
     * @param string $auth OAuth2 access token.
     * @param string $reaction Recommender system reaction id.
     *
     * @return mixed $result
     *      $result = [
     *          'status'    =>  (integer) HTTP status. Always returned.
     *          'result'    =>  [ (stdClass) Result of action. Returned on success.
     *              'deleted_id'    -> (string) Recommender system reaction id.
     *          ]
     *      ]
     */
    public function deleteReaction(string $auth, string $reaction)
    {
        $url = $this->getAPIBase() . '/reaction/' . $reaction;
        $headers = array('Authorization: ' . $auth);
        $result = $this->curl_client->call('DELETE', $url, $headers);
        return json_decode($result);
    }

    /**
     * Get all profiles from the recommender system.
     *
     * @param string $auth OAuth2 access token.
     *
     * @return mixed $result
     *      $result = [
     *          'status'    =>  (integer) HTTP status. Always returned.
     *          'result'    =>  [ (array of stdClass) All profiles. Returned on success.
     *              '_id'   ->  (string) Recommender system profile id.
     *              'external_id'   -> (string) Local id of the profile.
     *          ]
     *      ]
     */
    public function getProfiles(string $auth)
    {
        $url = $this->getAPIBase() . '/profile';
        $headers = array('Authorization: ' . $auth);
        $result = $this->curl_client->call('GET', $url, $headers);
        return json_decode($result);
    }

    /**
     * Get a specific profile from the recommender profile using the id that was
     * generated by the recommender system.
     *
     * @param string $auth OAuth2 access token.
     * @param string $profile Recommender system profile id.
     *
     * @return mixed $result
     *      $result = [
     *          'status'    =>  (integer) HTTP status. Always returned.
     *          'result'    =>  [ (stdClass) A profile. Returned on success.
     *              '_id'   ->  (string) Recommender system profile id.
     *              'external_id'   -> (string) Local id of the profile.
     *          ]
     *      ]
     */
    public function getProfile(string $auth, string $profile)
    {
        $url = $this->getAPIBase() . '/profile/' . $profile;
        $headers = array('Authorization: ' . $auth);
        $result = $this->curl_client->call('GET', $url, $headers);
        return json_decode($result);
    }

    /**
     * Add a profile to the recommender system.
     *
     * @param string $auth OAuth2 access token.
     * @param array $profile Profile data.
     *      $profile = [
     *          'external_id' => (string) Local id of the profile.
     *      ]
     *
     * @return mixed $result
     *      $result = [
     *          'status'    =>  (integer) HTTP status. Always returned.
     *          'result'    =>  [ (stdClass) Result of action. Returned on success.
     *              'inserted_id'   -> (string) Recommender system profile id.
     *          ]
     *      ]
     */
    public function postProfile(string $auth, array $profile)
    {
        $url = $this->getAPIBase() . '/profile';
        $headers = array(
            'Content-Type: application/json',
            'Authorization: ' . $auth
        );
        $result = $this->curl_client->call('POST', $url, $headers, $profile, TRUE);
        return json_decode($result);
    }

    /**
     * Update an existing profile in the recommender system.
     *
     * @param string $auth OAuth2 access token.
     * @param string $profile Recommender system profile id.
     * @param array $data The fields to be updated together with the new values.
     *
     * @return mixed $result
     *      $result = [
     *          'status'    =>  (integer) HTTP status. Always returned.
     *          'result'    =>  [ (array) Result of action. Returned on success.
     *              'updated_id'    ->  (string) Recommender system profile id.
     *          ]
     *      ]
     */
    public function putProfile(string $auth, string $profile, array $data)
    {
        $url = $this->getAPIBase() . '/profile/' . $profile;
        $headers = array(
            'Content-Type: application/json',
            'Authorization: ' . $auth
        );
        $result = $this->curl_client->call('PUT', $url, $headers, $data, TRUE);
        return json_decode($result);
    }

    /**
     * Delete a profile from the recommender system.
     *
     * @param string $auth OAuth2 access token.
     * @param string $profile Recommender system profile id.
     *
     * @return mixed $result
     *      $result = [
     *          'status'    =>  (integer) HTTP status. Always returned.
     *          'result'    =>  [ (array) Result of action. Returned on success.
     *              'deleted_id'    -> (string) Recommender system profile id.
     *          ]
     *      ]
     */
    public function deleteProfile(string $auth, string $profile)
    {
        $url = $this->getAPIBase() . '/profile/' . $profile;
        $headers = array('Authorization: ' . $auth);
        $result = $this->curl_client->call('DELETE', $url, $headers);
        return json_decode($result);
    }

    /**
     * Get the URL for obtaining OAuth2 access tokens.
     *
     * @return string
     */
    public function getTokenURL()
    {
        return $this->token_url;
    }

    /**
     * Get the base URL of the API.
     *
     * @return string
     */
    public function getAPIBase()
    {
        return $this->base_url . $this->version;
    }
}