<?php

use PHPUnit\Framework\TestCase;
use Kooyara\RecommenderSystem\API;
use Kooyara\RecommenderSystem\Config;
use Kooyara\RecommenderSystem\OAuth2Client;

class APITest extends TestCase {

    protected $oauth_client;
    protected $api;
    protected $token;
    protected $auth_string;

    protected $posted_profiles = array();

    protected function setUp()
    {
        $this->oauth_client = new OAuth2Client(
            Config::test_client_id,
            Config::test_client_secret
        );

        $this->api= new API(
            'testing',
            '1.0',
            Config::test_client_id,
            Config::test_client_secret
        );

        $this->token = $this->oauth_client->fetchToken(
            $this->api->getTokenURL(),
            Config::grant_type
        );

        $this->auth_string = $this->token->getTokenType() .
            ' ' . $this->token->getAccessToken();
    }


    public  function testInit() {
        $api = new API(
            'testing',
            '1.0',
            Config::test_client_id,
            Config::test_client_secret
        );

        $baseURL = sprintf(
            "%s://%s/",
            Config::test_protocol,
            Config::test_host
        );

        $tokenURL = $baseURL . Config::access_token_url;

        $this->assertEquals($api->getTokenURL(), $tokenURL);
    }

    public function testAPIBase() {
        $result = $this->api->index($this->auth_string);

        // Result MUST be an object.
        $this->assertInternalType('object', $result);

        // Result MUST have the status attribute and the status MUST be 200.
        $this->assertObjectHasAttribute('status', $result);
        $this->assertEquals(200, $result->status);

        // Result MUST have the message attribute and the message must specify
        // the API version.
        $this->assertObjectHasAttribute('message', $result);
        $expectedMsg = 'API version ' . Config::test_version;
        $this->assertEquals($expectedMsg, $result->message);
    }

    public function testPostProfile() {
        $profile = array('external_id' => uniqid());
        $result = $this->api->postProfile($this->auth_string, $profile);

        // Result MUST be an object.
        $this->assertInternalType('object', $result);

        // Result MUST have the status attribute and the status MUST be 200.
        $this->assertObjectHasAttribute('status', $result);
        $this->assertEquals(200, $result->status);

        // Result MUST have the result attribute which contains the id assigned
        // to the profile by the recommender system.
        $this->assertObjectHasAttribute('result', $result);
        $this->assertObjectHasAttribute('inserted_id', $result->result);

        return $result->result->inserted_id;
    }

    /**
     * @depends testPostProfile
     * @return mixed
     */
    public function testGetProfiles() {
        $result = $this->api->getProfiles($this->auth_string);

        // Result MUST be an object.
        $this->assertInternalType('object', $result);

        // Result MUST have the status attribute and the status MUST be 200.
        $this->assertObjectHasAttribute('status', $result);
        $this->assertEquals(200, $result->status);

        // Result MUST have the result attribute which contains an array of
        // profiles returned by the recommender system.
        $this->assertObjectHasAttribute('result', $result);
        $this->assertInternalType('array', $result->result);

        // An arbitrary element of the array of profiles should have the _id
        // and external_id attributes.
        $el = rand(0, count($result->result) - 1);
        $this->assertObjectHasAttribute('_id', $result->result[$el]);
        $this->assertObjectHasAttribute('external_id', $result->result[$el]);

        return $result->result[0]->_id;
    }

    /**
     * @depends testGetProfiles
     */
    public function testGetProfile() {
        $profile = func_get_args()[0];
        $result = $this->api->getProfile($this->auth_string, $profile);

        // Result MUST be an object.
        $this->assertInternalType('object', $result);

        // Result MUST have the status attribute and the status MUST be 200.
        $this->assertObjectHasAttribute('status', $result);
        $this->assertEquals(200, $result->status);

        // Result MUST have the result attribute which contains the id assigned
        // to the profile by the recommender system and the local (external) id.
        $this->assertObjectHasAttribute('result', $result);
        $this->assertObjectHasAttribute('_id', $result->result);
        $this->assertObjectHasAttribute('external_id', $result->result);

        return $profile;
    }

