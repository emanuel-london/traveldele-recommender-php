<?php
/**
 * CurlClient.php
 */

namespace Kooyara\RecommenderSystem;

/**
 * Class CurlClient
 * @package Kooyara\RecommenderSystem
 */
class CurlClient
{

    /**
     * Make a request against a remote URL.
     *
     * @param string $method The HTTP method to be used.
     * @param string $url The URL to call.
     * @param array|NULL $headers The HTTP headers for the call.
     * @param array|NULL $data Arbitrary request data.
     * @param bool $encode Indicate whether or not JSON encoding should be used.
     *
     * @return mixed Request response.
     */
    public function call(
        string $method,
        string $url,
        array $headers = NULL,
        array $data = NULL,
        bool $encode = FALSE
    ) {
        $curl = curl_init();

        // Use CURLOPT_CUSTOMREQUEST with the method string instead of using
        // CURLOPT_{METHOD}. This is necessary for PUT to work with post fields.
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST,$method);

        // Set request headers if any were passed.
        if ($headers) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }

        // Set request data if any were passed.
        // POST and PUT can optionally use JSON encoding.
        if ($data) {
            if (in_array($method, ['POST', 'PUT'])) {
                if ($encode) {
                    $dataString = json_encode($data);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $dataString);
                } else {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                }
            } else {
                $url = $url . '?' . http_build_query($data);
            }
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

        // Follow redirects.
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);

        $result = curl_exec($curl);

        return $result;
    }
}