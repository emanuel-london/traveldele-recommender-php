<?php

namespace Kooyara\RecommenderSystem;

class Token {

    /**
     * @var
     */
    private $access_token;
    /**
     * @var
     */
    private $expires_in;
    /**
     * @var int
     */
    private $expires_at;
    /**
     * @var
     */
    private $scope;
    /**
     * @var
     */
    private $token_type;

    /**
     * Token constructor.
     * @param \stdClass $token_data
     */
    function __construct(\stdClass $token_data)
    {
        $this->access_token = $token_data->access_token;
        $this->expires_in = $token_data->expires_in;
        $this->expires_at = time() + $this->expires_in;
        $this->scope = $token_data->scope;
        $this->token_type = $token_data->token_type;
    }

    /**
     * @return mixed
     */
    public function getAccessToken()
    {
        return $this->access_token;
    }

    /**
     * @return mixed
     */
    public function getExpiresIn()
    {
        return $this->expires_in;
    }

    /**
     * @return int
     */
    public function getExpiresAt(): int
    {
        return $this->expires_at;
    }

    /**
     * @return mixed
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @return mixed
     */
    public function getTokenType()
    {
        return $this->token_type;
    }
}