    /**
     * @depends testGetProfile
     */
    public function testPutProfile() {
        $profile = func_get_args()[0];
        $data = array(
            'name' => uniqid() . ' ' . uniqid()
        );
        $result = $this->api->putProfile($this->auth_string, $profile, $data);

        // Result MUST be an object.
        $this->assertInternalType('object', $result);

        // Result MUST have the status attribute and the status MUST be 200.
        $this->assertObjectHasAttribute('status', $result);
        $this->assertEquals(200, $result->status);

        // Result MUST have the result attribute which contains the id of the
        // updated profile.
        $this->assertObjectHasAttribute('result', $result);
        $this->assertObjectHasAttribute('updated_id', $result->result);

        return $profile;
    }

    /**
     * @depends testPutProfile
     */
    public function testDeleteProfile() {
        $profile = func_get_args()[0];
        $result = $this->api->deleteProfile($this->auth_string, $profile);

        // Result MUST be an object.
        $this->assertInternalType('object', $result);

        // Result MUST have the status attribute and the status MUST be 200.
        $this->assertObjectHasAttribute('status', $result);
        $this->assertEquals(200, $result->status);

        // Result MUST have the result attribute which contains the id of the
        // deleted profile.
        $this->assertObjectHasAttribute('result', $result);
        $this->assertObjectHasAttribute('deleted_id', $result->result);
    }

    /**
     * @depends testDeleteProfile
     */
    public function testGetStatements() {
        $result = $this->api->getStatements($this->auth_string);

        // Result MUST be an object.
        $this->assertInternalType('object', $result);

        // Result MUST have the status attribute and the status MUST be 200.
        $this->assertObjectHasAttribute('status', $result);
        $this->assertEquals(200, $result->status);

        // Result MUST have the result attribute which contains an array of
        // statements returned by the recommender system.
        $this->assertObjectHasAttribute('result', $result);
        $this->assertInternalType('array', $result->result);

        // An arbitrary element of the array of statements should have the _id
        // and statement attributes.
        $el = rand(0, count($result->result) - 1);
        $this->assertObjectHasAttribute('_id', $result->result[$el]);
        $this->assertObjectHasAttribute('statement', $result->result[$el]);
    }

    /**
     * @depends testGetStatements
     */
    public function testGetInactionStatement() {
        // Create a profile and push it to the recommender system.
        $profileData = array('external_id' => uniqid());
        $profile = $this->api->postProfile(
            $this->auth_string,
            $profileData
        )->result->inserted_id;

        // Get an arbitrary statement for which the profile has no recorded
        // reaction.
        $result = $this->api->getInactionStatement(
            $this->auth_string,
            $profile
        );

        // Result MUST be an object.
        $this->assertInternalType('object', $result);

        // Result MUST have the status attribute and the status MUST be 200.
        $this->assertObjectHasAttribute('status', $result);
        $this->assertEquals(200, $result->status);

        // Result MUST have the result attribute which contains the statement
        // returned by the recommender system, together with its id.
        $this->assertObjectHasAttribute('result', $result);
        $this->assertObjectHasAttribute('_id', $result->result);
        $this->assertObjectHasAttribute('statement', $result->result);

        return $profile;
    }

    /**
     * @depends testGetInactionStatement
     */
    public function testGetInactionStatementFiltered() {
        $profile = func_get_args()[0];

        // Get an arbitrary statement for which the profile has no recorded
        // reaction, filtered by a tag value.
        $filter = array('tags' => array('test'));
        $result = $this->api->getInactionStatement(
            $this->auth_string,
            $profile,
            $filter
        );

        // Result MUST be an object.
        $this->assertInternalType('object', $result);

        // Result MUST have the status attribute and the status MUST be 200.
        $this->assertObjectHasAttribute('status', $result);
        $this->assertEquals(200, $result->status);

        // Result MUST have the result attribute which contains the statement
        // returned by the recommender system, together with its id.
        $this->assertObjectHasAttribute('result', $result);
        $this->assertObjectHasAttribute('_id', $result->result);
        $this->assertObjectHasAttribute('statement', $result->result);

        // Delete the test profile that was created.
        $this->api->deleteProfile($this->auth_string, $profile);
    }

