<?php

use PHPUnit\Framework\TestCase;
use Kooyara\RecommenderSystem\Config;
use Kooyara\RecommenderSystem\OAuth2Client;

class OAuth2ClientTest extends TestCase {
    public function testFetchToken() {
        $oauth_client = new OAuth2Client(
            Config::test_client_id,
            Config::test_client_secret
        );

        $token_url = sprintf(
            "%s://%s/%s",
            Config::test_protocol,
            Config::test_host,
            Config::access_token_url
        );

        $result = $oauth_client->fetchToken($token_url, Config::grant_type);

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