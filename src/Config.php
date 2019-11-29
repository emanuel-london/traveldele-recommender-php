<?php
/**
 * Config.php
 */

namespace Kooyara\RecommenderSystem;

/**
 * Environment variable prefix for reading package config from
 * the system.
 */
define('ENV_PREFIX', 'KOOYARA_SDK_');

/**
 * Class Config
 * @package Kooyara\RecommenderSystem
 */
class Config
{

    /**
     * @var string
     */
    public static $access_token_url = 'oauth/token';

    /**
     * @var string
     */
    public static $grant_type = 'client_credentials';

    /**
     * @return string
     */
    public static function testingProtocol()
    {
        return getenv(ENV_PREFIX . 'TEST_PROTOCOL') ?: 'http';
    }

    /**
     * @return string
     */
    public static function testingHost()
    {
        return getenv(ENV_PREFIX . 'TEST_HOST') ?: '127.0.0.1:5000';
    }

    /**
     * @return string
     */
    public static function testingVersion()
    {
        return getenv(ENV_PREFIX . 'TEST_VERSION') ?: '1.0';
    }

    /**
     * @return string|false
     */
    public static function testingClientId()
    {
        return getenv(ENV_PREFIX . 'TEST_CLIENT_ID');
    }

    /**
     * @return string|false
     */
    public static function testingClientSecret()
    {
        return getenv(ENV_PREFIX . 'TEST_CLIENT_SECRET');
    }


    /**
     * @return string
     */
    public static function developmentProtocol()
    {
        return getenv(ENV_PREFIX . 'DEV_PROTOCOL') ?: 'http';
    }

    /**
     * @return string
     */
    public static function developmentHost()
    {
        return getenv(ENV_PREFIX . 'DEV_HOST') ?: 'ec2-3-133-151-137.us-east-2.compute.amazonaws.com';
    }

    /**
     * @return string
     */
    public static function developmentVersion()
    {
        return getenv(ENV_PREFIX . 'DEV_VERSION') ?: '1.0';
    }

    /**
     * @return string
     */
    public static function productionProtocol()
    {
        return getenv(ENV_PREFIX . 'PROD_PROTOCOL') ?: 'https';
    }

    /**
     * @return string
     */
    public static function productionHost()
    {
        return getenv(ENV_PREFIX . 'PROD_HOST') ?: 'ec2-3-133-151-137.us-east-2.compute.amazonaws.com';
    }

    /**
     * @return string
     */
    public static function productionVersion()
    {
        return getenv(ENV_PREFIX . 'PROD_VERSION') ?: '1.0';
    }
}