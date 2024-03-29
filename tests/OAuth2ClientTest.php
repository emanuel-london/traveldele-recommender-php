<?php
/**
 * OAuth2ClientTest.php
 */

use PHPUnit\Framework\TestCase;
use Kooyara\RecommenderSystem\Config;
use Kooyara\RecommenderSystem\OAuth2Client;

/**
 * Class OAuth2ClientTest
 */
class OAuth2ClientTest extends TestCase {

    /**
     * Confirm that the fetchToken() method of OAuth2Client works as expected.
     *
     * @return void
     */
    public function testFetchToken() {
        $oauth_client = new OAuth2Client(
            Config::testingClientId(),
            Config::testingClientSecret()
        );

        $token_url =  Config::testingProtocol() . '://'
            . Config::testingHost() . '/' . Config::$access_token_url;

        $result = $oauth_client->fetchToken($token_url, Config::$grant_type);

        // Result MUST be an object.
        $this->assertInternalType("object", $result);

        // Result MUST NOT have the `error` attribute.
        $this->assertObjectNotHasAttribute("error", $result);

        // Result MUST have the `access_token` attribute.
        $this->assertObjectHasAttribute("access_token", $result);

        // Result MUST have the `token_type` attribute.
        $this->assertObjectHasAttribute("token_type", $result);

        // Result MUST have the `expires_in` attribute.
        $this->assertObjectHasAttribute("expires_in", $result);

        // Result MUST have the `scope` attribute.
        $this->assertObjectHasAttribute("scope", $result);
    }
}