    public function testPostReaction() {
        // Create a profile and push it to the recommender system.
        $profileData = array('external_id' => uniqid());
        $profile = $this->api->postProfile(
            $this->auth_string,
            $profileData
        )->result->inserted_id;

        // Get an arbitrary statement for which the profile has no recorded
        // reaction.
        $statement = $this->api->getInactionStatement(
            $this->auth_string,
            $profile
        )->result->_id;

        // Post a random reaction (on a scale of 1 to 5) for the retrieved
        // statement.
        $reaction = array(
            'profile' => $profile,
            'statement' => $statement,
            'reaction' => rand(1, 5)
        );
        $result = $this->api->postReaction($this->auth_string, $reaction);

        // Result MUST be an object.
        $this->assertInternalType('object', $result);

        // Result MUST have the status attribute and the status MUST be 200.
        $this->assertObjectHasAttribute('status', $result);
        $this->assertEquals(200, $result->status);

        // Result MUST have the result attribute which contains the id assigned
        // to the reaction by the recommender system.
        $this->assertObjectHasAttribute('result', $result);
        $this->assertObjectHasAttribute('inserted_id', $result->result);

        return array($profile, $statement, $result->result->inserted_id);
    }

    /**
     * @depends testPostReaction
     */
    public function testGetReactions() {
        $args = func_get_args()[0];
        $profile = $args[0];
        $result = $this->api->getReactions($this->auth_string, $profile);

        // Result MUST be an object.
        $this->assertInternalType('object', $result);

        // Result MUST have the status attribute and the status MUST be 200.
        $this->assertObjectHasAttribute('status', $result);
        $this->assertEquals(200, $result->status);

        // Result MUST have the result attribute which contains an array of
        // reactions returned by the recommender system.
        $this->assertObjectHasAttribute('result', $result);
        $this->assertInternalType('array', $result->result);

        // An arbitrary element of the array of reactions should contain the
        // id of the corresponding statement and the value of the reaction
        $el = rand(0, count($result->result) - 1);
        $this->assertObjectHasAttribute('statement', $result->result[$el]);
        $this->assertObjectHasAttribute('reaction', $result->result[$el]);

        return $args;
    }

    /**
     * @depends testGetReactions
     */
    public function testGetReaction() {
        $args = func_get_args()[0];
        $profile = $args[0];
        $statement = $args[1];
        $reaction = $args[2];
        $result = $this->api->getReaction($this->auth_string, $reaction);

        // Result MUST be an object.
        $this->assertInternalType('object', $result);

        // Result MUST have the status attribute and the status MUST be 200.
        $this->assertObjectHasAttribute('status', $result);
        $this->assertEquals(200, $result->status);

        // Result MUST have the result attribute which contains the id assigned
        // to the reaction by the recommender system, together with the
        // corresponding profile and statement, and the value of the reaction.
        $this->assertObjectHasAttribute('result', $result);
        $this->assertObjectHasAttribute('profile', $result->result);
        $this->assertObjectHasAttribute('statement', $result->result);
        $this->assertObjectHasAttribute('reaction', $result->result);

        // The returned profile and statement should match the known values.
        $this->assertEquals($profile, $result->result->profile);
        $this->assertEquals($statement, $result->result->statement);

        return $args;
    }

    /**
     * @depends testGetReaction
     */
    public function testPutReaction() {
        $args = func_get_args()[0];
        $reactionId = $args[2];

        // Generate a new reaction value.
        $value = rand(1, 5);

        // Update the value of the reaction and push the updated reaction back
        // to the recommender system.
        $result = $this->api->putReaction($this->auth_string, $reactionId, array(
            'reaction' => $value
        ));

        // Result MUST be an object.
        $this->assertInternalType('object', $result);

        // Result MUST have the status attribute and the status MUST be 200.
        $this->assertObjectHasAttribute('status', $result);
        $this->assertEquals(200, $result->status);

        // Result MUST have the result attribute which contains the id of the
        // updated reaction.
        $this->assertObjectHasAttribute('result', $result);
        $this->assertObjectHasAttribute('updated_id', $result->result);

        // Retrieve the updated reaction from the recommender system and check
        // that the reaction value matches the local value that was generated.
        $reaction = $this->api->getReaction(
            $this->auth_string,
            $reactionId
        )->result;
        $this->assertEquals($value, $reaction->reaction);

        return $args;
    }

