<?php
/**
 * RecommenderSystem.php
 */

namespace Kooyara\RecommenderSystem;

/**
 * Class RecommenderSystem
 * @package Kooyara\RecommenderSystem
 */
class RecommenderSystem
{

    /**
     * All API calls are routed through this object.
     *
     * @var API
     */
    private $api;

    /**
     * All OAuth2 authentication calls are routed through this object.
     *
     * @var OAuth2Client
     */
    private $oauth2Client;

    /**
     * Token data retrieved from the OAuth2 server. The expiry time is appended
     * by the OAuth2 client and is calculated by adding the expires_in attribute
     * to the current system time.
     *
     * @var Token
     */
    private $token;

    /**
     * RecommenderSystem constructor.
     * @param string $env
     * @param string $client_id
     * @param string $client_secret
     */
    function __construct(string $env, string $client_id, string $client_secret)
    {
        // Get the API version based on the environment name.
        $func = __NAMESPACE__ . '\Config::' . $env . 'Version';
        $apiVersion = call_user_func($func);

        $this->api = new API($env, $apiVersion, $client_id, $client_secret);
        $this->oauth2Client = new OAuth2Client($client_id, $client_secret);
    }

    /**
     * Get profile matches from the recommender system.
     *
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
    public function getMatches(string $profile, array $data = NULL)
    {
        if (! $this->isTokenValid())
            $this->refreshToken();

        $result = $this->api->getMatches($this->getAuthString(), $profile, $data);
        return $result;
    }

    /**
     * Get all statements in the recommender system.
     *
     * @return mixed $result
     *      $result = [
     *          'status'    =>  (integer) HTTP status. Always returned.
     *          'result'    =>  [ (array of stdClass) All statements. Returned on success.
     *              '_id'   -> (string) Recommender system id of statement.
     *              'statement' -> (string) The statement.
     *          ]
     *      ]
     */
    public function getStatements()
    {
        if (! $this->isTokenValid())
            $this->refreshToken();

        $result = $this->api->getStatements($this->getAuthString());
        return $result;
    }

    /**
     * Get a random statement from the set of statements not reacted to by the
     * user.
     *
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
    public function getInactionStatement(string $profile, array $data = NULL)
    {
        if (! $this->isTokenValid())
            $this->refreshToken();

        $result = $this->api->getInactionStatement($this->getAuthString(), $profile, $data);
        return $result;
    }

    /**
     * Add a new reaction to the recommender system.
     *
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
    public function postReaction(array $reaction)
    {
        if (! $this->isTokenValid())
            $this->refreshToken();

        $result = $this->api->postReaction($this->getAuthString(), $reaction);
        return $result;
    }

    /**
     * Get all reactions in the recommender system.
     *
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
    public function getReactions(string $profile)
    {
        if (! $this->isTokenValid())
            $this->refreshToken();

        $result = $this->api->getReactions($this->getAuthString(), $profile);
        return $result;
    }

    /**
     * Get a reaction from the recommender system.
     *
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
    public function getReaction(string $reaction)
    {
        if (! $this->isTokenValid())
            $this->refreshToken();

        $result = $this->api->getReaction($this->getAuthString(), $reaction);
        return $result;
    }

    /**
     * Update an existing reaction in the recommender system.
     *
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
    public function putReaction(string $reaction, array $data)
    {
        if (! $this->isTokenValid())
            $this->refreshToken();

        $result = $this->api->putReaction($this->getAuthString(), $reaction, $data);
        return $result;
    }

    /**
     * Delete a reaction from the recommender system.
     *
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
    public function deleteReaction(string $reaction)
    {
        if (! $this->isTokenValid())
            $this->refreshToken();

        $result = $this->api->deleteReaction($this->getAuthString(), $reaction);
        return $result;
    }

    /**
     * Get all profiles from the recommender system.
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
    public function getProfiles()
    {
        if (! $this->isTokenValid())
            $this->refreshToken();

        $result = $this->api->getProfiles($this->getAuthString());
        return $result;
    }

    /**
     * Get a specific profile from the recommender profile using the id that was
     * generated by the recommender system.
     *
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
    public function getProfile(string $profile)
    {
        if (! $this->isTokenValid())
            $this->refreshToken();

        $result = $this->api->getProfile($this->getAuthString(), $profile);
        return $result;
    }

    /**
     * Add a profile to the recommender system.
     *
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
    public function postProfile(array $profile)
    {
        if (! $this->isTokenValid())
            $this->refreshToken();

        $result = $this->api->postProfile($this->getAuthString(), $profile);
        return $result;
    }

    /**
     * Update an existing profile in the recommender system.
     *
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
    public function putProfile(string $profile, array $data)
    {
        if (! $this->isTokenValid())
            $this->refreshToken();

        $result = $this->api->putProfile($this->getAuthString(), $profile, $data);
        return $result;
    }

    /**
     * Delete a profile from the recommender system.
     *
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
    public function deleteProfile(string $profile)
    {
        if (! $this->isTokenValid())
            $this->refreshToken();

        $result = $this->api->deleteProfile($this->getAuthString(), $profile);
        return $result;
    }

    /**
     * Get the authorization header value that is to be used when making calls
     * to the recommender system API.
     *
     * @return string Format: '{token_type} {access_token}'
     */
    private function getAuthString()
    {
        return $this->token->getTokenType() .' ' . $this->token->getAccessToken();
    }

    /**
     * Check whether the last fetched token is still valid based on the expiry
     * time.
     *
     * @return bool
     */
    private function isTokenValid()
    {
        if (! $this->token)
            return FALSE;

        return time() < ($this->token->getExpiresAt() - 1);
    }

    /**
     * Refreshes the OAuth2 token by making a request to the access token URL.
     *
     * @return void
     */
    private function refreshToken()
    {
        $this->token = $this->oauth2Client->fetchToken(
            $this->api->getTokenURL(),
            Config::$grant_type
        );
    }
}