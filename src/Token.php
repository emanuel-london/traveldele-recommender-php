<?php
/**
 * Token.php
 */

namespace Kooyara\RecommenderSystem;

/**
 * Class Token
 * @package Kooyara\RecommenderSystem
 */
class Token
{

    /**
     * @var string
     */
    private $access_token;

    /**
     * @var int
     */
    private $expires_in;

    /**
     * @var int
     */
    private $expires_at;

    /**
     * @var string
     */
    private $scope;

    /**
     * @var string
     */
    private $token_type;

    /**
     * Token constructor.
     *
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
     * @return string
     */
    public function getAccessToken()
    {
        return $this->access_token;
    }

    /**
     * @return int
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
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @return string
     */
    public function getTokenType()
    {
        return $this->token_type;
    }
}