    /**
     * @depends testPutReaction
     */
    public function testDeleteReaction() {
        $args = func_get_args()[0];
        $result = $this->api->deleteReaction($this->auth_string, $args[2]);

        // Result MUST be an object.
        $this->assertInternalType('object', $result);

        // Result MUST have the status attribute and the status MUST be 200.
        $this->assertObjectHasAttribute('status', $result);
        $this->assertEquals(200, $result->status);

        // Result MUST have the result attribute which contains the id of the
        // deleted reaction.
        $this->assertObjectHasAttribute('result', $result);
        $this->assertObjectHasAttribute('deleted_id', $result->result);

        // Delete the test profile that was created.
        $this->api->deleteProfile($this->auth_string, $args[0]);
    }

    public function testGetMatches() {
        // Declare storage variables for generated entities.
        // These will be used to delete them after the test.
        $profiles = array();
        $reactions = array();

        // Create two profiles.
        $profileOneData = array('external_id' => uniqid());
        $profileOne = $this->api->postProfile(
            $this->auth_string,
            $profileOneData
        )->result->inserted_id;
        $profiles[] = $profileOne;

        $profileTwoData = array('external_id' => uniqid());
        $profileTwo = $this->api->postProfile(
            $this->auth_string,
            $profileTwoData
        )->result->inserted_id;
        $profiles[] = $profileTwo;

        // Generate random reactions to 25 statements for each profile.
        for ($i = 0; $i < 25; $i++) {
            $inactionOne = $this->api->getInactionStatement(
                $this->auth_string,
                $profileOne
            )->result->_id;
            $randOne = rand(1, 5);
            $reactionOne = $this->api->postReaction(
                $this->auth_string,
                array(
                    'profile' => $profileOne,
                    'statement' => $inactionOne,
                    'reaction' => $randOne
                )
            )->result->inserted_id;
            $reactions[] = $reactionOne;

            $inactionTwo = $this->api->getInactionStatement(
                $this->auth_string,
                $profileTwo
            )->result->_id;
            $randTwo = rand(1, 5);
            $reactionTwo = $this->api->postReaction(
                $this->auth_string,
                array(
                    'profile' => $profileTwo,
                    'statement' => $inactionTwo,
                    'reaction' => $randTwo
                )
            )->result->inserted_id;
            $reactions[] = $reactionTwo;
        }

        // Get the matches for one of the profiles. It should include the second
        // profile.
        $result = $this->api->getMatches($this->auth_string, $profileOne);

        // Result MUST be an object.
        $this->assertInternalType('object', $result);

        // Result MUST have the status attribute and the status MUST be 200.
        $this->assertObjectHasAttribute('status', $result);
        $this->assertEquals(200, $result->status);

        // Result MUST have the result attribute which contains an array of
        // matches returned by the recommender system.
        $this->assertObjectHasAttribute('result', $result);
        $this->assertInternalType('array', $result->result);

        // An arbitrary element of the array of matches should contain the
        // the recommender system id, the local id and the similarity score.
        $el = rand(0, count($result->result) - 1);
        $this->assertObjectHasAttribute('_id', $result->result[$el]);
        $this->assertObjectHasAttribute('external_id', $result->result[$el]);
        $this->assertObjectHasAttribute('similarity', $result->result[$el]);

        // Cleanup.
        foreach ($reactions as $reaction) {
            $this->api->deleteReaction($this->auth_string, $reaction);
        }

        foreach ($profiles as $profile) {
            $this->api->deleteProfile($this->auth_string, $profile);
        }
    }
}